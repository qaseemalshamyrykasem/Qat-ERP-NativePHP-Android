<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Customer;

class CustomerPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'customers';
    }
}
