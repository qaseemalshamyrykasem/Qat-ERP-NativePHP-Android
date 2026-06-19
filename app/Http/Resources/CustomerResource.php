<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'total_debt' => (float) $this->total_debt,
            'total_paid' => (float) $this->total_paid,
            'remaining' => (float) $this->remaining,
            'last_payment_date' => $this->last_payment_date?->format('Y-m-d'),
            'status' => (bool) $this->status,
            'agent_id' => $this->agent_id,
            'agent_name' => $this->whenLoaded('agent', fn() => $this->agent?->name),
            'notes' => $this->notes,
        ];
    }
}
