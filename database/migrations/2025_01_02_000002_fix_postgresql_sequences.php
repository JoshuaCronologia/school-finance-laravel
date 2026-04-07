<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixPostgresqlSequences extends Migration
{
    /**
     * Fix PostgreSQL auto-increment sequences that are out of sync.
     *
     * This happens when seeders insert rows with explicit IDs — the sequence
     * doesn't advance, so the next INSERT tries id=1 and hits a duplicate key.
     */
    public function up()
    {
        // Only run on PostgreSQL
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // Get all tables that have an 'id' column with a sequence
        $sequences = DB::select("
            SELECT
                t.relname AS table_name,
                a.attname AS column_name,
                pg_get_serial_sequence(t.relname::text, a.attname::text) AS seq_name
            FROM pg_class t
            JOIN pg_attribute a ON a.attrelid = t.oid
            JOIN pg_namespace n ON n.oid = t.relnamespace
            WHERE n.nspname = 'public'
              AND a.attname = 'id'
              AND pg_get_serial_sequence(t.relname::text, a.attname::text) IS NOT NULL
        ");

        foreach ($sequences as $seq) {
            DB::statement("SELECT setval('{$seq->seq_name}', COALESCE((SELECT MAX(id) FROM \"{$seq->table_name}\"), 0) + 1, false)");
        }
    }

    public function down()
    {
        // No rollback needed — sequences are always correct after this
    }
}
