<?php

namespace App\Http\Controllers\GL;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankStatementItem;
use App\Models\ChartOfAccount;
use App\Models\IssuedCheck;
use App\Models\JournalEntryLine;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankReconciliationController extends Controller
{
    /**
     * Main page — tabbed: Reconciliation, Bank Accounts, Issued Checks, CIB
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'reconcile');
        $bankAccounts = BankAccount::with('chartAccount')->where('is_active', true)->orderBy('bank_name')->get();

        $data = compact('tab', 'bankAccounts');

        switch ($tab) {
            case 'accounts':
                $data['allBankAccounts'] = BankAccount::with('chartAccount')->orderBy('bank_name')->get();
                $data['coaAccounts'] = ChartOfAccount::where('account_type', 'asset')
                    ->where('account_code', '>=', '1010')
                    ->where('account_code', '<=', '1099')
                    ->orderBy('account_code')->get();
                break;

            case 'checks':
                $query = IssuedCheck::with('bankAccount')->orderByDesc('check_date');
                if ($request->filled('bank_account_id')) {
                    $query->where('bank_account_id', $request->bank_account_id);
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                $data['checks'] = $query->paginate(25);
                $data['selectedBankId'] = $request->bank_account_id;
                $data['selectedStatus'] = $request->status;
                break;

            case 'cib':
                $bankAccountId = $request->input('bank_account_id');
                $data['selectedBankId'] = $bankAccountId;
                $data['cibData'] = null;

                if ($bankAccountId) {
                    $bankAcct = BankAccount::with('chartAccount')->find($bankAccountId);
                    if ($bankAcct) {
                        $dateFrom = $request->input('date_from') ?: now()->startOfMonth()->toDateString();
                        $dateTo = $request->input('date_to') ?: now()->toDateString();
                        $data['dateFrom'] = $dateFrom;
                        $data['dateTo'] = $dateTo;

                        // Get all JE transactions for this bank's COA account
                        $transactions = JournalEntryLine::select(
                                'journal_entry_lines.*',
                                'je.entry_number', 'je.posting_date', 'je.reference_number',
                                'je.description as je_description'
                            )
                            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
                            ->where('je.status', 'posted')
                            ->where('journal_entry_lines.account_id', $bankAcct->chart_account_id)
                            ->whereDate('je.posting_date', '>=', $dateFrom)
                            ->whereDate('je.posting_date', '<=', $dateTo)
                            ->orderBy('je.posting_date')
                            ->get();

                        // Opening balance
                        $openingBalance = (float) JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
                            ->where('je.status', 'posted')
                            ->where('journal_entry_lines.account_id', $bankAcct->chart_account_id)
                            ->whereDate('je.posting_date', '<', $dateFrom)
                            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

                        // Cleared vs outstanding check summary
                        $clearedChecks = IssuedCheck::where('bank_account_id', $bankAccountId)->where('status', 'cleared')->sum('amount');
                        $outstandingChecks = IssuedCheck::where('bank_account_id', $bankAccountId)->where('status', 'outstanding')->sum('amount');

                        $data['cibData'] = (object) [
                            'bank_account' => $bankAcct,
                            'transactions' => $transactions,
                            'opening_balance' => $openingBalance,
                            'total_debit' => $transactions->sum('debit'),
                            'total_credit' => $transactions->sum('credit'),
                            'closing_balance' => $openingBalance + $transactions->sum('debit') - $transactions->sum('credit'),
                            'cleared_checks' => $clearedChecks,
                            'outstanding_checks' => $outstandingChecks,
                        ];
                    }
                }
                break;

            case 'statements':
                $bankAccountId = $request->input('bank_account_id');
                $data['selectedBankId'] = $bankAccountId;
                $query = BankStatement::with('bankAccount')->orderByDesc('statement_date');
                if ($bankAccountId) {
                    $query->where('bank_account_id', $bankAccountId);
                }
                $data['statements'] = $query->paginate(20);
                break;

            default: // reconcile
                $accountId = $request->input('bank_account_id');
                $asOfDate = $request->input('as_of_date', now()->toDateString());
                $statementBalance = $request->input('statement_balance');
                $data['reconData'] = null;
                $data['selectedBankId'] = $accountId;
                $data['asOfDate'] = $asOfDate;
                $data['statementBalance'] = $statementBalance;

                if ($accountId) {
                    $bankAcct = BankAccount::with('chartAccount')->find($accountId);
                    if ($bankAcct) {
                        $chartAccountId = $bankAcct->chart_account_id;

                        $bookBalance = (float) JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
                            ->where('je.status', 'posted')
                            ->whereDate('je.posting_date', '<=', $asOfDate)
                            ->where('journal_entry_lines.account_id', $chartAccountId)
                            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

                        // Outstanding checks (from issued_checks table)
                        $outstandingChecks = IssuedCheck::where('bank_account_id', $accountId)
                            ->where('status', 'outstanding')
                            ->whereDate('check_date', '<=', $asOfDate)
                            ->orderBy('check_date')
                            ->get();

                        // Deposits in transit (debits in last 3 days)
                        $depositsInTransit = JournalEntryLine::select('journal_entry_lines.*', 'je.entry_number', 'je.posting_date', 'je.description as je_description')
                            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
                            ->where('je.status', 'posted')
                            ->where('journal_entry_lines.account_id', $chartAccountId)
                            ->where('journal_entry_lines.debit', '>', 0)
                            ->whereDate('je.posting_date', '>=', \Carbon\Carbon::parse($asOfDate)->subDays(3))
                            ->whereDate('je.posting_date', '<=', $asOfDate)
                            ->get();

                        $totalOutstandingChecks = (float) $outstandingChecks->sum('amount');
                        $totalDepositsTransit = (float) $depositsInTransit->sum('debit');

                        $bankStatementBal = $statementBalance !== null ? (float) $statementBalance : null;
                        $adjustedBankBalance = $bankStatementBal !== null
                            ? $bankStatementBal + $totalDepositsTransit - $totalOutstandingChecks
                            : null;

                        $data['reconData'] = (object) [
                            'bank_account' => $bankAcct,
                            'book_balance' => $bookBalance,
                            'bank_statement_balance' => $bankStatementBal,
                            'deposits_in_transit' => $depositsInTransit,
                            'total_deposits_transit' => $totalDepositsTransit,
                            'outstanding_checks' => $outstandingChecks,
                            'total_outstanding_checks' => $totalOutstandingChecks,
                            'adjusted_bank_balance' => $adjustedBankBalance,
                            'difference' => $adjustedBankBalance !== null ? $adjustedBankBalance - $bookBalance : null,
                        ];
                    }
                }
                break;
        }

        return view('pages.gl.bank-reconciliation', $data);
    }

    /**
     * Store a new bank account.
     */
    public function storeBankAccount(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_type' => 'required|in:SA,CA',
            'account_number' => 'nullable|string|max:50',
            'account_label' => 'required|string|max:255',
            'chart_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        BankAccount::create($validated);

        return back()->with('success', 'Bank account created.');
    }

    /**
     * Store a new issued check (manual entry).
     */
    public function storeCheck(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'check_date' => 'required|date',
            'check_number' => 'required|string|max:50',
            'payee' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string',
        ]);

        $validated['status'] = 'outstanding';
        IssuedCheck::create($validated);

        return back()->with('success', 'Check recorded.');
    }

    /**
     * Mark check as cleared.
     */
    public function clearCheck(Request $request, IssuedCheck $check)
    {
        $validated = $request->validate([
            'cleared_date' => 'required|date',
        ]);

        $check->update([
            'status' => 'cleared',
            'cleared_date' => $validated['cleared_date'],
        ]);

        return back()->with('success', "Check #{$check->check_number} marked as cleared.");
    }

    /**
     * Mark check as voided.
     */
    public function voidCheck(IssuedCheck $check)
    {
        $check->update(['status' => 'voided']);
        return back()->with('success', "Check #{$check->check_number} voided.");
    }

    /**
     * Upload bank statement.
     */
    public function uploadStatement(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'statement_date' => 'required|date',
            'opening_balance' => 'required|numeric',
            'closing_balance' => 'required|numeric',
            'file' => 'required|file|max:5120',
        ]);

        $file = $request->file('file');
        $fileName = 'statement_' . $validated['bank_account_id'] . '_' . $validated['statement_date'] . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('bank-statements', $fileName, 'public');

        $statement = BankStatement::create([
            'bank_account_id' => $validated['bank_account_id'],
            'statement_date' => $validated['statement_date'],
            'period_label' => \Carbon\Carbon::parse($validated['statement_date'])->format('F Y'),
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'opening_balance' => $validated['opening_balance'],
            'closing_balance' => $validated['closing_balance'],
            'uploaded_by' => auth()->id(),
        ]);

        // Parse CSV if applicable
        if (in_array($file->getClientOriginalExtension(), ['csv', 'txt'])) {
            $this->parseCsvStatement($statement, $file->getRealPath());
        }

        // Auto-reconcile: match statement items against outstanding checks
        $autoCleared = $this->autoReconcile($statement);

        $totalItems = $statement->items()->count();
        $msg = "Bank statement uploaded. {$totalItems} transactions parsed.";
        if ($autoCleared > 0) {
            $msg .= " {$autoCleared} check(s) auto-matched and cleared.";
        }
        $unmatched = $totalItems - $autoCleared;
        if ($unmatched > 0) {
            $msg .= " {$unmatched} unmatched (subject to manual recon).";
        }

        return back()->with('success', $msg);
    }

    /**
     * Parse CSV bank statement into line items.
     */
    private function parseCsvStatement(BankStatement $statement, string $filePath)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) return;

        $header = fgetcsv($handle); // skip header row
        $totalDebit = 0;
        $totalCredit = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) continue;

            $date = $row[0] ?? '';
            $description = $row[1] ?? '';
            $debit = abs((float) str_replace(',', '', $row[2] ?? 0));
            $credit = abs((float) str_replace(',', '', $row[3] ?? 0));
            $balance = isset($row[4]) ? (float) str_replace(',', '', $row[4]) : 0;
            $reference = $row[5] ?? null;

            // Try to parse date
            try {
                $txnDate = \Carbon\Carbon::parse($date)->toDateString();
            } catch (\Exception $e) {
                continue;
            }

            BankStatementItem::create([
                'bank_statement_id' => $statement->id,
                'transaction_date' => $txnDate,
                'description' => $description,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $balance,
                'reference_number' => $reference,
            ]);

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        fclose($handle);

        $statement->update([
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ]);
    }

    /**
     * Auto-reconcile: match bank statement items against outstanding checks by check number.
     */
    private function autoReconcile(BankStatement $statement): int
    {
        $bankAccountId = $statement->bank_account_id;

        // Get all outstanding checks for this bank
        $outstandingChecks = IssuedCheck::where('bank_account_id', $bankAccountId)
            ->where('status', 'outstanding')
            ->get();

        if ($outstandingChecks->isEmpty()) {
            return 0;
        }

        $cleared = 0;

        foreach ($statement->items as $item) {
            if ($item->is_matched) continue;

            // Try to match by check number in description or reference
            $searchText = strtolower($item->description . ' ' . ($item->reference_number ?? ''));

            foreach ($outstandingChecks as $check) {
                if ($check->status !== 'outstanding') continue;

                $checkNum = strtolower($check->check_number);

                // Match if check number appears in description or reference
                if (strpos($searchText, $checkNum) !== false) {
                    // Also verify amount matches (within 0.01 tolerance)
                    if (abs($item->credit - (float) $check->amount) < 0.01 || abs($item->debit - (float) $check->amount) < 0.01) {
                        // Match found — clear the check
                        $check->update([
                            'status' => 'cleared',
                            'cleared_date' => $item->transaction_date,
                        ]);

                        $item->update([
                            'is_matched' => true,
                            'matched_check_id' => $check->id,
                        ]);

                        $cleared++;
                        break;
                    }
                }
            }
        }

        return $cleared;
    }

    /**
     * View bank statement details with manual match UI.
     */
    public function viewStatement(BankStatement $statement)
    {
        $statement->load('bankAccount', 'items');
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('bank_name')->get();

        // Outstanding checks for this bank (for manual matching dropdown)
        $outstandingChecks = IssuedCheck::where('bank_account_id', $statement->bank_account_id)
            ->where('status', 'outstanding')
            ->orderBy('check_date')
            ->get();

        // Summary totals
        $totalCleared = $statement->items->where('is_matched', true)->sum(function ($i) {
            return max($i->debit, $i->credit);
        });
        $totalUnmatched = $statement->items->where('is_matched', false)->sum(function ($i) {
            return max($i->debit, $i->credit);
        });

        // Balance check: bank statement closing balance vs book balance
        $bookBalance = (float) JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '<=', $statement->statement_date)
            ->where('journal_entry_lines.account_id', $statement->bankAccount->chart_account_id)
            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

        $outstandingTotal = (float) IssuedCheck::where('bank_account_id', $statement->bank_account_id)
            ->where('status', 'outstanding')
            ->sum('amount');

        $adjustedBankBalance = (float) $statement->closing_balance - $outstandingTotal;
        $difference = $adjustedBankBalance - $bookBalance;

        $tab = 'statement-detail';

        return view('pages.gl.bank-reconciliation', compact(
            'statement', 'bankAccounts', 'tab', 'outstandingChecks',
            'totalCleared', 'totalUnmatched', 'bookBalance', 'adjustedBankBalance', 'difference', 'outstandingTotal'
        ));
    }

    /**
     * Manually match a statement item to an issued check.
     */
    public function manualMatch(Request $request, BankStatementItem $item)
    {
        $validated = $request->validate([
            'check_id' => 'required|exists:issued_checks,id',
        ]);

        $check = IssuedCheck::findOrFail($validated['check_id']);

        $item->update([
            'is_matched' => true,
            'matched_check_id' => $check->id,
        ]);

        $check->update([
            'status' => 'cleared',
            'cleared_date' => $item->transaction_date,
        ]);

        return back()->with('success', "Statement item matched to check #{$check->check_number} and marked as cleared.");
    }

    /**
     * Unmatch a statement item (undo match).
     */
    public function unmatch(BankStatementItem $item)
    {
        if ($item->matched_check_id) {
            $check = IssuedCheck::find($item->matched_check_id);
            if ($check) {
                $check->update(['status' => 'outstanding', 'cleared_date' => null]);
            }
        }

        $item->update(['is_matched' => false, 'matched_check_id' => null]);

        return back()->with('success', 'Match removed. Check is back to outstanding.');
    }

    /**
     * PDF export.
     */
    public function pdf(Request $request)
    {
        (new AuditService)->logActivity('exported', 'bank_reconciliation', 'Downloaded bank reconciliation PDF');
        // Reuse existing PDF logic — redirect to index with params
        return $this->index($request);
    }
}
