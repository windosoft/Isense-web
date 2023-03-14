<?php

namespace App\Http\Controllers\Admin;

use App\Models\Device;
use App\Models\Groups;
use App\Models\Helpers;
use App\Models\Permissions;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    protected $actStatus = '';
    protected $delStatus = '';

    /**
     * GroupController constructor.
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
        $checkPermission = Permissions::checkActionPermission('view_group');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $isCompany = Helpers::isCompany();

        return view('backend.group.index', compact('isCompany'));
    }

    /**
     * paginate for group
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginate(Request $request)
    {
        $inputs = $request->all();
        $start = $inputs['start'];
        $limit = $inputs['length'];
        $dataList = [];
        $totalData = 0;
        try {

            $where = ["status" => $this->actStatus];

            $isCompany = Helpers::isCompany();
            if ($isCompany) {
                $where['company_id'] = Helpers::companyId();
            }

            $totalData = Groups::where($where)->count();

            $dataList = Groups::where($where)
                ->select(
                    'uuid', 'name', 'device_ids',
                    DB::raw("(SELECT first_name FROM users WHERE users.id = groups.company_id) AS company_name")
                )
                ->orderBy('id', 'desc')
                ->limit($limit)->offset($start)
                ->get()->toArray();

            foreach ($dataList as $key => $value) {
                $dataList[$key]['index'] = ++$start;

                $deviceIds = explode(',', $value['device_ids']);

                $deviceList = Device::whereIn('id', $deviceIds)->select('device_name')->get()->toArray();
                $deviceName = '';
                foreach ($deviceList as $k => $device) {
                    $deviceName .= $device['device_name'];
                    if (count($deviceList) != ($k + 1)) {
                        $deviceName .= ", ";
                    }
                }

                $dataList[$key]['device_name'] = $deviceName;
            }
        } catch (\Exception $exception) {
            Helpers::log('group pagination exception');
            Helpers::log($exception);
            $dataList = [];
            $totalData = 0;
        }
        $data = [
            "aaData" => $dataList,
            "iTotalDisplayRecords" => $totalData,
            "iTotalRecords" => $totalData,
            "sEcho" => $inputs['draw'],
        ];
        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = Permissions::checkActionPermission('add_group');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $isCompany = Helpers::isCompany();
        $companyList = [];
        $deviceList = [];
        if ($isCompany) {
            $companyId = Helpers::companyId();
            $deviceList = Helpers::selectBoxDeviceListByCompany($companyId);
        } else {
            $companyList = Helpers::selectBoxCompanyList();
        }

        return view('backend.group.create', compact('isCompany', 'companyList', 'deviceList'));
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
            $companyId = 0;
            $isCompany = Helpers::isCompany();
            if ($isCompany) {
                $companyId = Helpers::companyId();
            } else {
                $companyId = $request->company_id;
            }
            $groupName = $request->name;
            $checkName = Groups::where('name', $groupName)->where('company_id', $companyId)->count();
            if ($checkName > 0) {
                return response()->json(['status' => 409, 'message' => 'This group name already exists.']);
            } else {
                $deviceIds = implode(',', $request->device_id);
                $createData = [
                    "uuid" => Helpers::getUuid(),
                    "company_id" => $companyId,
                    "name" => $groupName,
                    "device_ids" => $deviceIds,
                    "description" => $request->description,
                    "parent" => 0,
                    "status" => $this->actStatus,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];
                Groups::create($createData);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This group has been successfully created.", "redirect" => route('admin.group.index')]);
            }
        } catch (\Exception $exception) {
            Helpers::log('group create : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(["status" => 500, "message" => "Ooops...Something went wrong. Please contact to support team."]);
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($uuid)
    {
        $checkPermission = Permissions::checkActionPermission('edit_group');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $groupData = Groups::where('uuid', $uuid)->first();
        if (!empty($groupData)) {
            $isCompany = Helpers::isCompany();
            $companyList = [];
            $companyId = $groupData->company_id;
            $groupData->company_name = Helpers::companyName($companyId);
            $groupData->device_ids = explode(',', $groupData->device_ids);

            $deviceList = Helpers::selectBoxDeviceListByCompany($companyId);

            return view('backend.group.edit', compact('groupData', 'isCompany', 'companyList', 'deviceList'));
        } else {
            return redirect()->route('admin.groups.index')->with('error', 'Ooops...Something went wrong. Please try again.');
        }
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
            $groupData = Groups::where('uuid', $uuid)->select('id', 'company_id')->first();
            if (!empty($groupData)) {
                $groupId = $groupData->id;
                $companyId = $groupData->company_id;
                $groupName = $request->name;

                $checkName = Groups::where('name', $groupName)
                    ->where('company_id', $companyId)
                    ->where('id', '!=', $groupId)
                    ->count();
                if ($checkName > 0) {
                    return response()->json(['status' => 409, 'message' => 'This group name already exists.']);
                } else {
                    $deviceIds = implode(',', $request->device_id);
                    $createData = [
                        "name" => $groupName,
                        "device_ids" => $deviceIds,
                        "description" => $request->description,
                        "status" => $this->actStatus,
                        "updated_at" => Carbon::now()
                    ];
                    Groups::where('id', $groupId)->update($createData);

                    DB::commit();
                    return response()->json(["status" => 200, "message" => "This group has been successfully updated.", "redirect" => route('admin.group.index')]);
                }
            } else {
                return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
            }
        } catch (\Exception $exception) {
            Helpers::log('group update : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(["status" => 500, "message" => "Ooops...Something went wrong. Please contact to support team."]);
        }
    }

    /**
     * Show the form for deleting the specified resource.
     *
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function delete($uuid)
    {
        $checkPermission = Permissions::checkActionPermission('delete_group');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        return view('backend.group.delete', compact('uuid'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($uuid)
    {
        DB::beginTransaction();
        try {
            $groupData = Groups::where('uuid', $uuid)->select('id')->first();
            if (!empty($groupData)) {
                $groupId = $groupData->id;
                $data = [
                    'status' => $this->delStatus,
                    'deleted_at' => Carbon::now()
                ];
                Groups::where('id', $groupId)->update($data);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This group has been successfully deleted.", "redirect" => route('admin.group.index')]);
            } else {
                return response()->json(['status' => 404, 'message' => 'Ooops...Something went wrong! Please try again.']);
            }
        } catch (\Exception $exception) {
            Helpers::log('group delete : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }

    /**
     * group list by company
     *
     * @param $company_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function listByCompany($company_id)
    {
        $groupList = Helpers::selectBoxGroupListByCompany($company_id);

        return response()->json(["status" => 200, "message" => "success", "data" => $groupList]);
    }
}
