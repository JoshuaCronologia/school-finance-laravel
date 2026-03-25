<?php

namespace App\Http\Controllers\GL;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankReconciliationController extends Controller
{
    public function index(Request $request)
    {
        // Get cash/bank accounts (1010-1050)
        $bankAccounts = ChartOfAccount::where('account_code', '>=', '1010')
            ->where('account_code', '<=', '1050')
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        $accountId = $request->input('account_id');
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $statementBalance = $request->input('statement_balance');

        $reconData = null;

        if ($accountId) {
            $account = ChartOfAccount::findOrFail($accountId);

            // Book balance: sum of all posted debits - credits for this account up to as_of_date
            $bookBalance = (float) JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
                ->where('je.status', 'posted')
                ->whereDate('je.posting_date', '<=', $asOfDate)
                ->where('journal_entry_lines.account_id', $accountId)
                ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

            // Recent transactions for this account (last 60 days up to as_of_date)
            $dateFrom = \Carbon\Carbon::parse($asOfDate)->subDays(60)->toDateString();

            $transactions = JournalEntryLine::select(
                    'journal_entry_lines.*',
                    'je.entry_number', 'je.entry_date', 'je.posting_date',
                    'je.reference_number', 'je.description as je_description', 'je.journal_type'
                )
                ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
                ->where('je.status', 'posted')
                ->where('journal_entry_lines.account_id', $accountId)
                ->whereDate('je.posting_date', '>=', $dateFrom)
                ->whereDate('je.posting_date', '<=', $asOfDate)
                ->orderBy('je.posting_date', 'desc')
                ->get();

            // Deposits in transit (debits in last 5 days - likely not yet in bank statement)
            $depositsInTransit = $transactions->filter(function ($t) use ($asOfDate) {
                $daysAgo = \Carbon\Carbon::parse($t->posting_date)->diffInDays(\Carbon\Carbon::parse($asOfDate));
                return $t->debit > 0 && $daysAgo <= 3;
            });

            // Outstanding checks (credits in last 30 days)
            $outstandingChecks = $transactions->filter(function ($t) use ($asOfDate) {
                $daysAgo = \Carbon\Carbon::parse($t->posting_date)->diffInDays(\Carbon\Carbon::parse($asOfDate));
                return $t->credit > 0 && $daysAgo <= 15;
            });

            $totalDepositsInTransit = $depositsInTransit->sum('debit');
            $totalOutstandingChecks = $outstandingChecks->sum('credit');

            // Adjusted bank balance = statement balance + deposits in transit - outstanding checks
            $bankStatementBal = $statementBalance !== null ? (float) $statementBalance : null;
            $adjustedBankBalance = $bankStatementBal !== null
                ? $bankStatementBal + $totalDepositsInTransit - $totalOutstandingChecks
                : null;

            // Difference
            $difference = $adjustedBankBalance !== null ? $adjustedBankBalance - $bookBalance : null;

            $reconData = (object) [
                'account'                 => $account,
                'book_balance'            => $bookBalance,
                'bank_statement_balance'  => $bankStatementBal,
                'deposits_in_transit'     => $depositsInTransit,
                'total_deposits_transit'  => $totalDepositsInTransit,
                'outstanding_checks'      => $outstandingChecks,
                'total_outstanding_checks' => $totalOutstandingChecks,
                'adjusted_bank_balance'   => $adjustedBankBalance,
                'difference'              => $difference,
                'transactions'            => $transactions,
            ];
        }

        return view('pages.gl.bank-reconciliation', compact('bankAccounts', 'accountId', 'asOfDate', 'statementBalance', 'reconData'));
    }

    public function pdf(Request $request)
    {
        // Reuse index logic
        $bankAccounts = ChartOfAccount::where('account_code', '>=', '1010')
            ->where('account_code', '<=', '1050')
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        $accountId = $request->input('account_id');
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $statementBalance = (float) $request->input('statement_balance', 0);

        if (!$accountId) {
            return back()->with('error', 'Please select a bank account first.');
        }

        $account = ChartOfAccount::findOrFail($accountId);

        $bookBalance = (float) JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '<=', $asOfDate)
            ->where('journal_entry_lines.account_id', $accountId)
            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

        $dateFrom = \Carbon\Carbon::parse($asOfDate)->subDays(60)->toDateString();

        $transactions = JournalEntryLine::select(
                'journal_entry_lines.*',
                'je.entry_number', 'je.posting_date', 'je.reference_number', 'je.description as je_description'
            )
            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->where('je.status', 'posted')
            ->where('journal_entry_lines.account_id', $accountId)
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $asOfDate)
            ->orderBy('je.posting_date', 'desc')
            ->get();

        $depositsInTransit = $transactions->filter(fn($t) => $t->debit > 0 && \Carbon\Carbon::parse($t->posting_date)->diffInDays(\Carbon\Carbon::parse($asOfDate)) <= 3);
        $outstandingChecks = $transactions->filter(fn($t) => $t->credit > 0 && \Carbon\Carbon::parse($t->posting_date)->diffInDays(\Carbon\Carbon::parse($asOfDate)) <= 15);

        $totalDepositsTransit = $depositsInTransit->sum('debit');
        $totalOutstandingChecks = $outstandingChecks->sum('credit');
        $adjustedBankBalance = $statementBalance + $totalDepositsTransit - $totalOutstandingChecks;
        $difference = $adjustedBankBalance - $bookBalance;

        $data = [
            'account' => $account,
            'asOfDate' => $asOfDate,
            'bookBalance' => $bookBalance,
            'statementBalance' => $statementBalance,
            'depositsInTransit' => $depositsInTransit,
            'totalDepositsTransit' => $totalDepositsTransit,
            'outstandingChecks' => $outstandingChecks,
            'totalOutstandingChecks' => $totalOutstandingChecks,
            'adjustedBankBalance' => $adjustedBankBalance,
            'difference' => $difference,
            'printedAt' => now()->format('F d, Y h:i A'),
        ];

        $pdf = Pdf::loadView('pages.gl.pdf-bank-reconciliation', $data)->setPaper('letter', 'portrait');

        return $pdf->download("Bank-Recon-{$account->account_code}-" . now()->format('Y-m-d') . ".pdf");
    }
}
