<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'wallet_type' => $this->wallet_type,
            'expense_date' => $this->expense_date?->format('Y-m-d'),
            'description' => $this->description,
            'created_by' => $this->created_by,
            'creator_name' => $this->whenLoaded('creator', fn() => $this->creator?->full_name),
        ];
    }
}
