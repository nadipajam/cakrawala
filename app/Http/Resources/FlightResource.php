<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlightResource extends JsonResource
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
            'flight_number' => $this->flight_number,
            'departure_time' => optional($this->departure_time)->toDateTimeString(),
            'arrival_time' => optional($this->arrival_time)->toDateTimeString(),
            'price' => (float) $this->price,
            'status' => $this->status,
            'airline' => $this->whenLoaded('airline', fn () => [
                'id' => $this->airline->id,
                'name' => $this->airline->name,
                'code' => $this->airline->code,
            ]),
            'airplane' => $this->whenLoaded('airplane', fn () => [
                'id' => $this->airplane->id,
                'model' => $this->airplane->model,
                'registration_number' => $this->airplane->registration_number,
                'capacity' => $this->airplane->capacity,
            ]),
            'departure_airport' => $this->whenLoaded('departureAirport', fn () => [
                'id' => $this->departureAirport->id,
                'code' => $this->departureAirport->code,
                'name' => $this->departureAirport->name,
                'city' => $this->departureAirport->city,
            ]),
            'arrival_airport' => $this->whenLoaded('arrivalAirport', fn () => [
                'id' => $this->arrivalAirport->id,
                'code' => $this->arrivalAirport->code,
                'name' => $this->arrivalAirport->name,
                'city' => $this->arrivalAirport->city,
            ]),
        ];
    }
}
