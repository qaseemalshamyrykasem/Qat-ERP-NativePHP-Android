<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'username'   => $this->username,
            'full_name'  => $this->full_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'role'       => $this->role,
            'agent_id'   => $this->agent_id,
            'agent_name' => $this->whenLoaded('agent', fn() => $this->agent?->name),
            'status'     => (bool) $this->status,
            'last_login' => $this->last_login?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
