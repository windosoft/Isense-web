<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branches extends Model
{
    use SoftDeletes;

    protected $table = "branches";
    protected $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'company_id', 'name', 'phone', 'email', 'key_person', 'address', 'status', 'created_at', 'updated_at',
        'deleted_at'
    ];

    protected $dates = ['deleted_at'];
}
