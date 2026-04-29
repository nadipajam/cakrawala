<?php

namespace App\Http\Requests\Booking;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'flight_id' => ['required', 'exists:flights,id'],
            'seat_class' => ['required', 'in:economy,business,first'],
            'passengers' => ['required', 'array', 'min:1'],
            'passengers.*.passenger_id' => ['required', 'exists:passengers,id'],
            'passengers.*.seat_id' => ['required', 'exists:seats,id'],
        ];
    }
}
