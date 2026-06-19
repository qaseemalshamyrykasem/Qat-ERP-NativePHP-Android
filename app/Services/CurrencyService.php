<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public function list(): array
    {
        return Cache::remember('currencies.list', 300, fn () => Currency::where('is_active', true)->get()->toArray());
    }

    public function convert(float $amount, string $fromCode, string $toCode): float
    {
        if ($fromCode === $toCode) return $amount;
        $from = Currency::where('code', $fromCode)->first();
        $to   = Currency::where('code', $toCode)->first();
        if (! $from || ! $to) return $amount;
        // Convert via base rate
        return $amount / (float) $from->exchange_rate * (float) $to->exchange_rate;
    }

    public function setDefault(int $currencyId): void
    {
        Currency::query()->update(['is_default' => false]);
        Currency::where('id', $currencyId)->update(['is_default' => true]);
        Cache::forget('currencies.list');
    }
}
