<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offline extends Model
{
    use SoftDeletes;

    protected $table = "offline";
    protected $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'company_id', 'group_id', 'device_id', 'name', 'offline_time', 'effective_start_date',
        'effective_end_date', 'effective_date_enable', 'repeat', 'effective_start_time', 'effective_end_time',
        'effective_time_enable', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected $dates = ['deleted_at'];

    static function getDataByDevice($deviceSN)
    {
        $actStatus = Helpers::$active;
        $offlineData = self::join('device AS D', 'D.id', '=', 'offline.device_id')
            ->where('offline.effective_date_enable', 1)
            ->where('offline.effective_time_enable', 1)
            ->where('offline.device_id', '!=', null)
            ->where('offline.status', $actStatus)
            ->where('D.device_sn', $deviceSN)
            ->where('D.status', $actStatus)
            ->select(
                'offline.*', 'D.id AS d_device_id', 'D.company_id AS d_company_id', 'D.branch_id AS d_branch_id',
                'D.terminal_id AS d_terminal_id', 'D.group_id AS d_group_id', 'D.device_name AS d_device_name',
                'D.device_sn AS d_device_sn', 'D.type_of_facility AS d_type_of_facility'
            )
            ->first();

        return $offlineData;
    }
}
