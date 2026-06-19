<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistributionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'distribution_no' => $this->distribution_no,
            'agent_id' => $this->agent_id,
            'agent_name' => $this->whenLoaded('agent', fn() => $this->agent?->name),
            'distribution_date' => $this->distribution_date?->format('Y-m-d'),
            'total_amount' => (float) $this->total_amount,
            'notes' => $this->notes,
            'items' => DistributionItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
