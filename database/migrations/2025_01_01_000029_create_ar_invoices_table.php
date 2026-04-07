<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('ar_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('posting_date')->nullable();
            $table->date('due_date');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->string('school_year')->nullable();
            $table->string('semester')->nullable();
            $table->string('billing_period')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('net_receivable', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'approved', 'posted', 'partially_paid', 'paid', 'overdue', 'cancelled', 'voided'])->default('draft');
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('invoice_date');
            $table->index('due_date');
            $table->index('customer_id');
            $table->index('status');
            $table->index('campus_id');
            $table->index('school_year');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ar_invoices');
    }
}
