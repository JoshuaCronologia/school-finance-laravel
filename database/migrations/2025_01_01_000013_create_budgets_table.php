<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('budget_name');
            $table->string('school_year');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fund_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('project')->nullable();
            $table->string('campus')->nullable();
            $table->decimal('annual_budget', 15, 2);
            $table->string('budget_owner')->nullable();
            $table->enum('status', ['draft', 'approved', 'closed'])->default('draft');
            $table->decimal('committed', 15, 2)->default(0);
            $table->decimal('actual', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('school_year');
            $table->index('status');
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
