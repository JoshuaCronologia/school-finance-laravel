<?php

namespace App\Services;

use App\Models\Notification;
use App\Services\Users\BranchUser;
use App\Services\Users\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class NotificationService
{
    /**
     * Get the current user identifier (works for both standard auth and SSO).
     */
    public static function currentUserId(): ?string
    {
        if (Auth::check()) {
            return (string) Auth::id();
        }

        $info = Session::get('user_info');
        if ($info) {
            // BranchLoginController format
            if (!empty($info['branch_user_id'])) {
                return (string) $info['branch_user_id'];
            }
            // LoginController / SharedFunctions::get_auth format
            if (!empty($info['branch_account']) && is_object($info['branch_account'])) {
                return (string) $info['branch_account']->id;
            }
        }

        // Final fallback: look up BranchUser from session
        $parentId = Session::get('user_id');
        $branchCode = Session::get('branch_code');
        if ($parentId && $branchCode) {
            $bu = BranchUser::where('parent_id', $parentId)
                ->where('branch_code', $branchCode)
                ->where('is_active', true)
                ->first();
            if ($bu) {
                return (string) $bu->id;
            }
        }

        return null;
    }

    /**
     * Send a notification to a specific user by ID (user.id or branch_user.id).
     */
    public static function send(string $userId, string $type, string $title, ?string $message = null, ?string $url = null, ?array $extra = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => array_merge(['url' => $url], $extra ?? []),
        ]);
    }

    /**
     * Send a notification to the current user + all admin users.
     */
    public static function broadcast(string $type, string $title, ?string $message = null, ?string $url = null, ?array $extra = [])
    {
        $currentId = self::currentUserId();
        $sentIds = [];

        // Always notify the user who performed the action
        if ($currentId) {
            self::send($currentId, $type, $title, $message, $url, $extra);
            $sentIds[] = $currentId;
        }

        // Notify all admin users (standard auth)
        User::where('is_active', true)->each(function ($user) use ($type, $title, $message, $url, $extra, &$sentIds) {
            $uid = (string) $user->id;
            if (!in_array($uid, $sentIds)) {
                self::send($uid, $type, $title, $message, $url, $extra);
                $sentIds[] = $uid;
            }
        });
    }

    /**
     * Send notification to all users with a specific role (standard users only).
     */
    public static function sendToRole(string $role, string $type, string $title, ?string $message = null, ?string $url = null)
    {
        User::role($role)->where('is_active', true)->each(function ($user) use ($type, $title, $message, $url) {
            self::send((string) $user->id, $type, $title, $message, $url);
        });
    }

    // =================================================================
    // Pre-built notification events — Budget
    // =================================================================

    public static function budgetCreated($budget)
    {
        self::broadcast('info', 'New Budget Created',
            "Budget \"{$budget->budget_name}\" (₱" . number_format($budget->annual_budget, 2) . ") was created.",
            route('budget.dashboard')
        );
    }

    // =================================================================
    // Pre-built notification events — Accounts Payable
    // =================================================================

    public static function billCreated($bill)
    {
        self::broadcast('info', 'New Bill Recorded',
            "{$bill->bill_number} — ₱" . number_format($bill->gross_amount, 2) . ' from ' . (optional($bill->vendor)->name ?? 'vendor'),
            route('ap.bills.show', $bill)
        );
    }

    public static function billApproved($bill)
    {
        self::broadcast('success', 'Bill Approved',
            "{$bill->bill_number} — ₱" . number_format($bill->gross_amount, 2) . ' approved for posting.',
            route('ap.bills.show', $bill)
        );
    }

    public static function billPosted($bill)
    {
        self::broadcast('success', 'Bill Posted to GL',
            "{$bill->bill_number} — ₱" . number_format($bill->gross_amount, 2) . ' posted to General Ledger.',
            route('ap.bills.show', $bill)
        );
    }

    public static function disbursementSubmitted($disbursement)
    {
        self::broadcast('warning', 'Disbursement Pending Approval',
            "{$disbursement->request_number} — ₱" . number_format($disbursement->amount, 2) . " from {$disbursement->payee_name}",
            route('ap.approval-queue')
        );
    }

    public static function disbursementApproved($disbursement)
    {
        if ($disbursement->created_by) {
            self::send((string) $disbursement->created_by, 'success', 'Disbursement Approved',
                "{$disbursement->request_number} has been approved and is ready for payment.",
                route('ap.disbursements.show', $disbursement)
            );
        }
    }

    public static function disbursementRejected($disbursement, ?string $reason = null)
    {
        if ($disbursement->created_by) {
            self::send((string) $disbursement->created_by, 'danger', 'Disbursement Rejected',
                "{$disbursement->request_number} was rejected." . ($reason ? " Reason: {$reason}" : ''),
                route('ap.disbursements.show', $disbursement)
            );
        }
    }

    public static function disbursementReturned($disbursement, ?string $reason = null)
    {
        if ($disbursement->created_by) {
            self::send((string) $disbursement->created_by, 'warning', 'Disbursement Returned for Revision',
                "{$disbursement->request_number} was returned." . ($reason ? " Reason: {$reason}" : ''),
                route('ap.disbursements.show', $disbursement)
            );
        }
    }

    public static function paymentProcessed($payment)
    {
        self::broadcast('success', 'Payment Processed',
            "Voucher {$payment->voucher_number} — ₱" . number_format($payment->net_amount, 2) . " paid.",
            route('ap.supplier-payments')
        );
    }

    public static function paymentVoided($payment)
    {
        self::broadcast('danger', 'Payment Voided',
            "Voucher {$payment->voucher_number} — ₱" . number_format($payment->gross_amount, 2) . " was voided.",
            route('ap.supplier-payments')
        );
    }

    // =================================================================
    // Pre-built notification events — Accounts Receivable
    // =================================================================

    public static function invoiceCreated($invoice)
    {
        $customerName = optional($invoice->customer)->name ?? 'customer';
        self::broadcast('info', 'New Invoice Created',
            "{$invoice->invoice_number} — ₱" . number_format($invoice->net_receivable, 2) . " for {$customerName}",
            route('ar.invoices.show', $invoice)
        );
    }

    public static function collectionReceived($collection)
    {
        $customerName = optional($collection->customer)->name ?? 'customer';
        self::broadcast('success', 'Collection Received',
            "OR {$collection->receipt_number} — ₱" . number_format($collection->amount_received, 2) . " from {$customerName}",
            route('ar.collections.show', $collection)
        );
    }

    public static function customerCreated($customer)
    {
        self::broadcast('info', 'New Customer Added',
            "{$customer->customer_code} — {$customer->name}",
            route('ar.customers.show', $customer)
        );
    }

    // =================================================================
    // Pre-built notification events — Vendors
    // =================================================================

    public static function vendorCreated($vendor)
    {
        self::broadcast('info', 'New Vendor Added',
            "{$vendor->vendor_code} — {$vendor->name}",
            route('vendors.show', $vendor)
        );
    }

    // =================================================================
    // Pre-built notification events — General Ledger
    // =================================================================

    public static function journalEntrySubmitted($je)
    {
        self::broadcast('warning', 'Journal Entry Pending Approval',
            "{$je->entry_number} — {$je->description}",
            route('gl.journal-entries.approval')
        );
    }

    public static function journalEntryApproved($je)
    {
        if ($je->created_by) {
            self::send((string) $je->created_by, 'success', 'Journal Entry Approved',
                "{$je->entry_number} has been approved and is ready for posting.",
                route('gl.journal-entries.show', $je)
            );
        }
    }

    public static function journalEntryPosted($je)
    {
        self::broadcast('info', 'Journal Entry Posted',
            "{$je->entry_number} — {$je->description}",
            route('gl.journal-entries.show', $je)
        );
    }

    public static function periodClosed($period)
    {
        self::broadcast('warning', 'Accounting Period Closed',
            "Period \"{$period->name}\" has been closed. No further entries allowed.",
            route('gl.period-closing')
        );
    }

    public static function periodReopened($period)
    {
        self::broadcast('info', 'Accounting Period Reopened',
            "Period \"{$period->name}\" has been reopened for entries.",
            route('gl.period-closing')
        );
    }
}
