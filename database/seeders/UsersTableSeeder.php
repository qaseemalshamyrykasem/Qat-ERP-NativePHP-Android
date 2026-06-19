<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Default password for all seeded users: "password"
        $password = Hash::make('password');

        User::updateOrCreate(['username' => 'admin'], [
            'password'   => $password,
            'full_name'  => 'أحمد محمد السعيد',
            'email'      => 'admin@qaterp.com',
            'phone'      => '777123456',
            'role'       => 'admin',
            'status'     => true,
        ]);

        User::updateOrCreate(['username' => 'manager1'], [
            'password'   => $password,
            'full_name'  => 'خالد عبدالله النعمي',
            'email'      => 'manager@qaterp.com',
            'phone'      => '777234567',
            'role'       => 'manager',
            'status'     => true,
        ]);

        User::updateOrCreate(['username' => 'accountant1'], [
            'password'   => $password,
            'full_name'  => 'فاطمة علي الحمزي',
            'email'      => 'accountant@qaterp.com',
            'phone'      => '777345678',
            'role'       => 'accountant',
            'status'     => true,
        ]);

        User::updateOrCreate(['username' => 'agent1'], [
            'password'   => $password,
            'full_name'  => 'محمد سالم البكري',
            'email'      => 'agent1@qaterp.com',
            'phone'      => '777456789',
            'role'       => 'agent',
            'agent_id'   => 1,
            'status'     => true,
        ]);

        User::updateOrCreate(['username' => 'agent2'], [
            'password'   => $password,
            'full_name'  => 'علي حسين الشمري',
            'email'      => 'agent2@qaterp.com',
            'phone'      => '777567890',
            'role'       => 'agent',
            'agent_id'   => 2,
            'status'     => true,
        ]);

        User::updateOrCreate(['username' => 'agent3'], [
            'password'   => $password,
            'full_name'  => 'يحيى عبدالرحمن',
            'email'      => 'agent3@qaterp.com',
            'phone'      => '777678901',
            'role'       => 'agent',
            'agent_id'   => 3,
            'status'     => true,
        ]);
    }
}
