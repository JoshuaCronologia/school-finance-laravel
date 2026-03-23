<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
