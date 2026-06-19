<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'entry_date'              => 'required|date',
            'description'             => 'nullable|string',
            'reference_type'          => 'nullable|string|max:50',
            'reference_id'            => 'nullable|integer',
            'lines'                   => 'required|array|min:2',
            'lines.*.account_code'    => 'required|string|exists:chart_of_accounts,code',
            'lines.*.debit'           => 'nullable|numeric|min:0',
            'lines.*.credit'          => 'nullable|numeric|min:0',
            'lines.*.description'     => 'nullable|string',
        ];
    }
}
