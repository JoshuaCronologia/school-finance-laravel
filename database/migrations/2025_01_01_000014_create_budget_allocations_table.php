<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetAllocationsTable extends Migration
{
    public function up()
    {
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('month')->unsigned();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->index('budget_id');
            $table->unique(['budget_id', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget_allocations');
    }
}
