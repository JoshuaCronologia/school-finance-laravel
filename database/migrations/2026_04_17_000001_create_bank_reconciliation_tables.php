<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankReconciliationTables extends Migration
{
    public function up()
    {
        // Bank accounts (BDO SA, BDO CA, BPI SA, etc.)
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('account_type', 10); // SA or CA
            $table->string('account_number')->nullable();
            $table->string('account_label'); // e.g. "BDO Savings Account"
            $table->foreignId('chart_account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('bank_name');
            $table->index('is_active');
        });

        // Issued checks tracking
        Schema::create('issued_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->date('check_date');
            $table->string('check_number');
            $table->string('payee');
            $table->decimal('amount', 15, 2);
            $table->string('status', 20)->default('outstanding'); // outstanding, cleared, voided
            $table->date('cleared_date')->nullable();
            $table->unsignedBigInteger('disbursement_payment_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('check_number');
            $table->index('check_date');
        });

        // Bank statements (uploaded)
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->date('statement_date');
            $table->string('period_label')->nullable(); // e.g. "April 2026"
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index('statement_date');
        });

        // Bank statement line items
        Schema::create('bank_statement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_id')->constrained('bank_statements')->onDelete('cascade');
            $table->date('transaction_date');
            $table->string('description');
            $table->string('reference_number')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('running_balance', 15, 2)->default(0);
            $table->boolean('is_matched')->default(false);
            $table->unsignedBigInteger('matched_check_id')->nullable();
            $table->timestamps();

            $table->index('transaction_date');
            $table->index('is_matched');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bank_statement_items');
        Schema::dropIfExists('bank_statements');
        Schema::dropIfExists('issued_checks');
        Schema::dropIfExists('bank_accounts');
    }
}
