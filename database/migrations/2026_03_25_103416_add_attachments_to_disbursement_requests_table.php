<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttachmentsToDisbursementRequestsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('disbursement_requests', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('disbursement_requests', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
}
