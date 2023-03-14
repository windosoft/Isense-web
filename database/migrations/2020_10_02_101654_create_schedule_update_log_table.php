<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleUpdateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_update_log', function (Blueprint $table) {
            $table->bigIncrements('sul_id');
            $table->uuid('sul_uuid');
            $table->unsignedBigInteger('su_id');
            $table->string('sul_email');
            $table->date('sul_date');
            $table->time('sul_time');
            $table->dateTime('sul_log_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_update_log');
    }
}
