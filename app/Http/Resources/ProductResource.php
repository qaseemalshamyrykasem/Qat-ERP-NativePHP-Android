<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'buy_price' => (float) $this->buy_price,
            'weighted_average_cost' => (float) $this->weighted_average_cost,
            'sell_price' => (float) $this->sell_price,
            'quantity' => (float) $this->quantity,
            'unit' => $this->unit,
            'min_quantity' => $this->min_quantity,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->whenLoaded('supplier', fn() => $this->supplier?->name),
            'status' => (bool) $this->status,
        ];
    }
}
