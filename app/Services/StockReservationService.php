<?php

namespace App\Services;

use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * StockReservationService — reserve stock for 5 minutes during POS checkout.
 */
class StockReservationService
{
    public function reserve(int $productId, float $quantity, ?int $agentId = null): ?StockReservation
    {
        $sessionId = session()->getId() ?? Str::uuid()->toString();
        $expiresAt = now()->addMinutes(config('qat.stock_reservation_minutes', 5));

        return DB::transaction(function () use ($productId, $quantity, $agentId, $sessionId, $expiresAt) {
            return StockReservation::create([
                'product_id' => $productId,
                'agent_id'   => $agentId,
                'quantity'   => $quantity,
                'session_id' => $sessionId,
                'status'     => 'active',
                'expires_at' => $expiresAt,
            ]);
        });
    }

    public function complete(int $reservationId): void
    {
        StockReservation::where('id', $reservationId)->update(['status' => 'completed']);
    }

    public function cancel(int $reservationId): void
    {
        StockReservation::where('id', $reservationId)->update(['status' => 'cancelled']);
    }

    public function purgeExpired(): int
    {
        return StockReservation::where('status', 'active')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Total reserved quantity for a product (active reservations only).
     */
    public function reservedQuantity(int $productId, ?int $agentId = null): float
    {
        $q = StockReservation::where('product_id', $productId)->active();
        if ($agentId) $q->where('agent_id', $agentId);
        return (float) $q->sum('quantity');
    }
}
