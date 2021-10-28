<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountingSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_subjects', function (Blueprint $table) {
            $table->increments('accounting_subjects_id');
            $table->string('name', 20);
            $table->unsignedBigInteger('business_category_id');
            $table->unsignedBigInteger('sort_order');
            $table->timestamps();
            $table->dateTime('deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_subjects');
    }
}
