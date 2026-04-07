<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Change model_id from unsignedBigInteger to varchar
 * to support UUID-based models like BranchUser with Spatie permissions.
 */
class AlterPermissionTablesModelIdToString extends Migration
{
    public function up()
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE model_has_permissions MODIFY model_id VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE model_has_roles MODIFY model_id VARCHAR(255) NOT NULL');
        } else {
            // PostgreSQL
            DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id TYPE VARCHAR(255) USING model_id::VARCHAR');
            DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id TYPE VARCHAR(255) USING model_id::VARCHAR');
        }
    }

    public function down()
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE model_has_permissions MODIFY model_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE model_has_roles MODIFY model_id BIGINT UNSIGNED NOT NULL');
        } else {
            DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id TYPE BIGINT USING model_id::BIGINT');
            DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id TYPE BIGINT USING model_id::BIGINT');
        }
    }
}
