<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\ChartOfAccount;
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

        $accounts = ChartOfAccount::select('chart_of_accounts.*')
            ->selectRaw('COALESCE(SUM(jel.debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(jel.credit), 0) as total_credit')
            ->selectRaw('COALESCE(SUM(jel.debit), 0) - COALESCE(SUM(jel.credit), 0) as net_balance')
            ->leftJoin('journal_entry_lines as jel', 'chart_of_accounts.id', '=', 'jel.account_id')
            ->leftJoin('journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('jel.journal_entry_id', '=', 'je.id')
                     ->where('je.status', '=', 'posted')
                     ->whereDate('je.posting_date', '<=', $asOfDate);
            })
            ->groupBy('chart_of_accounts.id')
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
            ->where('je.journal_type', 'CRJ')
            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

        // Cash paid to suppliers (DR to AP accounts)
        $cashToSuppliers = JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo)
            ->where('coa.account_type', 'asset')
            ->where('coa.account_code', 'like', '1010%')
            ->where('je.journal_type', 'CDJ')
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

        $accounts = ChartOfAccount::active()->orderBy('account_code')->get();

        $query = JournalEntryLine::select(
                'journal_entry_lines.*',
                'je.entry_number', 'je.entry_date', 'je.posting_date',
                'je.reference_number', 'je.journal_type', 'je.description as je_description',
                'coa.account_code', 'coa.account_name', 'coa.account_type'
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

        return view('pages.reports.general-ledger', compact(
            'entries', 'accounts', 'dateFrom', 'dateTo', 'accountId'
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
     * Budget vs Actual comparison.
     */
    public function budgetVsActual()
    {
        $budgets = Budget::with('department', 'category')
            ->where('status', 'active')
            ->get()
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

        return view('pages.reports.budget-vs-actual', compact('budgets', 'summary'));
    }

    /**
     * Monthly budget variance analysis.
     */
    public function monthlyVariance()
    {
        $budgets = Budget::with(['department', 'category', 'allocations'])
            ->where('status', 'active')
            ->get();

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $budgetedForMonth = 0;
            $actualForMonth = 0;

            foreach ($budgets as $budget) {
                $alloc = $budget->allocations->firstWhere('month', $m);
                $budgetedForMonth += $alloc ? (float) $alloc->amount : ($budget->annual_budget / 12);
            }

            // Actual expenses from JE lines for this month
            $actualForMonth = JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
                ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
                ->where('coa.account_type', 'expense')
                ->where('je.status', 'posted')
                ->whereMonth('je.posting_date', $m)
                ->whereYear('je.posting_date', now()->year)
                ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

            $variance = $budgetedForMonth - $actualForMonth;
            $variancePct = $budgetedForMonth > 0 ? ($variance / $budgetedForMonth) * 100 : 0;

            $monthlyData[$m] = [
                'month' => $m,
                'month_name' => date('F', mktime(0, 0, 0, $m, 1)),
                'budgeted' => $budgetedForMonth,
                'actual' => $actualForMonth,
                'variance' => $variance,
                'variance_pct' => $variancePct,
            ];
        }

        return view('pages.reports.monthly-variance', compact('monthlyData'));
    }

    // ---------------------------------------------------------------
    // Helper methods
    // ---------------------------------------------------------------

    private function getAccountBalances(string $asOfDate)
    {
        return ChartOfAccount::select('chart_of_accounts.*')
            ->selectRaw('COALESCE(SUM(jel.debit), 0) - COALESCE(SUM(jel.credit), 0) as balance')
            ->leftJoin('journal_entry_lines as jel', 'chart_of_accounts.id', '=', 'jel.account_id')
            ->leftJoin('journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('jel.journal_entry_id', '=', 'je.id')
                     ->where('je.status', '=', 'posted')
                     ->whereDate('je.posting_date', '<=', $asOfDate);
            })
            ->groupBy('chart_of_accounts.id')
            ->having(DB::raw('COALESCE(SUM(jel.debit), 0) + COALESCE(SUM(jel.credit), 0)'), '>', 0)
            ->orderBy('chart_of_accounts.account_code')
            ->get();
    }

    private function getAccountBalancesForPeriod(string $type, string $dateFrom, string $dateTo)
    {
        return ChartOfAccount::select('chart_of_accounts.*')
            ->selectRaw('COALESCE(SUM(jel.debit), 0) - COALESCE(SUM(jel.credit), 0) as balance')
            ->leftJoin('journal_entry_lines as jel', 'chart_of_accounts.id', '=', 'jel.account_id')
            ->leftJoin('journal_entries as je', function ($join) use ($dateFrom, $dateTo) {
                $join->on('jel.journal_entry_id', '=', 'je.id')
                     ->where('je.status', '=', 'posted')
                     ->whereDate('je.posting_date', '>=', $dateFrom)
                     ->whereDate('je.posting_date', '<=', $dateTo);
            })
            ->where('chart_of_accounts.account_type', $type)
            ->groupBy('chart_of_accounts.id')
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
