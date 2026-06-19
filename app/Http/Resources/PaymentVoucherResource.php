<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentVoucherResource extends JsonResource
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
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->whenLoaded('supplier', fn() => $this->supplier?->name),
            'description' => $this->description,
        ];
    }
}
