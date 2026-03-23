<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_collection_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('ar_collections')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('ar_invoices')->onDelete('cascade');
            $table->decimal('amount_applied', 15, 2);
            $table->timestamps();

            $table->index('collection_id');
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_collection_allocations');
    }
};
