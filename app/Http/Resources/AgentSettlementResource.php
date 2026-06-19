<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentSettlementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'settlement_no' => $this->settlement_no,
            'agent_id' => $this->agent_id,
            'agent_name' => $this->whenLoaded('agent', fn() => $this->agent?->name),
            'settlement_date' => $this->settlement_date?->format('Y-m-d'),
            'total_sales' => (float) $this->total_sales,
            'cash_sales' => (float) $this->cash_sales,
            'credit_sales' => (float) $this->credit_sales,
            'transfer_sales' => (float) $this->transfer_sales,
            'debt_payments' => (float) $this->debt_payments,
            'expenses' => $this->expenses,
            'commission_amount' => (float) $this->commission_amount,
            'shaqa_amount' => (float) $this->shaqa_amount,
            'discounts' => (float) $this->discounts,
            'shortages' => (float) $this->shortages,
            'net_due' => (float) $this->net_due,
            'notes' => $this->notes,
        ];
    }
}
