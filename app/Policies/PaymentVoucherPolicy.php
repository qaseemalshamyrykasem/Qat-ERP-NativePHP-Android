<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PaymentVoucher;

class PaymentVoucherPolicy extends BasePolicy
{
    protected function module(): string
    {
        return 'payment_vouchers';
    }
}
