<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:100',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'specialization' => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'status'         => 'nullable|boolean',
        ];
    }
}
