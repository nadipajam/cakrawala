<?php

namespace App\Http\Requests\ChangeRequest;

use App\Support\BookingChangeRequestCatalog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessBookingChangeRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in(BookingChangeRequestCatalog::statuses())],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'resolution_amount' => ['nullable', 'numeric', 'min:0'],
            'resolution_details' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
