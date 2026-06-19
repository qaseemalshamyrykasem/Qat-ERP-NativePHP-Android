<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reminder;

class ReminderPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'reminders';
    }
}
