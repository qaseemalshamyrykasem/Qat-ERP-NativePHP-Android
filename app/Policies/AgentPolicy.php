<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Agent;

class AgentPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'agents';
    }
}
