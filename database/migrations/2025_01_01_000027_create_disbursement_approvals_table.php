<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisbursementApprovalsTable extends Migration
{
    public function up()
    {
        Schema::create('disbursement_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disbursement_id')->constrained('disbursement_requests')->onDelete('cascade');
            $table->string('approver_role');
            $table->string('approver_name');
            $table->enum('action', ['approved', 'rejected', 'returned']);
            $table->text('comments')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index('disbursement_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('disbursement_approvals');
    }
}
