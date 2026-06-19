<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Expense;

class ExpensePolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'expenses';
    }
}
