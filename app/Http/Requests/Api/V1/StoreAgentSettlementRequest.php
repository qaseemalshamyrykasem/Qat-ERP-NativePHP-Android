<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgentSettlementRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'agent_id'           => 'required|integer|exists:agents,id',
            'settlement_date'    => 'required|date',
            'commission_amount'  => 'nullable|numeric|min:0',
            'shaqa_amount'       => 'nullable|numeric|min:0',
            'discounts'          => 'nullable|numeric|min:0',
            'shortages'          => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ];
    }
}
