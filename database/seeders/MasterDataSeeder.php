<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // ─── CAMPUSES ───────────────────────────────────────────────
        DB::table('campuses')->insert([
            ['id' => 1, 'code' => 'MAIN', 'name' => 'Main Campus', 'address' => 'Manila, Philippines', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'SOUTH', 'name' => 'South Campus', 'address' => 'Makati, Philippines', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'NORTH', 'name' => 'North Campus', 'address' => 'Quezon City, Philippines', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ─── DEPARTMENTS ────────────────────────────────────────────
        $departments = [
            ['id' => 1, 'code' => 'ACAD', 'name' => 'Academics', 'campus_id' => 1, 'head_name' => 'Dr. Maria Santos'],
            ['id' => 2, 'code' => 'ADMIN', 'name' => 'Administration', 'campus_id' => 1, 'head_name' => 'Roberto Tan'],
            ['id' => 3, 'code' => 'IT', 'name' => 'Information Technology', 'campus_id' => 1, 'head_name' => 'Carlos Reyes'],
            ['id' => 4, 'code' => 'HR', 'name' => 'Human Resources', 'campus_id' => 1, 'head_name' => 'Ana Mendoza'],
            ['id' => 5, 'code' => 'LIB', 'name' => 'Library', 'campus_id' => 1, 'head_name' => 'Teresa Cruz'],
            ['id' => 6, 'code' => 'SA', 'name' => 'Student Affairs', 'campus_id' => 1, 'head_name' => 'Miguel Torres'],
            ['id' => 7, 'code' => 'MAINT', 'name' => 'Maintenance', 'campus_id' => 1, 'head_name' => 'Pedro Garcia'],
        ];
        foreach ($departments as $dept) {
            DB::table('departments')->insert(array_merge($dept, ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]));
        }

        // ─── EXPENSE CATEGORIES ─────────────────────────────────────
        $categories = [
            ['id' => 1, 'code' => 'BOOKS', 'name' => 'Books & Publications'],
            ['id' => 2, 'code' => 'SOFTWARE', 'name' => 'Software & Licenses'],
            ['id' => 3, 'code' => 'UTILITIES', 'name' => 'Utilities'],
            ['id' => 4, 'code' => 'SUPPLIES', 'name' => 'Office Supplies'],
            ['id' => 5, 'code' => 'TRAVEL', 'name' => 'Travel & Transportation'],
            ['id' => 6, 'code' => 'PROFSERV', 'name' => 'Professional Services'],
            ['id' => 7, 'code' => 'EVENTS', 'name' => 'Events & Activities'],
            ['id' => 8, 'code' => 'HARDWARE', 'name' => 'Hardware & Equipment'],
            ['id' => 9, 'code' => 'REPAIRS', 'name' => 'Repairs & Maintenance'],
            ['id' => 10, 'code' => 'SALARIES', 'name' => 'Salaries & Wages'],
        ];
        foreach ($categories as $cat) {
            DB::table('expense_categories')->insert(array_merge($cat, ['parent_id' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]));
        }

        // ─── COST CENTERS (5 per department) ────────────────────────
        $ccId = 1;
        $ccNames = ['Operations', 'Projects', 'Training', 'Capital', 'General'];
        foreach ($departments as $dept) {
            foreach ($ccNames as $i => $ccName) {
                DB::table('cost_centers')->insert([
                    'id' => $ccId,
                    'code' => $dept['code'] . '-CC' . ($i + 1),
                    'name' => $dept['name'] . ' - ' . $ccName,
                    'department_id' => $dept['id'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $ccId++;
            }
        }

        // ─── FUND SOURCES ───────────────────────────────────────────
        DB::table('fund_sources')->insert([
            ['id' => 1, 'code' => 'GF', 'name' => 'General Fund', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'TF', 'name' => 'Tuition Fund', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'SP', 'name' => 'Special Projects', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ─── CHART OF ACCOUNTS (100 accounts) ──────────────────────
        $accounts = [
            // ASSETS (1000-1699)
            ['account_code' => '1010', 'account_name' => 'Cash on Hand', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1020', 'account_name' => 'Cash in Bank - BDO', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1030', 'account_name' => 'Cash in Bank - BPI', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1040', 'account_name' => 'Cash in Bank - Metrobank', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1050', 'account_name' => 'Petty Cash Fund', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1100', 'account_name' => 'Accounts Receivable', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1110', 'account_name' => 'Tuition Receivable', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1120', 'account_name' => 'Other Receivables', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1150', 'account_name' => 'Allowance for Doubtful Accounts', 'account_type' => 'asset', 'normal_balance' => 'credit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1200', 'account_name' => 'Prepaid Expenses', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1210', 'account_name' => 'Prepaid Insurance', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1220', 'account_name' => 'Prepaid Rent', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1230', 'account_name' => 'Prepaid Supplies', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1300', 'account_name' => 'Inventories', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '1500', 'account_name' => 'Property and Equipment', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1510', 'account_name' => 'Land', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1520', 'account_name' => 'Buildings', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1530', 'account_name' => 'Furniture and Fixtures', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1540', 'account_name' => 'Library Books', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1550', 'account_name' => 'Computer Equipment', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1560', 'account_name' => 'Transportation Equipment', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1570', 'account_name' => 'Laboratory Equipment', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1600', 'account_name' => 'Accumulated Depreciation - Buildings', 'account_type' => 'asset', 'normal_balance' => 'credit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1610', 'account_name' => 'Accumulated Depreciation - Furniture', 'account_type' => 'asset', 'normal_balance' => 'credit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1620', 'account_name' => 'Accumulated Depreciation - Computer Equipment', 'account_type' => 'asset', 'normal_balance' => 'credit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1630', 'account_name' => 'Accumulated Depreciation - Transportation', 'account_type' => 'asset', 'normal_balance' => 'credit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1640', 'account_name' => 'Accumulated Depreciation - Lab Equipment', 'account_type' => 'asset', 'normal_balance' => 'credit', 'fs_group' => 'Non-Current Assets'],
            ['account_code' => '1650', 'account_name' => 'Construction in Progress', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Non-Current Assets'],

            // LIABILITIES (2000-2710)
            ['account_code' => '2000', 'account_name' => 'Accounts Payable', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2010', 'account_name' => 'Accrued Expenses Payable', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2050', 'account_name' => 'Notes Payable - Short Term', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2100', 'account_name' => 'Withholding Tax Payable', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2110', 'account_name' => 'Withholding Tax - Compensation', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2120', 'account_name' => 'Withholding Tax - Expanded', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2200', 'account_name' => 'VAT Payable', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2210', 'account_name' => 'Output VAT', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2220', 'account_name' => 'Input VAT', 'account_type' => 'asset', 'normal_balance' => 'debit', 'fs_group' => 'Current Assets'],
            ['account_code' => '2300', 'account_name' => 'SSS Payable', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2310', 'account_name' => 'PhilHealth Payable', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2320', 'account_name' => 'Pag-IBIG Payable', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2400', 'account_name' => 'Accrued Liabilities', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2410', 'account_name' => 'Accrued Salaries', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2420', 'account_name' => 'Accrued Benefits', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2500', 'account_name' => 'Unearned Revenue', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2510', 'account_name' => 'Deferred Tuition Revenue', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2600', 'account_name' => 'Current Portion of Long-Term Debt', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2610', 'account_name' => 'Bank Loan - Current', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Current Liabilities'],
            ['account_code' => '2700', 'account_name' => 'Long-Term Debt', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Non-Current Liabilities'],
            ['account_code' => '2710', 'account_name' => 'Bank Loan - Non-Current', 'account_type' => 'liability', 'normal_balance' => 'credit', 'fs_group' => 'Non-Current Liabilities'],

            // EQUITY (3000-3100)
            ['account_code' => '3000', 'account_name' => 'Equity', 'account_type' => 'equity', 'normal_balance' => 'credit', 'fs_group' => 'Equity'],
            ['account_code' => '3010', 'account_name' => 'Retained Earnings', 'account_type' => 'equity', 'normal_balance' => 'credit', 'fs_group' => 'Equity'],
            ['account_code' => '3020', 'account_name' => 'Retained Earnings - Prior Period', 'account_type' => 'equity', 'normal_balance' => 'credit', 'fs_group' => 'Equity'],
            ['account_code' => '3100', 'account_name' => 'Capital', 'account_type' => 'equity', 'normal_balance' => 'credit', 'fs_group' => 'Equity'],
            ['account_code' => '3200', 'account_name' => 'Donated Capital', 'account_type' => 'equity', 'normal_balance' => 'credit', 'fs_group' => 'Equity'],

            // REVENUE (4000-4510)
            ['account_code' => '4000', 'account_name' => 'Revenue', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],
            ['account_code' => '4010', 'account_name' => 'Tuition Fees', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],
            ['account_code' => '4020', 'account_name' => 'Miscellaneous Fees', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],
            ['account_code' => '4030', 'account_name' => 'Laboratory Fees', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],
            ['account_code' => '4040', 'account_name' => 'Library Fees', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],
            ['account_code' => '4050', 'account_name' => 'Registration Fees', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],
            ['account_code' => '4060', 'account_name' => 'Athletic Fees', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],
            ['account_code' => '4070', 'account_name' => 'Computer Fees', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],
            ['account_code' => '4080', 'account_name' => 'Transcript Fees', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],
            ['account_code' => '4090', 'account_name' => 'Diploma Fees', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],
            ['account_code' => '4100', 'account_name' => 'Other Revenue', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Other Income'],
            ['account_code' => '4110', 'account_name' => 'Interest Income', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Other Income'],
            ['account_code' => '4120', 'account_name' => 'Rental Income', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Other Income'],
            ['account_code' => '4130', 'account_name' => 'Donations Received', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Other Income'],
            ['account_code' => '4500', 'account_name' => 'Cost of Services', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],
            ['account_code' => '4510', 'account_name' => 'Teaching Salaries', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'fs_group' => 'Revenue'],

            // EXPENSES (5000-5970)
            ['account_code' => '5000', 'account_name' => 'Operating Expenses', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5010', 'account_name' => 'Salaries and Wages', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5020', 'account_name' => 'Employee Benefits', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5030', 'account_name' => 'SSS Contribution', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5040', 'account_name' => 'PhilHealth Contribution', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5050', 'account_name' => 'Pag-IBIG Contribution', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5100', 'account_name' => 'Office Supplies Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5110', 'account_name' => 'Teaching Supplies Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5200', 'account_name' => 'Utilities Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5210', 'account_name' => 'Electricity Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5220', 'account_name' => 'Water Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5230', 'account_name' => 'Telephone & Internet Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5300', 'account_name' => 'Rent Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5400', 'account_name' => 'Insurance Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5500', 'account_name' => 'Depreciation Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5510', 'account_name' => 'Depreciation - Buildings', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5520', 'account_name' => 'Depreciation - Computer Equipment', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5530', 'account_name' => 'Depreciation - Furniture', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5540', 'account_name' => 'Depreciation - Transportation', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5600', 'account_name' => 'Repairs and Maintenance', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5700', 'account_name' => 'Transportation Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5800', 'account_name' => 'Professional Fees', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5810', 'account_name' => 'Audit Fees', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5820', 'account_name' => 'Legal Fees', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5830', 'account_name' => 'Consulting Fees', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5900', 'account_name' => 'Miscellaneous Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5910', 'account_name' => 'Bank Charges', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5920', 'account_name' => 'Advertising Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5930', 'account_name' => 'Training Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5940', 'account_name' => 'Representation Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5950', 'account_name' => 'Security Services', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5960', 'account_name' => 'Janitorial Services', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
            ['account_code' => '5970', 'account_name' => 'Bad Debts Expense', 'account_type' => 'expense', 'normal_balance' => 'debit', 'fs_group' => 'Operating Expenses'],
        ];
        foreach ($accounts as $i => $acct) {
            DB::table('chart_of_accounts')->insert(array_merge($acct, [
                'id' => $i + 1,
                'parent_id' => null,
                'is_active' => true,
                'is_postable' => true,
                'campus_id' => null,
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // ─── TAX CODES ──────────────────────────────────────────────
        DB::table('tax_codes')->insert([
            ['id' => 1, 'code' => 'VAT12', 'name' => 'VAT 12%', 'rate' => 12.00, 'type' => 'vat', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'EWT1', 'name' => 'EWT 1%', 'rate' => 1.00, 'type' => 'ewt', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'EWT2', 'name' => 'EWT 2%', 'rate' => 2.00, 'type' => 'ewt', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'EWT5', 'name' => 'EWT 5%', 'rate' => 5.00, 'type' => 'ewt', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'code' => 'EWT10', 'name' => 'EWT 10%', 'rate' => 10.00, 'type' => 'ewt', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'code' => 'EWT15', 'name' => 'EWT 15%', 'rate' => 15.00, 'type' => 'ewt', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'code' => 'FWT20', 'name' => 'Final Withholding Tax 20%', 'rate' => 20.00, 'type' => 'final', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'code' => 'FWT25', 'name' => 'Final Withholding Tax 25%', 'rate' => 25.00, 'type' => 'final', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ─── PAYMENT TERMS ──────────────────────────────────────────
        DB::table('payment_terms')->insert([
            ['id' => 1, 'name' => 'Net 30', 'days' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Net 60', 'days' => 60, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Due on Receipt', 'days' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ─── ACCOUNTING PERIODS (Jul 2025 - Jun 2026) ───────────────
        $months = [
            ['Jul', '2025-07-01', '2025-07-31'], ['Aug', '2025-08-01', '2025-08-31'],
            ['Sep', '2025-09-01', '2025-09-30'], ['Oct', '2025-10-01', '2025-10-31'],
            ['Nov', '2025-11-01', '2025-11-30'], ['Dec', '2025-12-01', '2025-12-31'],
            ['Jan', '2026-01-01', '2026-01-31'], ['Feb', '2026-02-01', '2026-02-28'],
            ['Mar', '2026-03-01', '2026-03-31'], ['Apr', '2026-04-01', '2026-04-30'],
            ['May', '2026-05-01', '2026-05-31'], ['Jun', '2026-06-01', '2026-06-30'],
        ];
        foreach ($months as $i => $m) {
            DB::table('accounting_periods')->insert([
                'id' => $i + 1,
                'name' => $m[0] . ' ' . substr($m[1], 0, 4),
                'school_year' => '2025-2026',
                'start_date' => $m[1],
                'end_date' => $m[2],
                'status' => $i < 6 ? 'closed' : 'open',
                'closed_by' => null,
                'closed_at' => $i < 6 ? $now : null,
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ─── NUMBERING SEQUENCES ────────────────────────────────────
        $sequences = [
            ['module' => 'journal_entry', 'prefix' => 'JE', 'current_number' => 78, 'pad_length' => 4],
            ['module' => 'ap_bill', 'prefix' => 'BILL', 'current_number' => 8, 'pad_length' => 4],
            ['module' => 'ap_payment', 'prefix' => 'PAY', 'current_number' => 2, 'pad_length' => 4],
            ['module' => 'ar_invoice', 'prefix' => 'INV', 'current_number' => 10, 'pad_length' => 4],
            ['module' => 'ar_collection', 'prefix' => 'OR', 'current_number' => 7, 'pad_length' => 4],
            ['module' => 'disbursement', 'prefix' => 'DR', 'current_number' => 10, 'pad_length' => 4],
            ['module' => 'disbursement_payment', 'prefix' => 'DV', 'current_number' => 2, 'pad_length' => 4],
        ];
        foreach ($sequences as $seq) {
            DB::table('numbering_sequences')->insert(array_merge($seq, ['created_at' => $now, 'updated_at' => $now]));
        }

        // ─── DEFAULT SETTINGS ───────────────────────────────────────
        $settings = [
            ['key' => 'school_name', 'value' => 'OrangeApps Educational Institution', 'category' => 'general', 'description' => 'School name'],
            ['key' => 'school_tin', 'value' => '000-123-456-789', 'category' => 'general', 'description' => 'School TIN'],
            ['key' => 'school_address', 'value' => 'Manila, Philippines', 'category' => 'general', 'description' => 'School address'],
            ['key' => 'school_year', 'value' => '2025-2026', 'category' => 'general', 'description' => 'Current school year'],
            ['key' => 'currency', 'value' => 'PHP', 'category' => 'finance', 'description' => 'Default currency'],
            ['key' => 'vat_rate', 'value' => '12', 'category' => 'tax', 'description' => 'Default VAT rate'],
            ['key' => 'fiscal_year_start', 'value' => '07', 'category' => 'finance', 'description' => 'Fiscal year start month'],
        ];
        foreach ($settings as $setting) {
            DB::table('settings')->insert(array_merge($setting, ['created_at' => $now, 'updated_at' => $now]));
        }
    }
}
