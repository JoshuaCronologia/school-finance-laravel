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

        if ($request->filled('account_id')) {
            $selectedAccount = ChartOfAccount::find($request->account_id);

            if ($selectedAccount) {
                $dateFrom = $request->input('date_from', now()->startOfYear()->toDateString());
                $dateTo = $request->input('date_to', now()->toDateString());

                // Calculate opening balance (all posted entries before date_from)
                $openingBalance = JournalEntryLine::select(
                        DB::raw('COALESCE(SUM(journal_entry_lines.debit), 0) - COALESCE(SUM(journal_entry_lines.credit), 0) as balance')
                    )
                    ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_entry_lines.account_id', $selectedAccount->id)
                    ->where('journal_entries.status', 'posted')
                    ->whereDate('journal_entries.posting_date', '<', $dateFrom)
                    ->value('balance') ?? 0;

                // For credit-normal accounts, flip the sign
                if ($selectedAccount->normal_balance === 'credit') {
                    $openingBalance = -$openingBalance;
                }

                // Get ledger entries in date range
                $ledgerEntries = JournalEntryLine::select(
                        'journal_entry_lines.*',
                        'journal_entries.entry_number',
                        'journal_entries.entry_date',
                        'journal_entries.posting_date',
                        'journal_entries.reference_number',
                        'journal_entries.journal_type',
                        'journal_entries.description as je_description'
                    )
                    ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_entry_lines.account_id', $selectedAccount->id)
                    ->where('journal_entries.status', 'posted')
                    ->whereDate('journal_entries.posting_date', '>=', $dateFrom)
                    ->whereDate('journal_entries.posting_date', '<=', $dateTo)
                    ->orderBy('journal_entries.posting_date')
                    ->orderBy('journal_entries.entry_number')
                    ->get();

                // Calculate running balance
                $runningBalance = (float) $openingBalance;
                $ledgerEntries = $ledgerEntries->map(function ($entry) use (&$runningBalance, $selectedAccount) {
                    if ($selectedAccount->normal_balance === 'debit') {
                        $runningBalance += (float) $entry->debit - (float) $entry->credit;
                    } else {
                        $runningBalance += (float) $entry->credit - (float) $entry->debit;
                    }
                    $entry->running_balance = $runningBalance;
                    return $entry;
                });
            }
        }

        return view('pages.gl.ledger-inquiry', compact(
            'accounts', 'selectedAccount', 'ledgerEntries', 'openingBalance'
        ));
    }
}
