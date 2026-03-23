<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── PERMISSIONS ────────────────────────────────────────────
        $permissions = [
            // Budget
            'budget.view', 'budget.create', 'budget.edit', 'budget.approve',
            // Disbursement
            'disbursement.view', 'disbursement.create', 'disbursement.approve', 'disbursement.pay',
            // AP - Bills
            'bill.view', 'bill.create', 'bill.approve', 'bill.post',
            // AR - Invoices
            'invoice.view', 'invoice.create',
            // AR - Collections
            'collection.view', 'collection.create',
            // Journal Entries
            'je.view', 'je.create', 'je.post', 'je.reverse',
            // Reports
            'report.view', 'report.export',
            // Period Management
            'period.close',
            // Settings
            'settings.manage',
            // Audit
            'audit.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ─── ROLES ──────────────────────────────────────────────────

        // Administrator - full access
        $admin = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        // Finance Manager - everything except settings
        $financeManager = Role::firstOrCreate(['name' => 'finance_manager', 'guard_name' => 'web']);
        $financeManager->syncPermissions(array_diff($permissions, ['settings.manage']));

        // Finance Staff - CRUD on transactions, view reports
        $financeStaff = Role::firstOrCreate(['name' => 'finance_staff', 'guard_name' => 'web']);
        $financeStaff->syncPermissions([
            'budget.view', 'budget.create', 'budget.edit',
            'disbursement.view', 'disbursement.create',
            'bill.view', 'bill.create',
            'invoice.view', 'invoice.create',
            'collection.view', 'collection.create',
            'je.view', 'je.create',
            'report.view', 'report.export',
            'audit.view',
        ]);

        // Treasury - payments and collections focus
        $treasury = Role::firstOrCreate(['name' => 'treasury', 'guard_name' => 'web']);
        $treasury->syncPermissions([
            'disbursement.view', 'disbursement.pay',
            'bill.view',
            'collection.view', 'collection.create',
            'je.view',
            'report.view', 'report.export',
        ]);

        // Accountant - GL and reporting focus
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'budget.view',
            'disbursement.view',
            'bill.view', 'bill.post',
            'invoice.view',
            'collection.view',
            'je.view', 'je.create', 'je.post', 'je.reverse',
            'report.view', 'report.export',
            'period.close',
            'audit.view',
        ]);

        // Department Head - budget and disbursement approvals
        $deptHead = Role::firstOrCreate(['name' => 'department_head', 'guard_name' => 'web']);
        $deptHead->syncPermissions([
            'budget.view', 'budget.create', 'budget.edit', 'budget.approve',
            'disbursement.view', 'disbursement.create', 'disbursement.approve',
            'report.view',
        ]);

        // Billing Staff - AR focus
        $billingStaff = Role::firstOrCreate(['name' => 'billing_staff', 'guard_name' => 'web']);
        $billingStaff->syncPermissions([
            'invoice.view', 'invoice.create',
            'collection.view',
            'report.view',
        ]);

        // Collector - collection focus
        $collector = Role::firstOrCreate(['name' => 'collector', 'guard_name' => 'web']);
        $collector->syncPermissions([
            'invoice.view',
            'collection.view', 'collection.create',
            'report.view',
        ]);

        // Auditor - view-only access
        $auditor = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
        $auditor->syncPermissions([
            'budget.view',
            'disbursement.view',
            'bill.view',
            'invoice.view',
            'collection.view',
            'je.view',
            'report.view', 'report.export',
            'audit.view',
        ]);

        // ─── DEFAULT ADMIN USER ─────────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'admin@orangeapps.edu.ph'],
            [
                'name' => 'Roberto Tan',
                'password' => Hash::make('password'),
                'phone' => '0917-000-0001',
                'department_id' => 2, // Administration
                'campus_id' => 1,     // Main Campus
                'role' => 'administrator',
                'is_active' => true,
            ]
        );
        $user->assignRole('administrator');
    }
}
