<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $like = "%{$q}%";

        $rows = DB::select("
            (SELECT 'Account' as type, 'book' as icon,
                    CONCAT(account_code, ' — ', account_name) as title,
                    account_type as subtitle,
                    id, 'gl.accounts.show' as route_name
             FROM chart_of_accounts
             WHERE account_name LIKE ? OR account_code LIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Vendor', 'truck', name, vendor_code, id, 'vendors.show'
             FROM vendors
             WHERE name LIKE ? OR vendor_code LIKE ? OR tin LIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Customer', 'users', name, customer_code, id, 'ar.customers.show'
             FROM customers
             WHERE name LIKE ? OR customer_code LIKE ? OR email LIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Bill', 'file-text', bill_number, COALESCE(description, 'AP Bill'), id, 'ap.bills.show'
             FROM ap_bills
             WHERE bill_number LIKE ? OR description LIKE ? OR reference_number LIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Invoice', 'file', invoice_number, COALESCE(description, 'AR Invoice'), id, 'ar.invoices.show'
             FROM ar_invoices
             WHERE invoice_number LIKE ? OR description LIKE ? OR invoice_number LIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Journal Entry', 'layers', entry_number, COALESCE(description, 'Journal Entry'), id, 'gl.journal-entries.show'
             FROM journal_entries
             WHERE entry_number LIKE ? OR description LIKE ? OR reference_number LIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Disbursement', 'banknotes', request_number,
                    CONCAT(COALESCE(payee_name, ''), ' — ', FORMAT(amount, 2)),
                    id, 'ap.disbursements.show'
             FROM disbursement_requests
             WHERE request_number LIKE ? OR payee_name LIKE ? OR description LIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Collection', 'receipt', receipt_number,
                    CONCAT(payment_method, ' — ', FORMAT(amount_received, 2)),
                    id, 'ar.collections.show'
             FROM ar_collections
             WHERE receipt_number LIKE ? OR reference_number LIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Payment', 'credit-card', voucher_number,
                    CONCAT(payment_method, ' — ', FORMAT(net_amount, 2)),
                    id, 'ap.payments.print'
             FROM disbursement_payments
             WHERE voucher_number LIKE ? OR check_number LIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Budget', 'calculator', budget_name,
                    CONCAT(school_year, ' — ', FORMAT(annual_budget, 2)),
                    id, 'budget.dashboard'
             FROM budgets
             WHERE budget_name LIKE ? OR project LIKE ?
             LIMIT 4)
            UNION ALL
            (SELECT 'Department', 'building', name, 'Department', id, 'budget.dashboard'
             FROM departments
             WHERE name LIKE ?
             LIMIT 3)
            LIMIT 20
        ", [
            $like, $like,
            $like, $like, $like,
            $like, $like, $like,
            $like, $like, $like,
            $like, $like, $like,
            $like, $like, $like,
            $like, $like, $like,
            $like, $like,
            $like, $like,
            $like, $like,
            $like,
        ]);

        $results = collect($rows)->map(function ($row) {
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
