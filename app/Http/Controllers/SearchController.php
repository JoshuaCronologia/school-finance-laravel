<?php

namespace App\Http\Controllers;

use App\Models\ApBill;
use App\Models\ArInvoice;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across transactions, accounts, vendors, and customers.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $like = "%{$q}%";
        $results = collect();

        // Chart of Accounts
        ChartOfAccount::where('account_name', 'ilike', $like)
            ->orWhere('account_code', 'ilike', $like)
            ->limit(5)
            ->get()
            ->each(function ($a) use ($results) {
                $results->push([
                    'type' => 'Account',
                    'icon' => 'book',
                    'title' => "{$a->account_code} — {$a->account_name}",
                    'subtitle' => ucfirst($a->account_type),
                    'url' => route('gl.accounts.show', $a),
                ]);
            });

        // Vendors
        Vendor::where('name', 'ilike', $like)
            ->orWhere('vendor_code', 'ilike', $like)
            ->limit(5)
            ->get()
            ->each(function ($v) use ($results) {
                $results->push([
                    'type' => 'Vendor',
                    'icon' => 'truck',
                    'title' => $v->name,
                    'subtitle' => $v->vendor_code,
                    'url' => route('vendors.show', $v),
                ]);
            });

        // Customers
        Customer::where('name', 'ilike', $like)
            ->orWhere('customer_code', 'ilike', $like)
            ->limit(5)
            ->get()
            ->each(function ($c) use ($results) {
                $results->push([
                    'type' => 'Customer',
                    'icon' => 'users',
                    'title' => $c->name,
                    'subtitle' => $c->customer_code,
                    'url' => route('ar.customers.show', $c),
                ]);
            });

        // AP Bills
        ApBill::where('bill_number', 'ilike', $like)
            ->orWhere('description', 'ilike', $like)
            ->orWhere('reference_number', 'ilike', $like)
            ->limit(5)
            ->get()
            ->each(function ($b) use ($results) {
                $results->push([
                    'type' => 'Bill',
                    'icon' => 'file-text',
                    'title' => $b->bill_number,
                    'subtitle' => $b->description ?: 'AP Bill',
                    'url' => route('ap.bills.show', $b),
                ]);
            });

        // AR Invoices
        ArInvoice::where('invoice_number', 'ilike', $like)
            ->orWhere('description', 'ilike', $like)
            ->limit(5)
            ->get()
            ->each(function ($inv) use ($results) {
                $results->push([
                    'type' => 'Invoice',
                    'icon' => 'file',
                    'title' => $inv->invoice_number,
                    'subtitle' => $inv->description ?: 'AR Invoice',
                    'url' => route('ar.invoices.show', $inv),
                ]);
            });

        // Journal Entries
        JournalEntry::where('entry_number', 'ilike', $like)
            ->orWhere('description', 'ilike', $like)
            ->orWhere('reference_number', 'ilike', $like)
            ->limit(5)
            ->get()
            ->each(function ($je) use ($results) {
                $results->push([
                    'type' => 'Journal Entry',
                    'icon' => 'layers',
                    'title' => $je->entry_number,
                    'subtitle' => $je->description ?: 'Journal Entry',
                    'url' => route('gl.journal-entries.show', $je),
                ]);
            });

        return response()->json(['results' => $results->take(15)->values()]);
    }
}
