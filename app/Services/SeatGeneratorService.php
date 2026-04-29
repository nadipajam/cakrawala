<?php

namespace App\Services;

use App\Models\Airplane;
use App\Models\BookingDetail;
use App\Models\Seat;
use App\Support\CabinClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SeatGeneratorService
{
    /**
     * @return Collection<int, Seat>
     */
    public function generateForAirplane(
        Airplane $airplane,
        int $firstRows = 0,
        int $businessRows = 0,
        bool $reset = false,
    ): Collection
    {
        return DB::transaction(function () use ($airplane, $firstRows, $businessRows, $reset) {
            if ($firstRows < 0 || $businessRows < 0) {
                throw ValidationException::withMessages([
                    'cabin' => ['Jumlah row kabin tidak boleh negatif.'],
                ]);
            }

            $existingSeatCount = $airplane->seats()->count();

            if ($existingSeatCount > 0 && ! $reset) {
                throw ValidationException::withMessages([
                    'reset' => ['Seat sudah ada. Aktifkan reset untuk generate ulang layout kabin.'],
                ]);
            }

            if ($reset && $this->airplaneHasBookings($airplane)) {
                throw ValidationException::withMessages([
                    'reset' => ['Pesawat ini sudah dipakai booking. Reset seat diblokir agar histori booking tidak hilang.'],
                ]);
            }

            $reservedSeats = ($firstRows * count(CabinClass::layout('first')))
                + ($businessRows * count(CabinClass::layout('business')));

            if ($reservedSeats > $airplane->capacity) {
                throw ValidationException::withMessages([
                    'cabin' => ['Total kursi first dan business melebihi kapasitas pesawat.'],
                ]);
            }

            if ($reset) {
                $airplane->seats()->delete();
            }

            if ($airplane->capacity <= 0) {
                return $airplane->seats()->orderByRaw('LENGTH(seat_number)')->orderBy('seat_number')->get();
            }

            $generatedSeats = $this->buildSeatPlan($airplane, $firstRows, $businessRows);

            if ($generatedSeats !== []) {
                Seat::query()->insert($generatedSeats);
            }

            return $airplane->seats()->orderByRaw('LENGTH(seat_number)')->orderBy('seat_number')->get();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildSeatPlan(Airplane $airplane, int $firstRows, int $businessRows): array
    {
        $rows = [];
        $rowNumber = 1;
        $remainingSeats = $airplane->capacity;

        for ($index = 0; $index < $firstRows; $index++) {
            $rows[] = ['row' => $rowNumber++, 'class' => 'first', 'columns' => CabinClass::layout('first')];
            $remainingSeats -= count(CabinClass::layout('first'));
        }

        for ($index = 0; $index < $businessRows; $index++) {
            $rows[] = ['row' => $rowNumber++, 'class' => 'business', 'columns' => CabinClass::layout('business')];
            $remainingSeats -= count(CabinClass::layout('business'));
        }

        while ($remainingSeats > 0) {
            $columns = CabinClass::layout('economy');

            if ($remainingSeats < count($columns)) {
                $columns = array_slice($columns, 0, $remainingSeats);
            }

            $rows[] = ['row' => $rowNumber++, 'class' => 'economy', 'columns' => $columns];
            $remainingSeats -= count($columns);
        }

        $timestamp = now();

        return collect($rows)
            ->flatMap(function (array $row) use ($airplane, $timestamp) {
                return collect($row['columns'])->map(fn (string $column) => [
                    'airplane_id' => $airplane->id,
                    'seat_number' => $row['row'].$column,
                    'class' => $row['class'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            })
            ->values()
            ->all();
    }

    protected function airplaneHasBookings(Airplane $airplane): bool
    {
        return BookingDetail::query()
            ->whereHas('seat', fn ($query) => $query->where('airplane_id', $airplane->id))
            ->exists();
    }
}
