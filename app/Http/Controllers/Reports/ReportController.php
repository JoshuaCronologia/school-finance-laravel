<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\JournalEntryLine;
use App\Models\Setting;
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
            ->filter(function ($a) { return $a->total_debit > 0 || $a->total_credit > 0; });

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
        $totalLiabilities = $liabilities->sum(function ($a) { return abs($a->balance); });
        $totalEquity = $equity->sum(function ($a) { return abs($a->balance); });

        // Net Income = Revenue - Expenses
        $revenue = $balances->where('account_type', 'revenue')->sum(function ($a) { return abs($a->balance); });
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

        $totalRevenue = $revenueAccounts->sum(function ($a) { return abs($a->balance); });
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

        $totalDebit = $entries->sum(function ($e) { return $e->lines->sum('debit'); });
        $totalCredit = $entries->sum(function ($e) { return $e->lines->sum('credit'); });

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

        // Handle export
        if ($request->filled('export')) {
            $schoolName = Setting::where('key', 'school_name')->value('value') ?? config('app.name');
            $schoolYear = $request->input('school_year', '2025-2026');
            $deptName = $request->filled('department_id')
                ? ($departments->firstWhere('id', $request->department_id)->name ?? 'ALL DEPARTMENTS')
                : 'INSTITUTIONAL';

            if ($request->export === 'pdf') {
                $data = [
                    'budgets' => $budgets->map(function ($b) { return (object) [
                        'budget_name' => $b->budget_name,
                        'department_name' => $b->department->name ?? '-',
                        'category_name' => $b->category->name ?? '-',
                        'annual_budget' => $b->annual_budget,
                        'committed' => $b->committed,
                        'actual' => $b->actual,
                        'remaining' => $b->remaining,
                        'variance' => $b->variance,
                        'variance_pct' => $b->variance_pct,
                    ]; })->sortBy('department_name'),
                    'summary' => array_merge($summary, [
                        'total_variance' => $summary['total_budget'] - $summary['total_actual'],
                    ]),
                    'schoolYear' => $schoolYear,
                    'schoolName' => $schoolName,
                    'departmentName' => $request->filled('department_id') ? $deptName : null,
                    'generatedAt' => now()->format('M d, Y h:i A'),
                ];
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.budget.pdf-budget-vs-actual', $data)
                    ->setPaper('letter', 'landscape');
                return $pdf->download('Budget-vs-Actual.pdf');
            }

            // CSV/Excel
            $callback = function () use ($budgets, $schoolName, $schoolYear, $deptName, $summary) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ["BUDGET VS ACTUAL REPORT SY {$schoolYear}"]);
                fputcsv($file, [$schoolName]);
                fputcsv($file, [strtoupper($deptName)]);
                fputcsv($file, ['Generated: ' . now()->format('F d, Y')]);
                fputcsv($file, []);
                fputcsv($file, ['', 'Approved Budget', 'Actual', 'Committed', 'Remaining', 'Variance (B-A)', 'Variance %']);
                foreach ($budgets->sortBy(function ($b) { return $b->department->name ?? ''; }) as $b) {
                    fputcsv($file, [
                        $b->budget_name . ' (' . ($b->department->name ?? '-') . ')',
                        number_format($b->annual_budget, 2),
                        number_format($b->actual, 2),
                        number_format($b->committed, 2),
                        number_format($b->remaining, 2),
                        number_format($b->variance, 2),
                        number_format($b->variance_pct, 1) . '%',
                    ]);
                }
                fputcsv($file, []);
                fputcsv($file, [
                    'TOTAL',
                    number_format($summary['total_budget'], 2),
                    number_format($summary['total_actual'], 2),
                    number_format($summary['total_committed'], 2),
                    number_format($summary['total_remaining'], 2),
                    number_format($summary['total_budget'] - $summary['total_actual'], 2),
                    ($summary['total_budget'] > 0 ? number_format((($summary['total_budget'] - $summary['total_actual']) / $summary['total_budget']) * 100, 1) : '0.0') . '%',
                ]);
                fclose($file);
            };
            return response()->streamDownload($callback, 'Budget-vs-Actual.csv', ['Content-Type' => 'text/csv']);
        }

        return view('pages.reports.budget-vs-actual', compact('budgets', 'summary', 'departments'));
    }

    /**
     * Monthly budget variance analysis – itemized per budget line, grouped by department.
     */
    public function monthlyVariance(Request $request)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $departmentId = $request->input('department_id');
        $selectedMonth = $request->input('month', now()->month);

        $query = Budget::with(['department', 'category', 'allocations'])
            ->whereIn('status', ['active', 'approved']);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $budgets = $query->get();

        // Build itemized data: each budget line with its monthly allocation
        $itemizedData = $budgets->map(function ($budget) use ($selectedMonth) {
            $alloc = $budget->allocations->firstWhere('month', $selectedMonth);
            $monthlyBudget = $alloc ? (float) $alloc->amount : round((float) $budget->annual_budget / 12, 2);

            // Actual comes from the budget model's actual field prorated
            // (since we don't have per-budget-per-month actuals, use annual actual / 12 as estimate)
            $monthlyActual = round((float) $budget->actual / 12, 2);

            $variance = $monthlyBudget - $monthlyActual;
            $variancePct = $monthlyBudget > 0 ? ($variance / $monthlyBudget) * 100 : 0;

            return (object) [
                'budget_name' => $budget->budget_name,
                'category' => $budget->category->name ?? '-',
                'department' => $budget->department->name ?? '-',
                'department_id' => $budget->department_id,
                'annual_budget' => (float) $budget->annual_budget,
                'monthly_budget' => $monthlyBudget,
                'monthly_actual' => $monthlyActual,
                'annual_actual' => (float) $budget->actual,
                'variance' => $variance,
                'variance_pct' => $variancePct,
            ];
        })->sortBy('department');

        // Group by department
        $groupedData = $itemizedData->groupBy('department');

        // Monthly summary for chart (all 12 months)
        $monthLabels = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $monthlyChartData = [];
        for ($m = 1; $m <= 12; $m++) {
            $budgetedForMonth = 0;
            foreach ($budgets as $budget) {
                $alloc = $budget->allocations->firstWhere('month', $m);
                $budgetedForMonth += $alloc ? (float) $alloc->amount : ((float) $budget->annual_budget / 12);
            }
            $actualForMonth = round($budgets->sum('actual') / 12, 2);

            $monthlyChartData[] = (object) [
                'month' => $m,
                'budget' => round($budgetedForMonth, 2),
                'actual' => $actualForMonth,
            ];
        }
        $monthlyChartData = collect($monthlyChartData);

        // Totals
        $totals = [
            'annual_budget' => $itemizedData->sum('annual_budget'),
            'monthly_budget' => $itemizedData->sum('monthly_budget'),
            'monthly_actual' => $itemizedData->sum('monthly_actual'),
            'annual_actual' => $itemizedData->sum('annual_actual'),
        ];
        $totals['variance'] = $totals['monthly_budget'] - $totals['monthly_actual'];
        $totals['variance_pct'] = $totals['monthly_budget'] > 0
            ? ($totals['variance'] / $totals['monthly_budget']) * 100 : 0;

        $selectedMonthName = date('F', mktime(0, 0, 0, $selectedMonth, 1));

        // Handle export
        if ($request->filled('export')) {
            $schoolName = Setting::where('key', 'school_name')->value('value') ?? config('app.name');
            $deptLabel = $departmentId
                ? strtoupper($departments->firstWhere('id', $departmentId)->name ?? 'DEPARTMENT')
                : 'INSTITUTIONAL';

            if ($request->export === 'pdf') {
                $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Monthly Variance</title>'
                    . '<style>* { margin:0; padding:0; } body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; }'
                    . '.header { text-align:center; margin-bottom:15px; border-bottom:2px solid #1a1a1a; padding-bottom:8px; }'
                    . '.header h1 { font-size:14px; margin-bottom:2px; } .header h2 { font-size:12px; font-weight:normal; margin-bottom:2px; } .header h3 { font-size:11px; margin-bottom:2px; }'
                    . '.header .sub { font-size:9px; color:#555; }'
                    . 'table { width:100%; border-collapse:collapse; margin-top:10px; } th,td { border:1px solid #ccc; padding:5px 8px; }'
                    . 'th { background:#2c3e50; color:#fff; font-size:9px; } td { font-size:9px; }'
                    . '.text-right { text-align:right; } .negative { color:#c0392b; } .positive { color:#27ae60; }'
                    . '.total-row { background:#ecf0f1; font-weight:bold; }'
                    . '.dept-header { background:#f0f4f8; font-weight:bold; font-size:10px; }'
                    . '.subtotal { background:#f9fafb; font-weight:bold; font-size:9px; }'
                    . '.footer { margin-top:15px; font-size:8px; color:#888; text-align:center; border-top:1px solid #ccc; padding-top:5px; }'
                    . '</style></head><body>'
                    . '<div class="header">'
                    . '<h1>MONTHLY VARIANCE - ' . e($selectedMonthName) . '</h1>'
                    . '<h2>' . e($schoolName) . '</h2>'
                    . '<h3>' . e($deptLabel) . '</h3>'
                    . '<div class="sub">Generated: ' . now()->format('M d, Y h:i A') . '</div>'
                    . '</div>'
                    . '<table><thead><tr>'
                    . '<th style="width:40%">Category / Budget Item</th>'
                    . '<th class="text-right" style="width:20%">Budget for ' . e($selectedMonthName) . '</th>'
                    . '<th class="text-right" style="width:20%">Actual Expenses</th>'
                    . '<th class="text-right" style="width:20%">Variance</th>'
                    . '</tr></thead><tbody>';

                foreach ($groupedData as $deptName => $items) {
                    $html .= '<tr class="dept-header"><td colspan="4">' . e($deptName) . '</td></tr>';
                    foreach ($items as $item) {
                        $cls = $item->variance < 0 ? 'negative' : 'positive';
                        $vFmt = ($item->variance < 0 ? '(' : '') . number_format(abs($item->variance), 2) . ($item->variance < 0 ? ')' : '');
                        $html .= '<tr>'
                            . '<td style="padding-left:16px">' . e($item->category) . ' — ' . e($item->budget_name) . '</td>'
                            . '<td class="text-right">' . number_format($item->monthly_budget, 2) . '</td>'
                            . '<td class="text-right">' . number_format($item->monthly_actual, 2) . '</td>'
                            . '<td class="text-right ' . $cls . '">' . $vFmt . '</td>'
                            . '</tr>';
                    }
                    $dB = $items->sum('monthly_budget'); $dA = $items->sum('monthly_actual'); $dV = $dB - $dA;
                    $dCls = $dV < 0 ? 'negative' : 'positive';
                    $html .= '<tr class="subtotal"><td class="text-right">' . e($deptName) . ' Subtotal</td>'
                        . '<td class="text-right">' . number_format($dB, 2) . '</td>'
                        . '<td class="text-right">' . number_format($dA, 2) . '</td>'
                        . '<td class="text-right ' . $dCls . '">' . ($dV < 0 ? '(' : '') . number_format(abs($dV), 2) . ($dV < 0 ? ')' : '') . '</td></tr>';
                }

                $vTotal = ($totals['variance'] < 0 ? '(' : '') . number_format(abs($totals['variance']), 2) . ($totals['variance'] < 0 ? ')' : '');
                $html .= '<tr class="total-row">'
                    . '<td class="text-right">TOTAL</td>'
                    . '<td class="text-right">' . number_format($totals['monthly_budget'], 2) . '</td>'
                    . '<td class="text-right">' . number_format($totals['monthly_actual'], 2) . '</td>'
                    . '<td class="text-right">' . $vTotal . '</td>'
                    . '</tr>';
                $html .= '</tbody></table><div class="footer">' . e($schoolName) . ' - Monthly Variance Report</div></body></html>';

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('letter', 'portrait');
                return $pdf->download('Monthly-Variance-' . $selectedMonthName . '.pdf');
            }

            // CSV
            $callback = function () use ($groupedData, $totals, $schoolName, $deptLabel, $selectedMonthName) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ["MONTHLY VARIANCE - {$selectedMonthName}"]);
                fputcsv($file, [$schoolName]);
                fputcsv($file, [$deptLabel]);
                fputcsv($file, ['Generated: ' . now()->format('F d, Y')]);
                fputcsv($file, []);
                fputcsv($file, ['Category / Budget Item', "Budget for {$selectedMonthName}", 'Actual Expenses', 'Variance']);
                foreach ($groupedData as $deptName => $items) {
                    fputcsv($file, [$deptName, '', '', '']);
                    foreach ($items as $item) {
                        $vFmt = ($item->variance < 0 ? '(' : '') . number_format(abs($item->variance), 2) . ($item->variance < 0 ? ')' : '');
                        fputcsv($file, [
                            '  ' . $item->category . ' - ' . $item->budget_name,
                            number_format($item->monthly_budget, 2),
                            number_format($item->monthly_actual, 2),
                            $vFmt,
                        ]);
                    }
                }
                fputcsv($file, []);
                $vTotal = ($totals['variance'] < 0 ? '(' : '') . number_format(abs($totals['variance']), 2) . ($totals['variance'] < 0 ? ')' : '');
                fputcsv($file, ['TOTAL',
                    number_format($totals['monthly_budget'], 2),
                    number_format($totals['monthly_actual'], 2),
                    $vTotal,
                ]);
                fclose($file);
            };
            return response()->streamDownload($callback, "Monthly-Variance-{$selectedMonthName}.csv", ['Content-Type' => 'text/csv']);
        }

        return view('pages.reports.monthly-variance', compact(
            'groupedData', 'itemizedData', 'monthlyChartData', 'monthLabels',
            'totals', 'departments', 'departmentId', 'selectedMonth', 'selectedMonthName'
        ));
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
