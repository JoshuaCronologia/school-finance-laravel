<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecurringJournalTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('recurring_journal_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->enum('frequency', ['monthly', 'quarterly', 'annually']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('auto_create')->default(false);
            $table->boolean('is_active')->default(true);
            $table->date('last_generated_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recurring_journal_templates');
    }
}
