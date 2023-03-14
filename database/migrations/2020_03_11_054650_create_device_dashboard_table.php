<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceDashboardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_dashboard', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->unsignedBigInteger('device_id')->default(0);
            $table->unsignedBigInteger('company_id')->default(0);
            $table->unsignedBigInteger('branch_id')->default(0);
            $table->unsignedBigInteger('terminal_id')->default(0);
            $table->string('temperature')->nullable();
            $table->string('temperature_color')->nullable();
            $table->string('temperature_alert')->nullable();
            $table->string('humidity')->nullable();
            $table->string('humidity_color')->nullable();
            $table->string('humidity_alert')->nullable();
            $table->string('offline')->nullable();
            $table->string('offline_color')->nullable();
            $table->string('offline_alert')->nullable();
            $table->string('voltage')->nullable();
            $table->string('voltage_color')->nullable();
            $table->string('voltage_alert')->nullable();
            $table->string('rssi')->nullable();
            $table->timestamp('last_seen')->useCurrent();
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
        Schema::dropIfExists('device_dashboard');
    }
}
