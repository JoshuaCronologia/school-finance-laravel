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
    /**
     * AP Aging report per vendor.
     */
    public function aging(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $today = \Carbon\Carbon::parse($asOfDate);

        $vendors = Vendor::where('is_active', true)
            ->whereHas('bills', function ($q) {
                $q->whereNotIn('status', ['cancelled', 'voided', 'paid'])
                  ->where('balance', '>', 0);
            })
            ->with(['bills' => function ($q) {
                $q->whereNotIn('status', ['cancelled', 'voided', 'paid'])
                  ->where('balance', '>', 0);
            }])
            ->orderBy('name')
            ->get();

        $agingData = [];
        $totals = ['current' => 0, 'days_1_30' => 0, 'days_31_60' => 0, 'days_61_90' => 0, 'over_90' => 0, 'total' => 0];

        foreach ($vendors as $vendor) {
            $buckets = ['current' => 0, 'days_1_30' => 0, 'days_31_60' => 0, 'days_61_90' => 0, 'over_90' => 0, 'total' => 0];

            foreach ($vendor->bills as $bill) {
                $daysOverdue = $today->diffInDays($bill->due_date, false);
                $balance = (float) $bill->balance;

                if ($daysOverdue >= 0) {
                    $buckets['current'] += $balance;
                } elseif ($daysOverdue >= -30) {
                    $buckets['days_1_30'] += $balance;
                } elseif ($daysOverdue >= -60) {
                    $buckets['days_31_60'] += $balance;
                } elseif ($daysOverdue >= -90) {
                    $buckets['days_61_90'] += $balance;
                } else {
                    $buckets['over_90'] += $balance;
                }

                $buckets['total'] += $balance;
            }

            $agingData[] = (object) array_merge(['vendor' => $vendor], $buckets);

            foreach ($totals as $key => &$val) {
                $val += $buckets[$key];
            }
        }

        $totals = (object) $totals;

        return view('pages.ap.aging', compact('agingData', 'totals', 'asOfDate'));
    }

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

        // Use a subquery to get outstanding balance in 1 query instead of N+1
        $query->addSelect(['outstanding_balance' => ApBill::selectRaw('COALESCE(SUM(balance), 0)')
            ->whereColumn('vendor_id', 'vendors.id')
            ->whereNotIn('status', ['cancelled', 'voided', 'paid'])
        ]);

        $vendors = $query->orderBy('name')->paginate(25);

        $paymentTerms = PaymentTerm::where('is_active', true)->get();

        return view('pages.ap.vendors.index', compact('vendors', 'paymentTerms'));
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
            'vat_type' => 'nullable|in:vatable,non-vatable,zero-rated,tax_exempt',
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
            'vat_type' => 'nullable|in:vatable,non-vatable,zero-rated,tax_exempt',
            'withholding_tax_type' => 'nullable|string|max:50',
            'payment_terms_id' => 'nullable|exists:payment_terms,id',
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $vendor->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'vendor' => $vendor, 'message' => 'Vendor updated.']);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }
}
