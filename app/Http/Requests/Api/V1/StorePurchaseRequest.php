<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'supplier_id'    => 'required|integer|exists:suppliers,id',
            'purchase_date'  => 'nullable|date',
            'payment_method' => 'required|in:cash,credit,transfer',
            'wallet_type'    => 'nullable|string|max:50',
            'paid_amount'    => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'nullable|integer|exists:products,id',
            'items.*.description'      => 'required|string|max:255',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.unit_price'       => 'required|numeric|min:0',
        ];
    }
}
