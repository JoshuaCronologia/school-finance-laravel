<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->enum('customer_type', ['student', 'parent', 'corporate', 'other']);
            $table->string('name');
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->string('grade_level')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('tin')->nullable();
            $table->foreignId('default_ar_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_code');
            $table->index('customer_type');
            $table->index('campus_id');
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
