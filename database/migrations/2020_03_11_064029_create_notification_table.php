<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->index();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('terminal_id')->nullable();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->double('temperature')->default('0.00');
            $table->double('humidity')->default('0.00');
            $table->integer('offline')->default(0);
            $table->double('voltage')->default('0.00');
            $table->text('notification');
            $table->timestamp('current_date_time')->useCurrent();
            $table->enum('notification_for', ['temperature', 'humidity', 'offline', 'voltage', 'alert']);
            $table->enum('alerttype', ['', 'SUCCESS', 'WARNING', 'DANGER', 'DISABLE', 'ALERT'])->comment('only data have if notification_for is alert');
            $table->string('device_color');
            $table->enum('read_status', ['unread', 'read'])->default('unread');
            $table->enum('status', ['A', 'I', 'D'])->default('A')->comment('A:ACTIVE I:INACTIVE D:SOFTDELETE	');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification');
    }
}
