<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleUpdateLog extends Model
{
    protected $table = 'schedule_update_log';
    protected $primaryKey = 'sul_id';
    public $timestamps = false;

    protected $guarded = ['sul_id'];
}
