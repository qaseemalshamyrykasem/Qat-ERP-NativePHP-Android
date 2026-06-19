<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_id' => $this->sale_id,
            'product_id' => $this->product_id,
            'product_name' => $this->whenLoaded('product', fn() => $this->product?->name),
            'description' => $this->description,
            'quality' => $this->quality,
            'quantity' => (float) $this->quantity,
            'unit' => $this->unit,
            'unit_price' => (float) $this->unit_price,
            'total_price' => (float) $this->total_price,
            'cogs_amount' => (float) $this->cogs_amount,
            'weighted_average_cost_at_sale' => (float) $this->weighted_average_cost_at_sale,
        ];
    }
}
