<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApPaymentAllocationsTable extends Migration
{
    public function up()
    {
        Schema::create('ap_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('ap_payments')->onDelete('cascade');
            $table->foreignId('bill_id')->constrained('ap_bills')->onDelete('cascade');
            $table->decimal('amount_applied', 15, 2);
            $table->timestamps();

            $table->index('payment_id');
            $table->index('bill_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ap_payment_allocations');
    }
}
