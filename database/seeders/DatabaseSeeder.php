<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: master data first, then roles/permissions (creates admin user),
     * then transactions (references master data and user).
     */
    public function run()
    {
        $this->call([
            MasterDataSeeder::class,
            RolePermissionSeeder::class,
            TransactionSeeder::class,
            SsoPermissionSeeder::class,
            SsoBranchUserSeeder::class,
        ]);

        // Reset all PostgreSQL sequences after seeding with explicit IDs
        $this->resetSequences();
    }

    /**
     * Reset PostgreSQL auto-increment sequences to match max ID in each table.
     * Prevents "duplicate key" errors after seeding with explicit IDs.
     */
    private function resetSequences()
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $tables = DB::select("
            SELECT t.relname AS table_name, a.attname AS column_name, s.relname AS sequence_name
            FROM pg_class s
            JOIN pg_depend d ON d.objid = s.oid
            JOIN pg_class t ON d.refobjid = t.oid
            JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = d.refobjsubid
            WHERE s.relkind = 'S'
              AND t.relkind = 'r'
              AND t.relname IN (SELECT tablename FROM pg_tables WHERE schemaname = 'public')
        ");

        foreach ($tables as $row) {
            try {
                DB::statement("SELECT setval('{$row->sequence_name}', COALESCE((SELECT MAX({$row->column_name}) FROM \"{$row->table_name}\"), 0) + 1, false)");
            } catch (\Exception $e) {
                // Skip sequences for tables that don't exist
            }
        }
    }
}
