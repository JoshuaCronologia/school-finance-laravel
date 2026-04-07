<?php

namespace App\Services;

use App\Models\ApBill;
use App\Models\ApPayment;
use App\Models\ArCollection;
use App\Models\ArInvoice;
use App\Models\ChartOfAccount;
use App\Models\DisbursementPayment;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class PostingService
{
    /**
     * Map legacy journal type codes to DB enum values.
     * DB enum: general, adjusting, closing, reversing, revenue, expense, payroll
     */
    private const JOURNAL_TYPE_MAP = [
        'PJ'  => 'expense',   // Purchase Journal
        'CDJ' => 'expense',   // Cash Disbursement Journal
        'SJ'  => 'revenue',   // Sales Journal
        'CRJ' => 'revenue',   // Cash Receipts Journal
        'GJ'  => 'general',   // General Journal
        'AJ'  => 'adjusting', // Adjusting Journal
    ];
    /**
     * Post an AP Bill to the General Ledger.
     * DR: Expense/Asset accounts (per bill lines) + DR Input VAT
     * CR: Accounts Payable + CR WHT Payable
     */
    public function postBill(ApBill $bill): JournalEntry
    {
        return DB::transaction(function () use ($bill) {
            $bill->load('lines.account', 'vendor');

            $entry = JournalEntry::create([
                'entry_number' => NumberingService::generate('JE'),
                'entry_date' => $bill->bill_date,
                'posting_date' => $bill->posting_date ?? now(),
                'reference_number' => $bill->bill_number,
                'journal_type' => self::JOURNAL_TYPE_MAP['PJ'],
                'description' => "AP Bill: {$bill->bill_number} - {$bill->vendor->name}",
                'source_module' => 'AP',
                'source_id' => $bill->id,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            $lineNumber = 1;

            // Debit expense/asset accounts per bill line
            foreach ($bill->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => $lineNumber++,
                    'account_id' => $line->account_id,
                    'description' => $line->description,
                    'debit' => $line->amount,
                    'credit' => 0,
                    'department_id' => $line->department_id ?? $bill->department_id,
                    'cost_center_id' => $bill->cost_center_id,
                    'fund_source_id' => $line->fund_source_id ?? null,
                ]);
            }

            // Debit Input VAT if applicable
            if ($bill->vat_amount > 0) {
                $vatAccount = ChartOfAccount::where('account_code', 'like', '1150%')->first();
                if ($vatAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'line_number' => $lineNumber++,
                        'account_id' => $vatAccount->id,
                        'description' => 'Input VAT',
                        'debit' => $bill->vat_amount,
                        'credit' => 0,
                    ]);
                }
            }

            // Credit Accounts Payable
            $apAccount = ChartOfAccount::where('account_code', 'like', '2010%')->first()
                ?? ChartOfAccount::where('account_type', 'liability')->first();

            $apCreditAmount = $bill->gross_amount + ($bill->vat_amount ?? 0) - ($bill->withholding_tax ?? 0);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'line_number' => $lineNumber++,
                'account_id' => $apAccount->id,
                'description' => "Payable to {$bill->vendor->name}",
                'debit' => 0,
                'credit' => $apCreditAmount,
                'payee_type' => 'vendor',
                'payee_id' => $bill->vendor_id,
                'due_date' => $bill->due_date,
            ]);

            // Credit Withholding Tax Payable if applicable
            if (($bill->withholding_tax ?? 0) > 0) {
                $whtAccount = ChartOfAccount::where('account_code', 'like', '2060%')->first();
                if ($whtAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'line_number' => $lineNumber++,
                        'account_id' => $whtAccount->id,
                        'description' => 'Withholding Tax Payable',
                        'debit' => 0,
                        'credit' => $bill->withholding_tax,
                    ]);
                }
            }

            $bill->update([
                'journal_entry_id' => $entry->id,
                'status' => 'posted',
            ]);

            app(AuditService::class)->log('post', 'ap_bill', $bill, null, "Posted to GL: {$entry->entry_number}");

            return $entry;
        });
    }

    /**
     * Post an AP Payment to the General Ledger.
     * DR: Accounts Payable
     * CR: Cash/Bank
     */
    public function postPayment(ApPayment $payment): JournalEntry
    {
        return DB::transaction(function () use ($payment) {
            $payment->load('vendor', 'allocations.bill');

            $entry = JournalEntry::create([
                'entry_number' => NumberingService::generate('JE'),
                'entry_date' => $payment->payment_date,
                'posting_date' => $payment->payment_date,
                'reference_number' => $payment->payment_number,
                'journal_type' => self::JOURNAL_TYPE_MAP['CDJ'],
                'description' => "Payment: {$payment->payment_number} - {$payment->vendor->name}",
                'source_module' => 'AP',
                'source_id' => $payment->id,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            $lineNumber = 1;

            // Debit AP
            $apAccount = ChartOfAccount::where('account_code', 'like', '2010%')->first()
                ?? ChartOfAccount::where('account_type', 'liability')->first();

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'line_number' => $lineNumber++,
                'account_id' => $apAccount->id,
                'description' => "Payment to {$payment->vendor->name}",
                'debit' => $payment->gross_amount,
                'credit' => 0,
                'payee_type' => 'vendor',
                'payee_id' => $payment->vendor_id,
            ]);

            // Credit Cash/Bank
            $cashAccount = ChartOfAccount::where('account_code', 'like', '1010%')->first()
                ?? ChartOfAccount::where('account_type', 'asset')->where('account_code', 'like', '1%')->first();

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'line_number' => $lineNumber++,
                'account_id' => $cashAccount->id,
                'description' => "Cash payment - {$payment->payment_method}",
                'debit' => 0,
                'credit' => $payment->net_amount,
            ]);

            // Handle discount
            if (($payment->discount_amount ?? 0) > 0) {
                $discountAccount = ChartOfAccount::where('account_name', 'like', '%Purchase Discount%')->first()
                    ?? $apAccount;

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => $lineNumber++,
                    'account_id' => $discountAccount->id,
                    'description' => 'Purchase Discount',
                    'debit' => 0,
                    'credit' => $payment->discount_amount,
                ]);
            }

            // Handle withholding tax credit
            if (($payment->withholding_tax ?? 0) > 0) {
                $whtAccount = ChartOfAccount::where('account_code', 'like', '2060%')->first();
                if ($whtAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'line_number' => $lineNumber++,
                        'account_id' => $whtAccount->id,
                        'description' => 'Withholding Tax Applied',
                        'debit' => 0,
                        'credit' => $payment->withholding_tax,
                    ]);
                }
            }

            $payment->update([
                'journal_entry_id' => $entry->id,
                'status' => 'posted',
            ]);

            app(AuditService::class)->log('post', 'ap_payment', $payment, null, "Posted to GL: {$entry->entry_number}");

            return $entry;
        });
    }

    /**
     * Post an AR Invoice to the General Ledger.
     * DR: Accounts Receivable
     * CR: Revenue accounts + CR Output VAT
     */
    public function postInvoice(ArInvoice $invoice): JournalEntry
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->load('lines.revenueAccount', 'customer');

            $entry = JournalEntry::create([
                'entry_number' => NumberingService::generate('JE'),
                'entry_date' => $invoice->invoice_date,
                'posting_date' => $invoice->posting_date ?? now(),
                'reference_number' => $invoice->invoice_number,
                'journal_type' => self::JOURNAL_TYPE_MAP['SJ'],
                'description' => "AR Invoice: {$invoice->invoice_number} - {$invoice->customer->name}",
                'source_module' => 'AR',
                'source_id' => $invoice->id,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            $lineNumber = 1;

            // Debit AR
            $arAccount = ChartOfAccount::where('account_code', 'like', '1120%')->first()
                ?? ChartOfAccount::where('account_type', 'asset')
                    ->where('account_name', 'like', '%receivable%')->first();

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'line_number' => $lineNumber++,
                'account_id' => $arAccount->id,
                'description' => "Receivable from {$invoice->customer->name}",
                'debit' => $invoice->net_receivable,
                'credit' => 0,
                'payee_type' => 'customer',
                'payee_id' => $invoice->customer_id,
                'due_date' => $invoice->due_date,
            ]);

            // Credit Revenue per line
            foreach ($invoice->lines as $line) {
                $accountId = $line->revenue_account_id
                    ?? optional(ChartOfAccount::where('account_type', 'revenue')->first())->id;

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => $lineNumber++,
                    'account_id' => $accountId,
                    'description' => $line->description,
                    'debit' => 0,
                    'credit' => $line->amount,
                    'department_id' => $line->department_id,
                ]);
            }

            // Credit Output VAT
            if (($invoice->tax_amount ?? 0) > 0) {
                $vatAccount = ChartOfAccount::where('account_code', 'like', '2050%')->first();
                if ($vatAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'line_number' => $lineNumber++,
                        'account_id' => $vatAccount->id,
                        'description' => 'Output VAT',
                        'debit' => 0,
                        'credit' => $invoice->tax_amount,
                    ]);
                }
            }

            $invoice->update([
                'journal_entry_id' => $entry->id,
                'status' => 'posted',
            ]);

            app(AuditService::class)->log('post', 'ar_invoice', $invoice, null, "Posted to GL: {$entry->entry_number}");

            return $entry;
        });
    }

    /**
     * Post an AR Collection to the General Ledger.
     * DR: Cash/Bank
     * CR: Accounts Receivable
     */
    public function postCollection(ArCollection $collection): JournalEntry
    {
        return DB::transaction(function () use ($collection) {
            $collection->load('customer', 'allocations.invoice');

            $entry = JournalEntry::create([
                'entry_number' => NumberingService::generate('JE'),
                'entry_date' => $collection->collection_date,
                'posting_date' => $collection->collection_date,
                'reference_number' => $collection->receipt_number,
                'journal_type' => self::JOURNAL_TYPE_MAP['CRJ'],
                'description' => "Collection: {$collection->receipt_number} - {$collection->customer->name}",
                'source_module' => 'AR',
                'source_id' => $collection->id,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            $lineNumber = 1;

            // Debit Cash/Bank
            $cashAccount = ChartOfAccount::where('account_code', 'like', '1010%')->first()
                ?? ChartOfAccount::where('account_type', 'asset')->where('account_code', 'like', '1%')->first();

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'line_number' => $lineNumber++,
                'account_id' => $cashAccount->id,
                'description' => "Receipt from {$collection->customer->name}",
                'debit' => $collection->amount_received,
                'credit' => 0,
            ]);

            // Credit AR
            $arAccount = ChartOfAccount::where('account_code', 'like', '1120%')->first()
                ?? ChartOfAccount::where('account_type', 'asset')
                    ->where('account_name', 'like', '%receivable%')->first();

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'line_number' => $lineNumber++,
                'account_id' => $arAccount->id,
                'description' => "Collection applied - {$collection->customer->name}",
                'debit' => 0,
                'credit' => $collection->applied_amount,
                'payee_type' => 'customer',
                'payee_id' => $collection->customer_id,
            ]);

            // Handle unapplied
            if (($collection->unapplied_amount ?? 0) > 0) {
                $unappliedAccount = ChartOfAccount::where('account_code', 'like', '2090%')->first() ?? $arAccount;

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => $lineNumber++,
                    'account_id' => $unappliedAccount->id,
                    'description' => 'Unapplied payment',
                    'debit' => 0,
                    'credit' => $collection->unapplied_amount,
                ]);
            }

            $collection->update([
                'journal_entry_id' => $entry->id,
                'status' => 'posted',
            ]);

            app(AuditService::class)->log('post', 'ar_collection', $collection, null, "Posted to GL: {$entry->entry_number}");

            return $entry;
        });
    }

    /**
     * Post a disbursement payment to the General Ledger.
     * DR: Expense accounts (from disbursement items)
     * CR: Cash/Bank
     */
    public function postDisbursement(DisbursementPayment $payment): JournalEntry
    {
        return DB::transaction(function () use ($payment) {
            $payment->load('disbursement.items');

            $entry = JournalEntry::create([
                'entry_number' => NumberingService::generate('JE'),
                'entry_date' => $payment->payment_date,
                'posting_date' => $payment->payment_date,
                'reference_number' => $payment->voucher_number,
                'journal_type' => self::JOURNAL_TYPE_MAP['CDJ'],
                'description' => "Disbursement: {$payment->voucher_number} - {$payment->disbursement->payee_name}",
                'source_module' => 'DISB',
                'source_id' => $payment->id,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            $lineNumber = 1;

            // Debit expense accounts from items
            foreach ($payment->disbursement->items as $item) {
                $expenseAccount = null;
                if ($item->account_code) {
                    $expenseAccount = ChartOfAccount::where('account_code', $item->account_code)->first();
                }
                $expenseAccount = $expenseAccount ?? ChartOfAccount::where('account_type', 'expense')->first();

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => $lineNumber++,
                    'account_id' => $expenseAccount->id,
                    'description' => $item->description,
                    'debit' => $item->amount,
                    'credit' => 0,
                    'department_id' => $payment->disbursement->department_id,
                    'cost_center_id' => $payment->disbursement->cost_center_id,
                ]);
            }

            // Credit WHT Payable if applicable
            if (($payment->withholding_tax ?? 0) > 0) {
                $whtAccount = ChartOfAccount::where('account_code', 'like', '2060%')->first();
                if ($whtAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'line_number' => $lineNumber++,
                        'account_id' => $whtAccount->id,
                        'description' => 'Withholding Tax Payable',
                        'debit' => 0,
                        'credit' => $payment->withholding_tax,
                    ]);
                }
            }

            // Credit Cash/Bank
            $cashAccount = ChartOfAccount::where('account_code', 'like', '1010%')->first()
                ?? ChartOfAccount::where('account_type', 'asset')->where('account_code', 'like', '1%')->first();

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'line_number' => $lineNumber++,
                'account_id' => $cashAccount->id,
                'description' => "Payment - {$payment->payment_method}",
                'debit' => 0,
                'credit' => $payment->net_amount,
            ]);

            app(AuditService::class)->log('post', 'disbursement_payment', $payment, null, "Posted to GL: {$entry->entry_number}");

            return $entry;
        });
    }

    /**
     * Create a reversing journal entry with opposite debits/credits.
     */
    public function reverseEntry(JournalEntry $entry): JournalEntry
    {
        return DB::transaction(function () use ($entry) {
            $entry->load('lines');

            $reversal = JournalEntry::create([
                'entry_number' => NumberingService::generate('JE'),
                'entry_date' => now(),
                'posting_date' => now(),
                'reference_number' => "REV-{$entry->entry_number}",
                'journal_type' => $entry->journal_type,
                'description' => "Reversal of {$entry->entry_number}: {$entry->description}",
                'source_module' => $entry->source_module,
                'source_id' => $entry->source_id,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            foreach ($entry->lines as $i => $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversal->id,
                    'line_number' => $i + 1,
                    'account_id' => $line->account_id,
                    'description' => "(Reversal) {$line->description}",
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                    'department_id' => $line->department_id,
                    'cost_center_id' => $line->cost_center_id,
                    'project' => $line->project,
                    'fund_source_id' => $line->fund_source_id,
                    'payee_type' => $line->payee_type,
                    'payee_id' => $line->payee_id,
                ]);
            }

            $entry->update(['status' => 'reversed']);

            app(AuditService::class)->log('reverse', 'journal_entry', $entry, null, "Reversed by {$reversal->entry_number}");

            return $reversal;
        });
    }
}
