<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'specialization' => $this->specialization,
            'notes' => $this->notes,
            'balance' => (float) $this->balance,
            'total_purchases' => (float) $this->total_purchases,
            'total_paid' => (float) $this->total_paid,
            'total_remaining' => (float) $this->total_remaining,
            'status' => (bool) $this->status,
        ];
    }
}
