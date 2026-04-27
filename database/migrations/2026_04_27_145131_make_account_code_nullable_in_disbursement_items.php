<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAccountCodeNullableInDisbursementItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE disbursement_items MODIFY account_code VARCHAR(20) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE disbursement_items MODIFY account_code VARCHAR(20) NOT NULL');
    }
}
