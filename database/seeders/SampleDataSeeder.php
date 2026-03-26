<?php

namespace Database\Seeders;

use App\Models\ApBill;
use App\Models\ApBillLine;
use App\Models\ApPayment;
use App\Models\ArCollection;
use App\Models\ArCollectionAllocation;
use App\Models\ArInvoice;
use App\Models\ArInvoiceLine;
use App\Models\Budget;
use App\Models\BudgetAllocation;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Department;
use App\Models\DisbursementApproval;
use App\Models\DisbursementItem;
use App\Models\DisbursementPayment;
use App\Models\DisbursementRequest;
use App\Models\ExpenseCategory;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\RecurringJournalLine;
use App\Models\RecurringJournalTemplate;
use App\Models\Vendor;
use App\Services\NumberingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating sample data for all modules...');

        $userId = 1;
        $now = now();

        // Get reference data
        $departments = Department::all();
        $vendors = Vendor::all();
        $customers = Customer::all();
        $categories = ExpenseCategory::all();
        $cashAccount = ChartOfAccount::where('account_code', '1010')->first();
        $arAccount = ChartOfAccount::where('account_code', 'like', '1200%')->first()
            ?? ChartOfAccount::where('account_name', 'like', '%Receivable%')->where('account_type', 'asset')->first();
        $apAccount = ChartOfAccount::where('account_code', 'like', '2010%')->first()
            ?? ChartOfAccount::where('account_name', 'like', '%Payable%')->where('account_type', 'liability')->first();
        $revenueAccount = ChartOfAccount::where('account_type', 'revenue')->first();
        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')->limit(5)->get();

        if (!$cashAccount || !$revenueAccount || $expenseAccounts->isEmpty()) {
            $this->command->error('Missing chart of accounts. Run MasterDataSeeder first.');
            return;
        }

        // ─── 1. ADDITIONAL BUDGETS ──────────────────────────────────
        $this->command->info('  Adding budgets...');
        $budgetData = [
            ['Library Books & Materials', 1, 1, 350000],
            ['IT Software Subscriptions', 2, 2, 450000],
            ['Sports Equipment', 5, 3, 200000],
            ['Science Lab Supplies', 3, 4, 180000],
            ['Faculty Development', 4, 5, 300000],
        ];

        foreach ($budgetData as [$name, $deptIdx, $catIdx, $amount]) {
            $dept = $departments->get($deptIdx % $departments->count());
            $cat = $categories->get($catIdx % $categories->count());
            if (!$dept || !$cat) continue;

            $committed = round($amount * 0.3, 2);
            $actual = round($amount * 0.15, 2);

            $budget = Budget::create([
                'budget_name' => $name,
                'school_year' => '2025-2026',
                'department_id' => $dept->id,
                'category_id' => $cat->id,
                'annual_budget' => $amount,
                'committed' => $committed,
                'actual' => $actual,
                'status' => 'approved',
            ]);

            // Monthly allocations
            for ($m = 1; $m <= 12; $m++) {
                BudgetAllocation::create([
                    'budget_id' => $budget->id,
                    'month' => $m,
                    'amount' => round($amount / 12, 2),
                ]);
            }
        }

        // ─── 2. AP BILLS ────────────────────────────────────────────
        $this->command->info('  Adding AP bills...');
        $billDescs = [
            'Computer monitors and peripherals',
            'Janitorial cleaning supplies Q1',
            'Electrical repair services',
            'Printing and binding - report cards',
            'Internet service - March 2026',
            'Water delivery service',
        ];

        foreach ($billDescs as $i => $desc) {
            $vendor = $vendors->get($i % $vendors->count());
            $dept = $departments->get($i % $departments->count());
            if (!$vendor || !$dept) continue;

            $gross = fake()->randomFloat(2, 5000, 80000);
            $vat = round($gross * 0.12, 2);
            $wht = round($gross * 0.02, 2);
            $net = $gross + $vat - $wht;
            $status = ['draft', 'approved', 'posted', 'posted', 'approved', 'draft'][$i] ?? 'draft';
            $billDate = $now->copy()->subDays(rand(5, 60));

            $bill = ApBill::create([
                'bill_number' => NumberingService::generate('BILL'),
                'bill_date' => $billDate,
                'posting_date' => $billDate,
                'due_date' => $billDate->copy()->addDays(30),
                'vendor_id' => $vendor->id,
                'department_id' => $dept->id,
                'campus_id' => 1,
                'description' => $desc,
                'reference_number' => 'REF-' . strtoupper(fake()->bothify('??###')),
                'gross_amount' => $gross,
                'vat_amount' => $vat,
                'withholding_tax' => $wht,
                'net_payable' => $net,
                'amount_paid' => $status === 'posted' ? $net : 0,
                'balance' => $status === 'posted' ? 0 : $net,
                'status' => $status,
                'created_by' => $userId,
            ]);

            $expAcct = $expenseAccounts->get($i % $expenseAccounts->count());
            ApBillLine::create([
                'bill_id' => $bill->id,
                'account_id' => $expAcct->id,
                'description' => $desc,
                'quantity' => 1,
                'unit_cost' => $gross,
                'amount' => $gross,
            ]);
        }

        // ─── 3. DISBURSEMENT REQUESTS ───────────────────────────────
        $this->command->info('  Adding disbursement requests...');
        $drDescs = [
            ['Classroom chairs replacement', 45000, 'approved'],
            ['Teacher training workshop', 25000, 'pending_approval'],
            ['Emergency generator repair', 75000, 'paid'],
            ['School event decorations', 15000, 'draft'],
            ['Air conditioning maintenance', 35000, 'approved'],
        ];

        foreach ($drDescs as $i => [$desc, $amount, $status]) {
            $vendor = $vendors->get($i % $vendors->count());
            $dept = $departments->get($i % $departments->count());
            if (!$vendor || !$dept) continue;

            $dr = DisbursementRequest::create([
                'request_number' => NumberingService::generate('DR'),
                'request_date' => $now->copy()->subDays(rand(3, 30)),
                'payee_type' => 'vendor',
                'payee_id' => $vendor->id,
                'payee_name' => $vendor->name,
                'department_id' => $dept->id,
                'category_id' => $categories->random()->id,
                'amount' => $amount,
                'description' => $desc,
                'payment_method' => ['check', 'bank_transfer', 'cash'][rand(0, 2)],
                'status' => $status,
            ]);

            DisbursementItem::create([
                'disbursement_id' => $dr->id,
                'description' => $desc,
                'quantity' => 1,
                'unit_cost' => $amount,
                'amount' => $amount,
                'account_code' => $expenseAccounts->random()->account_code,
            ]);

            if ($status === 'approved' || $status === 'paid') {
                DisbursementApproval::create([
                    'disbursement_id' => $dr->id,
                    'approver_role' => 'Finance Manager',
                    'approver_name' => 'Roberto Tan',
                    'action' => 'approved',
                    'comments' => 'Approved for processing',
                    'acted_at' => $now->copy()->subDays(rand(1, 5)),
                ]);
            }

            if ($status === 'paid') {
                DisbursementPayment::create([
                    'disbursement_id' => $dr->id,
                    'voucher_number' => NumberingService::generate('PV'),
                    'payment_date' => $now->copy()->subDays(rand(1, 10)),
                    'payment_method' => 'check',
                    'check_number' => 'CHK-' . rand(10000, 99999),
                    'gross_amount' => $amount,
                    'withholding_tax' => round($amount * 0.02, 2),
                    'net_amount' => round($amount * 0.98, 2),
                    'status' => 'completed',
                    'created_by' => $userId,
                ]);
            }
        }

        // ─── 4. AR INVOICES ─────────────────────────────────────────
        $this->command->info('  Adding AR invoices...');
        $invDescs = [
            ['Tuition fee 2nd Sem SY 2025-2026', 55000],
            ['Laboratory fee', 8000],
            ['Miscellaneous fees', 5500],
            ['Library fee', 3000],
            ['Athletic fee', 2500],
            ['Computer lab fee', 4000],
            ['Graduation fee', 6000],
            ['Summer class tuition', 25000],
        ];

        foreach ($invDescs as $i => [$desc, $amount]) {
            $customer = $customers->get($i % $customers->count());
            if (!$customer) continue;

            $invDate = $now->copy()->subDays(rand(10, 90));
            $dueDate = $invDate->copy()->addDays(30);
            $isPaid = $i < 3;

            $invoice = ArInvoice::create([
                'invoice_number' => NumberingService::generate('INV'),
                'invoice_date' => $invDate,
                'posting_date' => $invDate,
                'due_date' => $dueDate,
                'customer_id' => $customer->id,
                'campus_id' => 1,
                'school_year' => '2025-2026',
                'semester' => '2nd Semester',
                'description' => $desc,
                'gross_amount' => $amount,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'net_receivable' => $amount,
                'amount_paid' => $isPaid ? $amount : 0,
                'balance' => $isPaid ? 0 : $amount,
                'status' => $isPaid ? 'paid' : 'posted',
                'created_by' => $userId,
            ]);

            ArInvoiceLine::create([
                'invoice_id' => $invoice->id,
                'description' => $desc,
                'quantity' => 1,
                'unit_amount' => $amount,
                'amount' => $amount,
                'revenue_account_id' => $revenueAccount->id,
            ]);

            // Create collection for paid invoices
            if ($isPaid) {
                $collection = ArCollection::create([
                    'receipt_number' => NumberingService::generate('OR'),
                    'collection_date' => $invDate->copy()->addDays(rand(5, 20)),
                    'customer_id' => $customer->id,
                    'payment_method' => ['cash', 'bank_transfer', 'gcash'][rand(0, 2)],
                    'amount_received' => $amount,
                    'applied_amount' => $amount,
                    'unapplied_amount' => 0,
                    'status' => 'posted',
                    'collected_by' => $userId,
                ]);

                ArCollectionAllocation::create([
                    'collection_id' => $collection->id,
                    'invoice_id' => $invoice->id,
                    'amount_applied' => $amount,
                ]);
            }
        }

        // ─── 5. AP PAYMENTS ─────────────────────────────────────────
        $this->command->info('  Adding AP payments...');
        $postedBills = ApBill::where('status', 'posted')->limit(3)->get();
        foreach ($postedBills as $bill) {
            $existing = ApPayment::where('vendor_id', $bill->vendor_id)
                ->where('gross_amount', $bill->gross_amount)->exists();
            if ($existing) continue;

            ApPayment::create([
                'payment_number' => NumberingService::generate('PAY'),
                'vendor_id' => $bill->vendor_id,
                'payment_date' => $bill->bill_date->addDays(rand(10, 25)),
                'payment_method' => ['check', 'bank_transfer'][rand(0, 1)],
                'check_number' => 'CHK-' . rand(10000, 99999),
                'reference_number' => 'PAY-' . strtoupper(fake()->bothify('###??')),
                'gross_amount' => $bill->gross_amount,
                'withholding_tax' => $bill->withholding_tax ?? 0,
                'net_amount' => $bill->net_payable,
                'status' => 'completed',
                'created_by' => $userId,
            ]);
        }

        // ─── 6. RECURRING JOURNAL TEMPLATES ─────────────────────────
        $this->command->info('  Adding recurring templates...');
        $depreciationAcct = ChartOfAccount::where('account_name', 'like', '%Depreciation%')->first();
        $accumDepAcct = ChartOfAccount::where('account_name', 'like', '%Accumulated%')->first();

        if ($depreciationAcct && $accumDepAcct) {
            $template = RecurringJournalTemplate::create([
                'template_name' => 'Monthly Depreciation - Building',
                'frequency' => 'monthly',
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
                'description' => 'Monthly depreciation of school building',
                'is_active' => true,
                'auto_create' => false,
            ]);

            RecurringJournalLine::create([
                'template_id' => $template->id,
                'account_id' => $depreciationAcct->id,
                'description' => 'Building depreciation',
                'debit' => 50000,
                'credit' => 0,
            ]);

            RecurringJournalLine::create([
                'template_id' => $template->id,
                'account_id' => $accumDepAcct->id,
                'description' => 'Accumulated depreciation - Building',
                'debit' => 0,
                'credit' => 50000,
            ]);
        }

        // ─── 7. NOTIFICATIONS ───────────────────────────────────────
        $this->command->info('  Adding notifications...');
        $notifs = [
            ['success', 'Invoice Paid', 'INV-2026-0001 has been fully paid by Maria Clara Santos.', '/ar/invoices'],
            ['warning', 'Budget Alert', 'IT Software Subscriptions budget is at 85% utilization.', '/budget/dashboard'],
            ['info', 'New Vendor Registered', 'CloudTech Solutions Inc. has been added as a vendor.', '/vendors'],
            ['danger', 'Overdue Invoice', 'INV-2026-0005 from Juan dela Cruz is 15 days overdue.', '/ar/aging'],
            ['success', 'Journal Entry Posted', 'JE-2026-0010 has been posted to the general ledger.', '/gl/journal-entries'],
        ];

        foreach ($notifs as $i => [$type, $title, $msg, $url]) {
            DB::table('notifications')->insert([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $msg,
                'data' => json_encode(['url' => $url]),
                'read_at' => $i < 2 ? $now : null,
                'created_at' => $now->copy()->subHours(rand(1, 72)),
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Sample data created successfully!');
    }
}
