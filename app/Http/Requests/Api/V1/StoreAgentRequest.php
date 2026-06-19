<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:100',
            'phone'            => 'nullable|string|max:20',
            'area'             => 'nullable|string|max:100',
            'commission_rate'  => 'nullable|numeric|min:0|max:100',
            'status'           => 'nullable|in:active,inactive,suspended',
            'notes'            => 'nullable|string',
        ];
    }
}
