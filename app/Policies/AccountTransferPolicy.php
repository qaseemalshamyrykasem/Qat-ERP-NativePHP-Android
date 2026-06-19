<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AccountTransfer;

class AccountTransferPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'transfers';
    }
}
