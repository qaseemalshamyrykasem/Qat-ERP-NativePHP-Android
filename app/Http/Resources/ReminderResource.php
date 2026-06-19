<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReminderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'reminder_type' => $this->reminder_type,
            'title' => $this->title,
            'amount' => (float) $this->amount,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'notes' => $this->notes,
            'sms_enabled' => (bool) $this->sms_enabled,
            'sms_sent' => (bool) $this->sms_sent,
            'repeat_daily' => (bool) $this->repeat_daily,
            'status' => (bool) $this->status,
        ];
    }
}
