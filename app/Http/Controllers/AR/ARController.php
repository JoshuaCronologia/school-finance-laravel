<?php

namespace App\Http\Controllers\AR;

use App\Http\Controllers\Controller;
use App\Models\ArCollection;
use App\Models\ArInvoice;
use App\Models\Customer;
use App\Services\AuditService;
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
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $rows = DB::select("
            SELECT c.id as customer_id, c.name as customer_name, c.customer_code,
                COALESCE(SUM(CASE WHEN DATEDIFF(?, i.due_date) <= 0 THEN i.balance END), 0) as `current`,
                COALESCE(SUM(CASE WHEN DATEDIFF(?, i.due_date) BETWEEN 1 AND 30 THEN i.balance END), 0) as days_30,
                COALESCE(SUM(CASE WHEN DATEDIFF(?, i.due_date) BETWEEN 31 AND 60 THEN i.balance END), 0) as days_60,
                COALESCE(SUM(CASE WHEN DATEDIFF(?, i.due_date) BETWEEN 61 AND 90 THEN i.balance END), 0) as days_90,
                COALESCE(SUM(CASE WHEN DATEDIFF(?, i.due_date) > 90 THEN i.balance END), 0) as days_90_plus,
                COALESCE(SUM(i.balance), 0) as total
            FROM customers c
            JOIN ar_invoices i ON i.customer_id = c.id
            WHERE i.status NOT IN ('cancelled', 'voided', 'paid') AND i.balance > 0
            GROUP BY c.id, c.name, c.customer_code
            HAVING total > 0
            ORDER BY total DESC
        ", [$asOfDate, $asOfDate, $asOfDate, $asOfDate, $asOfDate]);

        $aging = collect($rows)->map(function ($r) {
            return (object) [
                'customer_code' => $r->customer_code,
                'customer_name' => $r->customer_name,
                'current' => (float) $r->current,
                'days_30' => (float) $r->days_30,
                'days_60' => (float) $r->days_60,
                'days_90' => (float) $r->days_90,
                'days_90_plus' => (float) $r->days_90_plus,
            ];
        });

        return view('pages.ar.aging', compact('aging', 'asOfDate'));
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
            $selectedCustomer = Customer::with('campus')->find($request->customer_id);

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

                // Running balance + cast to objects for blade
                $runningBalance = 0;
                $transactions = $transactions->map(function ($t) use (&$runningBalance) {
                    $runningBalance += $t['debit'] - $t['credit'];
                    $t['balance'] = $runningBalance;
                    return (object) $t;
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

    public function soaPdf(Customer $customer)
    {
        (new AuditService)->logActivity('exported', 'ar_soa', 'Downloaded SOA PDF');

        $customer->load('campus');
        $asOfDate = request('as_of_date', now()->toDateString());

        $invoices = ArInvoice::where('customer_id', $customer->id)
            ->whereNotIn('status', ['cancelled', 'voided'])
            ->whereDate('invoice_date', '<=', $asOfDate)
            ->orderBy('invoice_date')->get()
            ->map(function ($inv) { return (object) [
                'date' => $inv->invoice_date, 'reference' => $inv->invoice_number,
                'description' => $inv->description ?? 'Invoice',
                'debit' => (float) $inv->net_receivable, 'credit' => 0,
            ]; });

        $collections = ArCollection::where('customer_id', $customer->id)
            ->whereNotIn('status', ['cancelled', 'voided'])
            ->whereDate('collection_date', '<=', $asOfDate)
            ->orderBy('collection_date')->get()
            ->map(function ($col) { return (object) [
                'date' => $col->collection_date, 'reference' => $col->receipt_number,
                'description' => 'Payment - ' . $col->payment_method,
                'debit' => 0, 'credit' => (float) $col->amount_received,
            ]; });

        $runningBalance = 0;
        $transactions = $invoices->concat($collections)->sortBy('date')->values()
            ->map(function ($t) use (&$runningBalance) {
                $runningBalance += $t->debit - $t->credit;
                $t->balance = $runningBalance;
                return $t;
            });

        $totalInvoiced = $transactions->sum('debit');
        $totalCollected = $transactions->sum('credit');
        $balance = $totalInvoiced - $totalCollected;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.ar.soa-pdf', compact(
            'customer', 'transactions', 'totalInvoiced', 'totalCollected', 'balance', 'asOfDate'
        ))->setPaper('letter', 'portrait');

        return $pdf->download("SOA-{$customer->customer_code}-" . now()->format('Y-m-d') . ".pdf");
    }
}
