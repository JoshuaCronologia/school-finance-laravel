<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('ap_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->date('payment_date');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', ['cash', 'check', 'bank_transfer', 'online']);
            $table->string('bank_account')->nullable();
            $table->string('check_number')->nullable();
            $table->date('check_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('withholding_tax', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'completed', 'voided'])->default('draft');
            $table->text('remarks')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_date');
            $table->index('vendor_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ap_payments');
    }
}
