<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDashboardUpdateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_update_history', function (Blueprint $table) {
            $table->bigIncrements('dh_id');
            $table->uuid('dh_uuid');
            $table->unsignedBigInteger('dh_company_id');
            $table->unsignedBigInteger('dh_device_id');
            $table->string('dh_sensor_status');
            $table->string('dh_sensor_temp');
            $table->string('dh_sensor_humidity');
            $table->dateTime('dh_last_updated');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dashboard_update_history');
    }
}
