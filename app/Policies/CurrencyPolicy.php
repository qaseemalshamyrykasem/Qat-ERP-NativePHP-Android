<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Currency;

class CurrencyPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'currencies';
    }
}
