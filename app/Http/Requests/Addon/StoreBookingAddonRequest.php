<?php

namespace App\Http\Requests\Addon;

use App\Support\AddonCatalog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingAddonRequest extends FormRequest
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
            'addon_code' => ['required', 'string', Rule::in(array_keys(AddonCatalog::all()))],
            'booking_detail_id' => ['nullable', 'integer', 'exists:booking_details,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
            'notes' => ['nullable', 'string', 'max:300'],
        ];
    }
}
