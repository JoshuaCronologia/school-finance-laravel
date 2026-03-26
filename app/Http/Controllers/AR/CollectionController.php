<?php

namespace App\Http\Controllers\AR;

use App\Http\Controllers\Controller;
use App\Models\ArCollection;
use App\Models\ArCollectionAllocation;
use App\Models\ArInvoice;
use App\Models\Customer;
use App\Services\AuditService;
use App\Services\NumberingService;
use App\Services\PostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $query = ArCollection::with('customer');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('collection_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('collection_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $collections = $query->latest('collection_date')->paginate(20);

        $totalCollected = ArCollection::whereNotIn('status', ['cancelled', 'voided'])
            ->whereMonth('collection_date', now()->month)
            ->whereYear('collection_date', now()->year)
            ->sum('amount_received');

        $totalUnapplied = ArCollection::whereNotIn('status', ['cancelled', 'voided'])->sum('unapplied_amount');

        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('pages.ar.collections.index', compact(
            'collections', 'totalCollected', 'totalUnapplied', 'customers'
        ));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $openInvoices = ArInvoice::whereNotIn('status', ['cancelled', 'voided', 'paid'])
            ->where('balance', '>', 0)
            ->with('customer')
            ->orderBy('due_date')
            ->get();

        return view('pages.ar.collections.create', compact('customers', 'openInvoices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'collection_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,bank_transfer,online',
            'bank_account' => 'nullable|string|max:100',
            'check_number' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'amount_received' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => 'required|exists:ar_invoices,id',
            'allocations.*.amount_applied' => 'required|numeric|min:0',
        ]);

        try {
            $collection = DB::transaction(function () use ($validated) {
                $appliedAmount = collect($validated['allocations'] ?? [])->sum('amount_applied');
                $unapplied = $validated['amount_received'] - $appliedAmount;

                $collection = ArCollection::create([
                    'receipt_number' => NumberingService::generate('CR'),
                    'collection_date' => $validated['collection_date'],
                    'customer_id' => $validated['customer_id'],
                    'payment_method' => $validated['payment_method'],
                    'bank_account' => $validated['bank_account'] ?? null,
                    'check_number' => $validated['check_number'] ?? null,
                    'reference_number' => $validated['reference_number'] ?? null,
                    'amount_received' => $validated['amount_received'],
                    'applied_amount' => $appliedAmount,
                    'unapplied_amount' => max(0, $unapplied),
                    'collected_by' => auth()->id(),
                    'status' => 'posted',
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                foreach ($validated['allocations'] ?? [] as $alloc) {
                    if ($alloc['amount_applied'] > 0) {
                        ArCollectionAllocation::create([
                            'collection_id' => $collection->id,
                            'invoice_id' => $alloc['invoice_id'],
                            'amount_applied' => $alloc['amount_applied'],
                        ]);

                        $invoice = ArInvoice::find($alloc['invoice_id']);
                        $invoice->increment('amount_paid', $alloc['amount_applied']);
                        $invoice->decrement('balance', $alloc['amount_applied']);

                        if ($invoice->balance <= 0) {
                            $invoice->update(['status' => 'paid']);
                        }
                    }
                }

                // Post to GL
                app(PostingService::class)->postCollection($collection);

                app(AuditService::class)->log('create', 'ar_collection', $collection, null, 'Collection recorded');
                \App\Services\NotificationService::collectionReceived($collection->load('customer'));

                return $collection;
            });

            return redirect()->route('ar.collections.index')->with('success', 'Collection recorded successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to record collection: ' . $e->getMessage());
        }
    }

    public function show(ArCollection $collection)
    {
        $collection->load('customer', 'allocations.invoice', 'journalEntry.lines.account');

        return view('pages.ar.collections.show', compact('collection'));
    }

    public function printReceipt(ArCollection $collection)
    {
        $collection->load('customer', 'allocations.invoice');

        return view('pages.ar.collections.print-receipt', compact('collection'));
    }
}
