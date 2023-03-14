<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorInfo extends Model
{
    protected $table = "sensor_info";
    protected $primaryKey = "id";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'terminal_info', 'create_time'
    ];
}
