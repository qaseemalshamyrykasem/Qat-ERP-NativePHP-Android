<?php

namespace App\Policies;

use App\Models\User;
use App\Models\User;

class UserPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'users';
    }
}
