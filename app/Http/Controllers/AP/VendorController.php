<?php

namespace App\Http\Controllers\AP;

use App\Http\Controllers\Controller;
use App\Models\ApBill;
use App\Models\ApPayment;
use App\Models\PaymentTerm;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VendorController extends Controller
{
    /**
     * AP Aging report per vendor.
     */
    public function aging(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $cacheKey = 'ap:aging:' . $asOfDate;

        $result = Cache::remember($cacheKey, 300, function () use ($asOfDate) {
            // Single SQL query to bucket all AP aging by vendor instead of N+1
            $rows = \Illuminate\Support\Facades\DB::select("
                SELECT
                    v.id as vendor_id, v.name as vendor_name, v.vendor_code,
                    COALESCE(SUM(CASE WHEN ? ::date - b.due_date <= 0 THEN b.balance END), 0) as current,
                    COALESCE(SUM(CASE WHEN ? ::date - b.due_date BETWEEN 1 AND 30 THEN b.balance END), 0) as days_1_30,
                    COALESCE(SUM(CASE WHEN ? ::date - b.due_date BETWEEN 31 AND 60 THEN b.balance END), 0) as days_31_60,
                    COALESCE(SUM(CASE WHEN ? ::date - b.due_date BETWEEN 61 AND 90 THEN b.balance END), 0) as days_61_90,
                    COALESCE(SUM(CASE WHEN ? ::date - b.due_date > 90 THEN b.balance END), 0) as over_90,
                    COALESCE(SUM(b.balance), 0) as total
                FROM vendors v
                JOIN ap_bills b ON b.vendor_id = v.id
                WHERE v.is_active = true
                  AND b.status NOT IN ('cancelled', 'voided', 'paid')
                  AND b.balance > 0
                GROUP BY v.id, v.name, v.vendor_code
                ORDER BY v.name
            ", [$asOfDate, $asOfDate, $asOfDate, $asOfDate, $asOfDate]);

            $agingData = [];
            $totals = ['current' => 0, 'days_1_30' => 0, 'days_31_60' => 0, 'days_61_90' => 0, 'over_90' => 0, 'total' => 0];

            foreach ($rows as $row) {
                $agingData[] = (object) [
                    'vendor' => (object) ['id' => $row->vendor_id, 'name' => $row->vendor_name, 'vendor_code' => $row->vendor_code],
                    'current' => (float) $row->current,
                    'days_1_30' => (float) $row->days_1_30,
                    'days_31_60' => (float) $row->days_31_60,
                    'days_61_90' => (float) $row->days_61_90,
                    'over_90' => (float) $row->over_90,
                    'total' => (float) $row->total,
                ];

                $totals['current'] += (float) $row->current;
                $totals['days_1_30'] += (float) $row->days_1_30;
                $totals['days_31_60'] += (float) $row->days_31_60;
                $totals['days_61_90'] += (float) $row->days_61_90;
                $totals['over_90'] += (float) $row->over_90;
                $totals['total'] += (float) $row->total;
            }

            return ['agingData' => $agingData, 'totals' => (object) $totals];
        });

        $agingData = $result['agingData'];
        $totals = $result['totals'];

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
