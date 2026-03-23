<?php

namespace App\Http\Controllers\AR;

use App\Http\Controllers\Controller;
use App\Models\ArCollection;
use App\Models\ArInvoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ARController extends Controller
{
    /**
     * AR Aging report with customer breakdown by aging buckets.
     */
    public function aging(Request $request)
    {
        $invoices = ArInvoice::with('customer')
            ->whereNotIn('status', ['cancelled', 'voided', 'paid'])
            ->where('balance', '>', 0)
            ->get();

        $today = now();

        $agingBuckets = [
            'current' => ['label' => 'Current', 'items' => collect(), 'total' => 0],
            '1_30' => ['label' => '1-30 Days', 'items' => collect(), 'total' => 0],
            '31_60' => ['label' => '31-60 Days', 'items' => collect(), 'total' => 0],
            '61_90' => ['label' => '61-90 Days', 'items' => collect(), 'total' => 0],
            'over_90' => ['label' => 'Over 90 Days', 'items' => collect(), 'total' => 0],
        ];

        // Customer breakdown
        $customerAging = [];

        foreach ($invoices as $invoice) {
            $daysOverdue = $today->diffInDays($invoice->due_date, false);

            if ($daysOverdue >= 0) {
                $bucket = 'current';
            } elseif ($daysOverdue >= -30) {
                $bucket = '1_30';
            } elseif ($daysOverdue >= -60) {
                $bucket = '31_60';
            } elseif ($daysOverdue >= -90) {
                $bucket = '61_90';
            } else {
                $bucket = 'over_90';
            }

            $agingBuckets[$bucket]['items']->push($invoice);
            $agingBuckets[$bucket]['total'] += (float) $invoice->balance;

            // Customer breakdown
            $custId = $invoice->customer_id;
            if (!isset($customerAging[$custId])) {
                $customerAging[$custId] = [
                    'customer' => $invoice->customer,
                    'current' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, 'over_90' => 0, 'total' => 0,
                ];
            }
            $customerAging[$custId][$bucket] += (float) $invoice->balance;
            $customerAging[$custId]['total'] += (float) $invoice->balance;
        }

        $customerAging = collect($customerAging)->sortByDesc('total')->values();
        $grandTotal = $invoices->sum('balance');

        return view('pages.ar.aging', compact('agingBuckets', 'customerAging', 'grandTotal'));
    }

    /**
     * Statement of Account for a specific customer.
     */
    public function soa(Request $request)
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $selectedCustomer = null;
        $transactions = collect();
        $totalInvoiced = 0;
        $totalCollected = 0;
        $balance = 0;
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);

            if ($selectedCustomer) {
                $invoices = ArInvoice::where('customer_id', $selectedCustomer->id)
                    ->whereNotIn('status', ['cancelled', 'voided'])
                    ->whereDate('invoice_date', '<=', $asOfDate)
                    ->orderBy('invoice_date')
                    ->get()
                    ->map(function ($inv) {
                        return [
                            'date' => $inv->invoice_date,
                            'reference' => $inv->invoice_number,
                            'description' => $inv->description ?? 'Invoice',
                            'debit' => (float) $inv->net_receivable,
                            'credit' => 0,
                            'type' => 'invoice',
                        ];
                    });

                $collections = ArCollection::where('customer_id', $selectedCustomer->id)
                    ->whereNotIn('status', ['cancelled', 'voided'])
                    ->whereDate('collection_date', '<=', $asOfDate)
                    ->orderBy('collection_date')
                    ->get()
                    ->map(function ($col) {
                        return [
                            'date' => $col->collection_date,
                            'reference' => $col->receipt_number,
                            'description' => 'Payment received - ' . $col->payment_method,
                            'debit' => 0,
                            'credit' => (float) $col->amount_received,
                            'type' => 'collection',
                        ];
                    });

                $transactions = $invoices->concat($collections)->sortBy('date')->values();

                // Running balance
                $runningBalance = 0;
                $transactions = $transactions->map(function ($t) use (&$runningBalance) {
                    $runningBalance += $t['debit'] - $t['credit'];
                    $t['balance'] = $runningBalance;
                    return $t;
                });

                $totalInvoiced = $transactions->sum('debit');
                $totalCollected = $transactions->sum('credit');
                $balance = $totalInvoiced - $totalCollected;
            }
        }

        return view('pages.ar.soa', compact(
            'customers', 'selectedCustomer', 'transactions',
            'totalInvoiced', 'totalCollected', 'balance', 'asOfDate'
        ));
    }

    public function soaDetail(Customer $customer)
    {
        return $this->soa(request()->merge(['customer_id' => $customer->id]));
    }
}
