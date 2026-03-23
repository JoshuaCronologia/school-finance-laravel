<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();
            $table->date('entry_date');
            $table->date('posting_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->enum('journal_type', ['general', 'adjusting', 'closing', 'reversing', 'revenue', 'expense', 'payroll']);
            $table->text('description')->nullable();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('school_year')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'posted', 'reversed', 'cancelled'])->default('draft');
            $table->string('source_module')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('created_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('posted_by')->nullable();
            $table->string('reversed_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('entry_date');
            $table->index('posting_date');
            $table->index('status');
            $table->index('journal_type');
            $table->index('campus_id');
            $table->index(['source_module', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
