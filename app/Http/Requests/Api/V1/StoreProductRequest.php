<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'type'        => 'nullable|string|max:50',
            'buy_price'   => 'nullable|numeric|min:0',
            'sell_price'  => 'nullable|numeric|min:0',
            'quantity'    => 'nullable|numeric|min:0',
            'unit'        => 'nullable|string|max:20',
            'min_quantity'=> 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'status'      => 'nullable|boolean',
        ];
    }
}
