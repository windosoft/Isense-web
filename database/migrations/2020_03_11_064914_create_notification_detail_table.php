<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_detail', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->index();
            $table->uuid('notification_uuid');
            $table->unsignedBigInteger('user_id')->comment('company and company employee id');
            $table->enum('read_status', ['unread', 'read'])->default('unread');
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
        Schema::dropIfExists('notification_detail');
    }
}
