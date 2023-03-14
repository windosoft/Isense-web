<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'role_id', 'company_id', 'device_id', 'group_id', 'first_name', 'last_name', 'phone', 'email',
        'password', 'user_type', 'status', 'device_type', 'device_token', 'profile', 'mobile_device_id', 'time_zone',
        'api_token', 'address', 'remember_token', 'created_at', 'updated_at', 'deleted_at'
    ];

    static $admin = 'ADMIN';
    static $company = 'COMPANY';
    static $employee = 'USER';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = ['deleted_at'];

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
