<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptVoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'voucher_no' => $this->voucher_no,
            'voucher_date' => $this->voucher_date?->format('Y-m-d'),
            'account_id' => $this->account_id,
            'account_name' => $this->whenLoaded('account', fn() => $this->account?->name),
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'wallet_type' => $this->wallet_type,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->whenLoaded('customer', fn() => $this->customer?->name),
            'description' => $this->description,
        ];
    }
}
