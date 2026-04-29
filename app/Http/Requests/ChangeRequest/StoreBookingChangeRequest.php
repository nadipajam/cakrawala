<?php

namespace App\Http\Requests\ChangeRequest;

use App\Support\BookingChangeRequestCatalog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'request_type' => ['required', 'string', Rule::in(array_keys(BookingChangeRequestCatalog::types()))],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'preferred_flight_id' => ['nullable', 'integer', 'exists:flights,id'],
        ];
    }
}
