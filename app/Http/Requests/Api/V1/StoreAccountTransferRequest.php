<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountTransferRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'from_account_id'  => 'required|integer|exists:chart_of_accounts,id',
            'to_account_id'    => 'required|integer|exists:chart_of_accounts,id|different:from_account_id',
            'amount'           => 'required|numeric|min:0.01',
            'from_currency_id' => 'nullable|integer|exists:currencies,id',
            'to_currency_id'   => 'nullable|integer|exists:currencies,id',
            'exchange_rate'    => 'nullable|numeric|min:0',
            'transfer_date'    => 'nullable|date',
            'description'      => 'nullable|string',
        ];
    }
}
