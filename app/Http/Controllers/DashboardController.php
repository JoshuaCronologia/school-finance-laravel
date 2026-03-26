<?php

namespace App\Http\Controllers;

use App\Models\ApBill;
use App\Models\ApPayment;
use App\Models\ArInvoice;
use App\Models\Budget;
use App\Models\ChartOfAccount;
use App\Models\DisbursementRequest;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Vendor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Finance dashboard - budget overview, department spending, trends, recent disbursements.
     */
    public function finance()
    {
        // Cache heavy aggregations for 5 minutes — each Supabase query has
        // ~50-200ms network latency, caching cuts page load dramatically.
        $data = Cache::remember('dashboard:finance', 300, function () {
            // 1 query instead of 3 for budget totals
            $summary = Budget::selectRaw('
                COALESCE(SUM(annual_budget), 0) as total_budget,
                COALESCE(SUM(committed), 0) as committed,
                COALESCE(SUM(actual), 0) as actual
            ')->first();

            $totalBudget = (float) $summary->total_budget;
            $committed = (float) $summary->committed;
            $actual = (float) $summary->actual;
            $remaining = $totalBudget - $committed - $actual;

            $topDepartments = Budget::with('department')
                ->select('department_id', DB::raw('SUM(actual) as total_actual'), DB::raw('SUM(annual_budget) as total_budget'))
                ->groupBy('department_id')
                ->orderByDesc('total_actual')
                ->take(5)
                ->get();

            $monthlyExpenses = JournalEntryLine::select(
                    DB::raw('EXTRACT(MONTH FROM journal_entries.posting_date) as month'),
                    DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as total')
                )
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
                ->where('chart_of_accounts.account_type', 'expense')
                ->where('journal_entries.status', 'posted')
                ->whereYear('journal_entries.posting_date', now()->year)
                ->groupBy(DB::raw('EXTRACT(MONTH FROM journal_entries.posting_date)'))
                ->orderBy('month')
                ->get();

            $categoryRows = Budget::with('category')
                ->select('category_id', DB::raw('SUM(actual) as total'))
                ->groupBy('category_id')
                ->orderByDesc('total')
                ->take(5)
                ->get();

            $categoryLabels = $categoryRows->map(fn ($r) => $r->category->name ?? 'Uncategorized')->values();
            $categoryValues = $categoryRows->pluck('total')->map(fn ($v) => (float) $v)->values();

            $departmentRows = Budget::with('department')
                ->select(
                    'department_id',
                    DB::raw('SUM(annual_budget) as budget'),
                    DB::raw('SUM(actual) as actual'),
                    DB::raw('SUM(committed) as committed')
                )
                ->groupBy('department_id')
                ->get();

            $departmentLabels = $departmentRows->map(fn ($r) => $r->department->name ?? 'Unknown')->values();
            $departmentDatasets = [
                ['label' => 'Budget',    'data' => $departmentRows->pluck('budget')->map(fn ($v) => (float) $v)->values()],
                ['label' => 'Actual',    'data' => $departmentRows->pluck('actual')->map(fn ($v) => (float) $v)->values()],
                ['label' => 'Committed', 'data' => $departmentRows->pluck('committed')->map(fn ($v) => (float) $v)->values()],
            ];

            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            $monthlyLabels = $monthlyExpenses->map(fn ($r) => $months[$r->month - 1] ?? $r->month)->values();
            $monthlyDatasets = [
                ['label' => 'Expenses', 'data' => $monthlyExpenses->pluck('total')->map(fn ($v) => (float) $v)->values()],
            ];

            return compact(
                'totalBudget', 'committed', 'actual', 'remaining',
                'topDepartments',
                'departmentLabels', 'departmentDatasets',
                'monthlyLabels', 'monthlyDatasets',
                'categoryLabels', 'categoryValues'
            );
        });

        $recentDisbursements = Cache::remember('dashboard:finance:disbursements', 120, function () {
            return DisbursementRequest::with('department', 'category')
                ->latest('request_date')
                ->take(5)
                ->get();
        });

        return view('pages.dashboard.finance', array_merge($data, compact('recentDisbursements')));
    }

    /**
     * Accounting dashboard - AR/AP totals, cash balance, aging, top vendors/categories.
     */
    public function accounting()
    {
        $data = Cache::remember('dashboard:accounting', 300, function () {
            $totalReceivables = (float) ArInvoice::whereNotIn('status', ['cancelled', 'voided'])
                ->sum('balance');

            $totalPayables = (float) ApBill::whereNotIn('status', ['cancelled', 'voided'])
                ->sum('balance');

            // 1 query instead of 3: cash balance + revenue + expenses
            $financials = DB::selectOne("
                SELECT
                    COALESCE(SUM(CASE WHEN coa.account_code >= '1010' AND coa.account_code <= '1050'
                        THEN jel.debit - jel.credit END), 0) as cash_balance,
                    COALESCE(SUM(CASE WHEN coa.account_type = 'revenue'
                        AND EXTRACT(MONTH FROM je.posting_date) = ?
                        AND EXTRACT(YEAR FROM je.posting_date) = ?
                        THEN jel.credit - jel.debit END), 0) as total_revenue,
                    COALESCE(SUM(CASE WHEN coa.account_type = 'expense'
                        AND EXTRACT(MONTH FROM je.posting_date) = ?
                        AND EXTRACT(YEAR FROM je.posting_date) = ?
                        THEN jel.debit - jel.credit END), 0) as total_expenses
                FROM journal_entry_lines jel
                JOIN journal_entries je ON jel.journal_entry_id = je.id
                JOIN chart_of_accounts coa ON jel.account_id = coa.id
                WHERE je.status = 'posted'
            ", [now()->month, now()->year, now()->month, now()->year]);

            $cashBalance = (float) $financials->cash_balance;
            $totalRevenue = (float) $financials->total_revenue;
            $totalExpenses = (float) $financials->total_expenses;
            $netIncome = $totalRevenue - $totalExpenses;

            // 1 query instead of 2 for JE counts
            $jeCounts = JournalEntry::selectRaw("
                COUNT(CASE WHEN status = 'draft' THEN 1 END) as unposted,
                COUNT(CASE WHEN status = 'pending_approval' THEN 1 END) as pending_approval
            ")->first();
            $unpostedCount = (int) $jeCounts->unposted;
            $pendingApprovalCount = (int) $jeCounts->pending_approval;

            // 1 SQL query each instead of loading all rows into PHP loops
            $arAging = self::calculateAgingSQL('ar_invoices');
            $apAging = self::calculateAgingSQL('ap_bills');

            $overdueAR = $arAging->days_30 + $arAging->days_60 + $arAging->days_90 + $arAging->days_over_90;
            $overdueAP = $apAging->days_30 + $apAging->days_60 + $apAging->days_90 + $apAging->days_over_90;

            $topExpenseCategories = JournalEntryLine::select(
                    'chart_of_accounts.account_name',
                    DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as total')
                )
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
                ->where('chart_of_accounts.account_type', 'expense')
                ->where('journal_entries.status', 'posted')
                ->whereYear('journal_entries.posting_date', now()->year)
                ->groupBy('chart_of_accounts.id', 'chart_of_accounts.account_name')
                ->orderByDesc('total')
                ->take(5)
                ->get();

            $topVendors = ApPayment::select(
                    'vendors.name',
                    DB::raw('SUM(ap_payments.net_amount) as total_paid')
                )
                ->join('vendors', 'ap_payments.vendor_id', '=', 'vendors.id')
                ->where('ap_payments.status', 'posted')
                ->whereYear('ap_payments.payment_date', now()->year)
                ->groupBy('vendors.id', 'vendors.name')
                ->orderByDesc('total_paid')
                ->take(5)
                ->get();

            return compact(
                'totalReceivables', 'totalPayables', 'cashBalance',
                'netIncome', 'totalRevenue', 'totalExpenses',
                'unpostedCount', 'pendingApprovalCount',
                'arAging', 'apAging', 'overdueAR', 'overdueAP',
                'topExpenseCategories', 'topVendors'
            );
        });

        $recentJEs = Cache::remember('dashboard:accounting:recent_jes', 120, function () {
            return JournalEntry::with('lines.account')
                ->latest('entry_date')
                ->take(10)
                ->get();
        });

        return view('pages.dashboard.accounting', array_merge($data, compact('recentJEs')));
    }

    /**
     * Calculate aging buckets in a single SQL query instead of loading all rows into PHP.
     */
    private static function calculateAgingSQL(string $table): object
    {
        $row = DB::selectOne("
            SELECT
                COALESCE(SUM(CASE WHEN CURRENT_DATE - due_date <= 0 THEN balance END), 0) as current,
                COALESCE(SUM(CASE WHEN CURRENT_DATE - due_date BETWEEN 1 AND 30 THEN balance END), 0) as days_30,
                COALESCE(SUM(CASE WHEN CURRENT_DATE - due_date BETWEEN 31 AND 60 THEN balance END), 0) as days_60,
                COALESCE(SUM(CASE WHEN CURRENT_DATE - due_date BETWEEN 61 AND 90 THEN balance END), 0) as days_90,
                COALESCE(SUM(CASE WHEN CURRENT_DATE - due_date > 90 THEN balance END), 0) as days_over_90,
                COALESCE(SUM(balance), 0) as total
            FROM {$table}
            WHERE status NOT IN ('cancelled', 'voided', 'paid')
              AND balance > 0
        ");

        return (object) [
            'current'      => (float) $row->current,
            'days_30'      => (float) $row->days_30,
            'days_60'      => (float) $row->days_60,
            'days_90'      => (float) $row->days_90,
            'days_over_90' => (float) $row->days_over_90,
            'total'        => (float) $row->total,
        ];
    }
}
