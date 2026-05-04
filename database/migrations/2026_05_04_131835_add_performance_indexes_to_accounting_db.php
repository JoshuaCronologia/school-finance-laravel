<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexesToAccountingDb extends Migration
{
    public function up()
    {
        // journal_entry_lines: account_id is the most queried column (GL, COA show, reports)
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->index('account_id', 'jel_account_id_index');
        });

        // chart_of_accounts: filtered by is_postable + account_type in dropdowns and reports
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->index(['is_postable', 'account_type'], 'coa_postable_type_index');
            $table->index('account_type', 'coa_account_type_index');
        });

        // issued_checks: filtered by bank_account_id + status in bank reconciliation
        Schema::table('issued_checks', function (Blueprint $table) {
            $table->index(['bank_account_id', 'status'], 'ic_bank_account_status_index');
        });

        // disbursement_requests: filtered by status + request_date in index listing
        Schema::table('disbursement_requests', function (Blueprint $table) {
            $table->index(['status', 'request_date'], 'dr_status_request_date_index');
        });

        // fee_account_mappings: finance_fee_id looked up on every finance fee enrichment
        Schema::table('fee_account_mappings', function (Blueprint $table) {
            $table->index('finance_fee_id', 'fam_finance_fee_id_index');
        });
    }

    public function down()
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropIndex('jel_account_id_index');
        });

        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropIndex('coa_postable_type_index');
            $table->dropIndex('coa_account_type_index');
        });

        Schema::table('issued_checks', function (Blueprint $table) {
            $table->dropIndex('ic_bank_account_status_index');
        });

        Schema::table('disbursement_requests', function (Blueprint $table) {
            $table->dropIndex('dr_status_request_date_index');
        });

        Schema::table('fee_account_mappings', function (Blueprint $table) {
            $table->dropIndex('fam_finance_fee_id_index');
        });
    }
}
