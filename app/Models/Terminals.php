<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Terminals extends Model
{
    use SoftDeletes;

    /**
     * this table for gateway
     * @var string
     */
    protected $table = "terminals";
    protected $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'company_id', 'branch_id', 'department_id', 'name', 'imei', 'receiver_type',
        'password', 'remarks', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected $dates = ['deleted_at'];
}
