<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCoaTypesTable extends Migration
{
    public function up()
    {
        Schema::create('coa_types', function (Blueprint $table) {
            $table->id();
            $table->string('label');                          // "Current Asset", "Non-Current Asset"
            $table->enum('base_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->string('classification', 20)->nullable(); // current, non-current
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);     // system types can't be deleted
            $table->timestamps();
        });

        // Seed standard types
        $now = now();
        DB::table('coa_types')->insert([
            ['label' => 'Current Asset',       'base_type' => 'asset',     'classification' => 'current',     'normal_balance' => 'debit',  'sort_order' => 1,  'is_system' => true,  'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Non-Current Asset',   'base_type' => 'asset',     'classification' => 'non-current', 'normal_balance' => 'debit',  'sort_order' => 2,  'is_system' => true,  'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Current Liability',   'base_type' => 'liability', 'classification' => 'current',     'normal_balance' => 'credit', 'sort_order' => 3,  'is_system' => true,  'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Non-Current Liability','base_type' => 'liability', 'classification' => 'non-current', 'normal_balance' => 'credit', 'sort_order' => 4,  'is_system' => true,  'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Equity',              'base_type' => 'equity',    'classification' => null,           'normal_balance' => 'credit', 'sort_order' => 5,  'is_system' => true,  'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Revenue',             'base_type' => 'revenue',   'classification' => null,           'normal_balance' => 'credit', 'sort_order' => 6,  'is_system' => true,  'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Expense',             'base_type' => 'expense',   'classification' => null,           'normal_balance' => 'debit',  'sort_order' => 7,  'is_system' => true,  'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('coa_types');
    }
}
