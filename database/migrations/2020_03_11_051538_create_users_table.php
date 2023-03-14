<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->unsignedBigInteger('group_id')->default(0);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('email');
            $table->string('password');
            $table->enum('user_type', ['ADMIN', 'COMPANY', 'USER'])->default('ADMIN');
            $table->enum('status', ['A', 'I', 'D'])->default('A')->comment('A:ACTIVE I:INACTIVE D:SOFTDELETE	');
            $table->enum('device_type', ['ANDROID', 'IOS'])->default('ANDROID');
            $table->string('device_token')->nullable();
            $table->string('profile')->default('backend/images/default.png');
            $table->string('mobile_device_id')->nullable();
            $table->string('time_zone')->nullable();
            $table->string('api_token')->nullable();
            $table->string('address')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
