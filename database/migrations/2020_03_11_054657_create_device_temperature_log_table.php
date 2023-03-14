<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceTemperatureLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_temperature_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->unsignedBigInteger('device_id')->default(0);
            $table->string('device_sn');
            $table->string('temperature')->nullable();
            $table->string('humidity')->nullable();
            $table->string('rssi')->nullable();
            $table->string('vbv')->nullable();
            $table->string('is_low_voltage')->nullable();
            $table->string('notification_for')->nullable();
            $table->string('alerttype')->nullable();
            $table->string('device_color')->nullable();
            $table->timestamp('servertime')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_temperature_log');
    }
}
