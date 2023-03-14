<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Device extends Model
{
    use SoftDeletes;

    protected $table = "device";
    protected $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'company_id', 'branch_id', 'terminal_id', 'group_id', 'device_name', 'device_sn', 'type_of_facility',
        'expire_date', 'device_password', 'data_interval', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected $dates = ['deleted_at'];

    static function dataById($id)
    {
        $deviceData = Device::where('id', $id)
            ->where('status', Helpers::$active)
            ->select(
                'company_id', 'device_name', 'device_sn', 'type_of_facility', 'data_interval',
                DB::raw("(SELECT time_zone FROM users WHERE users.id = device.company_id) AS company_time_zone")
            )
            ->first();
        return $deviceData;
    }
}
