<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Supplier;

class SupplierPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'suppliers';
    }
}
