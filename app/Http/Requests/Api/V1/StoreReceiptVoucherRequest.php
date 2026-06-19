<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceiptVoucherRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'voucher_date'   => 'required|date',
            'account_id'     => 'required|integer|exists:chart_of_accounts,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|in:cash,transfer,check',
            'wallet_type'    => 'nullable|string|max:50',
            'customer_id'    => 'nullable|integer|exists:customers,id',
            'description'    => 'nullable|string',
        ];
    }
}
