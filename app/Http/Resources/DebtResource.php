<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->whenLoaded('customer', fn() => $this->customer?->name),
            'sale_id' => $this->sale_id,
            'agent_id' => $this->agent_id,
            'agent_name' => $this->whenLoaded('agent', fn() => $this->agent?->name),
            'total_amount' => (float) $this->total_amount,
            'paid_amount' => (float) $this->paid_amount,
            'remaining_amount' => (float) $this->remaining_amount,
            'status' => (bool) $this->status,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'notes' => $this->notes,
            'payments' => DebtPaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
