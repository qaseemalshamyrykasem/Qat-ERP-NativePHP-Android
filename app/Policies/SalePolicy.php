<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sale;

class SalePolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'sales';
    }
}
