<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Create implicit cast from integer to boolean for PostgreSQL
            // This allows Laravel's boolean bindings (1/0) to work with boolean columns
            DB::unprepared("
                DO \$\$
                BEGIN
                    IF NOT EXISTS (
                        SELECT 1 FROM pg_cast
                        WHERE castsource = 'integer'::regtype
                        AND casttarget = 'boolean'::regtype
                        AND castcontext = 'i'
                    ) THEN
                        CREATE CAST (integer AS boolean) WITH INOUT AS IMPLICIT;
                    END IF;
                END
                \$\$;
            ");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared("DROP CAST IF EXISTS (integer AS boolean);");
        }
    }
};
