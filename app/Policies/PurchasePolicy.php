<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Purchase;

class PurchasePolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'purchases';
    }
}
