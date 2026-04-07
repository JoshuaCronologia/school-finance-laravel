<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchUsersTable extends Migration
{
    public function up()
    {
        Schema::create('branch_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('parent_id');                     // ID from SIS (employee_id or student_id)
            $table->string('parent_type');                   // 'App\Models\Employee' or 'App\Models\Student'
            $table->string('branch_code');                   // e.g., 'main', 'pcc'
            $table->string('name');                          // cached display name
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['parent_id', 'parent_type', 'branch_code'], 'branch_users_unique');
            $table->index('branch_code');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('branch_users');
    }
}
