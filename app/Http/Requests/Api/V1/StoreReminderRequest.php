<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreReminderRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'entity_type'   => 'nullable|in:customer,supplier,agent,debt,general',
            'entity_id'     => 'nullable|integer',
            'reminder_type' => 'nullable|in:payment,receipt,event,debt_due',
            'title'         => 'required|string|max:200',
            'amount'        => 'nullable|numeric|min:0',
            'due_date'      => 'required|date',
            'notes'         => 'nullable|string',
            'sms_enabled'   => 'nullable|boolean',
            'repeat_daily'  => 'nullable|boolean',
        ];
    }
}
