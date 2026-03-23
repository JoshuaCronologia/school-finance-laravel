<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->onDelete('cascade');
            $table->integer('line_number');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->string('project')->nullable();
            $table->foreignId('fund_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payee_type')->nullable();
            $table->unsignedBigInteger('payee_id')->nullable();
            $table->date('due_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('journal_entry_id');
            $table->index('account_id');
            $table->index(['payee_type', 'payee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
