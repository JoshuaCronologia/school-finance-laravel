<?php

namespace App\Http\Controllers\AR;

use App\Http\Controllers\Controller;
use App\Models\ArCollection;
use App\Models\ArInvoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ARController extends Controller
{
    /**
     * AR Aging report with customer breakdown by aging buckets.
     */
    public function aging(Request $request)
    {
        $result = Cache::remember('ar:aging', 300, function () {
            // Single SQL query for customer aging buckets instead of loading all invoices
            $rows = DB::select("
                SELECT c.id as customer_id, c.name as customer_name, c.customer_code,
                    COALESCE(SUM(CASE WHEN CURRENT_DATE - i.due_date <= 0 THEN i.balance END), 0) as current,
                    COALESCE(SUM(CASE WHEN CURRENT_DATE - i.due_date BETWEEN 1 AND 30 THEN i.balance END), 0) as days_1_30,
                    COALESCE(SUM(CASE WHEN CURRENT_DATE - i.due_date BETWEEN 31 AND 60 THEN i.balance END), 0) as days_31_60,
                    COALESCE(SUM(CASE WHEN CURRENT_DATE - i.due_date BETWEEN 61 AND 90 THEN i.balance END), 0) as days_61_90,
                    COALESCE(SUM(CASE WHEN CURRENT_DATE - i.due_date > 90 THEN i.balance END), 0) as over_90,
                    COALESCE(SUM(i.balance), 0) as total
                FROM customers c
                JOIN ar_invoices i ON i.customer_id = c.id
                WHERE i.status NOT IN ('cancelled', 'voided', 'paid') AND i.balance > 0
                GROUP BY c.id, c.name, c.customer_code
                ORDER BY total DESC
            ");

            $agingBuckets = [
                'current' => ['label' => 'Current', 'items' => collect(), 'total' => 0],
                '1_30' => ['label' => '1-30 Days', 'items' => collect(), 'total' => 0],
                '31_60' => ['label' => '31-60 Days', 'items' => collect(), 'total' => 0],
                '61_90' => ['label' => '61-90 Days', 'items' => collect(), 'total' => 0],
                'over_90' => ['label' => 'Over 90 Days', 'items' => collect(), 'total' => 0],
            ];

            $customerAging = [];
            $grandTotal = 0;

            foreach ($rows as $row) {
                $customer = (object) ['id' => $row->customer_id, 'name' => $row->customer_name, 'customer_code' => $row->customer_code];
                $customerAging[] = [
                    'customer' => $customer,
                    'current' => (float) $row->current,
                    '1_30' => (float) $row->days_1_30,
                    '31_60' => (float) $row->days_31_60,
                    '61_90' => (float) $row->days_61_90,
                    'over_90' => (float) $row->over_90,
                    'total' => (float) $row->total,
                ];
                $agingBuckets['current']['total'] += (float) $row->current;
                $agingBuckets['1_30']['total'] += (float) $row->days_1_30;
                $agingBuckets['31_60']['total'] += (float) $row->days_31_60;
                $agingBuckets['61_90']['total'] += (float) $row->days_61_90;
                $agingBuckets['over_90']['total'] += (float) $row->over_90;
                $grandTotal += (float) $row->total;
            }

            $customerAging = collect($customerAging)->values();

            return compact('agingBuckets', 'customerAging', 'grandTotal');
        });

        return view('pages.ar.aging', $result);
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
