<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Trial Balance - sums of debits and credits per account for posted entries.
     */
    public function trialBalance(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $accounts = ChartOfAccount::select(
                'chart_of_accounts.id',
                'chart_of_accounts.account_code',
                'chart_of_accounts.account_name',
                'chart_of_accounts.account_type',
                'chart_of_accounts.normal_balance'
            )
            ->selectRaw('COALESCE(SUM(jel.debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(jel.credit), 0) as total_credit')
            ->selectRaw('COALESCE(SUM(jel.debit), 0) - COALESCE(SUM(jel.credit), 0) as net_balance')
            ->leftJoin('journal_entry_lines as jel', 'chart_of_accounts.id', '=', 'jel.account_id')
            ->leftJoin('journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('jel.journal_entry_id', '=', 'je.id')
                     ->where('je.status', '=', 'posted')
                     ->whereDate('je.posting_date', '<=', $asOfDate);
            })
            ->groupBy(
                'chart_of_accounts.id',
                'chart_of_accounts.account_code',
                'chart_of_accounts.account_name',
                'chart_of_accounts.account_type',
                'chart_of_accounts.normal_balance'
            )
            ->orderBy('chart_of_accounts.account_code')
            ->get()
            ->filter(fn($a) => $a->total_debit > 0 || $a->total_credit > 0);

        $totalDebit = $accounts->sum('total_debit');
        $totalCredit = $accounts->sum('total_credit');
        $isBalanced = round($totalDebit, 2) === round($totalCredit, 2);

        return view('pages.reports.trial-balance', compact(
            'accounts', 'totalDebit', 'totalCredit', 'isBalanced', 'asOfDate'
        ));
    }

    /**
     * Balance Sheet - Assets, Liabilities, Equity with Net Income.
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $balances = $this->getAccountBalances($asOfDate);

        $assets = $balances->where('account_type', 'asset');
        $liabilities = $balances->where('account_type', 'liability');
        $equity = $balances->where('account_type', 'equity');

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum(fn($a) => abs($a->balance));
        $totalEquity = $equity->sum(fn($a) => abs($a->balance));

        // Net Income = Revenue - Expenses
        $revenue = $balances->where('account_type', 'revenue')->sum(fn($a) => abs($a->balance));
        $expenses = $balances->where('account_type', 'expense')->sum('balance');
        $netIncome = $revenue - $expenses;

        $totalEquityWithNI = $totalEquity + $netIncome;

        return view('pages.reports.balance-sheet', compact(
            'assets', 'liabilities', 'equity',
            'totalAssets', 'totalLiabilities', 'totalEquity',
            'netIncome', 'totalEquityWithNI', 'asOfDate'
        ));
    }

    /**
     * Income Statement - Revenue, Expenses, Net Income with percentages.
     */
    public function incomeStatement(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfYear()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $revenueAccounts = $this->getAccountBalancesForPeriod('revenue', $dateFrom, $dateTo);
        $expenseAccounts = $this->getAccountBalancesForPeriod('expense', $dateFrom, $dateTo);

        $totalRevenue = $revenueAccounts->sum(fn($a) => abs($a->balance));
        $totalExpenses = $expenseAccounts->sum('balance');
        $netIncome = $totalRevenue - $totalExpenses;
        $netIncomeMargin = $totalRevenue > 0 ? ($netIncome / $totalRevenue) * 100 : 0;

        // Add percentage to each account
        $revenueAccounts = $revenueAccounts->map(function ($a) use ($totalRevenue) {
            $a->percentage = $totalRevenue > 0 ? (abs($a->balance) / $totalRevenue) * 100 : 0;
            return $a;
        });

        $expenseAccounts = $expenseAccounts->map(function ($a) use ($totalRevenue) {
            $a->percentage = $totalRevenue > 0 ? ($a->balance / $totalRevenue) * 100 : 0;
            return $a;
        });

        return view('pages.reports.income-statement', compact(
            'revenueAccounts', 'expenseAccounts',
            'totalRevenue', 'totalExpenses', 'netIncome', 'netIncomeMargin',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Cash Flow Statement - Operating, Investing, Financing activities.
     */
    public function cashFlow(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfYear()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        // Operating: Net Income + non-cash adjustments + changes in working capital
        $netIncome = $this->calculateNetIncome($dateFrom, $dateTo);

        // Cash received from customers (CR to AR accounts)
        $cashFromCustomers = JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo)
            ->where('coa.account_type', 'asset')
            ->where('coa.account_code', 'like', '1010%')
            ->where('je.journal_type', 'revenue')
            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

        // Cash paid to suppliers (DR to AP accounts)
        $cashToSuppliers = JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo)
            ->where('coa.account_type', 'asset')
            ->where('coa.account_code', 'like', '1010%')
            ->where('je.journal_type', 'expense')
            ->sum(DB::raw('journal_entry_lines.credit - journal_entry_lines.debit'));

        $operatingCashFlow = $cashFromCustomers - $cashToSuppliers;

        // Net change in cash
        $beginningCash = $this->getCashBalance($dateFrom);
        $endingCash = $this->getCashBalance($dateTo);
        $netCashChange = $endingCash - $beginningCash;

        return view('pages.reports.cash-flow', compact(
            'netIncome', 'cashFromCustomers', 'cashToSuppliers',
            'operatingCashFlow', 'beginningCash', 'endingCash', 'netCashChange',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * General Ledger - Full detail of all posted entries.
     */
    public function generalLedger(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $accountId = $request->input('account_id');

        $allAccounts = ChartOfAccount::active()->orderBy('account_code')->get();

        $query = JournalEntryLine::select(
                'journal_entry_lines.*',
                'je.entry_number', 'je.entry_date', 'je.posting_date',
                'je.reference_number', 'je.journal_type', 'je.description as je_description',
                'coa.account_code', 'coa.account_name', 'coa.account_type', 'coa.normal_balance'
            )
            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo);

        if ($accountId) {
            $query->where('journal_entry_lines.account_id', $accountId);
        }

        $entries = $query->orderBy('coa.account_code')
            ->orderBy('je.posting_date')
            ->get()
            ->groupBy('account_id');

        // Batch-fetch all opening balances in 1 query instead of N queries
        $accountIds = $entries->keys()->all();
        $openingBalances = [];
        if (!empty($accountIds)) {
            $rows = JournalEntryLine::select(
                    'journal_entry_lines.account_id',
                    DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as balance')
                )
                ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
                ->where('je.status', 'posted')
                ->whereDate('je.posting_date', '<', $dateFrom)
                ->whereIn('journal_entry_lines.account_id', $accountIds)
                ->groupBy('journal_entry_lines.account_id')
                ->get();

            foreach ($rows as $row) {
                $openingBalances[$row->account_id] = (float) $row->balance;
            }
        }

        // Build structured account data with opening balance and transactions
        $accounts = collect();
        foreach ($entries as $acctId => $transactions) {
            $first = $transactions->first();

            $openingBalance = $openingBalances[$acctId] ?? 0.0;

            // For credit-normal accounts, flip the sign
            if ($first->normal_balance === 'credit') {
                $openingBalance = -$openingBalance;
            }

            $totalDebit = $transactions->sum('debit');
            $totalCredit = $transactions->sum('credit');

            $account = (object) [
                'account_code'   => $first->account_code,
                'account_name'   => $first->account_name,
                'account_type'   => $first->account_type,
                'normal_balance' => $first->normal_balance,
                'opening_balance' => $openingBalance,
                'transactions'   => $transactions,
                'total_debit'    => $totalDebit,
                'total_credit'   => $totalCredit,
                'ending_balance' => 0,
            ];

            // Calculate ending balance
            if ($first->normal_balance === 'debit') {
                $account->ending_balance = $openingBalance + $totalDebit - $totalCredit;
            } else {
                $account->ending_balance = $openingBalance + $totalCredit - $totalDebit;
            }

            $accounts->push($account);
        }

        return view('pages.reports.general-ledger', compact(
            'accounts', 'allAccounts', 'dateFrom', 'dateTo', 'accountId'
        ));
    }

    /**
     * Expense schedule by category.
     */
    public function expenseSchedule(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfYear()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $expenses = JournalEntryLine::select(
                'coa.account_code', 'coa.account_name',
                DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as total')
            )
            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('coa.account_type', 'expense')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo)
            ->groupBy('coa.id', 'coa.account_code', 'coa.account_name')
            ->orderBy('coa.account_code')
            ->get();

        $totalExpenses = $expenses->sum('total');

        // Add percentages
        $expenses = $expenses->map(function ($e) use ($totalExpenses) {
            $e->percentage = $totalExpenses > 0 ? ($e->total / $totalExpenses) * 100 : 0;
            return $e;
        });

        return view('pages.reports.expense-schedule', compact('expenses', 'totalExpenses', 'dateFrom', 'dateTo'));
    }

    /**
     * General Journal - all posted journal entries.
     */
    public function generalJournal(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $entries = \App\Models\JournalEntry::with('lines.account')
            ->where('status', 'posted')
            ->whereDate('posting_date', '>=', $dateFrom)
            ->whereDate('posting_date', '<=', $dateTo)
            ->orderBy('posting_date')
            ->orderBy('entry_number')
            ->get();

        $totalDebit = $entries->sum(fn($e) => $e->lines->sum('debit'));
        $totalCredit = $entries->sum(fn($e) => $e->lines->sum('credit'));

        return view('pages.reports.general-journal', compact('entries', 'dateFrom', 'dateTo', 'totalDebit', 'totalCredit'));
    }

    /**
     * Cash Receipts Book - journal entries that debit cash/bank accounts.
     */
    public function cashReceiptsBook(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $entries = JournalEntryLine::select(
                'journal_entry_lines.*',
                'je.entry_number', 'je.posting_date', 'je.reference_number',
                'je.description as je_description', 'je.journal_type',
                'coa.account_code', 'coa.account_name'
            )
            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo)
            ->where('coa.account_code', '>=', '1010')
            ->where('coa.account_code', '<=', '1050')
            ->where('journal_entry_lines.debit', '>', 0)
            ->orderBy('je.posting_date')
            ->get();

        $totalAmount = $entries->sum('debit');

        return view('pages.reports.cash-receipts-book', compact('entries', 'dateFrom', 'dateTo', 'totalAmount'));
    }

    /**
     * Cash Disbursements Book - journal entries that credit cash/bank accounts.
     */
    public function cashDisbursementsBook(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $entries = JournalEntryLine::select(
                'journal_entry_lines.*',
                'je.entry_number', 'je.posting_date', 'je.reference_number',
                'je.description as je_description', 'je.journal_type',
                'coa.account_code', 'coa.account_name'
            )
            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo)
            ->where('coa.account_code', '>=', '1010')
            ->where('coa.account_code', '<=', '1050')
            ->where('journal_entry_lines.credit', '>', 0)
            ->orderBy('je.posting_date')
            ->get();

        $totalAmount = $entries->sum('credit');

        return view('pages.reports.cash-disbursements-book', compact('entries', 'dateFrom', 'dateTo', 'totalAmount'));
    }

    /**
     * Budget Performance Report – Institutional or per Department.
     * Columns: Approved Budget, Actual Expense, Variance (B-A), Variance %.
     */
    public function budgetPerformance(Request $request)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $schoolYear = $request->input('school_year', '2025-2026');
        $departmentId = $request->input('department_id');
        $asOfDate = $request->input('as_of_date', now()->format('Y-m-d'));

        // Calculate months elapsed (school year starts July)
        $asOfCarbon = \Carbon\Carbon::parse($asOfDate);
        $syStart = \Carbon\Carbon::parse(substr($schoolYear, 0, 4) . '-07-01');
        $monthsElapsed = $syStart->gt($asOfCarbon) ? 0 : $syStart->diffInMonths($asOfCarbon) + 1;
        $monthsElapsed = min($monthsElapsed, 12);
        $periodLabel = $monthsElapsed . ' Month' . ($monthsElapsed !== 1 ? 's' : '');

        $query = Budget::with('department', 'category')
            ->whereIn('status', ['active', 'approved'])
            ->where('school_year', $schoolYear);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $budgets = $query->get();

        $reportTitle = 'INSTITUTIONAL';
        $selectedDept = null;
        if ($departmentId) {
            $selectedDept = $departments->firstWhere('id', $departmentId);
            $reportTitle = strtoupper($selectedDept->name ?? 'DEPARTMENT');
        }

        // Group by category and aggregate
        $lineItems = $budgets->groupBy(fn ($b) => $b->category->name ?? 'Uncategorized')
            ->map(function ($group, $categoryName) {
                $approved = $group->sum('annual_budget');
                $actual = $group->sum('actual');
                $variance = $approved - $actual;
                return [
                    'category' => $categoryName,
                    'approved_budget' => $approved,
                    'actual' => $actual,
                    'variance' => $variance,
                    'variance_pct' => $approved > 0 ? ($variance / $approved) * 100 : 0,
                ];
            })
            ->sortByDesc('approved_budget')
            ->values();

        $totals = [
            'approved_budget' => $lineItems->sum('approved_budget'),
            'actual' => $lineItems->sum('actual'),
        ];
        $totals['variance'] = $totals['approved_budget'] - $totals['actual'];
        $totals['variance_pct'] = $totals['approved_budget'] > 0
            ? ($totals['variance'] / $totals['approved_budget']) * 100 : 0;

        return view('pages.reports.budget-performance', compact(
            'departments', 'schoolYear', 'departmentId', 'asOfDate',
            'reportTitle', 'selectedDept', 'lineItems', 'totals',
            'periodLabel', 'monthsElapsed'
        ));
    }

    /**
     * Budget vs Actual comparison.
     */
    public function budgetVsActual(Request $request)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $query = Budget::with('department', 'category')
            ->whereIn('status', ['active', 'approved']);

        if ($request->filled('school_year')) {
            $query->where('school_year', $request->school_year);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $budgets = $query->get()
            ->map(function ($b) {
                $b->remaining = $b->annual_budget - $b->committed - $b->actual;
                $b->utilization = $b->annual_budget > 0
                    ? (($b->committed + $b->actual) / $b->annual_budget) * 100 : 0;
                $b->variance = $b->annual_budget - $b->actual;
                $b->variance_pct = $b->annual_budget > 0
                    ? (($b->annual_budget - $b->actual) / $b->annual_budget) * 100 : 0;
                return $b;
            });

        $summary = [
            'total_budget' => $budgets->sum('annual_budget'),
            'total_committed' => $budgets->sum('committed'),
            'total_actual' => $budgets->sum('actual'),
            'total_remaining' => $budgets->sum('remaining'),
        ];
        $summary['overall_utilization'] = $summary['total_budget'] > 0
            ? (($summary['total_committed'] + $summary['total_actual']) / $summary['total_budget']) * 100 : 0;

        return view('pages.reports.budget-vs-actual', compact('budgets', 'summary', 'departments'));
    }

    /**
     * Monthly budget variance analysis.
     */
    public function monthlyVariance()
    {
        $budgets = Budget::with(['department', 'category', 'allocations'])
            ->whereIn('status', ['active', 'approved'])
            ->get();

        // 1 query for ALL 12 months instead of 12 separate queries
        $monthlyActuals = collect(DB::select("
            SELECT EXTRACT(MONTH FROM je.posting_date)::int as month,
                   COALESCE(SUM(jel.debit - jel.credit), 0) as total
            FROM journal_entry_lines jel
            JOIN journal_entries je ON jel.journal_entry_id = je.id
            JOIN chart_of_accounts coa ON jel.account_id = coa.id
            WHERE coa.account_type = 'expense'
              AND je.status = 'posted'
              AND EXTRACT(YEAR FROM je.posting_date) = ?
            GROUP BY EXTRACT(MONTH FROM je.posting_date)
        ", [now()->year]))->keyBy('month');

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $budgetedForMonth = 0;

            foreach ($budgets as $budget) {
                $alloc = $budget->allocations->firstWhere('month', $m);
                $budgetedForMonth += $alloc ? (float) $alloc->amount : ($budget->annual_budget / 12);
            }

            $actualForMonth = (float) ($monthlyActuals->get($m)->total ?? 0);

            $variance = $budgetedForMonth - $actualForMonth;
            $variancePct = $budgetedForMonth > 0 ? ($variance / $budgetedForMonth) * 100 : 0;

            $monthlyData[$m] = (object) [
                'month' => $m,
                'month_name' => date('F', mktime(0, 0, 0, $m, 1)),
                'budget' => $budgetedForMonth,
                'actual' => $actualForMonth,
                'variance' => $variance,
                'variance_pct' => $variancePct,
            ];
        }

        $monthlyData = collect($monthlyData)->values();

        return view('pages.reports.monthly-variance', compact('monthlyData'));
    }

    // ---------------------------------------------------------------
    // Helper methods
    // ---------------------------------------------------------------

    private function getAccountBalances(string $asOfDate)
    {
        return ChartOfAccount::select(
                'chart_of_accounts.id',
                'chart_of_accounts.account_code',
                'chart_of_accounts.account_name',
                'chart_of_accounts.account_type',
                'chart_of_accounts.normal_balance'
            )
            ->selectRaw('COALESCE(SUM(jel.debit), 0) - COALESCE(SUM(jel.credit), 0) as balance')
            ->leftJoin('journal_entry_lines as jel', 'chart_of_accounts.id', '=', 'jel.account_id')
            ->leftJoin('journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('jel.journal_entry_id', '=', 'je.id')
                     ->where('je.status', '=', 'posted')
                     ->whereDate('je.posting_date', '<=', $asOfDate);
            })
            ->groupBy(
                'chart_of_accounts.id',
                'chart_of_accounts.account_code',
                'chart_of_accounts.account_name',
                'chart_of_accounts.account_type',
                'chart_of_accounts.normal_balance'
            )
            ->having(DB::raw('COALESCE(SUM(jel.debit), 0) + COALESCE(SUM(jel.credit), 0)'), '>', 0)
            ->orderBy('chart_of_accounts.account_code')
            ->get();
    }

    private function getAccountBalancesForPeriod(string $type, string $dateFrom, string $dateTo)
    {
        return ChartOfAccount::select(
                'chart_of_accounts.id',
                'chart_of_accounts.account_code',
                'chart_of_accounts.account_name',
                'chart_of_accounts.account_type',
                'chart_of_accounts.normal_balance'
            )
            ->selectRaw('COALESCE(SUM(jel.debit), 0) - COALESCE(SUM(jel.credit), 0) as balance')
            ->leftJoin('journal_entry_lines as jel', 'chart_of_accounts.id', '=', 'jel.account_id')
            ->leftJoin('journal_entries as je', function ($join) use ($dateFrom, $dateTo) {
                $join->on('jel.journal_entry_id', '=', 'je.id')
                     ->where('je.status', '=', 'posted')
                     ->whereDate('je.posting_date', '>=', $dateFrom)
                     ->whereDate('je.posting_date', '<=', $dateTo);
            })
            ->where('chart_of_accounts.account_type', $type)
            ->groupBy(
                'chart_of_accounts.id',
                'chart_of_accounts.account_code',
                'chart_of_accounts.account_name',
                'chart_of_accounts.account_type',
                'chart_of_accounts.normal_balance'
            )
            ->having(DB::raw('COALESCE(SUM(jel.debit), 0) + COALESCE(SUM(jel.credit), 0)'), '>', 0)
            ->orderBy('chart_of_accounts.account_code')
            ->get();
    }

    private function calculateNetIncome(string $dateFrom, string $dateTo): float
    {
        $revenue = JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('coa.account_type', 'revenue')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo)
            ->sum(DB::raw('journal_entry_lines.credit - journal_entry_lines.debit'));

        $expenses = JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('coa.account_type', 'expense')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo)
            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

        return (float) $revenue - (float) $expenses;
    }

    private function getCashBalance(string $asOfDate): float
    {
        return (float) JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '<=', $asOfDate)
            ->where('coa.account_code', '>=', '1010')
            ->where('coa.account_code', '<=', '1050')
            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));
    }
}
