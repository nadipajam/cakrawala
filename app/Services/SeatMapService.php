<?php

namespace App\Services;

use App\Support\CabinClass;
use Illuminate\Support\Collection;

class SeatMapService
{
    /**
     * @param  Collection<int, mixed>  $seats
     * @param  Collection<int, int>|array<int, int>  $availableSeatIds
     * @return array<string, mixed>
     */
    public function build(Collection $seats, Collection|array $availableSeatIds = []): array
    {
        $availableIds = collect($availableSeatIds)->map(fn ($id) => (int) $id)->values()->all();

        $mappedSeats = $seats
            ->values()
            ->map(fn ($seat, $index) => $this->mapSeat($seat, $index, $availableIds));

        $classes = [];

        foreach (CabinClass::ORDER as $classKey) {
            $classSeats = $mappedSeats
                ->where('class', $classKey)
                ->sortBy(fn (array $seat) => sprintf('%05d-%s', $seat['row'], $seat['column']))
                ->values();

            if ($classSeats->isEmpty()) {
                continue;
            }

            $columns = $classSeats->pluck('column')->unique()->sort()->values();
            $splitPoint = (int) ceil($columns->count() / 2);
            $leftColumns = $columns->slice(0, $splitPoint)->values();
            $rightColumns = $columns->slice($splitPoint)->values();
            $rows = $classSeats
                ->groupBy('row')
                ->sortKeys()
                ->map(fn (Collection $items, $rowNumber) => [
                    'number' => (int) $rowNumber,
                    'seats' => $items->keyBy('column')->all(),
                ])
                ->values()
                ->all();

            $classes[$classKey] = [
                'key' => $classKey,
                'label' => CabinClass::label($classKey),
                'short_label' => CabinClass::shortLabel($classKey),
                'description' => CabinClass::description($classKey),
                'total_count' => $classSeats->count(),
                'available_count' => $classSeats->where('available', true)->count(),
                'left_columns' => $leftColumns->all(),
                'right_columns' => $rightColumns->all(),
                'left_columns_count' => max($leftColumns->count(), 1),
                'right_columns_count' => max($rightColumns->count(), 1),
                'rows' => $rows,
            ];
        }

        return [
            'classes' => $classes,
            'class_keys' => array_keys($classes),
            'available_ids' => $availableIds,
        ];
    }

    /**
     * @param  array<int, int>  $availableIds
     * @return array<string, mixed>
     */
    protected function mapSeat(mixed $seat, int $index, array $availableIds): array
    {
        $seatNumber = strtoupper((string) $seat->seat_number);
        $row = null;
        $column = null;

        if (preg_match('/^([A-Z]+)(\d+)$/', $seatNumber, $matches) === 1) {
            $column = $matches[1];
            $row = (int) $matches[2];
        } elseif (preg_match('/^(\d+)([A-Z]+)$/', $seatNumber, $matches) === 1) {
            $row = (int) $matches[1];
            $column = $matches[2];
        } else {
            $row = $index + 1;
            $column = 'A';
        }

        return [
            'id' => (int) $seat->id,
            'seat_number' => $seatNumber,
            'display_label' => $row.$column,
            'row' => $row,
            'column' => $column,
            'class' => CabinClass::normalize($seat->class),
            'available' => in_array((int) $seat->id, $availableIds, true),
        ];
    }
}
