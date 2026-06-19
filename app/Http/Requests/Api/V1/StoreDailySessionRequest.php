<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailySessionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'opening_balance' => 'required|numeric|min:0',
            'session_date'    => 'nullable|date',
        ];
    }
}
