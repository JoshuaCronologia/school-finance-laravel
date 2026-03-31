<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Change model_id from unsignedBigInteger to string (varchar)
 * to support UUID-based models like BranchUser with Spatie permissions.
 */
return new class extends Migration
{
    public function up(): void
    {
        // model_has_permissions
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->string('model_id')->change();
        });

        // model_has_roles
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->string('model_id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->change();
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->change();
        });
    }
};
