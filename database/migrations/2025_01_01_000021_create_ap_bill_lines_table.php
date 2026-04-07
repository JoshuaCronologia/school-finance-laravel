<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApBillLinesTable extends Migration
{
    public function up()
    {
        Schema::create('ap_bill_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('ap_bills')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->foreignId('tax_code_id')->nullable()->constrained('tax_codes')->nullOnDelete();
            $table->foreignId('withholding_tax_code_id')->nullable()->constrained('tax_codes')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('project')->nullable();
            $table->foreignId('fund_source_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('bill_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ap_bill_lines');
    }
}
