<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentAndClassificationToChartOfAccounts extends Migration
{
    public function up()
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete()->after('campus_id');
            $table->string('account_classification', 20)->nullable()->after('department_id'); // current, non-current
        });
    }

    public function down()
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'account_classification']);
        });
    }
}
