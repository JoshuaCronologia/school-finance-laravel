<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $like = "%{$q}%";

        $rows = DB::select("
            (SELECT 'Account' as type, 'book' as icon,
                    account_code || ' — ' || account_name as title,
                    INITCAP(account_type) as subtitle,
                    id, 'gl.accounts.show' as route_name
             FROM chart_of_accounts
             WHERE account_name ILIKE ? OR account_code ILIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Vendor', 'truck', name, vendor_code, id, 'vendors.show'
             FROM vendors
             WHERE name ILIKE ? OR vendor_code ILIKE ? OR tin ILIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Customer', 'users', name, customer_code, id, 'ar.customers.show'
             FROM customers
             WHERE name ILIKE ? OR customer_code ILIKE ? OR email ILIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Bill', 'file-text', bill_number, COALESCE(description, 'AP Bill'), id, 'ap.bills.show'
             FROM ap_bills
             WHERE bill_number ILIKE ? OR description ILIKE ? OR reference_number ILIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Invoice', 'file', invoice_number, COALESCE(description, 'AR Invoice'), id, 'ar.invoices.show'
             FROM ar_invoices
             WHERE invoice_number ILIKE ? OR description ILIKE ? OR invoice_number ILIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Journal Entry', 'layers', entry_number, COALESCE(description, 'Journal Entry'), id, 'gl.journal-entries.show'
             FROM journal_entries
             WHERE entry_number ILIKE ? OR description ILIKE ? OR reference_number ILIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Disbursement', 'banknotes', request_number,
                    COALESCE(payee_name, '') || ' — ₱' || TRIM(TO_CHAR(amount, '999,999,990.00')),
                    id, 'ap.disbursements.show'
             FROM disbursement_requests
             WHERE request_number ILIKE ? OR payee_name ILIKE ? OR description ILIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Collection', 'receipt', receipt_number,
                    payment_method || ' — ₱' || TRIM(TO_CHAR(amount_received, '999,999,990.00')),
                    id, 'ar.collections.show'
             FROM ar_collections
             WHERE receipt_number ILIKE ? OR reference_number ILIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Payment', 'credit-card', voucher_number,
                    payment_method || ' — ₱' || TRIM(TO_CHAR(net_amount, '999,999,990.00')),
                    id, 'ap.payments.print'
             FROM disbursement_payments
             WHERE voucher_number ILIKE ? OR check_number ILIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Budget', 'calculator', budget_name,
                    school_year || ' — ₱' || TRIM(TO_CHAR(annual_budget, '999,999,990.00')),
                    id, 'budget.dashboard'
             FROM budgets
             WHERE budget_name ILIKE ? OR project ILIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Department', 'building', name, 'Department', id, 'budget.dashboard'
             FROM departments
             WHERE name ILIKE ?
             LIMIT 3)
            LIMIT 20
        ", [
            $like, $like,                 // accounts
            $like, $like, $like,          // vendors
            $like, $like, $like,          // customers
            $like, $like, $like,          // bills
            $like, $like, $like,          // invoices
            $like, $like, $like,          // journal entries
            $like, $like, $like,          // disbursements
            $like, $like,                 // collections
            $like, $like,                 // payments
            $like, $like,                 // budgets
            $like,                        // departments
        ]);

        $results = collect($rows)->map(function ($row) {
            // Budget and Department don't have individual show pages — link to dashboard
            $url = in_array($row->route_name, ['budget.dashboard'])
                ? route($row->route_name)
                : route($row->route_name, $row->id);

            return [
                'type' => $row->type,
                'icon' => $row->icon,
                'title' => $row->title,
                'subtitle' => $row->subtitle,
                'url' => $url,
            ];
        });

        return response()->json(['results' => $results]);
    }
}
