<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'area' => $this->area,
            'balance' => (float) $this->balance,
            'status' => (bool) $this->status,
            'commission_rate' => $this->commission_rate,
            'notes' => $this->notes,
        ];
    }
}
