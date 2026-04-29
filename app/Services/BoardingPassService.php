<?php

namespace App\Services;

use App\Models\BookingDetail;
use App\Support\CabinClass;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BoardingPassService
{
    public function __construct(
        protected PortalNotificationService $notificationService
    ) {
    }

    public function checkIn(BookingDetail $detail): BookingDetail
    {
        $detail->loadMissing([
            'booking.user',
            'booking.flight.airline',
            'booking.flight.departureAirport',
            'booking.flight.arrivalAirport',
            'passenger',
            'seat',
        ]);

        $this->guardCheckInWindow($detail);

        return DB::transaction(function () use ($detail) {
            $detail->fill([
                'boarding_status' => 'checked_in',
                'checked_in_at' => $detail->checked_in_at ?? now(),
                'checkin_reference' => $detail->checkin_reference ?: $this->generateCheckinReference($detail),
                'boarding_group' => $detail->boarding_group ?: $this->boardingGroup($detail),
                'gate_number' => $detail->gate_number ?: $this->gateNumber($detail),
            ])->save();

            $detail = $this->ensureAssetsGenerated($detail);
            $detail->loadMissing('booking.user');

            $this->notificationService->checkInCompleted($detail);

            return $detail;
        });
    }

    /**
     * @return array{can_check_in: bool, reason: string|null}
     */
    public function checkInAvailability(BookingDetail $detail): array
    {
        $detail->loadMissing([
            'booking.flight',
        ]);

        $booking = $detail->booking;
        $flight = $booking?->flight;

        if (! $flight) {
            return [
                'can_check_in' => false,
                'reason' => 'Data flight tidak ditemukan.',
            ];
        }

        if (! in_array($booking->status, ['confirmed', 'completed'], true)) {
            return [
                'can_check_in' => false,
                'reason' => 'Booking harus berstatus confirmed terlebih dahulu.',
            ];
        }

        if ($detail->boarding_status === 'boarded') {
            return [
                'can_check_in' => false,
                'reason' => 'Passenger sudah boarded.',
            ];
        }

        if (in_array($detail->boarding_status, ['checked_in', 'boarded'], true)) {
            return [
                'can_check_in' => false,
                'reason' => 'Passenger sudah check-in.',
            ];
        }

        $opensAt = $flight->departure_time?->copy()->subHours(48);
        $closesAt = $flight->departure_time?->copy()->subMinutes(45);

        if (! $opensAt || ! $closesAt) {
            return [
                'can_check_in' => false,
                'reason' => 'Jadwal flight tidak valid untuk check-in.',
            ];
        }

        $now = now();

        if ($now->lt($opensAt)) {
            return [
                'can_check_in' => false,
                'reason' => 'Check-in baru dibuka 48 jam sebelum keberangkatan.',
            ];
        }

        if ($now->gte($closesAt)) {
            return [
                'can_check_in' => false,
                'reason' => 'Check-in sudah ditutup 45 menit sebelum keberangkatan.',
            ];
        }

        return [
            'can_check_in' => true,
            'reason' => null,
        ];
    }

    public function updateBoardingStatus(BookingDetail $detail, string $status): BookingDetail
    {
        if (! in_array($status, ['not_checked_in', 'checked_in', 'boarded'], true)) {
            throw ValidationException::withMessages([
                'status' => ['Status boarding tidak valid.'],
            ]);
        }

        return DB::transaction(function () use ($detail, $status) {
            $detail->loadMissing([
                'booking.user',
                'booking.flight.airline',
                'booking.flight.departureAirport',
                'booking.flight.arrivalAirport',
                'passenger',
                'seat',
            ]);

            if ($status === 'not_checked_in') {
                $detail->update([
                    'boarding_status' => 'not_checked_in',
                    'checked_in_at' => null,
                    'boarded_at' => null,
                    'checkin_reference' => null,
                    'boarding_group' => null,
                    'gate_number' => null,
                    'boarding_pass_pdf_path' => null,
                    'boarding_pass_qr_path' => null,
                ]);

                return $detail->fresh();
            }

            if ($status === 'checked_in' && ! $detail->checked_in_at) {
                $detail->checked_in_at = now();
            }

            if ($status === 'boarded') {
                $detail->checked_in_at = $detail->checked_in_at ?? now();
                $detail->boarded_at = now();
            }

            $detail->boarding_status = $status;
            $detail->checkin_reference = $detail->checkin_reference ?: $this->generateCheckinReference($detail);
            $detail->boarding_group = $detail->boarding_group ?: $this->boardingGroup($detail);
            $detail->gate_number = $detail->gate_number ?: $this->gateNumber($detail);
            $detail->save();

            if (in_array($status, ['checked_in', 'boarded'], true)) {
                $detail = $this->ensureAssetsGenerated($detail);
            }

            return $detail->fresh();
        });
    }

    public function ensureAssetsGenerated(BookingDetail $detail): BookingDetail
    {
        $detail->loadMissing([
            'booking.flight.airline',
            'booking.flight.departureAirport',
            'booking.flight.arrivalAirport',
            'passenger',
            'seat',
        ]);

        $qrExists = $detail->boarding_pass_qr_path && Storage::disk('public')->exists($detail->boarding_pass_qr_path);
        $pdfExists = $detail->boarding_pass_pdf_path && Storage::disk('public')->exists($detail->boarding_pass_pdf_path);

        if ($qrExists && $pdfExists) {
            return $detail;
        }

        $bookingCode = (string) ($detail->booking?->booking_code ?: 'booking');
        $directory = 'boarding-passes/'.Str::slug(Str::lower($bookingCode), '-').'/'.$detail->id;
        $qrPath = $directory.'/boarding-pass.svg';
        $pdfPath = $directory.'/boarding-pass.pdf';
        $qrMarkup = $this->buildQrMarkup($this->qrPayload($detail));

        Storage::disk('public')->put($qrPath, $qrMarkup);
        Storage::disk('public')->put($pdfPath, $this->buildPdf($detail, $qrMarkup));

        $detail->forceFill([
            'boarding_pass_qr_path' => $qrPath,
            'boarding_pass_pdf_path' => $pdfPath,
        ])->save();

        return $detail->fresh();
    }

    protected function guardCheckInWindow(BookingDetail $detail): void
    {
        $booking = $detail->booking;
        $flight = $booking?->flight;

        if (! $flight) {
            throw ValidationException::withMessages([
                'flight' => ['Data flight tidak ditemukan untuk proses check-in.'],
            ]);
        }

        if (! in_array($booking->status, ['confirmed', 'completed'], true)) {
            throw ValidationException::withMessages([
                'booking' => ['Check-in hanya bisa dilakukan untuk booking confirmed/completed.'],
            ]);
        }

        if ($detail->boarding_status === 'boarded') {
            throw ValidationException::withMessages([
                'boarding' => ['Passenger sudah berstatus boarded.'],
            ]);
        }

        $now = now();
        $opensAt = $flight->departure_time?->copy()->subHours(48);
        $closesAt = $flight->departure_time?->copy()->subMinutes(45);

        if (! $opensAt || ! $closesAt) {
            throw ValidationException::withMessages([
                'flight' => ['Jadwal flight tidak valid untuk check-in.'],
            ]);
        }

        if ($now->lt($opensAt)) {
            throw ValidationException::withMessages([
                'checkin' => ['Check-in belum dibuka. Check-in tersedia 48 jam sebelum keberangkatan.'],
            ]);
        }

        if ($now->gte($closesAt)) {
            throw ValidationException::withMessages([
                'checkin' => ['Check-in sudah ditutup (45 menit sebelum jadwal keberangkatan).'],
            ]);
        }
    }

    protected function buildQrMarkup(string $payload): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => false,
            'imageTransparent' => false,
            'scale' => 6,
        ]);

        return (new QRCode($options))->render($payload);
    }

    protected function buildPdf(BookingDetail $detail, string $qrMarkup): string
    {
        $options = new Options([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
        ]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.boarding-pass', [
            'detail' => $detail,
            'booking' => $detail->booking,
            'flight' => $detail->booking->flight,
            'cabinLabel' => CabinClass::shortLabel($detail->seat?->class),
            'qrMarkup' => $qrMarkup,
        ])->render());
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    protected function qrPayload(BookingDetail $detail): string
    {
        $booking = $detail->booking;
        $flight = $booking?->flight;

        return implode("\n", array_filter([
            'Cakrawala Boarding Pass',
            'Check-in Ref: '.$detail->checkin_reference,
            'Ticket: '.$detail->ticket_number,
            'Booking: '.$booking?->booking_code,
            'Passenger: '.$detail->passenger?->full_name,
            'Seat: '.($detail->seat?->seat_number ?: '-').' / '.CabinClass::shortLabel($detail->seat?->class),
            'Group: '.($detail->boarding_group ?: '-'),
            'Gate: '.($detail->gate_number ?: '-'),
            'Flight: '.$flight?->flight_number,
            'Route: '.($flight?->departureAirport?->code ?: '-').' -> '.($flight?->arrivalAirport?->code ?: '-'),
            'Departure: '.optional($flight?->departure_time)->format('d M Y H:i'),
        ]));
    }

    protected function generateCheckinReference(BookingDetail $detail): string
    {
        $bookingCode = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) ($detail->booking?->booking_code ?: 'BK')));

        return 'CI-'.$bookingCode.'-'.str_pad((string) $detail->id, 4, '0', STR_PAD_LEFT);
    }

    protected function boardingGroup(BookingDetail $detail): string
    {
        $class = CabinClass::normalize($detail->seat?->class);
        $row = (int) preg_replace('/\D+/', '', (string) $detail->seat?->seat_number);

        if ($class === 'first') {
            return 'A1';
        }

        if ($class === 'business') {
            return $row <= 8 ? 'B1' : 'B2';
        }

        return $row <= 20 ? 'C1' : 'C2';
    }

    protected function gateNumber(BookingDetail $detail): string
    {
        $seed = ($detail->booking?->flight_id ?? 0) + $detail->id;

        return 'G'.str_pad((string) (($seed % 28) + 1), 2, '0', STR_PAD_LEFT);
    }
}
