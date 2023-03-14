<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleUpdateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_update', function (Blueprint $table) {
            $table->bigIncrements('su_id');
            $table->uuid('su_uuid');
            $table->unsignedBigInteger('su_company_id');
            $table->text('su_devices');
            $table->integer('su_type')->comment('1 For Daiy , 2 For Weekly , 3 For Monthly');
            $table->string('su_week_day');
            $table->string('su_month_date');
            $table->time('su_time');
            $table->string('su_email');
            $table->tinyInteger('su_status')->comment('1 For Active , 0 For Deleted');
            $table->dateTime('su_created_date');
            $table->dateTime('su_created_by');
            $table->dateTime('su_updated_date');
            $table->dateTime('su_updated_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_update');
    }
}
