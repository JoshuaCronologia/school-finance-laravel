<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('campus_id')->constrained()->onDelete('cascade');
            $table->string('head_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('campus_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }
}
