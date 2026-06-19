<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transfer_no' => $this->transfer_no,
            'from_account_id' => $this->from_account_id,
            'from_account_name' => $this->whenLoaded('from_account', fn() => $this->from_account?->name),
            'to_account_id' => $this->to_account_id,
            'to_account_name' => $this->whenLoaded('to_account', fn() => $this->to_account?->name),
            'from_currency_id' => $this->from_currency_id,
            'to_currency_id' => $this->to_currency_id,
            'amount' => (float) $this->amount,
            'converted_amount' => (float) $this->converted_amount,
            'exchange_rate' => (float) $this->exchange_rate,
            'transfer_date' => $this->transfer_date?->format('Y-m-d'),
            'description' => $this->description,
            'status' => (bool) $this->status,
        ];
    }
}
