<?php

namespace App\Policies;

use App\Models\User;
use App\Models\JournalEntry;

class JournalEntryPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'journal_entries';
    }
}
