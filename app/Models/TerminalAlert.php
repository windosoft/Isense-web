<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerminalAlert extends Model
{
    protected $table = "terminal_alert";
    protected $primaryKey = "id";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'company_id', 'branch_id', 'department_id', 'terminal_id', 'temperature_info', 'notification',
        'current_date_time'
    ];

}
