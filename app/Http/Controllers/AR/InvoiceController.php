<?php

namespace App\Http\Controllers\AR;

use App\Http\Controllers\Controller;
use App\Models\ArInvoice;
use App\Models\ArInvoiceLine;
use App\Models\Campus;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Department;
use App\Models\TaxCode;
use App\Services\AuditService;
use App\Services\NumberingService;
use App\Services\PostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = ArInvoice::with('customer', 'campus');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->latest('invoice_date')->paginate(20);

        // Stats — 1 query instead of 5
        $stats = ArInvoice::selectRaw("
            COALESCE(SUM(CASE WHEN status NOT IN ('cancelled','voided') THEN net_receivable END), 0) as total_invoiced,
            COALESCE(SUM(CASE WHEN status NOT IN ('cancelled','voided') THEN amount_paid END), 0) as total_collected,
            COALESCE(SUM(CASE WHEN status NOT IN ('cancelled','voided','paid') THEN balance END), 0) as total_outstanding,
            COALESCE(SUM(CASE WHEN status NOT IN ('cancelled','voided','paid') AND due_date < CURRENT_DATE THEN balance END), 0) as total_overdue,
            COUNT(CASE WHEN status NOT IN ('cancelled','voided') THEN 1 END) as invoice_count
        ")->first();
        $totalInvoiced = (float) $stats->total_invoiced;
        $totalCollected = (float) $stats->total_collected;
        $totalOutstanding = (float) $stats->total_outstanding;
        $totalOverdue = (float) $stats->total_overdue;
        $invoiceCount = (int) $stats->invoice_count;

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $campuses = Campus::all();
        $departments = Department::where('is_active', true)->get();
        $revenueAccounts = ChartOfAccount::active()->where('account_type', 'revenue')->orderBy('account_code')->get();
        $taxCodes = TaxCode::where('is_active', true)->get();

        return view('pages.ar.invoices.index', compact(
            'invoices', 'totalInvoiced', 'totalCollected', 'totalOutstanding', 'totalOverdue', 'invoiceCount',
            'customers', 'campuses', 'departments', 'revenueAccounts', 'taxCodes'
        ));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $campuses = Campus::all();
        $departments = Department::where('is_active', true)->get();
        $revenueAccounts = ChartOfAccount::active()->where('account_type', 'revenue')->orderBy('account_code')->get();
        $taxCodes = TaxCode::where('is_active', true)->get();

        return view('pages.ar.invoices.create', compact(
            'customers', 'campuses', 'departments', 'revenueAccounts', 'taxCodes'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'campus_id' => 'nullable|exists:campuses,id',
            'school_year' => 'nullable|string|max:20',
            'semester' => 'nullable|string|max:20',
            'billing_period' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.fee_code' => 'nullable|string|max:20',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_amount' => 'required|numeric|min:0',
            'lines.*.amount' => 'required|numeric|min:0',
            'lines.*.revenue_account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.department_id' => 'nullable|exists:departments,id',
            'lines.*.tax_code_id' => 'nullable|exists:tax_codes,id',
        ]);

        try {
            $invoice = DB::transaction(function () use ($validated) {
                $grossAmount = collect($validated['lines'])->sum('amount');
                $taxAmount = 0;

                foreach ($validated['lines'] as $line) {
                    if (!empty($line['tax_code_id'])) {
                        $tax = TaxCode::find($line['tax_code_id']);
                        if ($tax) {
                            $taxAmount += $line['amount'] * ($tax->rate / 100);
                        }
                    }
                }

                $netReceivable = $grossAmount + $taxAmount;

                $invoice = ArInvoice::create([
                    'invoice_number' => NumberingService::generate('INV'),
                    'invoice_date' => $validated['invoice_date'],
                    'posting_date' => $validated['invoice_date'],
                    'due_date' => $validated['due_date'],
                    'customer_id' => $validated['customer_id'],
                    'campus_id' => $validated['campus_id'] ?? null,
                    'school_year' => $validated['school_year'] ?? null,
                    'semester' => $validated['semester'] ?? null,
                    'billing_period' => $validated['billing_period'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'gross_amount' => $grossAmount,
                    'discount_amount' => 0,
                    'tax_amount' => $taxAmount,
                    'net_receivable' => $netReceivable,
                    'amount_paid' => 0,
                    'balance' => $netReceivable,
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['lines'] as $line) {
                    ArInvoiceLine::create([
                        'invoice_id' => $invoice->id,
                        'fee_code' => $line['fee_code'] ?? null,
                        'description' => $line['description'],
                        'quantity' => $line['quantity'],
                        'unit_amount' => $line['unit_amount'],
                        'amount' => $line['amount'],
                        'revenue_account_id' => $line['revenue_account_id'],
                        'department_id' => $line['department_id'] ?? null,
                        'tax_code_id' => $line['tax_code_id'] ?? null,
                    ]);
                }

                app(AuditService::class)->log('create', 'ar_invoice', $invoice, null, 'Invoice created');
                \App\Services\NotificationService::invoiceCreated($invoice->load('customer'));

                return $invoice;
            });

            return redirect()->route('ar.invoices.show', $invoice)->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function show(ArInvoice $invoice)
    {
        $invoice->load([
            'customer', 'campus',
            'lines.revenueAccount', 'lines.department',
            'allocations.collection',
            'journalEntry.lines.account',
        ]);

        return view('pages.ar.invoices.show', compact('invoice'));
    }

    public function update(Request $request, ArInvoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be updated.');
        }

        $validated = $request->validate([
            'status' => 'nullable|in:draft,posted,cancelled',
            'customer_id' => 'nullable|exists:customers,id',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'lines' => 'nullable|array|min:1',
            'lines.*.description' => 'required_with:lines|string',
            'lines.*.quantity' => 'required_with:lines|numeric|min:0.01',
            'lines.*.unit_amount' => 'required_with:lines|numeric|min:0',
            'lines.*.amount' => 'required_with:lines|numeric|min:0',
            'lines.*.revenue_account_id' => 'required_with:lines|exists:chart_of_accounts,id',
        ]);

        // Handle posting
        if (($validated['status'] ?? null) === 'posted') {
            try {
                $entry = app(PostingService::class)->postInvoice($invoice);
                app(AuditService::class)->log('post', 'ar_invoice', $invoice, null, "Posted to GL: {$entry->entry_number}");
                return back()->with('success', 'Invoice posted to GL.');
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to post invoice: ' . $e->getMessage());
            }
        }

        // Handle update
        try {
            DB::transaction(function () use ($validated, $invoice) {
                $oldValues = $invoice->toArray();

                if (isset($validated['lines'])) {
                    $grossAmount = collect($validated['lines'])->sum('amount');
                    $invoice->lines()->delete();

                    foreach ($validated['lines'] as $line) {
                        ArInvoiceLine::create([
                            'invoice_id' => $invoice->id,
                            'description' => $line['description'],
                            'quantity' => $line['quantity'],
                            'unit_amount' => $line['unit_amount'],
                            'amount' => $line['amount'],
                            'revenue_account_id' => $line['revenue_account_id'],
                        ]);
                    }

                    $invoice->update([
                        'gross_amount' => $grossAmount,
                        'net_receivable' => $grossAmount,
                        'balance' => $grossAmount,
                    ]);
                }

                $invoice->update(array_filter([
                    'customer_id' => $validated['customer_id'] ?? null,
                    'due_date' => $validated['due_date'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'status' => $validated['status'] ?? null,
                ], fn($v) => $v !== null));

                app(AuditService::class)->log('update', 'ar_invoice', $invoice, $oldValues, 'Invoice updated');
            });

            return back()->with('success', 'Invoice updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }
}
