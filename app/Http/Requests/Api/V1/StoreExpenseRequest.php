<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'category'       => 'required|string|max:50',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|in:cash,transfer',
            'wallet_type'    => 'nullable|string|max:50',
            'expense_date'   => 'nullable|date',
            'description'    => 'nullable|string',
        ];
    }
}
