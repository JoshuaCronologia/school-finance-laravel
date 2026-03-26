<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Global search across transactions, accounts, vendors, and customers.
     * Uses a single UNION query instead of 6 separate queries.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $like = "%{$q}%";

        // 1 UNION query instead of 6 separate roundtrips to Supabase
        $rows = DB::select("
            (SELECT 'Account' as type, 'book' as icon,
                    account_code || ' — ' || account_name as title,
                    INITCAP(account_type) as subtitle,
                    id, 'gl.accounts.show' as route_name
             FROM chart_of_accounts
             WHERE account_name ILIKE ? OR account_code ILIKE ?
             LIMIT 5)
            UNION ALL
            (SELECT 'Vendor', 'truck', name, vendor_code, id, 'vendors.show'
             FROM vendors
             WHERE name ILIKE ? OR vendor_code ILIKE ?
             LIMIT 5)
            UNION ALL
            (SELECT 'Customer', 'users', name, customer_code, id, 'ar.customers.show'
             FROM customers
             WHERE name ILIKE ? OR customer_code ILIKE ?
             LIMIT 5)
            UNION ALL
            (SELECT 'Bill', 'file-text', bill_number, COALESCE(description, 'AP Bill'), id, 'ap.bills.show'
             FROM ap_bills
             WHERE bill_number ILIKE ? OR description ILIKE ? OR reference_number ILIKE ?
             LIMIT 5)
            UNION ALL
            (SELECT 'Invoice', 'file', invoice_number, COALESCE(description, 'AR Invoice'), id, 'ar.invoices.show'
             FROM ar_invoices
             WHERE invoice_number ILIKE ? OR description ILIKE ?
             LIMIT 5)
            UNION ALL
            (SELECT 'Journal Entry', 'layers', entry_number, COALESCE(description, 'Journal Entry'), id, 'gl.journal-entries.show'
             FROM journal_entries
             WHERE entry_number ILIKE ? OR description ILIKE ? OR reference_number ILIKE ?
             LIMIT 5)
            LIMIT 15
        ", [
            $like, $like,           // accounts
            $like, $like,           // vendors
            $like, $like,           // customers
            $like, $like, $like,    // bills
            $like, $like,           // invoices
            $like, $like, $like,    // journal entries
        ]);

        $results = collect($rows)->map(fn ($row) => [
            'type' => $row->type,
            'icon' => $row->icon,
            'title' => $row->title,
            'subtitle' => $row->subtitle,
            'url' => route($row->route_name, $row->id),
        ]);

        return response()->json(['results' => $results]);
    }
}