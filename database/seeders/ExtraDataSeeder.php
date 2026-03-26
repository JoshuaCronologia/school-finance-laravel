<?php

namespace Database\Seeders;

use App\Models\ApPayment;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\RecurringJournalLine;
use App\Models\RecurringJournalTemplate;
use App\Services\NumberingService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExtraDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $cash = ChartOfAccount::where('account_code', '1010')->first();
        $ar = ChartOfAccount::where('account_type', 'asset')->where('account_name', 'like', '%Receivable%')->first();
        $ap = ChartOfAccount::where('account_type', 'liability')->where('account_name', 'like', '%Payable%')->first();
        $revenue = ChartOfAccount::where('account_type', 'revenue')->first();
        $expenses = ChartOfAccount::where('account_type', 'expense')->limit(5)->get();
        $salaries = ChartOfAccount::where('account_name', 'like', '%Salaries%')
            ->orWhere('account_name', 'like', '%Payroll%')->first() ?? $expenses->first();

        // ─── SPECIAL JOURNAL ENTRIES ──────────────────────────────
        $this->command->info('Adding special journal entries (CRJ, CDJ, SJ, PJ)...');

        // DB allows: general, adjusting, closing, reversing, revenue, expense, payroll
        $entries = [
            ['revenue', 'Tuition collection - Batch 1', 250000],
            ['revenue', 'Tuition collection - Batch 2', 180000],
            ['revenue', 'Miscellaneous fee collection', 45000],
            ['revenue', 'Rental income - canteen', 35000],
            ['revenue', 'Lab fee charges', 45000],
            ['revenue', 'Tuition charges - Grade 10', 320000],
            ['expense', 'Supplier payment - office supplies', 28000],
            ['expense', 'Utility payment - electricity March', 65000],
            ['expense', 'Insurance premium', 95000],
            ['expense', 'Textbook purchase order', 120000],
            ['expense', 'Laboratory equipment', 85000],
            ['expense', 'Computer hardware', 210000],
            ['payroll', 'Payroll - March 2026', 450000],
            ['payroll', 'Payroll - February 2026', 420000],
            ['adjusting', 'Accrued interest income', 15000],
            ['adjusting', 'Prepaid insurance adjustment', 25000],
            ['general', 'Opening balance adjustment', 100000],
            ['general', 'Fund transfer between accounts', 500000],
        ];

        $debitAcctMap = [
            'revenue' => $cash, 'expense' => $expenses->first(), 'payroll' => $salaries,
            'adjusting' => $ar, 'general' => $cash,
        ];
        $creditAcctMap = [
            'revenue' => $revenue, 'expense' => $cash, 'payroll' => $cash,
            'adjusting' => $revenue, 'general' => $ap,
        ];

        foreach ($entries as [$type, $desc, $amount]) {
            $debitAcct = $debitAcctMap[$type];
            $creditAcct = $creditAcctMap[$type];
            if (!$debitAcct || !$creditAcct) continue;

            // Use different expense accounts for variety
            if ($type === 'expense') {
                $debitAcct = $expenses->random();
            }

            $date = $now->copy()->subDays(rand(5, 90));

            $je = JournalEntry::create([
                'entry_number' => NumberingService::generate('JE'),
                'entry_date' => $date,
                'posting_date' => $date,
                'journal_type' => $type,
                'description' => $desc,
                'status' => 'posted',
                'created_by' => 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $je->id, 'line_number' => 1,
                'account_id' => $debitAcct->id, 'description' => $desc,
                'debit' => $amount, 'credit' => 0,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $je->id, 'line_number' => 2,
                'account_id' => $creditAcct->id, 'description' => $desc,
                'debit' => 0, 'credit' => $amount,
            ]);
        }

        // ─── RECURRING TEMPLATES ──────────────────────────────────
        $this->command->info('Adding recurring journal templates...');

        $templates = [
            ['Monthly Payroll Accrual', 'monthly', $salaries, $ap, 350000],
            ['Quarterly Insurance Expense', 'quarterly', $expenses[3] ?? $expenses[0], $cash, 95000],
            ['Annual License Renewal', 'annually', $expenses[1] ?? $expenses[0], $cash, 150000],
        ];

        foreach ($templates as [$name, $freq, $debitAcct, $creditAcct, $amount]) {
            if (!$debitAcct || !$creditAcct) continue;
            if (RecurringJournalTemplate::where('template_name', $name)->exists()) continue;

            $tpl = RecurringJournalTemplate::create([
                'template_name' => $name,
                'frequency' => $freq,
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
                'description' => $name . ' - recurring entry',
                'is_active' => true,
                'auto_create' => false,
            ]);

            RecurringJournalLine::create([
                'template_id' => $tpl->id, 'account_id' => $debitAcct->id,
                'description' => $name, 'debit' => $amount, 'credit' => 0,
            ]);
            RecurringJournalLine::create([
                'template_id' => $tpl->id, 'account_id' => $creditAcct->id,
                'description' => $name, 'debit' => 0, 'credit' => $amount,
            ]);
        }

        // ─── TAX DATA: AP Payments with WHT for BIR/Tax pages ────
        $this->command->info('Adding AP payments with withholding tax (for BIR 2307, 1601-E, alphalist)...');

        $vendors = \App\Models\Vendor::limit(6)->get();
        foreach ($vendors as $vendor) {
            for ($m = 1; $m <= 3; $m++) {
                $gross = rand(20000, 100000);
                $wht = round($gross * 0.02, 2);
                ApPayment::create([
                    'payment_number' => NumberingService::generate('PAY'),
                    'vendor_id' => $vendor->id,
                    'payment_date' => Carbon::create(2026, $m, rand(5, 25)),
                    'payment_method' => $m % 2 === 0 ? 'bank_transfer' : 'check',
                    'check_number' => $m % 2 !== 0 ? 'CHK-' . rand(10000, 99999) : null,
                    'reference_number' => 'REF-' . strtoupper(substr(md5(rand()), 0, 5)),
                    'gross_amount' => $gross,
                    'withholding_tax' => $wht,
                    'net_amount' => $gross - $wht,
                    'status' => 'completed',
                    'created_by' => 1,
                ]);
            }
        }

        // Disbursement payments with WHT (spread across quarters for alphalist)
        $this->command->info('Adding disbursement payments with WHT (quarterly data)...');

        $drId = DB::table('disbursement_requests')->min('id') ?? 1;
        for ($m = 1; $m <= 12; $m++) {
            $gross = rand(15000, 80000);
            $wht = round($gross * 0.02, 2);
            DB::table('disbursement_payments')->insert([
                'disbursement_id' => $drId,
                'voucher_number' => NumberingService::generate('PV'),
                'payment_date' => Carbon::create(2025, $m, rand(5, 25)),
                'payment_method' => 'check',
                'check_number' => 'CHK-' . rand(10000, 99999),
                'gross_amount' => $gross,
                'withholding_tax' => $wht,
                'net_amount' => $gross - $wht,
                'status' => 'completed',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Extra data created successfully!');
    }
}
