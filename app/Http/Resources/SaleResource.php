<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'agent_id' => $this->agent_id,
            'agent_name' => $this->whenLoaded('agent', fn() => $this->agent?->name),
            'customer_id' => $this->customer_id,
            'customer_name' => $this->whenLoaded('customer', fn() => $this->customer?->name),
            'sale_date' => $this->sale_date?->format('Y-m-d'),
            'total_amount' => (float) $this->total_amount,
            'discount_amount' => (float) $this->discount_amount,
            'final_amount' => (float) $this->final_amount,
            'paid_amount' => (float) $this->paid_amount,
            'payment_method' => $this->payment_method,
            'wallet_type' => $this->wallet_type,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'creator_name' => $this->whenLoaded('creator', fn() => $this->creator?->full_name),
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
