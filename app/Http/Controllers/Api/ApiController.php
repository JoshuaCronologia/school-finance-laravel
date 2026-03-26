<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\ApBill;
use App\Models\ApPayment;
use App\Models\ArCollection;
use App\Models\ArInvoice;
use App\Models\AuditLog;
use App\Models\Budget;
use App\Models\BudgetAllocation;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\DisbursementPayment;
use App\Models\DisbursementRequest;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Setting;
use App\Models\Vendor;
use App\Services\AuditService;
use App\Services\BudgetService;
use App\Services\NumberingService;
use App\Services\PostingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    // ---------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------

    private function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    private function error(string $message, int $code = 400, $errors = null): JsonResponse
    {
        return response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ]), $code);
    }

    // ---------------------------------------------------------------
    // DASHBOARD
    // ---------------------------------------------------------------

    public function financeDashboard(): JsonResponse
    {
        return $this->success(Cache::remember('api:finance_dashboard', 300, function () {
            $summary = Budget::selectRaw('
                COALESCE(SUM(annual_budget), 0) as total_budget,
                COALESCE(SUM(committed), 0) as committed,
                COALESCE(SUM(actual), 0) as actual
            ')->first();

            return [
                'total_budget' => (float) $summary->total_budget,
                'committed' => (float) $summary->committed,
                'actual' => (float) $summary->actual,
                'remaining' => (float) $summary->total_budget - $summary->committed - $summary->actual,
                'recent_disbursements' => DisbursementRequest::with('department')
                    ->latest('request_date')->take(5)->get(),
            ];
        }));
    }

    public function accountingDashboard(): JsonResponse
    {
        return $this->success(Cache::remember('api:accounting_dashboard', 300, function () {
            $totalAR = (float) ArInvoice::whereNotIn('status', ['cancelled', 'voided'])->sum('balance');
            $totalAP = (float) ApBill::whereNotIn('status', ['cancelled', 'voided'])->sum('balance');
            $unpostedCount = JournalEntry::where('status', 'draft')->count();

            return [
                'total_ar' => $totalAR,
                'total_ap' => $totalAP,
                'unposted_je_count' => $unpostedCount,
                'recent_entries' => JournalEntry::latest('entry_date')->take(10)->get(),
            ];
        }));
    }

    // ---------------------------------------------------------------
    // BUDGETS
    // ---------------------------------------------------------------

    public function budgetIndex(Request $request): JsonResponse
    {
        $query = Budget::with('department', 'category');
        if ($request->filled('school_year')) $query->where('school_year', $request->school_year);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);

        return $this->success($query->orderBy('department_id')->paginate($request->input('per_page', 25)));
    }

    public function budgetStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'budget_name' => 'required|string|max:255',
            'school_year' => 'required|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'required|exists:expense_categories,id',
            'annual_budget' => 'required|numeric|min:0',
        ]);

        $validated['status'] = 'active';
        $validated['committed'] = 0;
        $validated['actual'] = 0;
        $budget = Budget::create($validated);

        return $this->success($budget, 'Budget created.', 201);
    }

    public function budgetShow(Budget $budget): JsonResponse
    {
        $budget->load('department', 'category', 'allocations');
        return $this->success($budget);
    }

    public function budgetUpdate(Request $request, Budget $budget): JsonResponse
    {
        $validated = $request->validate([
            'budget_name' => 'sometimes|string|max:255',
            'annual_budget' => 'sometimes|numeric|min:0',
        ]);

        $budget->update($validated);
        return $this->success($budget, 'Budget updated.');
    }

    public function budgetDestroy(Budget $budget): JsonResponse
    {
        if ($budget->committed > 0 || $budget->actual > 0) {
            return $this->error('Cannot delete budget with committed or actual spending.');
        }
        $budget->allocations()->delete();
        $budget->delete();
        return $this->success(null, 'Budget deleted.');
    }

    // ---------------------------------------------------------------
    // AP BILLS
    // ---------------------------------------------------------------

    public function billIndex(Request $request): JsonResponse
    {
        $query = ApBill::with('vendor', 'department');
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('vendor_id')) $query->where('vendor_id', $request->vendor_id);

        return $this->success($query->latest('bill_date')->paginate($request->input('per_page', 20)));
    }

    public function billShow(ApBill $bill): JsonResponse
    {
        $bill->load('vendor', 'lines.account', 'payments', 'journalEntry.lines');
        return $this->success($bill);
    }

    public function billApprove(ApBill $bill): JsonResponse
    {
        if ($bill->status !== 'draft') return $this->error('Only draft bills can be approved.');
        $bill->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        return $this->success($bill, 'Bill approved.');
    }

    // ---------------------------------------------------------------
    // DISBURSEMENTS
    // ---------------------------------------------------------------

    public function disbursementIndex(Request $request): JsonResponse
    {
        $query = DisbursementRequest::with('department', 'category', 'payment');
        if ($request->filled('status')) $query->where('status', $request->status);

        return $this->success($query->latest('request_date')->paginate($request->input('per_page', 20)));
    }

    public function disbursementShow(DisbursementRequest $disbursement): JsonResponse
    {
        $disbursement->load('department', 'category', 'items', 'approvals', 'payment');
        return $this->success($disbursement);
    }

    // ---------------------------------------------------------------
    // VENDORS
    // ---------------------------------------------------------------

    public function vendorIndex(Request $request): JsonResponse
    {
        $query = Vendor::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('vendor_code', 'like', "%{$search}%"));
        }

        return $this->success($query->orderBy('name')->paginate($request->input('per_page', 25)));
    }

    public function vendorShow(Vendor $vendor): JsonResponse
    {
        return $this->success($vendor);
    }

    public function vendorTransactions(Vendor $vendor): JsonResponse
    {
        return $this->success([
            'bills' => ApBill::where('vendor_id', $vendor->id)->latest('bill_date')->take(20)->get(),
            'payments' => ApPayment::where('vendor_id', $vendor->id)->latest('payment_date')->take(20)->get(),
        ]);
    }

    // ---------------------------------------------------------------
    // AR INVOICES
    // ---------------------------------------------------------------

    public function invoiceIndex(Request $request): JsonResponse
    {
        $query = ArInvoice::with('customer');
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);

        return $this->success($query->latest('invoice_date')->paginate($request->input('per_page', 20)));
    }

    public function invoiceShow(ArInvoice $invoice): JsonResponse
    {
        $invoice->load('customer', 'lines.revenueAccount', 'allocations.collection');
        return $this->success($invoice);
    }

    public function agingReport(): JsonResponse
    {
        $invoices = ArInvoice::with('customer')
            ->whereNotIn('status', ['cancelled', 'voided', 'paid'])
            ->where('balance', '>', 0)->get();

        $buckets = ['current' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, 'over_90' => 0];
        $today = now();

        foreach ($invoices as $inv) {
            $days = $today->diffInDays($inv->due_date, false);
            if ($days >= 0) $buckets['current'] += (float) $inv->balance;
            elseif ($days >= -30) $buckets['1_30'] += (float) $inv->balance;
            elseif ($days >= -60) $buckets['31_60'] += (float) $inv->balance;
            elseif ($days >= -90) $buckets['61_90'] += (float) $inv->balance;
            else $buckets['over_90'] += (float) $inv->balance;
        }

        return $this->success(['buckets' => $buckets, 'total' => array_sum($buckets)]);
    }

    // ---------------------------------------------------------------
    // AR COLLECTIONS
    // ---------------------------------------------------------------

    public function collectionIndex(Request $request): JsonResponse
    {
        $query = ArCollection::with('customer');
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);

        return $this->success($query->latest('collection_date')->paginate($request->input('per_page', 20)));
    }

    public function collectionShow(ArCollection $collection): JsonResponse
    {
        $collection->load('customer', 'allocations.invoice');
        return $this->success($collection);
    }

    // ---------------------------------------------------------------
    // CUSTOMERS
    // ---------------------------------------------------------------

    public function customerIndex(Request $request): JsonResponse
    {
        $query = Customer::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('customer_code', 'like', "%{$search}%"));
        }

        return $this->success($query->orderBy('name')->paginate($request->input('per_page', 25)));
    }

    public function customerShow(Customer $customer): JsonResponse
    {
        return $this->success($customer);
    }

    public function customerStatement(Customer $customer, Request $request): JsonResponse
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $invoices = ArInvoice::where('customer_id', $customer->id)
            ->whereNotIn('status', ['cancelled', 'voided'])
            ->whereDate('invoice_date', '<=', $asOfDate)
            ->orderBy('invoice_date')->get();

        $collections = ArCollection::where('customer_id', $customer->id)
            ->whereNotIn('status', ['cancelled', 'voided'])
            ->whereDate('collection_date', '<=', $asOfDate)
            ->orderBy('collection_date')->get();

        return $this->success([
            'customer' => $customer,
            'invoices' => $invoices,
            'collections' => $collections,
            'total_invoiced' => $invoices->sum('net_receivable'),
            'total_collected' => $collections->sum('amount_received'),
            'balance' => $invoices->sum('net_receivable') - $collections->sum('amount_received'),
        ]);
    }

    // ---------------------------------------------------------------
    // CHART OF ACCOUNTS
    // ---------------------------------------------------------------

    public function coaIndex(Request $request): JsonResponse
    {
        $query = ChartOfAccount::with('parent');
        if ($request->filled('account_type')) $query->where('account_type', $request->account_type);

        return $this->success($query->orderBy('account_code')->paginate($request->input('per_page', 50)));
    }

    public function coaTree(): JsonResponse
    {
        $accounts = ChartOfAccount::with('children')
            ->whereNull('parent_id')
            ->orderBy('account_code')
            ->get();

        return $this->success($accounts);
    }

    public function coaShow(ChartOfAccount $chartOfAccount): JsonResponse
    {
        $chartOfAccount->load('parent', 'children');
        return $this->success($chartOfAccount);
    }

    // ---------------------------------------------------------------
    // JOURNAL ENTRIES
    // ---------------------------------------------------------------

    public function jeIndex(Request $request): JsonResponse
    {
        $query = JournalEntry::with('lines.account');
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('journal_type')) $query->where('journal_type', $request->journal_type);

        return $this->success($query->latest('entry_date')->paginate($request->input('per_page', 20)));
    }

    public function jeShow(JournalEntry $journalEntry): JsonResponse
    {
        $journalEntry->load('lines.account', 'lines.department');
        return $this->success($journalEntry);
    }

    public function jePost(JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->status !== 'draft') return $this->error('Only draft entries can be posted.');

        $journalEntry->update([
            'status' => 'posted',
            'posting_date' => now(),
            'posted_by' => auth()->id(),
        ]);

        return $this->success($journalEntry, 'Journal entry posted.');
    }

    public function jeReverse(JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->status !== 'posted') return $this->error('Only posted entries can be reversed.');

        $reversal = app(PostingService::class)->reverseEntry($journalEntry);
        return $this->success($reversal, "Reversal entry {$reversal->entry_number} created.");
    }

    public function ledgerInquiry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:chart_of_accounts,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfYear()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();

        $entries = JournalEntryLine::select('journal_entry_lines.*', 'je.entry_number', 'je.posting_date', 'je.description as je_description')
            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->where('journal_entry_lines.account_id', $validated['account_id'])
            ->where('je.status', 'posted')
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo)
            ->orderBy('je.posting_date')
            ->get();

        return $this->success($entries);
    }

    public function periodStatus(): JsonResponse
    {
        return $this->success(AccountingPeriod::orderBy('start_date', 'desc')->get());
    }

    // ---------------------------------------------------------------
    // REPORTS
    // ---------------------------------------------------------------

    public function trialBalance(Request $request): JsonResponse
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
            ->filter(fn($a) => $a->total_debit > 0 || $a->total_credit > 0)
            ->values();

        return $this->success([
            'accounts' => $accounts,
            'total_debit' => $accounts->sum('total_debit'),
            'total_credit' => $accounts->sum('total_credit'),
        ]);
    }

    public function budgetVsActual(): JsonResponse
    {
        $budgets = Budget::with('department', 'category')
            ->where('status', 'active')
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->budget_name,
                'department' => $b->department?->name,
                'category' => $b->category?->name,
                'annual_budget' => $b->annual_budget,
                'committed' => $b->committed,
                'actual' => $b->actual,
                'remaining' => $b->annual_budget - $b->committed - $b->actual,
                'utilization_pct' => $b->annual_budget > 0
                    ? round(($b->committed + $b->actual) / $b->annual_budget * 100, 1) : 0,
            ]);

        return $this->success($budgets);
    }

    // ---------------------------------------------------------------
    // TAX
    // ---------------------------------------------------------------

    public function bir2307(Request $request): JsonResponse
    {
        $query = ApPayment::with('vendor')
            ->where('status', 'posted')
            ->where('withholding_tax', '>', 0);

        if ($request->filled('vendor_id')) $query->where('vendor_id', $request->vendor_id);
        if ($request->filled('date_from')) $query->whereDate('payment_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('payment_date', '<=', $request->date_to);

        return $this->success($query->orderBy('payment_date')->get());
    }

    // ---------------------------------------------------------------
    // AUDIT
    // ---------------------------------------------------------------

    public function auditIndex(Request $request): JsonResponse
    {
        $query = AuditLog::query();
        if ($request->filled('module')) $query->where('module', $request->module);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);

        return $this->success($query->latest()->paginate($request->input('per_page', 25)));
    }

    // ---------------------------------------------------------------
    // SETTINGS
    // ---------------------------------------------------------------

    public function settingsIndex(): JsonResponse
    {
        return $this->success(Setting::orderBy('category')->orderBy('key')->get()->groupBy('category'));
    }

    public function settingsUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $s) {
            Setting::set($s['key'], $s['value'] ?? '');
        }

        return $this->success(null, 'Settings updated.');
    }
}
