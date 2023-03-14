<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Permissions extends Model
{
    protected $table = "permissions";
    protected $primaryKey = "id";

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'created_at', 'updated_at'
    ];

    /**
     * Gateway means Receiver and terminal
     * Sensor means device
     *
     * @var array
     */
    static $permissionList = [
        'company', 'branch', 'gateway', 'sensor', 'group', 'employee', 'temperatures', 'humidity', 'voltage', 'offline',
        'message_center', 'report', 'mobile_dashboard','schedule_update',
    ];
    static $actionList = ['view', 'add', 'edit', 'delete'];

    static function allPermissions()
    {
        $permissions = self::$permissionList;
        $actions = self::$actionList;

        $permissionList = [];
        foreach ($permissions as $permission) {
            $name = strtolower(str_replace(' ', '_', trim($permission)));
            foreach ($actions as $action) {
                $actionName = strtolower(str_replace(' ', '_', trim($action)));

                $permissionName = $actionName . "_" . $name;

                if ($permissionName == 'view_dashboard') {
                    $permissionList[] = $permissionName;
                    break;
                } elseif ($permissionName == 'view_message_center') {
                    $permissionList[] = $permissionName;
                    break;
                } elseif ($permissionName == 'view_report') {
                    $permissionList[] = $permissionName;
                    break;
                } elseif ($permissionName == 'view_mobile_dashboard') {
                    $permissionList[] = $permissionName;
                    break;
                } else {
                    $permissionList[] = $permissionName;
                }
            }
        }
        return $permissionList;
    }

    static function checkActionPermission($permissionName)
    {
        $userData = Auth::user();
        if (!empty($userData)) {
            $roleId = $userData->role_id;
            if ($roleId == Roles::$admin) {
                return true;
            }

            if (!is_array($permissionName)) {
                $permissionName = [$permissionName];
            }

            $checkPermission = DB::table('role_permissions')->join('permissions AS P', 'P.id', '=', 'role_permissions.permissions_id')
                ->where('role_permissions.roles_id', $roleId)
                ->whereIn('P.name', $permissionName)
                ->count();
            if ($checkPermission == 0) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
}
