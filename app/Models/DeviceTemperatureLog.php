<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceTemperatureLog extends Model
{
    use SoftDeletes;

    protected $table = "device_temperature_log";
    protected $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'device_id', 'device_sn', 'temperature', 'humidity', 'rssi', 'vbv', 'is_low_voltage',
        'notification_for', 'alerttype', 'device_color', 'servertime', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected $dates = ['deleted_at'];
}
