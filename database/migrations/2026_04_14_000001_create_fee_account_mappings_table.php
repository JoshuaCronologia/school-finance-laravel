<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeAccountMappingsTable extends Migration
{
    public function up()
    {
        Schema::create('fee_account_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('finance_fee_id');
            $table->string('finance_fee_name');
            $table->unsignedBigInteger('account_id');
            $table->timestamps();

            $table->unique('finance_fee_id');
            $table->index('account_id');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_account_mappings');
    }
}
