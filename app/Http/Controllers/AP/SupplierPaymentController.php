<?php

namespace App\Http\Controllers\AP;

use App\Http\Controllers\Controller;
use App\Models\ApBill;
use App\Models\ApPayment;
use App\Models\ApPaymentAllocation;
use App\Models\Vendor;
use App\Services\AuditService;
use App\Services\NumberingService;
use App\Services\PostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = ApPayment::with('vendor');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('check_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($v) => $v->where('name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->latest('payment_date')->paginate(20);

        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('pages.ap.supplier-payments', compact('payments', 'vendors'));
    }

    public function create()
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        $outstandingBills = ApBill::with('vendor')
            ->whereIn('status', ['posted', 'approved'])
            ->where('balance', '>', 0)
            ->orderBy('due_date')
            ->get();

        return view('pages.ap.supplier-payment-create', compact('vendors', 'outstandingBills'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,bank_transfer,online',
            'bank_account' => 'nullable|string|max:100',
            'check_number' => 'nullable|string|max:50',
            'check_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'withholding_tax' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'allocations' => 'required|array|min:1',
            'allocations.*.bill_id' => 'required|exists:ap_bills,id',
            'allocations.*.amount_applied' => 'required|numeric|min:0.01',
        ]);

        try {
            $payment = DB::transaction(function () use ($validated) {
                $grossAmount = collect($validated['allocations'])->sum('amount_applied');
                $discount = $validated['discount_amount'] ?? 0;
                $wht = $validated['withholding_tax'] ?? 0;
                $netAmount = $grossAmount - $discount - $wht;

                $payment = ApPayment::create([
                    'payment_number' => NumberingService::generate('PAY'),
                    'payment_date' => $validated['payment_date'],
                    'vendor_id' => $validated['vendor_id'],
                    'payment_method' => $validated['payment_method'],
                    'bank_account' => $validated['bank_account'] ?? null,
                    'check_number' => $validated['check_number'] ?? null,
                    'check_date' => $validated['check_date'] ?? null,
                    'reference_number' => $validated['reference_number'] ?? null,
                    'gross_amount' => $grossAmount,
                    'discount_amount' => $discount,
                    'withholding_tax' => $wht,
                    'net_amount' => $netAmount,
                    'status' => 'draft',
                    'remarks' => $validated['remarks'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['allocations'] as $alloc) {
                    if ($alloc['amount_applied'] > 0) {
                        ApPaymentAllocation::create([
                            'payment_id' => $payment->id,
                            'bill_id' => $alloc['bill_id'],
                            'amount_applied' => $alloc['amount_applied'],
                        ]);

                        $bill = ApBill::find($alloc['bill_id']);
                        $bill->decrement('balance', $alloc['amount_applied']);
                        if ($bill->balance <= 0) {
                            $bill->update(['status' => 'paid']);
                        }
                    }
                }

                // Post to GL
                $entry = app(PostingService::class)->postPayment($payment);

                app(AuditService::class)->log('create', 'ap_payment', $payment, null,
                    "Payment created and posted: {$entry->entry_number}");

                return $payment;
            });

            return redirect()->route('ap.supplier-payments.show', $payment)
                ->with('success', "Payment {$payment->payment_number} created and posted.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create payment: ' . $e->getMessage());
        }
    }

    public function show(ApPayment $payment)
    {
        $payment->load([
            'vendor', 'allocations.bill',
            'journalEntry.lines.account',
        ]);

        return view('pages.ap.supplier-payment-show', compact('payment'));
    }
}
