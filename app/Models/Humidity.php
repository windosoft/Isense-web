<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Humidity extends Model
{
    use SoftDeletes;

    protected $table = "humidity";
    protected $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'company_id', 'group_id', 'device_id', 'name', 'warning_low_humidity_threshold',
        'warning_high_humidity_threshold', 'low_humidity_threshold', 'high_humidity_threshold',
        'dramatic_changes_value', 'effective_start_date', 'effective_end_date', 'effective_date_enable',
        'repeat', 'effective_start_time', 'effective_end_time', 'effective_time_enable', 'status', 'created_at',
        'updated_at', 'deleted_at'
    ];

    protected $dates = ['deleted_at'];

    static function getDataByDevice($deviceSN)
    {
        $actStatus = Helpers::$active;
        $humidityData = self::join('device AS D', 'D.id', '=', 'humidity.device_id')
            ->where('humidity.effective_date_enable', 1)
            ->where('humidity.effective_time_enable', 1)
            ->where('humidity.device_id', '!=', null)
            ->where('humidity.status', $actStatus)
            ->where('D.device_sn', $deviceSN)
            ->where('D.status', $actStatus)
            ->select(
                'humidity.*', 'humidity.warning_low_humidity_threshold AS low_temp_warning',
                'humidity.warning_high_humidity_threshold AS high_temp_warning',
                'humidity.low_humidity_threshold AS low_temp_threshold',
                'humidity.high_humidity_threshold AS high_temp_threshold',
                'D.id AS d_device_id', 'D.company_id AS d_company_id', 'D.branch_id AS d_branch_id',
                'D.terminal_id AS d_terminal_id', 'D.group_id AS d_group_id', 'D.device_name AS d_device_name',
                'D.device_sn AS d_device_sn', 'D.type_of_facility AS d_type_of_facility'
            )
            ->first();

        return $humidityData;
    }
}
