<?php

namespace App\Services;

use App\Models\Notification;
use App\Services\Users\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Send a notification to a specific user.
     */
    public static function send(int $userId, string $type, string $title, ?string $message = null, ?string $url = null, ?array $extra = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => array_merge(['url' => $url], $extra),
        ]);
    }

    /**
     * Send a notification to all users (broadcast).
     */
    public static function broadcast(string $type, string $title, ?string $message = null, ?string $url = null, ?array $extra = [])
    {
        $currentUserId = Auth::id();

        User::where('id', '!=', $currentUserId)->each(function ($user) use ($type, $title, $message, $url, $extra) {
            self::send($user->id, $type, $title, $message, $url, $extra);
        });
    }

    /**
     * Send notification to all users with a specific role.
     */
    public static function sendToRole(string $role, string $type, string $title, ?string $message = null, ?string $url = null)
    {
        User::role($role)->where('id', '!=', Auth::id())->each(function ($user) use ($type, $title, $message, $url) {
            self::send($user->id, $type, $title, $message, $url);
        });
    }

    // =================================================================
    // Pre-built notification events
    // =================================================================

    public static function budgetCreated($budget)
    {
        self::broadcast('info', 'New Budget Created',
            "Budget \"{$budget->budget_name}\" (₱" . number_format($budget->annual_budget, 2) . ") was created.",
            route('budget.dashboard')
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
            self::send($disbursement->created_by, 'success', 'Disbursement Approved',
                "{$disbursement->request_number} has been approved and is ready for payment.",
                route('ap.disbursements.show', $disbursement)
            );
        }
    }

    public static function disbursementRejected($disbursement, ?string $reason = null)
    {
        if ($disbursement->created_by) {
            self::send($disbursement->created_by, 'danger', 'Disbursement Rejected',
                "{$disbursement->request_number} was rejected." . ($reason ? " Reason: {$reason}" : ''),
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

    public static function journalEntryPosted($je)
    {
        self::broadcast('info', 'Journal Entry Posted',
            "{$je->entry_number} — {$je->description}",
            route('gl.journal-entries.show', $je)
        );
    }

    public static function billCreated($bill)
    {
        self::broadcast('info', 'New Bill Recorded',
            "{$bill->bill_number} — ₱" . number_format($bill->gross_amount, 2) . ' from ' . (optional($bill->vendor)->name ?? 'vendor'),
            route('ap.bills.show', $bill)
        );
    }
}
