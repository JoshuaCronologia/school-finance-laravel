<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disbursement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disbursement_id')->constrained('disbursement_requests')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->string('account_code');
            $table->string('tax_code')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('disbursement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disbursement_items');
    }
};
