<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disbursement_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disbursement_id')->constrained('disbursement_requests')->onDelete('cascade');
            $table->string('voucher_number')->unique();
            $table->date('payment_date');
            $table->string('payment_method');
            $table->string('bank_account')->nullable();
            $table->string('check_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('withholding_tax', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->enum('status', ['completed', 'voided'])->default('completed');
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index('disbursement_id');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disbursement_payments');
    }
};
