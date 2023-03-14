<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roles extends Model
{
    use SoftDeletes;

    protected $table = "roles";
    protected $primaryKey = "id";

    static $roleList = ['Admin', 'Company', 'Employee'];

    static $admin = 1;
    static $company = 2;
    static $employee = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'name', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected $dates = ['deleted_at'];
}
