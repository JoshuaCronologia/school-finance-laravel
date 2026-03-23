<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_collections', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->date('collection_date');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', ['cash', 'check', 'bank_transfer', 'online', 'gcash', 'maya']);
            $table->string('bank_account')->nullable();
            $table->string('check_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->decimal('amount_received', 15, 2);
            $table->decimal('applied_amount', 15, 2)->default(0);
            $table->decimal('unapplied_amount', 15, 2)->default(0);
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('collected_by')->nullable();
            $table->enum('status', ['draft', 'posted', 'partially_applied', 'fully_applied', 'reversed', 'cancelled'])->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('collection_date');
            $table->index('customer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_collections');
    }
};
