<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreDebtPaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'debt_id'        => 'required|integer|exists:debts,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|in:cash,transfer,other',
            'wallet_type'    => 'nullable|string|max:50',
            'payment_date'   => 'nullable|date',
            'notes'          => 'nullable|string',
        ];
    }
}
