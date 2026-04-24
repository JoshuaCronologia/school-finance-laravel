<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationIndexes extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Speed up queries filtering by user_id + created_at
            $table->index(['user_id', 'created_at']);
            // Speed up unread notification queries
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['user_id', 'read_at']);
        });
    }
}
