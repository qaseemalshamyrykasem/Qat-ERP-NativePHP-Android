<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DailySession;

class DailySessionPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'daily_session';
    }
}
