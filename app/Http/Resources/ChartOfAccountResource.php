<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'name_en' => $this->whenLoaded('name_en', fn() => $this->name_en?->name),
            'parent_id' => $this->parent_id,
            'account_type' => $this->account_type,
            'level' => $this->level,
            'is_active' => (bool) $this->is_active,
            'balance_direction' => $this->balance_direction,
            'current_balance' => (float) $this->current_balance,
        ];
    }
}
