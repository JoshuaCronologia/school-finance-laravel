<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disbursement_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->date('request_date');
            $table->date('due_date')->nullable();
            $table->enum('payee_type', ['vendor', 'employee', 'other']);
            $table->unsignedBigInteger('payee_id')->nullable();
            $table->string('payee_name');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->string('project')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'check', 'bank_transfer']);
            $table->text('description')->nullable();
            $table->foreignId('budget_id')->nullable()->constrained()->nullOnDelete();
            $table->string('requested_by')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'paid', 'cancelled'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index('request_date');
            $table->index('status');
            $table->index('department_id');
            $table->index(['payee_type', 'payee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disbursement_requests');
    }
};
