<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Distribution;

class DistributionPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'distributions';
    }
}
