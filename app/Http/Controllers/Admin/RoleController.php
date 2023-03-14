<?php

namespace App\Http\Controllers\Admin;

use App\Models\Helpers;
use App\Models\Permissions;
use App\Models\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    protected $actStatus = '';
    protected $delStatus = '';

    /**
     * RoleController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
        $this->delStatus = Helpers::$delete;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = Permissions::checkActionPermission('view_roles');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $roleList = Roles::where('id', '!=', Roles::$admin)->get();
        return view('backend.roles.index', compact('roleList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = Permissions::checkActionPermission('add_roles');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        return view('backend.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $roleName = trim($request->role_name);
            $checkName = Roles::where('name', $roleName)->count();
            if ($checkName > 0) {
                return response()->json(['status' => 409, 'message' => 'This role name already exists.']);
            } else {
                $createData = [
                    'uuid' => Helpers::getUuid(),
                    'name' => $roleName,
                    'status' => $this->actStatus,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                Roles::create($createData);

                DB::commit();
                return response()->json(['status' => 200, 'message' => 'This information has been saved', 'redirect' => route('admin.roles.index')]);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('role create : exception');
            Helpers::log($exception);
            return response()->json(['status' => 500, 'message' => 'Ooops....something went wrong. Please try again']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($uuid)
    {
        $checkPermission = Permissions::checkActionPermission('edit_roles');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $roleData = Roles::where('uuid', $uuid)->first();
        $roleData->role_name = $roleData->name;
        return view('backend.roles.edit', compact('roleData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $uuid)
    {
        DB::beginTransaction();
        try {
            $roleName = trim($request->role_name);
            $checkName = Roles::where('name', $roleName)->where('uuid', '!=', $uuid)->count();

            if ($checkName > 0) {
                return response()->json(['status' => 409, 'message' => 'The role name is already exists.']);
            } else {
                $updateData = [
                    'name' => $roleName,
                    'updated_at' => Carbon::now()
                ];
                Roles::where('uuid', $uuid)->update($updateData);

                DB::commit();
                return response()->json(['status' => 200, 'message' => 'This information has been updated', 'redirect' => route('admin.roles.index')]);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('role update : exception');
            Helpers::log($exception);
            return response()->json(['status' => 500, 'message' => 'Ooops....something went wrong. Please try again']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function permissions($uuid)
    {
        try {
            $checkPermission = Permissions::checkActionPermission('edit_roles');
            if ($checkPermission == false) {
                return view('backend.access-denied');
            }
            $roleData = Roles::where('uuid', $uuid)->first();
            if (!empty($roleData)) {
                $roleId = $roleData->id;
                $allowPermissionList = [];
                $rolePermissionList = DB::table('role_permissions')->join('permissions AS P', 'P.id', '=', 'role_permissions.permissions_id')
                    ->where('role_permissions.roles_id', $roleId)
                    ->select(['name'])
                    ->get();


                foreach ($rolePermissionList as $value) {
                    array_push($allowPermissionList, $value->name);
                }
                $roleData->allowPermission = $allowPermissionList;

                $roleData->actionList = Permissions::$actionList;
                $permissionList = Permissions::$permissionList;
                if (in_array($roleId, [Roles::$employee, Roles::$company])) {
                    unset($permissionList[0]);
                }
                $roleData->moduleList = $permissionList;
                $roleData->permissionList = Permissions::allPermissions();

                return view('backend.roles.permission', compact('roleData'));
            } else {
                return redirect()->route('admin.roles.index')->with('error', 'Ooops...Something went wrong. Data not found.');
            }
        } catch (\Exception $exception) {
            Helpers::log('Permission role : exception');
            Helpers::log($exception);
            return redirect()->route('admin.roles.index')->with('error', 'Ooops...Something went wrong. Please try again.');
        }
    }

    /**
     * Role Permission update
     *
     * @param Request $request
     * @param $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissionUpdate(Request $request, $uuid)
    {
        DB::beginTransaction();
        try {

            $roleData = Roles::where('uuid', $uuid)->first();
            $roleId = $roleData->id;
            DB::table('role_permissions')->where('roles_id', $roleId)->delete();
            $permissions = $request->permissions;
            if (isset($permissions) && count($permissions) > 0) {
                foreach ($permissions as $value) {
                    $permissionData = Permissions::where('name', $value)->first();
                    if (!empty($permissionData)) {
                        $data = [
                            'roles_id' => $roleId,
                            'permissions_id' => $permissionData->id
                        ];
                        DB::table('role_permissions')->insert($data);
                    }
                }
            }

            DB::commit();
            return response()->json(['status' => 200, 'message' => 'Role permission has been updated!']);
        } catch (\Exception $exception) {
            Helpers::log('Role Permission update : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Role permission has not been updated!']);
        }
    }
}
