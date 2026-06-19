<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailySessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_date' => $this->session_date?->format('Y-m-d'),
            'opening_balance' => (float) $this->opening_balance,
            'total_sales' => (float) $this->total_sales,
            'total_cash' => (float) $this->total_cash,
            'total_credit' => (float) $this->total_credit,
            'total_transfers' => (float) $this->total_transfers,
            'total_expenses' => (float) $this->total_expenses,
            'total_debt_payments' => (float) $this->total_debt_payments,
            'net_profit' => (float) $this->net_profit,
            'expected_balance' => (float) $this->expected_balance,
            'actual_balance' => (float) $this->actual_balance,
            'difference' => (float) $this->difference,
            'status' => (bool) $this->status,
            'notes' => $this->notes,
            'opened_at' => $this->opened_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
        ];
    }
}
