<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'products';
    }
}
