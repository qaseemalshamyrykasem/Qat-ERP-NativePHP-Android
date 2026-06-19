<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'setting_key'   => 'required|string|max:100',
            'setting_value' => 'nullable|string',
        ];
    }
}
