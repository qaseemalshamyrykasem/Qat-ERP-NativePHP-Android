<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entry_no' => $this->entry_no,
            'entry_date' => $this->entry_date?->format('Y-m-d'),
            'description' => $this->description,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'total_debit' => (float) $this->total_debit,
            'total_credit' => (float) $this->total_credit,
            'status' => (bool) $this->status,
            'lines' => JournalEntryLineResource::collection($this->whenLoaded('lines')),
        ];
    }
}
