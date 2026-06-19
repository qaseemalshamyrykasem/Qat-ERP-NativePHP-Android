<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCurrencyRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'code'          => 'required|string|max:10|unique:currencies,code',
            'name'          => 'required|string|max:50',
            'symbol'        => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'is_default'    => 'nullable|boolean',
            'is_active'     => 'nullable|boolean',
        ];
    }
}
