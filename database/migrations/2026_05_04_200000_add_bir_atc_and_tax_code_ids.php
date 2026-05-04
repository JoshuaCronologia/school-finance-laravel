<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBirAtcAndTaxCodeIds extends Migration
{
    public function up()
    {
        // 1. Add bir_atc to tax_codes
        Schema::table('tax_codes', function (Blueprint $table) {
            $table->string('bir_atc', 10)->nullable()->after('type');
        });

        // 2. Add tax_code_id to disbursement_payments
        Schema::table('disbursement_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_code_id')->nullable()->after('disbursement_id');
            $table->foreign('tax_code_id')->references('id')->on('tax_codes')->nullOnDelete();
        });

        // 3. Add tax_code_id to ap_payments
        Schema::table('ap_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_code_id')->nullable()->after('vendor_id');
            $table->foreign('tax_code_id')->references('id')->on('tax_codes')->nullOnDelete();
        });

        // 4. Seed BIR ATC values for existing tax codes
        $atcMap = [
            'EWT1'  => 'WC158', // Prof. fees (individuals ≤720K/yr) — 1% transitional
            'EWT2'  => 'WC160', // Other services (individuals >720K, or corps) — 2%
            'EWT5'  => 'WC120', // Rentals / professional — 5%
            'EWT10' => 'WC010', // Professional fees — 10%
            'EWT15' => 'WC015', // Income distributed by REIT — 15%
            'FWT20' => 'WF020', // Final withholding on interest income — 20%
            'FWT25' => 'WF025', // Final withholding on NRA/NRFC — 25%
        ];

        foreach ($atcMap as $code => $atc) {
            DB::table('tax_codes')->where('code', $code)->update(['bir_atc' => $atc]);
        }
    }

    public function down()
    {
        Schema::table('ap_payments', function (Blueprint $table) {
            $table->dropForeign(['tax_code_id']);
            $table->dropColumn('tax_code_id');
        });

        Schema::table('disbursement_payments', function (Blueprint $table) {
            $table->dropForeign(['tax_code_id']);
            $table->dropColumn('tax_code_id');
        });

        Schema::table('tax_codes', function (Blueprint $table) {
            $table->dropColumn('bir_atc');
        });
    }
}
