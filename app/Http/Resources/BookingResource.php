<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'total_passengers' => $this->total_passengers,
            'total_price' => (float) $this->total_price,
            'status' => $this->status,
            'expired_at' => optional($this->expired_at)->toDateTimeString(),
            'flight' => $this->whenLoaded('flight', fn () => [
                'id' => $this->flight->id,
                'flight_number' => $this->flight->flight_number,
                'departure_time' => optional($this->flight->departure_time)->toDateTimeString(),
                'arrival_time' => optional($this->flight->arrival_time)->toDateTimeString(),
            ]),
            'details' => $this->whenLoaded('details', fn () => $this->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'price' => (float) $detail->price,
                    'ticket_number' => $detail->ticket_number,
                    'boarding_status' => $detail->boarding_status,
                    'checked_in_at' => optional($detail->checked_in_at)->toDateTimeString(),
                    'boarded_at' => optional($detail->boarded_at)->toDateTimeString(),
                    'checkin_reference' => $detail->checkin_reference,
                    'boarding_group' => $detail->boarding_group,
                    'gate_number' => $detail->gate_number,
                    'boarding_pass_pdf_path' => $detail->boarding_pass_pdf_path,
                    'boarding_pass_qr_path' => $detail->boarding_pass_qr_path,
                    'passenger' => $detail->passenger ? [
                        'id' => $detail->passenger->id,
                        'full_name' => $detail->passenger->full_name,
                    ] : null,
                    'seat' => $detail->seat ? [
                        'id' => $detail->seat->id,
                        'seat_number' => $detail->seat->seat_number,
                        'class' => $detail->seat->class,
                    ] : null,
                    'ticket' => $detail->ticket ? [
                        'id' => $detail->ticket->id,
                        'qr_code_path' => $detail->ticket->qr_code_path,
                        'pdf_path' => $detail->ticket->pdf_path,
                        'issued_at' => optional($detail->ticket->issued_at)->toDateTimeString(),
                    ] : null,
                    'addons' => $detail->relationLoaded('addons')
                        ? $detail->addons->map(fn ($addon) => [
                            'id' => $addon->id,
                            'addon_code' => $addon->addon_code,
                            'addon_name' => $addon->addon_name,
                            'addon_type' => $addon->addon_type,
                            'quantity' => (int) $addon->quantity,
                            'unit_price' => (float) $addon->unit_price,
                            'total_price' => (float) $addon->total_price,
                            'status' => $addon->status,
                        ])->values()
                        : [],
                ];
            })->values()),
            'addons' => $this->whenLoaded('addons', fn () => $this->addons->map(fn ($addon) => [
                'id' => $addon->id,
                'booking_detail_id' => $addon->booking_detail_id,
                'addon_code' => $addon->addon_code,
                'addon_name' => $addon->addon_name,
                'addon_type' => $addon->addon_type,
                'quantity' => (int) $addon->quantity,
                'unit_price' => (float) $addon->unit_price,
                'total_price' => (float) $addon->total_price,
                'status' => $addon->status,
            ])->values()),
            'change_requests' => $this->whenLoaded('changeRequests', fn () => $this->changeRequests->map(fn ($item) => [
                'id' => $item->id,
                'request_type' => $item->request_type,
                'reason' => $item->reason,
                'preferred_flight_id' => $item->preferred_flight_id,
                'status' => $item->status,
                'admin_notes' => $item->admin_notes,
                'resolution_amount' => (float) ($item->resolution_amount ?? 0),
                'resolution_details' => $item->resolution_details,
                'processed_at' => optional($item->processed_at)->toDateTimeString(),
            ])->values()),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
