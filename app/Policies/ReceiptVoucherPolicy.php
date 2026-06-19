<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ReceiptVoucher;

class ReceiptVoucherPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'receipt_vouchers';
    }
}
