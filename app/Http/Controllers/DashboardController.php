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
                DB::raw('MONTH(journal_entries.posting_date) as month'),
                DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as total')
            )
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.account_type', 'expense')
            ->where('journal_entries.status', 'posted')
            ->whereYear('journal_entries.posting_date', now()->year)
            ->groupBy(DB::raw('MONTH(journal_entries.posting_date)'))
            ->orderBy('month')
            ->get();

        $recentDisbursements = DisbursementRequest::with('department', 'category')
            ->latest('request_date')
            ->take(5)
            ->get();

        $spendingByCategory = Budget::with('category')
            ->select('category_id', DB::raw('SUM(actual) as total'))
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $departmentBudgets = Budget::with('department')
            ->select(
                'department_id',
                DB::raw('SUM(annual_budget) as budget'),
                DB::raw('SUM(actual) as actual'),
                DB::raw('SUM(committed) as committed')
            )
            ->groupBy('department_id')
            ->get();

        return view('pages.dashboard.finance', compact(
            'totalBudget', 'committed', 'actual', 'remaining',
            'topDepartments', 'monthlyExpenses', 'recentDisbursements',
            'spendingByCategory', 'departmentBudgets'
        ));
    }

    /**
     * Accounting dashboard - AR/AP totals, cash balance, aging, top vendors/categories.
     */
    public function accounting()
    {
        $totalAR = ArInvoice::whereNotIn('status', ['cancelled', 'voided'])
            ->sum('balance');

        $totalAP = ApBill::whereNotIn('status', ['cancelled', 'voided'])
            ->sum('balance');

        $cashBalance = JournalEntryLine::select(DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as balance'))
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->where('chart_of_accounts.account_code', '>=', '1010')
            ->where('chart_of_accounts.account_code', '<=', '1050')
            ->value('balance') ?? 0;

        $currentMonthRevenue = JournalEntryLine::select(DB::raw('SUM(journal_entry_lines.credit - journal_entry_lines.debit) as total'))
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.account_type', 'revenue')
            ->where('journal_entries.status', 'posted')
            ->whereMonth('journal_entries.posting_date', now()->month)
            ->whereYear('journal_entries.posting_date', now()->year)
            ->value('total') ?? 0;

        $currentMonthExpenses = JournalEntryLine::select(DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as total'))
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.account_type', 'expense')
            ->where('journal_entries.status', 'posted')
            ->whereMonth('journal_entries.posting_date', now()->month)
            ->whereYear('journal_entries.posting_date', now()->year)
            ->value('total') ?? 0;

        $recentEntries = JournalEntry::with('lines')
            ->latest('entry_date')
            ->take(10)
            ->get();

        $unpostedCount = JournalEntry::where('status', 'draft')->count();

        $arAging = $this->calculateArAging();
        $apAging = $this->calculateApAging();

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
            'totalAR', 'totalAP', 'cashBalance',
            'currentMonthRevenue', 'currentMonthExpenses',
            'recentEntries', 'unpostedCount',
            'arAging', 'apAging',
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
