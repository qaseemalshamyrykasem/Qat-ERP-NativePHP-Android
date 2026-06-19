<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:100',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'agent_id' => 'nullable|integer|exists:agents,id',
            'status'   => 'nullable|in:active,inactive,blocked',
            'notes'    => 'nullable|string',
        ];
    }
}
