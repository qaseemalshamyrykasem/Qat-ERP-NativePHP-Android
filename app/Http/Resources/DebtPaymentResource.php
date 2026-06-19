<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'debt_id'        => $this->debt_id,
            'amount'         => (float) $this->amount,
            'payment_date'   => $this->payment_date?->format('Y-m-d'),
            'payment_method' => $this->payment_method,
            'wallet_type'    => $this->wallet_type,
            'notes'          => $this->notes,
            'created_by'     => $this->created_by,
        ];
    }
}
