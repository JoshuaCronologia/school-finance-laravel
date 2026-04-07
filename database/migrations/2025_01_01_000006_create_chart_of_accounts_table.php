<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChartOfAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique();
            $table->string('account_name');
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->string('fs_group')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_postable')->default(true);
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('account_type');
            $table->index('parent_id');
            $table->index('campus_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chart_of_accounts');
    }
}
