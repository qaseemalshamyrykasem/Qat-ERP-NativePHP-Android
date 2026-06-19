<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'code'             => 'required|string|max:20|unique:chart_of_accounts,code',
            'name'             => 'required|string|max:100',
            'name_en'          => 'nullable|string|max:100',
            'parent_id'        => 'nullable|integer|exists:chart_of_accounts,id',
            'account_type'     => 'required|in:asset,liability,equity,revenue,expense',
            'level'            => 'nullable|integer|min:1',
            'balance_direction'=> 'nullable|in:debit,credit',
            'is_active'        => 'nullable|boolean',
        ];
    }
}
