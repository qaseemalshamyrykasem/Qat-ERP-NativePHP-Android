<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Debt;

class DebtPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'debts';
    }
}
