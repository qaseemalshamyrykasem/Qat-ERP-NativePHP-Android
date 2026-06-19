<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CloseDailySessionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'actual_balance' => 'required|numeric',
            'notes'          => 'nullable|string',
        ];
    }
}
