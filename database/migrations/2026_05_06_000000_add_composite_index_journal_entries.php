<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompositeIndexJournalEntries extends Migration
{
    public function up()
    {
        // Composite index speeds up all period-range reports that filter
        // WHERE status = 'posted' AND posting_date BETWEEN x AND y
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(['status', 'posting_date'], 'je_status_posting_date_index');
        });

        // Composite index on journal_entry_lines for the GROUP BY report query
        // (account_id JOIN + journal_entry_id lookup)
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->index(['journal_entry_id', 'account_id'], 'jel_je_account_index');
        });
    }

    public function down()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex('je_status_posting_date_index');
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropIndex('jel_je_account_index');
        });
    }
}
