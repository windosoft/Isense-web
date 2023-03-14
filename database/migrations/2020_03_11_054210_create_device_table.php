<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->unsignedBigInteger('company_id')->default(0);
            $table->unsignedBigInteger('branch_id')->default(0);
            $table->unsignedBigInteger('terminal_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('device_name');
            $table->string('device_sn');
            $table->string('type_of_facility');
            $table->dateTime('expire_date');
            $table->string('device_password');
            $table->integer('data_interval')->default(0);
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
        Schema::dropIfExists('device');
    }
}
