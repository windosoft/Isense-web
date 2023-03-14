<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceDashboard extends Model
{
    use SoftDeletes;

    protected $table = "device_dashboard";
    protected $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'device_id', 'company_id', 'branch_id', 'terminal_id', 'temperature', 'temperature_color',
        'temperature_alert', 'humidity', 'humidity_color', 'humidity_alert', 'offline', 'offline_color',
        'offline_alert', 'voltage', 'voltage_color', 'voltage_alert', 'rssi', 'last_seen', 'created_at',
        'updated_at', 'deleted_at'
    ];

    protected $dates = ['deleted_at'];
}
