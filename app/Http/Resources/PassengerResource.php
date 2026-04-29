<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PassengerResource extends JsonResource
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
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'birth_date' => optional($this->birth_date)->toDateString(),
            'passport_number' => $this->passport_number,
            'identity_number' => $this->identity_number,
            'nationality' => $this->nationality,
        ];
    }
}
