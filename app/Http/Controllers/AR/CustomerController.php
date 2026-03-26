<?php

namespace App\Http\Controllers\AR;

use App\Http\Controllers\Controller;
use App\Models\ArInvoice;
use App\Models\Campus;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('invoices');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $query->addSelect(['outstanding_balance' => ArInvoice::selectRaw('COALESCE(SUM(balance), 0)')
            ->whereColumn('customer_id', 'customers.id')
            ->whereNotIn('status', ['cancelled', 'voided', 'paid'])
        ]);

        $customers = $query->orderBy('name')->paginate(25);

        $campuses = Campus::all();
        $arAccounts = ChartOfAccount::active()->where('account_type', 'asset')
            ->where('account_name', 'like', '%receivable%')
            ->orderBy('account_code')->get();

        return view('pages.ar.customers.index', compact('customers', 'campuses', 'arAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_code' => 'required|string|max:20|unique:customers',
            'customer_type' => 'required|in:student,parent,organization,government,other',
            'name' => 'required|string|max:255',
            'campus_id' => 'nullable|exists:campuses,id',
            'grade_level' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'tin' => 'nullable|string|max:20',
            'default_ar_account_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        $validated['is_active'] = true;
        $customer = Customer::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'customer' => $customer, 'message' => 'Customer created.']);
        }

        return redirect()->route('ar.customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->loadCount('invoices');

        $invoices = ArInvoice::where('customer_id', $customer->id)
            ->latest('invoice_date')
            ->paginate(15, ['*'], 'invoices_page');

        $stats = ArInvoice::selectRaw("
                COALESCE(SUM(CASE WHEN status NOT IN ('cancelled','voided','paid') THEN balance END), 0) as outstanding_balance,
                COALESCE(SUM(CASE WHEN status NOT IN ('cancelled','voided') THEN net_receivable END), 0) as total_invoiced,
                COALESCE(SUM(CASE WHEN status NOT IN ('cancelled','voided') THEN amount_paid END), 0) as total_paid
            ")
            ->where('customer_id', $customer->id)
            ->first();

        $outstandingBalance = (float) $stats->outstanding_balance;
        $totalInvoiced = (float) $stats->total_invoiced;
        $totalPaid = (float) $stats->total_paid;

        return view('pages.ar.customers.show', compact(
            'customer', 'invoices', 'outstandingBalance', 'totalInvoiced', 'totalPaid'
        ));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'customer_code' => "required|string|max:20|unique:customers,customer_code,{$customer->id}",
            'name' => 'required|string|max:255',
            'customer_type' => 'required|in:student,parent,organization,government,other',
            'campus_id' => 'nullable|exists:campuses,id',
            'grade_level' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'tin' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $customer->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'customer' => $customer, 'message' => 'Customer updated.']);
        }

        return redirect()->route('ar.customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->invoices()->exists()) {
            return back()->with('error', 'Cannot delete customer with existing invoices.');
        }

        $customer->delete();

        return redirect()->route('ar.customers.index')->with('success', 'Customer deleted successfully.');
    }
}
