<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Helper to find account ID by code from the seeded chart_of_accounts.
     */
    private function acctId(string $code): int
    {
        static $map = null;
        if ($map === null) {
            $map = DB::table('chart_of_accounts')->pluck('id', 'account_code')->toArray();
        }
        if (!isset($map[$code])) {
            throw new \RuntimeException("Account code $code not found");
        }
        return $map[$code];
    }

    public function run()
    {
        $now = Carbon::now();

        // ─── VENDORS ────────────────────────────────────────────────
        $vendors = [
            ['id' => 1, 'vendor_code' => 'V-001', 'name' => 'TechSoft Solutions Inc.', 'vendor_type' => 'corporate', 'contact_person' => 'John Cruz', 'phone' => '02-8888-1234', 'email' => 'john@techsoft.ph', 'address' => 'BGC, Taguig City', 'tin' => '123-456-789-000', 'vat_type' => 'vatable', 'withholding_tax_type' => 'EWT2'],
            ['id' => 2, 'vendor_code' => 'V-002', 'name' => 'Office Depot Philippines', 'vendor_type' => 'corporate', 'contact_person' => 'Maria Luna', 'phone' => '02-7777-5678', 'email' => 'maria@officedepot.ph', 'address' => 'Ortigas Center, Pasig City', 'tin' => '234-567-890-000', 'vat_type' => 'vatable', 'withholding_tax_type' => 'EWT1'],
            ['id' => 3, 'vendor_code' => 'V-003', 'name' => 'Green Facilities Management', 'vendor_type' => 'corporate', 'contact_person' => 'Ramon Verde', 'phone' => '02-6666-9012', 'email' => 'ramon@greenfm.ph', 'address' => 'Makati City', 'tin' => '345-678-901-000', 'vat_type' => 'vatable', 'withholding_tax_type' => 'EWT2'],
            ['id' => 4, 'vendor_code' => 'V-004', 'name' => 'National Book Store', 'vendor_type' => 'corporate', 'contact_person' => 'Lisa Ramos', 'phone' => '02-5555-3456', 'email' => 'corporate@nationalbookstore.ph', 'address' => 'Mandaluyong City', 'tin' => '456-789-012-000', 'vat_type' => 'vatable', 'withholding_tax_type' => 'EWT1'],
            ['id' => 5, 'vendor_code' => 'V-005', 'name' => 'Manila Water Company', 'vendor_type' => 'corporate', 'contact_person' => 'Water Services', 'phone' => '02-1627-0000', 'email' => 'billing@manilawater.com', 'address' => 'Quezon City', 'tin' => '567-890-123-000', 'vat_type' => 'vatable', 'withholding_tax_type' => 'EWT2'],
            ['id' => 6, 'vendor_code' => 'V-006', 'name' => 'Juan Dela Cruz', 'vendor_type' => 'individual', 'contact_person' => 'Juan Dela Cruz', 'phone' => '0917-123-4567', 'email' => 'juan@email.com', 'address' => 'Manila', 'tin' => '678-901-234-000', 'vat_type' => 'non_vat', 'withholding_tax_type' => 'EWT10'],
            ['id' => 7, 'vendor_code' => 'V-007', 'name' => 'Maria Santos Consulting', 'vendor_type' => 'individual', 'contact_person' => 'Maria Santos', 'phone' => '0918-234-5678', 'email' => 'maria.santos@consulting.ph', 'address' => 'Quezon City', 'tin' => '789-012-345-000', 'vat_type' => 'non_vat', 'withholding_tax_type' => 'EWT15'],
        ];
        foreach ($vendors as $v) {
            DB::table('vendors')->insert(array_merge($v, [
                'payment_terms_id' => 1, 'credit_limit' => 500000, 'bank_name' => null,
                'account_name' => null, 'account_number' => null, 'default_ap_account_id' => $this->acctId('2000'),
                'default_expense_account_id' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]));
        }

        // ─── CUSTOMERS (Students) ───────────────────────────────────
        $customers = [
            ['id' => 1, 'customer_code' => 'STU-001', 'customer_type' => 'student', 'name' => 'Maria Clara Santos', 'campus_id' => 1, 'grade_level' => 'Grade 12'],
            ['id' => 2, 'customer_code' => 'STU-002', 'customer_type' => 'student', 'name' => 'Jose Rizal Jr.', 'campus_id' => 1, 'grade_level' => 'Grade 11'],
            ['id' => 3, 'customer_code' => 'STU-003', 'customer_type' => 'student', 'name' => 'Andrea Bonifacio', 'campus_id' => 1, 'grade_level' => 'Grade 12'],
            ['id' => 4, 'customer_code' => 'STU-004', 'customer_type' => 'student', 'name' => 'Carlos Garcia III', 'campus_id' => 2, 'grade_level' => 'Grade 10'],
            ['id' => 5, 'customer_code' => 'STU-005', 'customer_type' => 'student', 'name' => 'Isabella Reyes', 'campus_id' => 2, 'grade_level' => 'Grade 11'],
            ['id' => 6, 'customer_code' => 'STU-006', 'customer_type' => 'student', 'name' => 'Gabriel Mendoza', 'campus_id' => 1, 'grade_level' => 'Grade 12'],
            ['id' => 7, 'customer_code' => 'STU-007', 'customer_type' => 'student', 'name' => 'Sofia Torres', 'campus_id' => 3, 'grade_level' => 'Grade 10'],
            ['id' => 8, 'customer_code' => 'STU-008', 'customer_type' => 'student', 'name' => 'Miguel Angeles', 'campus_id' => 3, 'grade_level' => 'Grade 11'],
        ];
        foreach ($customers as $c) {
            DB::table('customers')->insert(array_merge($c, [
                'contact_person' => null, 'email' => strtolower(str_replace(' ', '.', $c['name'])) . '@student.edu.ph',
                'phone' => null, 'billing_address' => 'Manila, Philippines', 'tin' => null,
                'default_ar_account_id' => $this->acctId('1110'), 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]));
        }

        // ─── BUDGETS ────────────────────────────────────────────────
        $budgets = [
            ['id' => 1, 'budget_name' => 'IT Infrastructure Upgrade', 'department_id' => 3, 'category_id' => 8, 'annual_budget' => 2500000, 'status' => 'approved', 'committed' => 1200000, 'actual' => 800000],
            ['id' => 2, 'budget_name' => 'Library Book Acquisition', 'department_id' => 5, 'category_id' => 1, 'annual_budget' => 500000, 'status' => 'approved', 'committed' => 250000, 'actual' => 180000],
            ['id' => 3, 'budget_name' => 'Faculty Development Program', 'department_id' => 1, 'category_id' => 6, 'annual_budget' => 1000000, 'status' => 'approved', 'committed' => 400000, 'actual' => 350000],
            ['id' => 4, 'budget_name' => 'Campus Maintenance', 'department_id' => 7, 'category_id' => 9, 'annual_budget' => 1500000, 'status' => 'approved', 'committed' => 800000, 'actual' => 600000],
            ['id' => 5, 'budget_name' => 'Student Activities', 'department_id' => 6, 'category_id' => 7, 'annual_budget' => 750000, 'status' => 'approved', 'committed' => 300000, 'actual' => 250000],
            ['id' => 6, 'budget_name' => 'Office Supplies Budget', 'department_id' => 2, 'category_id' => 4, 'annual_budget' => 300000, 'status' => 'approved', 'committed' => 150000, 'actual' => 120000],
            ['id' => 7, 'budget_name' => 'Software Licenses', 'department_id' => 3, 'category_id' => 2, 'annual_budget' => 800000, 'status' => 'draft', 'committed' => 0, 'actual' => 0],
            ['id' => 8, 'budget_name' => 'HR Training Budget', 'department_id' => 4, 'category_id' => 6, 'annual_budget' => 400000, 'status' => 'approved', 'committed' => 100000, 'actual' => 80000],
            ['id' => 9, 'budget_name' => 'Travel & Transport', 'department_id' => 2, 'category_id' => 5, 'annual_budget' => 200000, 'status' => 'approved', 'committed' => 80000, 'actual' => 50000],
            ['id' => 10, 'budget_name' => 'Utilities Budget', 'department_id' => 2, 'category_id' => 3, 'annual_budget' => 1200000, 'status' => 'approved', 'committed' => 600000, 'actual' => 540000],
        ];
        foreach ($budgets as $b) {
            DB::table('budgets')->insert(array_merge($b, [
                'school_year' => '2025-2026', 'cost_center_id' => null, 'fund_source_id' => 1,
                'project' => null, 'campus' => 'Main', 'budget_owner' => 'Roberto Tan',
                'notes' => null, 'created_at' => $now, 'updated_at' => $now,
            ]));
        }

        // ─── BUDGET ALLOCATIONS (monthly for first 5 budgets) ───────
        foreach (range(1, 5) as $budgetId) {
            $annual = $budgets[$budgetId - 1]['annual_budget'];
            $monthly = round($annual / 12, 2);
            foreach (range(1, 12) as $month) {
                DB::table('budget_allocations')->insert([
                    'budget_id' => $budgetId, 'month' => $month,
                    'amount' => $month === 12 ? $annual - ($monthly * 11) : $monthly,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // ─── DISBURSEMENT REQUESTS ──────────────────────────────────
        $disbursements = [
            ['id' => 1, 'request_number' => 'DR-0001', 'request_date' => '2025-08-15', 'payee_type' => 'vendor', 'payee_id' => 1, 'payee_name' => 'TechSoft Solutions Inc.', 'department_id' => 3, 'category_id' => 8, 'amount' => 150000, 'status' => 'paid', 'payment_method' => 'check', 'description' => 'Server equipment purchase'],
            ['id' => 2, 'request_number' => 'DR-0002', 'request_date' => '2025-09-01', 'payee_type' => 'vendor', 'payee_id' => 2, 'payee_name' => 'Office Depot Philippines', 'department_id' => 2, 'category_id' => 4, 'amount' => 25000, 'status' => 'paid', 'payment_method' => 'check', 'description' => 'Office supplies quarterly order'],
            ['id' => 3, 'request_number' => 'DR-0003', 'request_date' => '2025-09-15', 'payee_type' => 'vendor', 'payee_id' => 3, 'payee_name' => 'Green Facilities Management', 'department_id' => 7, 'category_id' => 9, 'amount' => 75000, 'status' => 'approved', 'payment_method' => 'bank_transfer', 'description' => 'HVAC maintenance service'],
            ['id' => 4, 'request_number' => 'DR-0004', 'request_date' => '2025-10-01', 'payee_type' => 'vendor', 'payee_id' => 4, 'payee_name' => 'National Book Store', 'department_id' => 5, 'category_id' => 1, 'amount' => 45000, 'status' => 'approved', 'payment_method' => 'check', 'description' => 'Library books acquisition'],
            ['id' => 5, 'request_number' => 'DR-0005', 'request_date' => '2025-10-15', 'payee_type' => 'vendor', 'payee_id' => 6, 'payee_name' => 'Juan Dela Cruz', 'department_id' => 1, 'category_id' => 6, 'amount' => 30000, 'status' => 'pending_approval', 'payment_method' => 'bank_transfer', 'description' => 'Guest lecture honorarium'],
            ['id' => 6, 'request_number' => 'DR-0006', 'request_date' => '2025-11-01', 'payee_type' => 'vendor', 'payee_id' => 7, 'payee_name' => 'Maria Santos Consulting', 'department_id' => 4, 'category_id' => 6, 'amount' => 50000, 'status' => 'pending_approval', 'payment_method' => 'check', 'description' => 'HR consulting services'],
            ['id' => 7, 'request_number' => 'DR-0007', 'request_date' => '2025-11-15', 'payee_type' => 'vendor', 'payee_id' => 5, 'payee_name' => 'Manila Water Company', 'department_id' => 2, 'category_id' => 3, 'amount' => 18000, 'status' => 'approved', 'payment_method' => 'bank_transfer', 'description' => 'Water utility bill Nov'],
            ['id' => 8, 'request_number' => 'DR-0008', 'request_date' => '2025-12-01', 'payee_type' => 'vendor', 'payee_id' => 1, 'payee_name' => 'TechSoft Solutions Inc.', 'department_id' => 3, 'category_id' => 2, 'amount' => 120000, 'status' => 'draft', 'payment_method' => 'check', 'description' => 'Software license renewal'],
            ['id' => 9, 'request_number' => 'DR-0009', 'request_date' => '2025-12-15', 'payee_type' => 'employee', 'payee_id' => null, 'payee_name' => 'Carlos Reyes', 'department_id' => 3, 'category_id' => 5, 'amount' => 15000, 'status' => 'draft', 'payment_method' => 'cash', 'description' => 'Travel reimbursement - conference'],
            ['id' => 10, 'request_number' => 'DR-0010', 'request_date' => '2026-01-05', 'payee_type' => 'vendor', 'payee_id' => 2, 'payee_name' => 'Office Depot Philippines', 'department_id' => 2, 'category_id' => 4, 'amount' => 35000, 'status' => 'draft', 'payment_method' => 'check', 'description' => 'Printer toner and paper'],
        ];
        foreach ($disbursements as $d) {
            DB::table('disbursement_requests')->insert(array_merge($d, [
                'due_date' => Carbon::parse($d['request_date'])->addDays(30)->format('Y-m-d'),
                'cost_center_id' => null, 'project' => null, 'budget_id' => null,
                'requested_by' => 'Roberto Tan', 'created_at' => $now, 'updated_at' => $now,
            ]));
        }

        // ─── DISBURSEMENT ITEMS ─────────────────────────────────────
        foreach ($disbursements as $d) {
            DB::table('disbursement_items')->insert([
                'disbursement_id' => $d['id'],
                'description' => $d['description'],
                'quantity' => 1, 'unit_cost' => $d['amount'], 'amount' => $d['amount'],
                'account_code' => '5100', 'tax_code' => null, 'remarks' => null,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ─── DISBURSEMENT APPROVALS ─────────────────────────────────
        foreach ([1, 2, 3, 4, 7] as $did) {
            DB::table('disbursement_approvals')->insert([
                'disbursement_id' => $did, 'approver_role' => 'finance_manager',
                'approver_name' => 'Roberto Tan', 'action' => 'approved',
                'comments' => 'Approved', 'acted_at' => $now,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ─── DISBURSEMENT PAYMENTS ──────────────────────────────────
        DB::table('disbursement_payments')->insert([
            ['id' => 1, 'disbursement_id' => 1, 'voucher_number' => 'DV-0001', 'payment_date' => '2025-08-20', 'payment_method' => 'check', 'bank_account' => 'BDO', 'check_number' => '000101', 'reference_number' => null, 'gross_amount' => 150000, 'withholding_tax' => 3000, 'net_amount' => 147000, 'status' => 'completed', 'created_by' => 'Roberto Tan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'disbursement_id' => 2, 'voucher_number' => 'DV-0002', 'payment_date' => '2025-09-05', 'payment_method' => 'check', 'bank_account' => 'BDO', 'check_number' => '000102', 'reference_number' => null, 'gross_amount' => 25000, 'withholding_tax' => 250, 'net_amount' => 24750, 'status' => 'completed', 'created_by' => 'Roberto Tan', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ─── AP BILLS ───────────────────────────────────────────────
        $bills = [
            ['id' => 1, 'bill_number' => 'BILL-0001', 'bill_date' => '2025-08-10', 'due_date' => '2025-09-09', 'vendor_id' => 1, 'gross_amount' => 150000, 'vat_amount' => 16071.43, 'withholding_tax' => 3000, 'net_payable' => 163071.43, 'amount_paid' => 163071.43, 'balance' => 0, 'status' => 'paid', 'description' => 'Server equipment'],
            ['id' => 2, 'bill_number' => 'BILL-0002', 'bill_date' => '2025-08-25', 'due_date' => '2025-09-24', 'vendor_id' => 2, 'gross_amount' => 25000, 'vat_amount' => 2678.57, 'withholding_tax' => 250, 'net_payable' => 27428.57, 'amount_paid' => 27428.57, 'balance' => 0, 'status' => 'paid', 'description' => 'Office supplies'],
            ['id' => 3, 'bill_number' => 'BILL-0003', 'bill_date' => '2025-09-15', 'due_date' => '2025-10-15', 'vendor_id' => 3, 'gross_amount' => 75000, 'vat_amount' => 8035.71, 'withholding_tax' => 1500, 'net_payable' => 81535.71, 'amount_paid' => 0, 'balance' => 81535.71, 'status' => 'posted', 'description' => 'HVAC maintenance'],
            ['id' => 4, 'bill_number' => 'BILL-0004', 'bill_date' => '2025-10-01', 'due_date' => '2025-10-31', 'vendor_id' => 4, 'gross_amount' => 45000, 'vat_amount' => 4821.43, 'withholding_tax' => 450, 'net_payable' => 49371.43, 'amount_paid' => 0, 'balance' => 49371.43, 'status' => 'approved', 'description' => 'Library books'],
            ['id' => 5, 'bill_number' => 'BILL-0005', 'bill_date' => '2025-10-15', 'due_date' => '2025-11-14', 'vendor_id' => 5, 'gross_amount' => 18000, 'vat_amount' => 1928.57, 'withholding_tax' => 360, 'net_payable' => 19568.57, 'amount_paid' => 0, 'balance' => 19568.57, 'status' => 'posted', 'description' => 'Water utility Oct'],
            ['id' => 6, 'bill_number' => 'BILL-0006', 'bill_date' => '2025-11-01', 'due_date' => '2025-12-01', 'vendor_id' => 6, 'gross_amount' => 30000, 'vat_amount' => 0, 'withholding_tax' => 3000, 'net_payable' => 27000, 'amount_paid' => 0, 'balance' => 27000, 'status' => 'pending_approval', 'description' => 'Guest lecture'],
            ['id' => 7, 'bill_number' => 'BILL-0007', 'bill_date' => '2025-11-15', 'due_date' => '2025-12-15', 'vendor_id' => 7, 'gross_amount' => 50000, 'vat_amount' => 0, 'withholding_tax' => 7500, 'net_payable' => 42500, 'amount_paid' => 0, 'balance' => 42500, 'status' => 'draft', 'description' => 'HR consulting'],
            ['id' => 8, 'bill_number' => 'BILL-0008', 'bill_date' => '2025-12-01', 'due_date' => '2025-12-31', 'vendor_id' => 1, 'gross_amount' => 120000, 'vat_amount' => 12857.14, 'withholding_tax' => 2400, 'net_payable' => 130457.14, 'amount_paid' => 0, 'balance' => 130457.14, 'status' => 'draft', 'description' => 'Software licenses'],
        ];
        foreach ($bills as $b) {
            DB::table('ap_bills')->insert(array_merge($b, [
                'posting_date' => in_array($b['status'], ['posted', 'paid']) ? $b['bill_date'] : null,
                'campus_id' => 1, 'department_id' => 2, 'cost_center_id' => null, 'category_id' => 4,
                'payment_terms_id' => 1, 'reference_number' => null, 'journal_entry_id' => null,
                'created_by' => 'Roberto Tan', 'approved_by' => in_array($b['status'], ['approved', 'posted', 'paid']) ? 'Roberto Tan' : null,
                'created_at' => $now, 'updated_at' => $now,
            ]));
        }

        // ─── AP BILL LINES ──────────────────────────────────────────
        foreach ($bills as $b) {
            DB::table('ap_bill_lines')->insert([
                'bill_id' => $b['id'], 'account_id' => $this->acctId('5100'),
                'description' => $b['description'], 'quantity' => 1,
                'unit_cost' => $b['gross_amount'], 'amount' => $b['gross_amount'],
                'tax_code_id' => $b['vat_amount'] > 0 ? 1 : null,
                'withholding_tax_code_id' => null, 'department_id' => null,
                'project' => null, 'fund_source_id' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ─── AP PAYMENTS ────────────────────────────────────────────
        DB::table('ap_payments')->insert([
            ['id' => 1, 'payment_number' => 'PAY-0001', 'payment_date' => '2025-08-20', 'vendor_id' => 1, 'payment_method' => 'check', 'bank_account' => 'BDO', 'check_number' => '000101', 'check_date' => '2025-08-20', 'reference_number' => null, 'gross_amount' => 166071.43, 'discount_amount' => 0, 'withholding_tax' => 3000, 'net_amount' => 163071.43, 'journal_entry_id' => null, 'status' => 'completed', 'remarks' => null, 'created_by' => 'Roberto Tan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'payment_number' => 'PAY-0002', 'payment_date' => '2025-09-05', 'vendor_id' => 2, 'payment_method' => 'check', 'bank_account' => 'BDO', 'check_number' => '000102', 'check_date' => '2025-09-05', 'reference_number' => null, 'gross_amount' => 27678.57, 'discount_amount' => 0, 'withholding_tax' => 250, 'net_amount' => 27428.57, 'journal_entry_id' => null, 'status' => 'completed', 'remarks' => null, 'created_by' => 'Roberto Tan', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ─── AP PAYMENT ALLOCATIONS ─────────────────────────────────
        DB::table('ap_payment_allocations')->insert([
            ['payment_id' => 1, 'bill_id' => 1, 'amount_applied' => 163071.43, 'created_at' => $now, 'updated_at' => $now],
            ['payment_id' => 2, 'bill_id' => 2, 'amount_applied' => 27428.57, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // ─── AR INVOICES (tuition for 8 students + 2 misc) ─────────
        $invoices = [];
        $tuitionAmount = 45000;
        for ($i = 1; $i <= 8; $i++) {
            $invoices[] = [
                'id' => $i, 'invoice_number' => 'INV-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'invoice_date' => '2025-07-15', 'due_date' => '2025-08-15',
                'customer_id' => $i, 'school_year' => '2025-2026', 'semester' => '1st Semester',
                'description' => 'Tuition fees 1st Sem SY 2025-2026',
                'gross_amount' => $tuitionAmount, 'discount_amount' => 0, 'tax_amount' => 0,
                'net_receivable' => $tuitionAmount,
                'amount_paid' => $i <= 5 ? $tuitionAmount : ($i <= 7 ? 25000 : 0),
                'balance' => $i <= 5 ? 0 : ($i <= 7 ? 20000 : $tuitionAmount),
                'status' => $i <= 5 ? 'paid' : ($i <= 7 ? 'partially_paid' : 'posted'),
            ];
        }
        // 2 misc fee invoices
        $invoices[] = ['id' => 9, 'invoice_number' => 'INV-0009', 'invoice_date' => '2025-08-01', 'due_date' => '2025-09-01', 'customer_id' => 1, 'school_year' => '2025-2026', 'semester' => '1st Semester', 'description' => 'Lab and library fees', 'gross_amount' => 5000, 'discount_amount' => 0, 'tax_amount' => 0, 'net_receivable' => 5000, 'amount_paid' => 5000, 'balance' => 0, 'status' => 'paid'];
        $invoices[] = ['id' => 10, 'invoice_number' => 'INV-0010', 'invoice_date' => '2025-08-01', 'due_date' => '2025-09-01', 'customer_id' => 2, 'school_year' => '2025-2026', 'semester' => '1st Semester', 'description' => 'Registration and misc fees', 'gross_amount' => 3000, 'discount_amount' => 0, 'tax_amount' => 0, 'net_receivable' => 3000, 'amount_paid' => 3000, 'balance' => 0, 'status' => 'paid'];

        foreach ($invoices as $inv) {
            DB::table('ar_invoices')->insert(array_merge($inv, [
                'posting_date' => $inv['invoice_date'], 'campus_id' => 1,
                'billing_period' => '1st Semester', 'reference_number' => null,
                'journal_entry_id' => null, 'created_by' => 'Roberto Tan',
                'created_at' => $now, 'updated_at' => $now,
            ]));
        }

        // ─── AR INVOICE LINES ───────────────────────────────────────
        foreach ($invoices as $inv) {
            $revenueAcct = str_contains($inv['description'], 'Tuition') ? '4010' : (str_contains($inv['description'], 'Lab') ? '4030' : '4020');
            DB::table('ar_invoice_lines')->insert([
                'invoice_id' => $inv['id'], 'fee_code' => $revenueAcct,
                'description' => $inv['description'], 'quantity' => 1,
                'unit_amount' => $inv['gross_amount'], 'amount' => $inv['gross_amount'],
                'revenue_account_id' => $this->acctId($revenueAcct),
                'department_id' => 1, 'project' => null, 'tax_code_id' => null,
                'discount_type' => null, 'remarks' => null,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ─── AR COLLECTIONS ────────────────────────────────────────
        $collections = [
            ['id' => 1, 'receipt_number' => 'OR-0001', 'collection_date' => '2025-07-20', 'customer_id' => 1, 'payment_method' => 'cash', 'amount_received' => 45000, 'applied_amount' => 45000, 'unapplied_amount' => 0, 'status' => 'fully_applied'],
            ['id' => 2, 'receipt_number' => 'OR-0002', 'collection_date' => '2025-07-22', 'customer_id' => 2, 'payment_method' => 'check', 'amount_received' => 45000, 'applied_amount' => 45000, 'unapplied_amount' => 0, 'status' => 'fully_applied'],
            ['id' => 3, 'receipt_number' => 'OR-0003', 'collection_date' => '2025-07-25', 'customer_id' => 3, 'payment_method' => 'bank_transfer', 'amount_received' => 45000, 'applied_amount' => 45000, 'unapplied_amount' => 0, 'status' => 'fully_applied'],
            ['id' => 4, 'receipt_number' => 'OR-0004', 'collection_date' => '2025-08-01', 'customer_id' => 4, 'payment_method' => 'gcash', 'amount_received' => 45000, 'applied_amount' => 45000, 'unapplied_amount' => 0, 'status' => 'fully_applied'],
            ['id' => 5, 'receipt_number' => 'OR-0005', 'collection_date' => '2025-08-05', 'customer_id' => 5, 'payment_method' => 'maya', 'amount_received' => 53000, 'applied_amount' => 53000, 'unapplied_amount' => 0, 'status' => 'fully_applied'],
            ['id' => 6, 'receipt_number' => 'OR-0006', 'collection_date' => '2025-08-10', 'customer_id' => 6, 'payment_method' => 'cash', 'amount_received' => 25000, 'applied_amount' => 25000, 'unapplied_amount' => 0, 'status' => 'fully_applied'],
            ['id' => 7, 'receipt_number' => 'OR-0007', 'collection_date' => '2025-08-15', 'customer_id' => 7, 'payment_method' => 'cash', 'amount_received' => 25000, 'applied_amount' => 25000, 'unapplied_amount' => 0, 'status' => 'fully_applied'],
        ];
        foreach ($collections as $c) {
            DB::table('ar_collections')->insert(array_merge($c, [
                'bank_account' => null, 'check_number' => $c['payment_method'] === 'check' ? 'CHK-' . $c['id'] : null,
                'reference_number' => null, 'journal_entry_id' => null,
                'collected_by' => 'Roberto Tan', 'remarks' => null,
                'created_at' => $now, 'updated_at' => $now,
            ]));
        }

        // ─── AR COLLECTION ALLOCATIONS ──────────────────────────────
        $allocations = [
            ['collection_id' => 1, 'invoice_id' => 1, 'amount_applied' => 45000],
            ['collection_id' => 2, 'invoice_id' => 2, 'amount_applied' => 45000],
            ['collection_id' => 3, 'invoice_id' => 3, 'amount_applied' => 45000],
            ['collection_id' => 4, 'invoice_id' => 4, 'amount_applied' => 45000],
            ['collection_id' => 5, 'invoice_id' => 5, 'amount_applied' => 45000],
            ['collection_id' => 5, 'invoice_id' => 9, 'amount_applied' => 5000],
            ['collection_id' => 5, 'invoice_id' => 10, 'amount_applied' => 3000],
            ['collection_id' => 6, 'invoice_id' => 6, 'amount_applied' => 25000],
            ['collection_id' => 7, 'invoice_id' => 7, 'amount_applied' => 25000],
        ];
        foreach ($allocations as $a) {
            DB::table('ar_collection_allocations')->insert(array_merge($a, ['created_at' => $now, 'updated_at' => $now]));
        }

        // ─── JOURNAL ENTRIES (78 entries - balanced trial balance) ──
        $this->seedJournalEntries($now);

        // ─── AUDIT LOGS ─────────────────────────────────────────────
        $auditActions = [
            ['action' => 'created', 'module' => 'budget', 'record_type' => 'Budget', 'record_id' => 1, 'remarks' => 'Created IT Infrastructure Upgrade budget'],
            ['action' => 'approved', 'module' => 'budget', 'record_type' => 'Budget', 'record_id' => 1, 'remarks' => 'Budget approved'],
            ['action' => 'created', 'module' => 'ap', 'record_type' => 'ApBill', 'record_id' => 1, 'remarks' => 'Created bill for TechSoft Solutions'],
            ['action' => 'approved', 'module' => 'ap', 'record_type' => 'ApBill', 'record_id' => 1, 'remarks' => 'Bill approved for payment'],
            ['action' => 'posted', 'module' => 'ap', 'record_type' => 'ApBill', 'record_id' => 1, 'remarks' => 'Bill posted to GL'],
            ['action' => 'created', 'module' => 'ar', 'record_type' => 'ArInvoice', 'record_id' => 1, 'remarks' => 'Created tuition invoice for Maria Clara Santos'],
            ['action' => 'posted', 'module' => 'ar', 'record_type' => 'ArInvoice', 'record_id' => 1, 'remarks' => 'Invoice posted'],
            ['action' => 'created', 'module' => 'ar', 'record_type' => 'ArCollection', 'record_id' => 1, 'remarks' => 'Collection received from Maria Clara Santos'],
            ['action' => 'posted', 'module' => 'gl', 'record_type' => 'JournalEntry', 'record_id' => 1, 'remarks' => 'Journal entry posted'],
            ['action' => 'created', 'module' => 'disbursement', 'record_type' => 'DisbursementRequest', 'record_id' => 1, 'remarks' => 'Created disbursement request for server equipment'],
            ['action' => 'approved', 'module' => 'disbursement', 'record_type' => 'DisbursementRequest', 'record_id' => 1, 'remarks' => 'Disbursement approved'],
            ['action' => 'created', 'module' => 'budget', 'record_type' => 'Budget', 'record_id' => 2, 'remarks' => 'Created Library Book Acquisition budget'],
            ['action' => 'approved', 'module' => 'budget', 'record_type' => 'Budget', 'record_id' => 2, 'remarks' => 'Budget approved'],
            ['action' => 'created', 'module' => 'ap', 'record_type' => 'ApBill', 'record_id' => 2, 'remarks' => 'Created bill for Office Depot'],
            ['action' => 'closed', 'module' => 'period', 'record_type' => 'AccountingPeriod', 'record_id' => 1, 'remarks' => 'Closed Jul 2025 period'],
            ['action' => 'closed', 'module' => 'period', 'record_type' => 'AccountingPeriod', 'record_id' => 2, 'remarks' => 'Closed Aug 2025 period'],
            ['action' => 'closed', 'module' => 'period', 'record_type' => 'AccountingPeriod', 'record_id' => 3, 'remarks' => 'Closed Sep 2025 period'],
            ['action' => 'closed', 'module' => 'period', 'record_type' => 'AccountingPeriod', 'record_id' => 4, 'remarks' => 'Closed Oct 2025 period'],
            ['action' => 'closed', 'module' => 'period', 'record_type' => 'AccountingPeriod', 'record_id' => 5, 'remarks' => 'Closed Nov 2025 period'],
            ['action' => 'closed', 'module' => 'period', 'record_type' => 'AccountingPeriod', 'record_id' => 6, 'remarks' => 'Closed Dec 2025 period'],
        ];
        foreach ($auditActions as $i => $a) {
            DB::table('audit_logs')->insert(array_merge($a, [
                'user_id' => 1, 'user_name' => 'Roberto Tan',
                'old_values' => null, 'new_values' => null,
                'ip_address' => '192.168.1.1', 'user_agent' => 'Mozilla/5.0',
                'created_at' => $now->copy()->subDays(20 - $i), 'updated_at' => $now,
            ]));
        }

        // ─── NOTIFICATIONS ──────────────────────────────────────────
        $notifications = [
            ['type' => 'success', 'title' => 'Budget Approved', 'message' => 'IT Infrastructure Upgrade budget has been approved.', 'data' => json_encode(['url' => '/budget/dashboard'])],
            ['type' => 'info', 'title' => 'New Bill Received', 'message' => 'Bill from TechSoft Solutions Inc. for ₱150,000.00.', 'data' => json_encode(['url' => '/ap/bills'])],
            ['type' => 'warning', 'title' => 'Payment Due Reminder', 'message' => 'Bill BILL-0003 from Green Facilities is due in 5 days.', 'data' => json_encode(['url' => '/ap/payment-processing'])],
            ['type' => 'success', 'title' => 'Collection Received', 'message' => 'Received ₱45,000.00 from Maria Clara Santos.', 'data' => json_encode(['url' => '/ar/collections'])],
            ['type' => 'info', 'title' => 'Period Closed', 'message' => 'Accounting period Jul 2025 has been closed.', 'data' => json_encode(['url' => '/gl/period-closing'])],
            ['type' => 'success', 'title' => 'Disbursement Approved', 'message' => 'DR-0001 for ₱150,000.00 has been approved.', 'data' => json_encode(['url' => '/ap/disbursements'])],
            ['type' => 'info', 'title' => 'System Update', 'message' => 'School Finance ERP has been updated to v2.0.', 'data' => json_encode(['url' => '/'])],
        ];
        foreach ($notifications as $i => $n) {
            DB::table('notifications')->insert(array_merge($n, [
                'user_id' => 1,
                'read_at' => $i < 3 ? $now : null,
                'created_at' => $now->copy()->subDays(7 - $i), 'updated_at' => $now,
            ]));
        }
    }

    /**
     * Seed 78 journal entries with balanced debits and credits.
     * Structure:
     *   JE-0001: Opening balances
     *   JE-0002..0007: Monthly payroll (Jul-Dec)
     *   JE-0008..0013: Monthly revenue - tuition (Jul-Dec)
     *   JE-0014..0019: Monthly revenue - misc fees (Jul-Dec)
     *   JE-0020..0025: Monthly utilities (Jul-Dec)
     *   JE-0026..0031: Monthly supplies (Jul-Dec)
     *   JE-0032..0037: Monthly maintenance (Jul-Dec)
     *   JE-0038..0043: Monthly professional fees (Jul-Dec)
     *   JE-0044..0049: Monthly depreciation (Jul-Dec)
     *   JE-0050..0055: Monthly insurance (Jul-Dec)
     *   JE-0056..0061: Monthly interest income (Jul-Dec)
     *   JE-0062..0067: Monthly bank charges (Jul-Dec)
     *   JE-0068..0073: Monthly security/janitorial (Jul-Dec)
     *   JE-0074..0078: Additional adjusting entries
     */
    private function seedJournalEntries(Carbon $now)
    {
        $jeId = 0;
        $lineId = 0;
        $months = [
            ['2025-07-01', 'Jul 2025'], ['2025-08-01', 'Aug 2025'], ['2025-09-01', 'Sep 2025'],
            ['2025-10-01', 'Oct 2025'], ['2025-11-01', 'Nov 2025'], ['2025-12-01', 'Dec 2025'],
        ];

        $insertJE = function (string $date, string $type, string $desc, array $lines) use (&$jeId, &$lineId, $now) {
            $jeId++;
            DB::table('journal_entries')->insert([
                'id' => $jeId,
                'entry_number' => 'JE-' . str_pad($jeId, 4, '0', STR_PAD_LEFT),
                'entry_date' => $date,
                'posting_date' => $date,
                'reference_number' => null,
                'journal_type' => $type,
                'description' => $desc,
                'campus_id' => 1,
                'department_id' => null,
                'school_year' => '2025-2026',
                'status' => 'posted',
                'source_module' => null,
                'source_id' => null,
                'created_by' => 'Roberto Tan',
                'approved_by' => 'Roberto Tan',
                'posted_by' => 'Roberto Tan',
                'reversed_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            foreach ($lines as $i => $line) {
                $lineId++;
                DB::table('journal_entry_lines')->insert([
                    'id' => $lineId,
                    'journal_entry_id' => $jeId,
                    'line_number' => $i + 1,
                    'account_id' => $this->acctId($line[0]),
                    'description' => $line[3] ?? $desc,
                    'debit' => $line[1],
                    'credit' => $line[2],
                    'department_id' => null,
                    'cost_center_id' => null,
                    'project' => null,
                    'fund_source_id' => null,
                    'payee_type' => null,
                    'payee_id' => null,
                    'due_date' => null,
                    'remarks' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        };

        // JE-0001: Opening Balances
        $insertJE('2025-07-01', 'general', 'Opening balances for SY 2025-2026', [
            // Assets (debits)
            ['1010', 500000, 0, 'Cash on Hand - opening'],
            ['1020', 5000000, 0, 'Cash in Bank BDO - opening'],
            ['1030', 3000000, 0, 'Cash in Bank BPI - opening'],
            ['1040', 2000000, 0, 'Cash in Bank Metrobank - opening'],
            ['1050', 50000, 0, 'Petty Cash Fund - opening'],
            ['1100', 200000, 0, 'Accounts Receivable - opening'],
            ['1210', 240000, 0, 'Prepaid Insurance - opening'],
            ['1230', 60000, 0, 'Prepaid Supplies - opening'],
            ['1510', 10000000, 0, 'Land - opening'],
            ['1520', 25000000, 0, 'Buildings - opening'],
            ['1530', 2000000, 0, 'Furniture and Fixtures - opening'],
            ['1550', 3000000, 0, 'Computer Equipment - opening'],
            ['1560', 1500000, 0, 'Transportation Equipment - opening'],
            // Contra assets (credits)
            ['1600', 0, 5000000, 'Accum Dep Buildings - opening'],
            ['1610', 0, 400000, 'Accum Dep Furniture - opening'],
            ['1620', 0, 900000, 'Accum Dep Computer - opening'],
            ['1630', 0, 450000, 'Accum Dep Transportation - opening'],
            // Liabilities (credits)
            ['2000', 0, 350000, 'Accounts Payable - opening'],
            ['2300', 0, 120000, 'SSS Payable - opening'],
            ['2310', 0, 45000, 'PhilHealth Payable - opening'],
            ['2320', 0, 30000, 'Pag-IBIG Payable - opening'],
            ['2400', 0, 100000, 'Accrued Liabilities - opening'],
            ['2710', 0, 2000000, 'Bank Loan Non-Current - opening'],
            // Equity (credits)
            ['3010', 0, 8155000, 'Retained Earnings - opening'],
            ['3100', 0, 30000000, 'Capital - opening'],
        ]);
        // Verify: Debits = 52,550,000; Credits = 5,000,000 + 400,000 + 900,000 + 450,000 + 350,000 + 120,000 + 45,000 + 30,000 + 100,000 + 2,000,000 + 8,155,000 + 30,000,000 = 47,550,000
        // Wait - need to rebalance. Let me recalculate.
        // Total Debits: 500000+5000000+3000000+2000000+50000+200000+240000+60000+10000000+25000000+2000000+3000000+1500000 = 52,550,000
        // Total Credits: 5000000+400000+900000+450000+350000+120000+45000+30000+100000+2000000+8155000+30000000 = 47,550,000
        // Difference: 5,000,000. Need to add 5M more to credits.
        // Fix: Increase Retained Earnings to 13,155,000
        // Actually let me just recalculate and fix the opening entry. Let me update the retained earnings.
        DB::table('journal_entry_lines')
            ->where('journal_entry_id', 1)
            ->where('description', 'Retained Earnings - opening')
            ->update(['credit' => 13155000]);

        // Monthly entries (Jul-Dec, 6 months)
        foreach ($months as $mi => [$date, $monthName]) {
            // JE: Payroll
            $insertJE($date, 'payroll', "Payroll for $monthName", [
                ['5010', 850000, 0, 'Salaries and Wages'],
                ['5020', 80000, 0, 'Employee Benefits'],
                ['5030', 42000, 0, 'SSS Contribution - employer'],
                ['5040', 15000, 0, 'PhilHealth Contribution - employer'],
                ['5050', 10000, 0, 'Pag-IBIG Contribution - employer'],
                ['1020', 0, 850000, 'Cash in Bank BDO - payroll'],
                ['2300', 0, 42000, 'SSS Payable'],
                ['2310', 0, 15000, 'PhilHealth Payable'],
                ['2320', 0, 10000, 'Pag-IBIG Payable'],
                ['2110', 0, 80000, 'WHT Compensation'],
            ]);

            // JE: Tuition Revenue
            $insertJE($date, 'revenue', "Tuition revenue for $monthName", [
                ['1110', 360000, 0, 'Tuition Receivable'],
                ['4010', 0, 360000, 'Tuition Fees'],
            ]);

            // JE: Misc Fee Revenue
            $insertJE($date, 'revenue', "Miscellaneous fees for $monthName", [
                ['1100', 25000, 0, 'Accounts Receivable - misc fees'],
                ['4020', 0, 15000, 'Miscellaneous Fees'],
                ['4030', 0, 5000, 'Laboratory Fees'],
                ['4040', 0, 3000, 'Library Fees'],
                ['4050', 0, 2000, 'Registration Fees'],
            ]);

            // JE: Utilities
            $insertJE($date, 'expense', "Utilities expense for $monthName", [
                ['5210', 45000, 0, 'Electricity Expense'],
                ['5220', 12000, 0, 'Water Expense'],
                ['5230', 8000, 0, 'Telephone & Internet'],
                ['1020', 0, 65000, 'Cash in Bank BDO - utilities'],
            ]);

            // JE: Supplies
            $insertJE($date, 'expense', "Supplies expense for $monthName", [
                ['5100', 15000, 0, 'Office Supplies'],
                ['5110', 10000, 0, 'Teaching Supplies'],
                ['1020', 0, 25000, 'Cash in Bank BDO - supplies'],
            ]);

            // JE: Maintenance
            $insertJE($date, 'expense', "Repairs & maintenance for $monthName", [
                ['5600', 20000, 0, 'Repairs and Maintenance'],
                ['1020', 0, 20000, 'Cash in Bank BDO - maintenance'],
            ]);

            // JE: Professional Fees
            $insertJE($date, 'expense', "Professional services for $monthName", [
                ['5800', 25000, 0, 'Professional Fees'],
                ['2120', 0, 2500, 'WHT Expanded'],
                ['1020', 0, 22500, 'Cash in Bank BDO - professional'],
            ]);

            // JE: Depreciation
            $insertJE($date, 'adjusting', "Depreciation for $monthName", [
                ['5510', 41667, 0, 'Depreciation - Buildings'],
                ['5520', 25000, 0, 'Depreciation - Computer Equipment'],
                ['5530', 16667, 0, 'Depreciation - Furniture'],
                ['5540', 12500, 0, 'Depreciation - Transportation'],
                ['1600', 0, 41667, 'Accum Dep Buildings'],
                ['1620', 0, 25000, 'Accum Dep Computer'],
                ['1610', 0, 16667, 'Accum Dep Furniture'],
                ['1630', 0, 12500, 'Accum Dep Transportation'],
            ]);

            // JE: Insurance
            $insertJE($date, 'adjusting', "Insurance amortization for $monthName", [
                ['5400', 20000, 0, 'Insurance Expense'],
                ['1210', 0, 20000, 'Prepaid Insurance'],
            ]);

            // JE: Interest Income
            $insertJE($date, 'revenue', "Interest income for $monthName", [
                ['1030', 5000, 0, 'Cash in Bank BPI - interest'],
                ['4110', 0, 5000, 'Interest Income'],
            ]);

            // JE: Bank Charges
            $insertJE($date, 'expense', "Bank charges for $monthName", [
                ['5910', 1500, 0, 'Bank Charges'],
                ['1020', 0, 1500, 'Cash in Bank BDO - bank charges'],
            ]);

            // JE: Security & Janitorial
            $insertJE($date, 'expense', "Security & janitorial for $monthName", [
                ['5950', 35000, 0, 'Security Services'],
                ['5960', 25000, 0, 'Janitorial Services'],
                ['2120', 0, 3000, 'WHT Expanded - services'],
                ['1020', 0, 57000, 'Cash in Bank BDO - services'],
            ]);
        }

        // JE-0074: Collection applied
        $insertJE('2025-08-15', 'general', 'Collections applied to tuition receivables', [
            ['1020', 283000, 0, 'Cash in Bank BDO - collections'],
            ['1110', 0, 283000, 'Tuition Receivable - collections applied'],
        ]);

        // JE-0075: AP payment - TechSoft
        $insertJE('2025-08-20', 'general', 'Payment to TechSoft Solutions - server equipment', [
            ['2000', 150000, 0, 'Accounts Payable - TechSoft'],
            ['1020', 0, 147000, 'Cash in Bank BDO - AP payment'],
            ['2120', 0, 3000, 'WHT Expanded - TechSoft'],
        ]);

        // JE-0076: AP payment - Office Depot
        $insertJE('2025-09-05', 'general', 'Payment to Office Depot - supplies', [
            ['2000', 25000, 0, 'Accounts Payable - Office Depot'],
            ['1020', 0, 24750, 'Cash in Bank BDO - AP payment'],
            ['2120', 0, 250, 'WHT Expanded - Office Depot'],
        ]);

        // JE-0077: Advertising expense
        $insertJE('2025-10-15', 'expense', 'Advertising and enrollment campaign', [
            ['5920', 50000, 0, 'Advertising Expense'],
            ['1020', 0, 50000, 'Cash in Bank BDO - advertising'],
        ]);

        // JE-0078: Training expense
        $insertJE('2025-11-20', 'expense', 'Faculty training workshop', [
            ['5930', 35000, 0, 'Training Expense'],
            ['1020', 0, 35000, 'Cash in Bank BDO - training'],
        ]);
    }
}
