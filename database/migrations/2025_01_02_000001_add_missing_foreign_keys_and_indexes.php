<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------
        // 1. Foreign keys on user_id columns that were missing constraints
        // ---------------------------------------------------------------
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('accounting_periods', function (Blueprint $table) {
            $table->index('closed_by');
            $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
        });

        // ---------------------------------------------------------------
        // 2. Missing indexes on recurring_journal_lines FK columns
        // ---------------------------------------------------------------
        Schema::table('recurring_journal_lines', function (Blueprint $table) {
            $table->index('account_id');
            $table->index('department_id');
        });

        // ---------------------------------------------------------------
        // 3. disbursement_items: add proper FK columns for account and tax
        // ---------------------------------------------------------------
        Schema::table('disbursement_items', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('amount')
                ->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('tax_code_id')->nullable()->after('account_code')
                ->constrained('tax_codes')->nullOnDelete();
        });

        // ---------------------------------------------------------------
        // 4. Composite indexes for query patterns that use 2 columns together
        //    (single-column indexes on status, due_date, etc. already exist)
        // ---------------------------------------------------------------

        // Aging: WHERE status NOT IN (...) AND due_date < X
        Schema::table('ap_bills', function (Blueprint $table) {
            $table->index(['status', 'due_date'], 'ap_bills_status_due_date_index');
            $table->index(['status', 'balance'], 'ap_bills_status_balance_index');
        });

        Schema::table('ar_invoices', function (Blueprint $table) {
            $table->index(['status', 'due_date'], 'ar_invoices_status_due_date_index');
            $table->index(['status', 'balance'], 'ar_invoices_status_balance_index');
        });

        // Reports: WHERE status = 'posted' AND posting_date BETWEEN X AND Y
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(['status', 'posting_date'], 'journal_entries_status_posting_date_index');
        });

        // Dashboard: WHERE status = 'active' GROUP BY department_id
        Schema::table('budgets', function (Blueprint $table) {
            $table->index(['status', 'department_id'], 'budgets_status_department_id_index');
        });

        // Tax reports: WHERE status = 'completed' AND payment_date ...
        Schema::table('disbursement_payments', function (Blueprint $table) {
            $table->index(['status', 'payment_date'], 'disbursement_payments_status_payment_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('accounting_periods', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropIndex(['closed_by']);
        });

        Schema::table('recurring_journal_lines', function (Blueprint $table) {
            $table->dropIndex(['account_id']);
            $table->dropIndex(['department_id']);
        });

        Schema::table('disbursement_items', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
            $table->dropForeign(['tax_code_id']);
            $table->dropColumn('tax_code_id');
        });

        Schema::table('ap_bills', function (Blueprint $table) {
            $table->dropIndex('ap_bills_status_due_date_index');
            $table->dropIndex('ap_bills_status_balance_index');
        });

        Schema::table('ar_invoices', function (Blueprint $table) {
            $table->dropIndex('ar_invoices_status_due_date_index');
            $table->dropIndex('ar_invoices_status_balance_index');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex('journal_entries_status_posting_date_index');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex('budgets_status_department_id_index');
        });

        Schema::table('disbursement_payments', function (Blueprint $table) {
            $table->dropIndex('disbursement_payments_status_payment_date_index');
        });
    }
};
