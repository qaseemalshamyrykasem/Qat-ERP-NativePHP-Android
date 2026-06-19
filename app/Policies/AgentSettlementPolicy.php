<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AgentSettlement;

class AgentSettlementPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'agent_settlements';
    }
}
