<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'payment_method' => $this->payment_method,
            'amount' => (float) $this->amount,
            'payment_status' => $this->payment_status,
            'transaction_code' => $this->transaction_code,
            'paid_at' => optional($this->paid_at)->toDateTimeString(),
            'proof_file' => $this->proof_file,
            'booking' => $this->whenLoaded('booking', fn () => [
                'id' => $this->booking->id,
                'booking_code' => $this->booking->booking_code,
                'status' => $this->booking->status,
                'total_price' => (float) $this->booking->total_price,
            ]),
        ];
    }
}
