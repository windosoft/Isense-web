<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardUpdateHistory extends Model
{
    protected $table = 'dashboard_update_history';
    protected $primaryKey = 'dh_id';
    public $timestamps = false;

    protected $guarded = ['dh_id'];
}
