<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApDebitMemosTable extends Migration
{
    public function up()
    {
        Schema::create('ap_debit_memos', function (Blueprint $table) {
            $table->id();
            $table->string('memo_number')->unique();
            $table->date('memo_date');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('bill_id')->nullable()->constrained('ap_bills')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->decimal('amount', 15, 2);
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'approved', 'posted', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->index('vendor_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ap_debit_memos');
    }
}
