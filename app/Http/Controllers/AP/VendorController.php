<?php

namespace App\Http\Controllers\AP;

use App\Http\Controllers\Controller;
use App\Models\ApBill;
use App\Models\ApPayment;
use App\Models\PaymentTerm;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::withCount('bills');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('vendor_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('tin', 'like', "%{$search}%");
            });
        }

        if ($request->filled('vendor_type')) {
            $query->where('vendor_type', $request->vendor_type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $vendors = $query->orderBy('name')->paginate(25);

        // Attach outstanding balance per vendor
        $vendors->getCollection()->transform(function ($vendor) {
            $vendor->outstanding_balance = ApBill::where('vendor_id', $vendor->id)
                ->whereNotIn('status', ['cancelled', 'voided', 'paid'])
                ->sum('balance');
            return $vendor;
        });

        $paymentTerms = PaymentTerm::where('is_active', true)->get();

        return view('pages.ap.vendors', compact('vendors', 'paymentTerms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_code' => 'required|string|max:20|unique:vendors',
            'name' => 'required|string|max:255',
            'vendor_type' => 'required|in:supplier,contractor,utility,government,individual,other',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tin' => 'nullable|string|max:20',
            'vat_type' => 'nullable|in:vatable,non-vatable,zero-rated',
            'withholding_tax_type' => 'nullable|string|max:50',
            'payment_terms_id' => 'nullable|exists:payment_terms,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = true;
        $vendor = Vendor::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'vendor' => $vendor, 'message' => 'Vendor created.']);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->loadCount('bills');

        $bills = ApBill::where('vendor_id', $vendor->id)
            ->latest('bill_date')
            ->paginate(15, ['*'], 'bills_page');

        $payments = ApPayment::where('vendor_id', $vendor->id)
            ->latest('payment_date')
            ->paginate(15, ['*'], 'payments_page');

        $outstandingBalance = ApBill::where('vendor_id', $vendor->id)
            ->whereNotIn('status', ['cancelled', 'voided', 'paid'])
            ->sum('balance');

        $totalPurchases = ApBill::where('vendor_id', $vendor->id)
            ->whereNotIn('status', ['cancelled', 'voided'])
            ->sum('gross_amount');

        $totalPayments = ApPayment::where('vendor_id', $vendor->id)
            ->where('status', 'posted')
            ->sum('net_amount');

        return view('pages.ap.vendor-show', compact(
            'vendor', 'bills', 'payments', 'outstandingBalance', 'totalPurchases', 'totalPayments'
        ));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'vendor_code' => "required|string|max:20|unique:vendors,vendor_code,{$vendor->id}",
            'name' => 'required|string|max:255',
            'vendor_type' => 'required|in:supplier,contractor,utility,government,individual,other',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tin' => 'nullable|string|max:20',
            'vat_type' => 'nullable|in:vatable,non-vatable,zero-rated',
            'payment_terms_id' => 'nullable|exists:payment_terms,id',
            'is_active' => 'boolean',
        ]);

        $vendor->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'vendor' => $vendor, 'message' => 'Vendor updated.']);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }
}
