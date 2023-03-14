<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemperatureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temperature', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->string('name');
            $table->string('low_temp_warning');
            $table->string('high_temp_warning');
            $table->string('low_temp_threshold');
            $table->string('high_temp_threshold');
            $table->integer('dramatic_changes_value');
            $table->date('effective_start_date');
            $table->date('effective_end_date');
            $table->string('effective_date_enable');
            $table->string('repeat');
            $table->time('effective_start_time');
            $table->time('effective_end_time');
            $table->string('effective_time_enable');
            $table->enum('status', ['A', 'I', 'D'])->default('A')->comment('A:ACTIVE I:INACTIVE D:SOFTDELETE	');
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
        Schema::dropIfExists('temperature');
    }
}
