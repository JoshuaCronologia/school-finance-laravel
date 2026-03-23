<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ap_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique();
            $table->date('bill_date');
            $table->date('posting_date')->nullable();
            $table->date('due_date');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('payment_terms_id')->nullable()->constrained('payment_terms')->nullOnDelete();
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('withholding_tax', 15, 2)->default(0);
            $table->decimal('net_payable', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'posted', 'partially_paid', 'paid', 'cancelled', 'voided'])->default('draft');
            $table->string('created_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('bill_date');
            $table->index('due_date');
            $table->index('vendor_id');
            $table->index('status');
            $table->index('campus_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ap_bills');
    }
};
