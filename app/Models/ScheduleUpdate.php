<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleUpdate extends Model
{
    protected $table = 'schedule_update';
    protected $primaryKey = 'su_id';
    public $timestamps = false;

    protected $guarded = ['su_id'];
}
