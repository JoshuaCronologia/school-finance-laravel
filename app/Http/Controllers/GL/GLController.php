<?php

namespace App\Http\Controllers\GL;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GLController extends Controller
{
    /**
     * Detailed ledger inquiry per account with running balance.
     */
    public function ledgerInquiry(Request $request)
    {
        $accounts = ChartOfAccount::active()->orderBy('account_code')->get();
        $selectedAccount = null;
        $ledgerEntries = collect();
        $openingBalance = 0;
        $totalDebits = 0;
        $totalCredits = 0;

        if ($request->filled('account_id')) {
            $selectedAccount = ChartOfAccount::with('parent')->find($request->account_id);

            if ($selectedAccount) {
                $dateFrom = $request->input('date_from') ?: now()->startOfYear()->toDateString();
                $dateTo = $request->input('date_to') ?: now()->toDateString();

                // Opening balance
                $openingBalance = JournalEntryLine::select(
                        DB::raw('COALESCE(SUM(journal_entry_lines.debit), 0) - COALESCE(SUM(journal_entry_lines.credit), 0) as balance')
                    )
                    ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_entry_lines.account_id', $selectedAccount->id)
                    ->where('journal_entries.status', 'posted')
                    ->whereDate('journal_entries.posting_date', '<', $dateFrom)
                    ->value('balance') ?? 0;

                if ($selectedAccount->normal_balance === 'credit') {
                    $openingBalance = -$openingBalance;
                }

                // JE entries in date range with full details
                $jeEntries = JournalEntryLine::select(
                        'journal_entry_lines.*',
                        'journal_entries.entry_number',
                        'journal_entries.entry_date',
                        'journal_entries.posting_date',
                        'journal_entries.reference_number',
                        'journal_entries.journal_type',
                        'journal_entries.description as je_description',
                        'journal_entries.source_module',
                        'journal_entries.source_id'
                    )
                    ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_entry_lines.account_id', $selectedAccount->id)
                    ->where('journal_entries.status', 'posted')
                    ->whereDate('journal_entries.posting_date', '>=', $dateFrom)
                    ->whereDate('journal_entries.posting_date', '<=', $dateTo)
                    ->orderBy('journal_entries.posting_date')
                    ->orderBy('journal_entries.entry_number')
                    ->get();

                $ledgerEntries = $jeEntries->map(function ($e) {
                    $e->source_type = 'JE';
                    return $e;
                });

                // Merge finance fee collections if this is a mapped revenue account
                try {
                    $feeEntries = \App\Services\FinanceFeeService::glEntries($dateFrom, $dateTo, $selectedAccount->id);
                    if ($feeEntries->isNotEmpty() && $feeEntries->has($selectedAccount->id)) {
                        foreach ($feeEntries->get($selectedAccount->id) as $fee) {
                            $fee->source_type = 'FEE';
                            $ledgerEntries->push($fee);
                        }
                    }
                } catch (\Exception $e) {}

                // Sort merged entries by date
                $ledgerEntries = $ledgerEntries->sortBy('posting_date')->values();

                // Calculate running balance + totals
                $runningBalance = (float) $openingBalance;
                $ledgerEntries = $ledgerEntries->map(function ($entry) use (&$runningBalance, $selectedAccount, &$totalDebits, &$totalCredits) {
                    $debit = (float) ($entry->debit ?? 0);
                    $credit = (float) ($entry->credit ?? 0);
                    $totalDebits += $debit;
                    $totalCredits += $credit;

                    if ($selectedAccount->normal_balance === 'debit') {
                        $runningBalance += $debit - $credit;
                    } else {
                        $runningBalance += $credit - $debit;
                    }
                    $entry->running_balance = $runningBalance;
                    return $entry;
                });
            }
        }

        $closingBalance = $openingBalance + ($selectedAccount && $selectedAccount->normal_balance === 'debit' ? $totalDebits - $totalCredits : $totalCredits - $totalDebits);

        $ledgerData = $ledgerEntries;

        return view('pages.gl.ledger-inquiry', compact(
            'accounts', 'selectedAccount', 'ledgerEntries', 'ledgerData',
            'openingBalance', 'closingBalance', 'totalDebits', 'totalCredits'
        ));
    }
}
