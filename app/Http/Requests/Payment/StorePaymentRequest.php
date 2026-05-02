<?php

namespace App\Http\Requests\Payment;

use App\Support\PaymentMethodCatalog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
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
        $method = (string) $this->input('payment_method');
        $type = PaymentMethodCatalog::type($method);
        $requiresProof = PaymentMethodCatalog::requiresProof($method);
        $isGateway = $type === 'gateway';

        return [
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_method' => ['required', 'string', 'max:50', Rule::in(array_keys(PaymentMethodCatalog::checkoutOptions()))],
            'payer_name' => [in_array($type, ['qris', 'gateway'], true) ? 'nullable' : 'required', 'string', 'max:120'],
            'payer_phone' => [in_array($type, ['e_wallet', 'card'], true) && ! $isGateway ? 'required' : 'nullable', 'string', 'max:30'],
            'payer_bank_name' => [in_array($type, ['bank_transfer', 'virtual_account'], true) ? 'required' : 'nullable', 'string', 'max:120'],
            'payer_bank_account_number' => [in_array($type, ['bank_transfer', 'virtual_account'], true) ? 'required' : 'nullable', 'string', 'max:60'],
            'payment_notes' => [$type === 'card' ? 'required' : 'nullable', 'string', 'max:1000'],
            'proof_file' => [$requiresProof ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }
}
