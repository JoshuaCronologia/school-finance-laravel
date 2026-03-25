<?php

namespace App\Http\Controllers\AP;

use App\Http\Controllers\Controller;
use App\Models\ApBill;
use App\Models\ApBillLine;
use App\Models\Campus;
use App\Models\ChartOfAccount;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\ExpenseCategory;
use App\Models\PaymentTerm;
use App\Models\TaxCode;
use App\Models\Vendor;
use App\Services\AuditService;
use App\Services\NumberingService;
use App\Services\PostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $query = ApBill::with('vendor', 'department', 'campus');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('bill_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('bill_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($v) => $v->where('name', 'like', "%{$search}%"));
            });
        }

        $bills = $query->latest('bill_date')->paginate(20);

        $totalOutstanding = ApBill::whereNotIn('status', ['cancelled', 'voided', 'paid'])->sum('balance');
        $totalOverdue = ApBill::whereNotIn('status', ['cancelled', 'voided', 'paid'])
            ->where('due_date', '<', now())->sum('balance');

        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('pages.ap.bills.index', compact('bills', 'totalOutstanding', 'totalOverdue', 'vendors'));
    }

    public function create()
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $accounts = ChartOfAccount::active()->postable()->orderBy('account_code')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $campuses = Campus::where('is_active', true)->get();
        $costCenters = CostCenter::where('is_active', true)->get();
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $paymentTerms = PaymentTerm::where('is_active', true)->get();
        $taxCodes = TaxCode::where('is_active', true)->get();

        return view('pages.ap.bills.create', compact(
            'vendors', 'accounts', 'departments', 'campuses',
            'costCenters', 'categories', 'paymentTerms', 'taxCodes'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'campus_id' => 'nullable|exists:campuses,id',
            'department_id' => 'nullable|exists:departments,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'category_id' => 'nullable|exists:expense_categories,id',
            'payment_terms_id' => 'nullable|exists:payment_terms,id',
            'lines' => 'required|array|min:1',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_cost' => 'required|numeric|min:0',
            'lines.*.amount' => 'required|numeric|min:0',
            'lines.*.tax_code_id' => 'nullable|exists:tax_codes,id',
            'lines.*.department_id' => 'nullable|exists:departments,id',
        ]);

        try {
            $bill = DB::transaction(function () use ($validated) {
                $grossAmount = collect($validated['lines'])->sum('amount');
                $vatAmount = 0;
                $whtAmount = 0;

                foreach ($validated['lines'] as $line) {
                    if (!empty($line['tax_code_id'])) {
                        $tax = TaxCode::find($line['tax_code_id']);
                        if ($tax && $tax->type === 'vat') {
                            $vatAmount += $line['amount'] * ($tax->rate / 100);
                        }
                    }
                }

                $bill = ApBill::create([
                    'bill_number' => NumberingService::generate('BILL'),
                    'bill_date' => $validated['bill_date'],
                    'posting_date' => $validated['bill_date'],
                    'due_date' => $validated['due_date'],
                    'vendor_id' => $validated['vendor_id'],
                    'campus_id' => $validated['campus_id'] ?? null,
                    'department_id' => $validated['department_id'] ?? null,
                    'cost_center_id' => $validated['cost_center_id'] ?? null,
                    'category_id' => $validated['category_id'] ?? null,
                    'payment_terms_id' => $validated['payment_terms_id'] ?? null,
                    'reference_number' => $validated['reference_number'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'gross_amount' => $grossAmount,
                    'vat_amount' => $vatAmount,
                    'withholding_tax' => $whtAmount,
                    'net_payable' => $grossAmount + $vatAmount - $whtAmount,
                    'balance' => $grossAmount + $vatAmount - $whtAmount,
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['lines'] as $line) {
                    ApBillLine::create([
                        'bill_id' => $bill->id,
                        'account_id' => $line['account_id'],
                        'description' => $line['description'],
                        'quantity' => $line['quantity'],
                        'unit_cost' => $line['unit_cost'],
                        'amount' => $line['amount'],
                        'tax_code_id' => $line['tax_code_id'] ?? null,
                        'department_id' => $line['department_id'] ?? null,
                    ]);
                }

                app(AuditService::class)->log('create', 'ap_bill', $bill, null, 'Bill created');

                return $bill;
            });

            return redirect()->route('ap.bills.show', $bill)->with('success', 'Bill created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create bill: ' . $e->getMessage());
        }
    }

    public function show(ApBill $bill)
    {
        $bill->load([
            'vendor', 'department', 'campus', 'costCenter', 'category',
            'lines.account', 'lines.taxCode',
            'payments.allocations',
            'journalEntry.lines.account',
        ]);

        return view('pages.ap.bills.show', compact('bill'));
    }

    public function edit(ApBill $bill)
    {
        if ($bill->status !== 'draft') {
            return redirect()->route('ap.bills.show', $bill)
                ->with('error', 'Only draft bills can be edited.');
        }

        $bill->load('lines');
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $accounts = ChartOfAccount::active()->postable()->orderBy('account_code')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $campuses = Campus::where('is_active', true)->get();
        $costCenters = CostCenter::where('is_active', true)->get();
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $paymentTerms = PaymentTerm::where('is_active', true)->get();
        $taxCodes = TaxCode::where('is_active', true)->get();

        return view('pages.ap.bills.create', compact(
            'bill', 'vendors', 'accounts', 'departments', 'campuses',
            'costCenters', 'categories', 'paymentTerms', 'taxCodes'
        ));
    }

    public function update(Request $request, ApBill $bill)
    {
        if ($bill->status !== 'draft') {
            return back()->with('error', 'Only draft bills can be updated.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'category_id' => 'nullable|exists:expense_categories,id',
            'lines' => 'required|array|min:1',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_cost' => 'required|numeric|min:0',
            'lines.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated, $bill) {
                $oldValues = $bill->toArray();
                $grossAmount = collect($validated['lines'])->sum('amount');

                $bill->update([
                    'vendor_id' => $validated['vendor_id'],
                    'bill_date' => $validated['bill_date'],
                    'due_date' => $validated['due_date'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'department_id' => $validated['department_id'] ?? null,
                    'campus_id' => $validated['campus_id'] ?? null,
                    'category_id' => $validated['category_id'] ?? null,
                    'gross_amount' => $grossAmount,
                    'net_payable' => $grossAmount,
                    'balance' => $grossAmount,
                ]);

                $bill->lines()->delete();

                foreach ($validated['lines'] as $line) {
                    ApBillLine::create([
                        'bill_id' => $bill->id,
                        'account_id' => $line['account_id'],
                        'description' => $line['description'],
                        'quantity' => $line['quantity'],
                        'unit_cost' => $line['unit_cost'],
                        'amount' => $line['amount'],
                    ]);
                }

                app(AuditService::class)->log('update', 'ap_bill', $bill, $oldValues, 'Bill updated');
            });

            return redirect()->route('ap.bills.show', $bill)->with('success', 'Bill updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update bill: ' . $e->getMessage());
        }
    }

    public function approve(ApBill $bill)
    {
        if ($bill->status !== 'draft') {
            return back()->with('error', 'Only draft bills can be approved.');
        }

        $bill->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        app(AuditService::class)->log('approve', 'ap_bill', $bill, null, 'Bill approved');

        return back()->with('success', 'Bill approved successfully.');
    }

    public function post(ApBill $bill)
    {
        if ($bill->status !== 'approved') {
            return back()->with('error', 'Only approved bills can be posted.');
        }

        try {
            $entry = app(PostingService::class)->postBill($bill);
            app(AuditService::class)->log('post', 'ap_bill', $bill, null, "Posted to GL: {$entry->entry_number}");

            return back()->with('success', "Bill posted to GL. Entry: {$entry->entry_number}");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to post bill: ' . $e->getMessage());
        }
    }
}
