<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Groups extends Model
{
    use SoftDeletes;

    protected $table = "groups";
    protected $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'company_id', 'name', 'device_ids', 'description', 'parent', 'status', 'created_at', 'updated_at',
        'deleted_at'
    ];

    protected $dates = ['deleted_at'];
}
