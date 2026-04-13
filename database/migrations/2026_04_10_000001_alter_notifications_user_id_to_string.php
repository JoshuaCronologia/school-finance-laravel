<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterNotificationsUserIdToString extends Migration
{
    public function up()
    {
        // Drop FK if it exists
        $fkExists = collect(DB::select("
            SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'notifications'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME = 'notifications_user_id_foreign'
        "))->isNotEmpty();

        if ($fkExists) {
            DB::statement('ALTER TABLE notifications DROP FOREIGN KEY notifications_user_id_foreign');
        }

        // Change user_id to VARCHAR(36) to hold both integer IDs and UUIDs
        DB::statement('ALTER TABLE notifications MODIFY user_id VARCHAR(36) NOT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE notifications MODIFY user_id BIGINT UNSIGNED NOT NULL');
    }
}
