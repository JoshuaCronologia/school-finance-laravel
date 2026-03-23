<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_credit_memos', function (Blueprint $table) {
            $table->id();
            $table->string('memo_number')->unique();
            $table->date('memo_date');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('ar_invoices')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->decimal('amount', 15, 2);
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'approved', 'posted', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_credit_memos');
    }
};
