<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\FeeAccountMapping;
use App\Models\JournalEntryLine;
use App\Models\Setting;
use App\Services\FinanceFeeService;
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

        $totals = [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => $totalDebit - $totalCredit,
        ];

        return view('pages.reports.trial-balance', compact(
            'accounts', 'totals', 'totalDebit', 'totalCredit', 'isBalanced', 'asOfDate'
        ));
    }

    /**
     * Balance Sheet - Assets, Liabilities, Equity with Net Income.
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $balances = $this->getAccountBalances($asOfDate);

        // Add finance fee collections to revenue and cash
        $financeRevenue = 0;
        try {
            $feeRevenue = FinanceFeeService::mappedRevenue('2000-01-01', $asOfDate);
            foreach ($feeRevenue as $fee) {
                // Add to revenue accounts
                $existing = $balances->firstWhere('id', $fee->account_id);
                if ($existing) {
                    $existing->balance = abs($existing->balance) + $fee->total_amount;
                }
                $financeRevenue += $fee->total_amount;
            }

            // Add total finance collections to Cash on Hand (1010)
            if ($financeRevenue > 0) {
                $cashAccount = $balances->first(function ($a) {
                    return $a->account_code === '1010';
                });
                if ($cashAccount) {
                    $cashAccount->balance += $financeRevenue;
                } else {
                    // Cash account not in balances yet, add it
                    $cashAcct = ChartOfAccount::where('account_code', '1010')->first();
                    if ($cashAcct) {
                        $balances->push((object) [
                            'id' => $cashAcct->id,
                            'account_code' => '1010',
                            'account_name' => $cashAcct->account_name,
                            'account_type' => 'asset',
                            'normal_balance' => 'debit',
                            'balance' => $financeRevenue,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Finance DB unavailable
        }

        $assets = $balances->where('account_type', 'asset');
        $liabilities = $balances->where('account_type', 'liability');
        $equity = $balances->where('account_type', 'equity');

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum(function ($a) { return abs($a->balance); });
        $totalEquity = $equity->sum(function ($a) { return abs($a->balance); });

        // Net Income = Revenue - Expenses (revenue already includes finance fees)
        $revenue = $balances->where('account_type', 'revenue')->sum(function ($a) { return abs($a->balance); });
        $expenses = $balances->where('account_type', 'expense')->sum('balance');
        $netIncome = $revenue - $expenses;

        $totalEquityWithNI = $totalEquity + $netIncome;

        // Group accounts by code prefix for collapsible sections
        $bsGroups = [
            'asset' => [
                '10' => 'Cash & Cash Equivalents',
                '11' => 'Receivables',
                '12' => 'Prepaid Expenses',
                '13' => 'Inventories',
                '15' => 'Property & Equipment',
                '16' => 'Accumulated Depreciation',
                '22' => 'Other Current Assets',
            ],
            'liability' => [
                '20' => 'Accounts Payable',
                '21' => 'Tax Payables',
                '22' => 'VAT',
                '23' => 'Government Contributions',
                '24' => 'Accrued Liabilities',
                '25' => 'Deferred Revenue',
                '26' => 'Current Loans',
                '27' => 'Long-Term Debt',
            ],
        ];

        $groupAccounts = function ($accounts, $groupDefs) {
            $groups = [];
            $used = [];
            foreach ($groupDefs as $prefix => $label) {
                $pfx = (string) $prefix;
                $items = $accounts->filter(function ($a) use ($pfx) {
                    return substr($a->account_code, 0, strlen($pfx)) === $pfx;
                });
                if ($items->isNotEmpty()) {
                    $groups[] = (object) [
                        'label' => $label,
                        'total' => $items->sum(function ($a) { return abs($a->balance); }),
                        'accounts' => $items->values(),
                    ];
                    foreach ($items as $item) $used[] = $item->id;
                }
            }
            // Catch ungrouped
            $remaining = $accounts->whereNotIn('id', $used);
            if ($remaining->isNotEmpty()) {
                $groups[] = (object) [
                    'label' => 'Other',
                    'total' => $remaining->sum(function ($a) { return abs($a->balance); }),
                    'accounts' => $remaining->values(),
                ];
            }
            return $groups;
        };

        $assetGroups = $groupAccounts($assets, $bsGroups['asset']);
        $liabilityGroups = $groupAccounts($liabilities, $bsGroups['liability']);

        return view('pages.reports.balance-sheet', compact(
            'assetGroups', 'liabilityGroups', 'equity',
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

        // Merge finance fee collections into revenue (mapped fees only)
        $feeRevenue = collect();
        try {
            $feeRevenue = FinanceFeeService::mappedRevenue($dateFrom, $dateTo);
        } catch (\Exception $e) {
            // Finance DB unavailable, skip
        }

        // Add fee revenue to existing revenue accounts or create new entries
        foreach ($feeRevenue as $fee) {
            $existing = $revenueAccounts->firstWhere('id', $fee->account_id);
            if ($existing) {
                $existing->balance = abs($existing->balance) + $fee->total_amount;
                $existing->from_finance = true;
            } else {
                // Lookup parent_id from COA for proper grouping
                $coaAccount = ChartOfAccount::find($fee->account_id);
                $revenueAccounts->push((object) [
                    'id' => $fee->account_id,
                    'account_code' => $fee->account_code,
                    'account_name' => $fee->account_name,
                    'account_type' => 'revenue',
                    'normal_balance' => 'credit',
                    'parent_id' => $coaAccount ? $coaAccount->parent_id : null,
                    'balance' => $fee->total_amount,
                    'from_finance' => true,
                ]);
            }
        }

        // Re-sort by account code
        $revenueAccounts = $revenueAccounts->sortBy('account_code')->values();

        $totalRevenue = $revenueAccounts->sum(function ($a) { return abs($a->balance); });
        $totalExpenses = $expenseAccounts->sum('balance');
        $netIncome = $totalRevenue - $totalExpenses;
        $netIncomeMargin = $totalRevenue > 0 ? ($netIncome / $totalRevenue) * 100 : 0;

        // Group revenue: parent accounts with children are collapsible groups
        $parentIds = ChartOfAccount::where('account_type', 'revenue')
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->unique()
            ->all();

        $revenueGroups = [];
        $standaloneRevenue = [];

        foreach ($revenueAccounts as $account) {
            $acctId = $account->id;
            $parentId = $account->parent_id ?? null;

            if ($parentId && in_array($parentId, $parentIds)) {
                // This is a child — add to parent group
                if (!isset($revenueGroups[$parentId])) {
                    $parent = ChartOfAccount::find($parentId);
                    $revenueGroups[$parentId] = (object) [
                        'id' => $parentId,
                        'account_code' => $parent->account_code ?? '',
                        'account_name' => $parent->account_name ?? 'Other',
                        'total' => 0,
                        'children' => [],
                    ];
                }
                $revenueGroups[$parentId]->total += abs($account->balance);
                $revenueGroups[$parentId]->children[] = $account;
            } elseif (in_array($acctId, $parentIds)) {
                // This is a parent that has children — skip as standalone, handled via group
                if (!isset($revenueGroups[$acctId])) {
                    $revenueGroups[$acctId] = (object) [
                        'id' => $acctId,
                        'account_code' => $account->account_code,
                        'account_name' => $account->account_name,
                        'total' => abs($account->balance),
                        'children' => [],
                    ];
                } else {
                    $revenueGroups[$acctId]->total += abs($account->balance);
                }
            } else {
                // Standalone account (no parent, no children)
                $standaloneRevenue[] = $account;
            }
        }

        // Sort groups by account code
        uasort($revenueGroups, function ($a, $b) { return strcmp($a->account_code, $b->account_code); });

        // Group expenses by parent too
        $expParentIds = ChartOfAccount::where('account_type', 'expense')
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->unique()
            ->all();

        $expenseGroups = [];
        $standaloneExpense = [];

        foreach ($expenseAccounts as $account) {
            $acctId = $account->id;
            $parentId = $account->parent_id ?? null;

            if ($parentId && in_array($parentId, $expParentIds)) {
                if (!isset($expenseGroups[$parentId])) {
                    $parent = ChartOfAccount::find($parentId);
                    $expenseGroups[$parentId] = (object) [
                        'id' => $parentId,
                        'account_code' => $parent->account_code ?? '',
                        'account_name' => $parent->account_name ?? 'Other',
                        'total' => 0,
                        'children' => [],
                    ];
                }
                $expenseGroups[$parentId]->total += abs($account->balance);
                $expenseGroups[$parentId]->children[] = $account;
            } elseif (in_array($acctId, $expParentIds)) {
                if (!isset($expenseGroups[$acctId])) {
                    $expenseGroups[$acctId] = (object) [
                        'id' => $acctId,
                        'account_code' => $account->account_code,
                        'account_name' => $account->account_name,
                        'total' => abs($account->balance),
                        'children' => [],
                    ];
                } else {
                    $expenseGroups[$acctId]->total += abs($account->balance);
                }
            } else {
                $standaloneExpense[] = $account;
            }
        }

        uasort($expenseGroups, function ($a, $b) { return strcmp($a->account_code, $b->account_code); });

        return view('pages.reports.income-statement', compact(
            'revenueGroups', 'standaloneRevenue', 'expenseGroups', 'standaloneExpense',
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
        $dateFrom = $request->input('date_from') ?: now()->startOfMonth()->toDateString();
        $dateTo = $request->input('date_to') ?: now()->toDateString();
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

        // Merge finance fee collections as virtual GL entries
        try {
            $feeEntries = FinanceFeeService::glEntries($dateFrom, $dateTo, $accountId);
            foreach ($feeEntries as $feeAcctId => $feeTransactions) {
                if ($entries->has($feeAcctId)) {
                    $entries[$feeAcctId] = $entries[$feeAcctId]->merge($feeTransactions)->sortBy('posting_date')->values();
                } else {
                    $entries[$feeAcctId] = $feeTransactions;
                }
            }
        } catch (\Exception $e) {
            // Finance DB unavailable, skip
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

        $accounts = $accounts->sortBy('account_code')->values();

        return view('pages.reports.general-ledger', compact(
            'accounts', 'allAccounts', 'dateFrom', 'dateTo', 'accountId'
        ));
    }

    /**
     * Expense schedule by category.
     */
    public function expenseSchedule(Request $request)
    {
        $dateFrom = $request->input('date_from') ?: now()->startOfYear()->toDateString();
        $dateTo = $request->input('date_to') ?: now()->toDateString();

        $rows = JournalEntryLine::select(
                'coa.id', 'coa.account_code', 'coa.account_name',
                DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as total')
            )
            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('coa.account_type', 'expense')
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo)
            ->groupBy('coa.id', 'coa.account_code', 'coa.account_name')
            ->havingRaw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) > 0')
            ->orderBy('coa.account_code')
            ->get();

        // Map to view-expected fields (category_name, amount)
        $expenses = $rows->map(function ($r) {
            return (object) [
                'account_code' => $r->account_code,
                'account_name' => $r->account_name,
                'category_name' => $r->account_code . ' - ' . $r->account_name,
                'amount' => (float) $r->total,
            ];
        });

        $totalExpenses = $expenses->sum('amount');

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

    /**
     * Fee Collections Report — data from Finance DB.
     */
    public function feeCollections(Request $request)
    {
        $schoolYears = FinanceFeeService::schoolYears();
        $selectedYear = $request->input('school_year', date('Y'));

        $feeSummary = FinanceFeeService::summaryByFee($selectedYear);
        $totalCollected = $feeSummary->sum('total_amount');
        $totalTransactions = $feeSummary->sum('txn_count');

        return view('pages.reports.fee-collections', compact(
            'schoolYears', 'selectedYear', 'feeSummary', 'totalCollected', 'totalTransactions'
        ));
    }

    /**
     * Fee collection receipts — browse individual OR transactions.
     */
    public function feeReceipts(Request $request)
    {
        $schoolYears = FinanceFeeService::schoolYears();
        $feeNames    = FinanceFeeService::feeNames();
        $selectedYear = $request->input('school_year');
        $search      = $request->input('search');
        $feeName     = $request->input('fee_name');
        $dateFrom    = $request->input('date_from');
        $dateTo      = $request->input('date_to');

        $receipts = FinanceFeeService::receipts($selectedYear, $search, $feeName, $dateFrom, $dateTo);

        return view('pages.reports.fee-receipts', compact(
            'receipts', 'schoolYears', 'feeNames',
            'selectedYear', 'search', 'feeName', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Single receipt detail — itemized fee breakdown.
     */
    public function feeReceiptDetail($id)
    {
        $receipt = FinanceFeeService::receiptDetail($id);

        if (!$receipt) {
            return back()->with('error', 'Receipt not found.');
        }

        return view('pages.reports.fee-receipt-detail', compact('receipt'));
    }

    public function summaryOfCollection(Request $request)
    {
        $defaultFrom = now()->startOfMonth()->toDateString();
        $defaultTo   = now()->toDateString();
        $dateFrom    = $request->input('date_from', $defaultFrom);
        $dateTo      = $request->input('date_to', $defaultTo);
        $search      = $request->input('search');

        $records = FinanceFeeService::summaryOfCollection($dateFrom, $dateTo, $search);

        return view('pages.reports.fin-summary-of-collection', compact('records', 'dateFrom', 'dateTo', 'search'));
    }

    public function summaryOfCollectionPerFee(Request $request)
    {
        $defaultFrom = now()->startOfMonth()->toDateString();
        $defaultTo   = now()->toDateString();
        $dateFrom    = $request->input('date_from', $defaultFrom);
        $dateTo      = $request->input('date_to', $defaultTo);
        $feeName     = $request->input('fee_name');
        $feeNames    = FinanceFeeService::feeNames();

        $records = FinanceFeeService::summaryOfCollectionPerFee($dateFrom, $dateTo, $feeName);

        return view('pages.reports.fin-summary-per-fee', compact('records', 'dateFrom', 'dateTo', 'feeName', 'feeNames'));
    }

    public function feeListReport(Request $request)
    {
        $defaultFrom = now()->startOfMonth()->toDateString();
        $defaultTo   = now()->toDateString();
        $dateFrom    = $request->input('date_from', $defaultFrom);
        $dateTo      = $request->input('date_to', $defaultTo);
        $feeName     = $request->input('fee_name');
        $feeNames    = FinanceFeeService::feeNames();

        $records = FinanceFeeService::feeListReport($dateFrom, $dateTo, $feeName);

        return view('pages.reports.fin-fee-list', compact('records', 'dateFrom', 'dateTo', 'feeName', 'feeNames'));
    }

    public function cashReceiptBooksFinance(Request $request)
    {
        $defaultFrom = now()->startOfMonth()->toDateString();
        $defaultTo   = now()->toDateString();
        $dateFrom    = $request->input('date_from', $defaultFrom);
        $dateTo      = $request->input('date_to', $defaultTo);
        $search      = $request->input('search');

        $records = FinanceFeeService::cashReceiptBooksFinance($dateFrom, $dateTo, $search);

        return view('pages.reports.fin-cash-receipt-books', compact('records', 'dateFrom', 'dateTo', 'search'));
    }

    /**
     * Fee Account Mapping — settings page (lazy-loading version).
     * Only loads group headers on initial render; fees are fetched per-group via AJAX.
     */
    public function feeAccountMappings()
    {
        try {
            [$financeGroups, $allAccounts, $revenueAccounts] = cache()->remember('fee_mappings_data', 300, function () {
                // Single JOIN query — group headers + child counts only (no loading all fee rows)
                try {
                    $groups = DB::connection('finance')->table('chart_of_accounts as p')
                        ->join('chart_of_accounts as c', 'c.parent_id', '=', 'p.id')
                        ->whereNull('p.parent_id')
                        ->whereNull('p.deleted_at')
                        ->select('p.id', 'p.name', DB::raw('COUNT(c.id) as child_count'))
                        ->groupBy('p.id', 'p.name')
                        ->orderBy('p.name')
                        ->get();
                } catch (\Exception $e) {
                    $groups = DB::connection('finance')->table('chart_of_accounts as p')
                        ->join('chart_of_accounts as c', 'c.parent_id', '=', 'p.id')
                        ->whereNull('p.parent_id')
                        ->select('p.id', 'p.name', DB::raw('COUNT(c.id) as child_count'))
                        ->groupBy('p.id', 'p.name')
                        ->orderBy('p.name')
                        ->get();
                }

                $allAccounts = ChartOfAccount::whereNull('parent_id')
                    ->select('id', 'account_code', 'account_name', 'account_type')
                    ->orderBy('account_code')->get();

                $revenueAccounts = ChartOfAccount::whereIn('account_type', ['revenue', 'expense'])
                    ->select('id', 'account_code', 'account_name', 'account_type')
                    ->orderBy('account_code')->get();

                return [$groups, $allAccounts, $revenueAccounts];
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to connect to Finance database: ' . $e->getMessage());
        }

        // Mapped counts per group — fresh small query (not cached, changes frequently)
        $mappedFeeIds = FeeAccountMapping::pluck('finance_fee_id')->all();
        $parentCounts = [];
        if (!empty($mappedFeeIds)) {
            $parentCounts = DB::connection('finance')->table('chart_of_accounts')
                ->whereIn('id', $mappedFeeIds)
                ->select('parent_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('parent_id')
                ->pluck('cnt', 'parent_id')
                ->toArray();
        }

        $financeGroups = $financeGroups->map(function ($g) use ($parentCounts) {
            $g->mapped_count = $parentCounts[$g->id] ?? 0;
            return $g;
        });

        $totalMapped = FeeAccountMapping::count();
        $totalFees   = $financeGroups->sum('child_count');

        return view('pages.reports.fee-account-mappings', compact(
            'financeGroups', 'allAccounts', 'revenueAccounts', 'totalMapped', 'totalFees'
        ));
    }

    /**
     * AJAX endpoint — returns a partial view with all fees for one finance group.
     */
    public function feeGroupFees($groupId)
    {
        $group = DB::connection('finance')->table('chart_of_accounts')
            ->where('id', $groupId)
            ->whereNull('parent_id')
            ->select('id', 'name')
            ->first();

        if (!$group) {
            return response('Not found', 404);
        }

        try {
            $fees = DB::connection('finance')->table('chart_of_accounts')
                ->where('parent_id', $groupId)
                ->whereNull('deleted_at')
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            $fees = DB::connection('finance')->table('chart_of_accounts')
                ->where('parent_id', $groupId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }

        $feeIds  = $fees->pluck('id')->all();
        $mappings = FeeAccountMapping::with('account:id,account_code,account_name')
            ->whereIn('finance_fee_id', $feeIds)
            ->get()
            ->keyBy('finance_fee_id');

        $revenueAccounts = cache()->remember('fee_mappings_accounts', 300, function () {
            return ChartOfAccount::whereIn('account_type', ['revenue', 'expense'])
                ->select('id', 'account_code', 'account_name', 'account_type')
                ->orderBy('account_code')
                ->get();
        });

        return view('pages.reports.partials.fee-group-fees', compact(
            'fees', 'mappings', 'revenueAccounts', 'group'
        ));
    }

    /**
     * Auto-generate COA sub-accounts from finance fees, grouped by finance parent category.
     * Maps finance parent groups to accounting parent accounts based on group_mapping input.
     */
    public function autoGenerateFeeAccounts(Request $request)
    {
        $groupMappings = $request->input('group_mappings', []);

        if (empty($groupMappings)) {
            return back()->with('error', 'Please select at least one accounting account for the groups.');
        }

        // Get all finance fees with their parent info
        $financeFees = DB::connection('finance')->table('chart_of_accounts')
            ->whereNull('deleted_at')
            ->whereNotNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        if ($financeFees->isEmpty()) {
            return back()->with('error', 'No fees found in finance database.');
        }

        $existingMappings = FeeAccountMapping::pluck('finance_fee_id')->toArray();

        // Track sub-account counters per parent
        $counters = [];

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($financeFees, $groupMappings, $existingMappings, &$counters, &$created, &$skipped) {
            foreach ($financeFees as $fee) {
                if (in_array($fee->id, $existingMappings)) {
                    $skipped++;
                    continue;
                }

                $financeParentId = (string) $fee->parent_id;

                // Skip if no mapping defined for this finance parent group
                if (empty($groupMappings[$financeParentId])) {
                    $skipped++;
                    continue;
                }

                $acctParentId = $groupMappings[$financeParentId];
                $acctParent = ChartOfAccount::find($acctParentId);
                if (!$acctParent) continue;

                // Get or init counter for this parent
                if (!isset($counters[$acctParentId])) {
                    $lastSub = ChartOfAccount::where('account_code', 'like', $acctParent->account_code . '-%')
                        ->orderByDesc('account_code')->first();
                    $counters[$acctParentId] = $lastSub
                        ? (int) str_replace($acctParent->account_code . '-', '', $lastSub->account_code)
                        : 0;
                }

                $counters[$acctParentId]++;
                $subCode = $acctParent->account_code . '-' . str_pad($counters[$acctParentId], 3, '0', STR_PAD_LEFT);

                // Determine account type from parent
                $accountType = $acctParent->account_type;
                $normalBalance = $acctParent->normal_balance;

                $account = ChartOfAccount::create([
                    'account_code' => $subCode,
                    'account_name' => $fee->name,
                    'account_type' => $accountType,
                    'parent_id' => $acctParentId,
                    'normal_balance' => $normalBalance,
                    'is_active' => true,
                    'is_postable' => true,
                ]);

                FeeAccountMapping::create([
                    'finance_fee_id' => $fee->id,
                    'finance_fee_name' => $fee->name,
                    'account_id' => $account->id,
                ]);

                $created++;
            }
        });

        // Bust account cache so new sub-accounts appear immediately in dropdowns
        cache()->forget('fee_mappings_accounts');
        cache()->forget('fee_mappings_finance_fees');
        cache()->forget('fee_mappings_data');

        return back()->with('success', "{$created} sub-accounts created and mapped. {$skipped} skipped (already mapped or no group selected).");
    }

    /**
     * Save fee account mappings.
     */
    public function saveFeeAccountMappings(Request $request)
    {
        $validated = $request->validate([
            'mappings' => 'required|array',
            'mappings.*.finance_fee_id' => 'required|integer',
            'mappings.*.finance_fee_name' => 'required|string',
            'mappings.*.account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        foreach ($validated['mappings'] as $mapping) {
            if (!empty($mapping['account_id'])) {
                FeeAccountMapping::updateOrCreate(
                    ['finance_fee_id' => $mapping['finance_fee_id']],
                    [
                        'finance_fee_name' => $mapping['finance_fee_name'],
                        'account_id' => $mapping['account_id'],
                    ]
                );
            } else {
                FeeAccountMapping::where('finance_fee_id', $mapping['finance_fee_id'])->delete();
            }
        }

        return back()->with('success', 'Fee account mappings saved successfully.');
    }

    /**
     * Budget Performance Report — Revenue & Expenses with budget vs actual, collapsible.
     */
    public function budgetPerformance(Request $request)
    {
        $dateFrom = $request->input('date_from') ?: now()->startOfYear()->toDateString();
        $dateTo = $request->input('date_to') ?: now()->toDateString();

        // === REVENUE (from finance fees + JE) ===
        $revenueAccounts = $this->getAccountBalancesForPeriod('revenue', $dateFrom, $dateTo);

        // Merge finance fee revenue
        try {
            $feeRevenue = FinanceFeeService::mappedRevenue($dateFrom, $dateTo);
            foreach ($feeRevenue as $fee) {
                $existing = $revenueAccounts->firstWhere('id', $fee->account_id);
                if ($existing) {
                    $existing->balance = abs($existing->balance) + $fee->total_amount;
                } else {
                    $coaAccount = ChartOfAccount::find($fee->account_id);
                    $revenueAccounts->push((object) [
                        'id' => $fee->account_id,
                        'account_code' => $fee->account_code,
                        'account_name' => $fee->account_name,
                        'account_type' => 'revenue',
                        'normal_balance' => 'credit',
                        'parent_id' => $coaAccount ? $coaAccount->parent_id : null,
                        'balance' => $fee->total_amount,
                    ]);
                }
            }
        } catch (\Exception $e) {}

        $revenueAccounts = $revenueAccounts->sortBy('account_code')->values();
        $totalRevenue = $revenueAccounts->sum(function ($a) { return abs($a->balance); });

        // Group revenue by parent
        $revenueGroups = $this->groupByParent($revenueAccounts, 'revenue');

        // === EXPENSES (from JE + budget data) ===
        $expenseAccounts = $this->getAccountBalancesForPeriod('expense', $dateFrom, $dateTo);
        $totalExpenses = $expenseAccounts->sum('balance');

        // Get budget data grouped by category
        $budgets = \App\Models\Budget::with('category', 'department')
            ->whereIn('status', ['active', 'approved'])
            ->get();

        $totalBudget = (float) $budgets->sum('annual_budget');
        $totalCommitted = (float) $budgets->sum('committed');
        $totalActual = (float) $budgets->sum('actual');

        // Budget by category
        $budgetByCategory = $budgets->groupBy(function ($b) {
            return $b->category->name ?? 'Uncategorized';
        })->map(function ($items, $category) {
            return (object) [
                'category' => $category,
                'budget' => (float) $items->sum('annual_budget'),
                'committed' => (float) $items->sum('committed'),
                'actual' => (float) $items->sum('actual'),
                'items' => $items,
            ];
        })->sortByDesc('budget')->values();

        $netIncome = $totalRevenue - $totalExpenses;

        return view('pages.reports.budget-performance', compact(
            'revenueGroups', 'totalRevenue',
            'expenseAccounts', 'totalExpenses',
            'totalBudget', 'totalCommitted', 'totalActual',
            'budgetByCategory', 'netIncome',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Group accounts by parent_id for collapsible display.
     */
    private function groupByParent($accounts, $type)
    {
        $parentIds = ChartOfAccount::where('account_type', $type)
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->unique()
            ->all();

        $groups = [];
        $standalone = [];

        foreach ($accounts as $account) {
            $acctId = $account->id;
            $parentId = $account->parent_id ?? null;

            if ($parentId && in_array($parentId, $parentIds)) {
                if (!isset($groups[$parentId])) {
                    $parent = ChartOfAccount::find($parentId);
                    $groups[$parentId] = (object) [
                        'id' => $parentId,
                        'account_code' => $parent->account_code ?? '',
                        'account_name' => $parent->account_name ?? 'Other',
                        'total' => 0,
                        'children' => [],
                    ];
                }
                $groups[$parentId]->total += abs($account->balance);
                $groups[$parentId]->children[] = $account;
            } elseif (in_array($acctId, $parentIds)) {
                if (!isset($groups[$acctId])) {
                    $groups[$acctId] = (object) [
                        'id' => $acctId,
                        'account_code' => $account->account_code,
                        'account_name' => $account->account_name,
                        'total' => abs($account->balance),
                        'children' => [],
                    ];
                } else {
                    $groups[$acctId]->total += abs($account->balance);
                }
            } else {
                $standalone[] = $account;
            }
        }

        uasort($groups, function ($a, $b) { return strcmp($a->account_code, $b->account_code); });

        return ['groups' => $groups, 'standalone' => $standalone];
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
