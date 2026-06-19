<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'sales' => ['view','add','edit','delete','export'],
            'purchases' => ['view','add','edit','delete','export'],
            'products' => ['view','add','edit','delete','restock','distribute','adjust_stock'],
            'customers' => ['view','add','edit','delete','export'],
            'suppliers' => ['view','add','edit','delete','settle','export'],
            'agents' => ['view','add','edit','delete','export'],
            'debts' => ['view','add','edit','delete','pay','mark_overdue'],
            'expenses' => ['view','add','edit','delete'],
            'distributions' => ['view','add','edit','delete'],
            'agent_settlements' => ['view','add','edit','delete'],
            'daily_session' => ['view','open','close'],
            'reports' => ['view','daily','monthly','supplier','agent','debts','export'],
            'accounts' => ['view','add','edit','delete'],
            'journal_entries' => ['view','add','edit','delete'],
            'transfers' => ['view','add','delete'],
            'receipt_vouchers' => ['view','add','delete'],
            'payment_vouchers' => ['view','add','delete'],
            'reminders' => ['view','add','edit','delete'],
            'currencies' => ['view','add','edit'],
            'notifications' => ['view'],
            'settings' => ['view','edit'],
            'users' => ['view','add','edit','delete','permissions'],
            'pos' => ['view','sell'],
        ];

        // Map modules to legacy permission names: <module>.<action>
        $allPerms = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $allPerms[] = [
                    'name'        => "{$module}.{$action}",
                    'description' => "صلاحية {$action} على {$module}",
                    'module'      => $module,
                ];
            }
        }

        foreach ($allPerms as $p) {
            DB::table('permissions')->insertOrIgnore($p + ['created_at' => now(), 'updated_at' => now()]);
        }

        // Default role-permission assignments
        // Admin: wildcard (handled by Gate::before)
        // Manager: everything except users management
        // Agent: only their own data
        // Accountant: read + financial modules

        $managerPerms = collect($allPerms)->pluck('name')
            ->filter(fn($n) => ! str_starts_with($n, 'users.') || $n === 'users.view')
            ->all();
        $this->assignRole('manager', $managerPerms);

        $agentPerms = [
            'sales.view', 'sales.add', 'sales.edit', 'sales.delete',
            'pos.view', 'pos.sell',
            'debts.view', 'debts.add', 'debts.pay',
            'customers.view', 'customers.add', 'customers.edit',
            'distributions.view',
            'agent_settlements.view', 'agent_settlements.add',
            'daily_session.view',
            'notifications.view', 'reminders.view',
            'reports.view', 'reports.daily',
        ];
        $this->assignRole('agent', $agentPerms);

        $accountantPerms = [
            'sales.view', 'purchases.view', 'expenses.view', 'debts.view',
            'reports.view', 'reports.daily', 'reports.monthly', 'reports.supplier', 'reports.agent', 'reports.debts',
            'accounts.view', 'journal_entries.view', 'journal_entries.add', 'journal_entries.edit',
            'transfers.view', 'transfers.add',
            'receipt_vouchers.view', 'receipt_vouchers.add',
            'payment_vouchers.view', 'payment_vouchers.add',
            'daily_session.view', 'daily_session.open', 'daily_session.close',
        ];
        $this->assignRole('accountant', $accountantPerms);
    }

    private function assignRole(string $role, array $permissions): void
    {
        DB::table('role_permissions')->where('role', $role)->delete();
        $permIds = DB::table('permissions')->whereIn('name', $permissions)->pluck('id');
        $rows = $permIds->map(fn($id) => ['role' => $role, 'permission_id' => $id])->all();
        if ($rows) DB::table('role_permissions')->insert($rows);
    }
}
