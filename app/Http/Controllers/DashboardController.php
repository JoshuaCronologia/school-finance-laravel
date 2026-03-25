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
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Finance dashboard - budget overview, department spending, trends, recent disbursements.
     */
    public function finance()
    {
        $totalBudget = Budget::sum('annual_budget');
        $committed = Budget::sum('committed');
        $actual = Budget::sum('actual');
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

        $recentDisbursements = DisbursementRequest::with('department', 'category')
            ->latest('request_date')
            ->take(5)
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

        return view('pages.dashboard.finance', compact(
            'totalBudget', 'committed', 'actual', 'remaining',
            'topDepartments', 'recentDisbursements',
            'departmentLabels', 'departmentDatasets',
            'monthlyLabels', 'monthlyDatasets',
            'categoryLabels', 'categoryValues'
        ));
    }

    /**
     * Accounting dashboard - AR/AP totals, cash balance, aging, top vendors/categories.
     */
    public function accounting()
    {
        $totalReceivables = ArInvoice::whereNotIn('status', ['cancelled', 'voided'])
            ->sum('balance');

        $totalPayables = ApBill::whereNotIn('status', ['cancelled', 'voided'])
            ->sum('balance');

        $cashBalance = JournalEntryLine::select(DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as balance'))
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->where('chart_of_accounts.account_code', '>=', '1010')
            ->where('chart_of_accounts.account_code', '<=', '1050')
            ->value('balance') ?? 0;

        $totalRevenue = JournalEntryLine::select(DB::raw('SUM(journal_entry_lines.credit - journal_entry_lines.debit) as total'))
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.account_type', 'revenue')
            ->where('journal_entries.status', 'posted')
            ->whereMonth('journal_entries.posting_date', now()->month)
            ->whereYear('journal_entries.posting_date', now()->year)
            ->value('total') ?? 0;

        $totalExpenses = JournalEntryLine::select(DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as total'))
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.account_type', 'expense')
            ->where('journal_entries.status', 'posted')
            ->whereMonth('journal_entries.posting_date', now()->month)
            ->whereYear('journal_entries.posting_date', now()->year)
            ->value('total') ?? 0;

        $netIncome = $totalRevenue - $totalExpenses;

        $recentJEs = JournalEntry::with('lines.account')
            ->latest('entry_date')
            ->take(10)
            ->get();

        $unpostedCount = JournalEntry::whereIn('status', ['draft'])->count();
        $pendingApprovalCount = JournalEntry::where('status', 'pending_approval')->count();

        $arAgingArray = $this->calculateArAging();
        $apAgingArray = $this->calculateApAging();

        $arAging = (object) [
            'current'      => $arAgingArray['current'],
            'days_30'      => $arAgingArray['1_30'],
            'days_60'      => $arAgingArray['31_60'],
            'days_90'      => $arAgingArray['61_90'],
            'days_over_90' => $arAgingArray['over_90'],
            'total'        => array_sum($arAgingArray),
        ];

        $apAging = (object) [
            'current'      => $apAgingArray['current'],
            'days_30'      => $apAgingArray['1_30'],
            'days_60'      => $apAgingArray['31_60'],
            'days_90'      => $apAgingArray['61_90'],
            'days_over_90' => $apAgingArray['over_90'],
            'total'        => array_sum($apAgingArray),
        ];

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

        return view('pages.dashboard.accounting', compact(
            'totalReceivables', 'totalPayables', 'cashBalance',
            'netIncome', 'totalRevenue', 'totalExpenses',
            'recentJEs', 'unpostedCount', 'pendingApprovalCount',
            'arAging', 'apAging', 'overdueAR', 'overdueAP',
            'topExpenseCategories', 'topVendors'
        ));
    }

    private function calculateArAging(): array
    {
        $invoices = ArInvoice::whereNotIn('status', ['cancelled', 'voided', 'paid'])
            ->where('balance', '>', 0)->get();

        $buckets = ['current' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, 'over_90' => 0];
        $today = now();

        foreach ($invoices as $invoice) {
            $daysOverdue = $today->diffInDays($invoice->due_date, false);
            if ($daysOverdue >= 0) $buckets['current'] += (float) $invoice->balance;
            elseif ($daysOverdue >= -30) $buckets['1_30'] += (float) $invoice->balance;
            elseif ($daysOverdue >= -60) $buckets['31_60'] += (float) $invoice->balance;
            elseif ($daysOverdue >= -90) $buckets['61_90'] += (float) $invoice->balance;
            else $buckets['over_90'] += (float) $invoice->balance;
        }

        return $buckets;
    }

    private function calculateApAging(): array
    {
        $bills = ApBill::whereNotIn('status', ['cancelled', 'voided', 'paid'])
            ->where('balance', '>', 0)->get();

        $buckets = ['current' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, 'over_90' => 0];
        $today = now();

        foreach ($bills as $bill) {
            $daysOverdue = $today->diffInDays($bill->due_date, false);
            if ($daysOverdue >= 0) $buckets['current'] += (float) $bill->balance;
            elseif ($daysOverdue >= -30) $buckets['1_30'] += (float) $bill->balance;
            elseif ($daysOverdue >= -60) $buckets['31_60'] += (float) $bill->balance;
            elseif ($daysOverdue >= -90) $buckets['61_90'] += (float) $bill->balance;
            else $buckets['over_90'] += (float) $bill->balance;
        }

        return $buckets;
    }
}
