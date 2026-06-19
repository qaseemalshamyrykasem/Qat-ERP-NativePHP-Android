<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistributionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'agent_id'           => 'required|integer|exists:agents,id',
            'distribution_date'  => 'nullable|date',
            'notes'              => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'nullable|integer|exists:products,id',
            'items.*.description'      => 'required|string|max:255',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.unit_price'       => 'required|numeric|min:0',
        ];
    }
}
