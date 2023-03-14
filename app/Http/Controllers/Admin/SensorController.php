<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branches;
use App\Models\Device;
use App\Models\DeviceDashboard;
use App\Models\DeviceTemperatureLog;
use App\Models\Helpers;
use App\Models\Permissions;
use App\Models\Roles;
use App\Models\Terminals;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SensorController extends Controller
{
    protected $actStatus = '';
    protected $delStatus = '';

    /**
     * SensorController constructor.
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
        $checkPermission = Permissions::checkActionPermission('view_sensor');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $isCompany = Helpers::isCompany();

        return view('backend.sensor.index', compact('isCompany'));
    }

    /**
     * paginate for sensor
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
            $userData = Auth::user();
            $userRole = $userData->role_id;

            $where = "status = '" . $this->actStatus . "' ";
            $isCompany = Helpers::isCompany();
            if ($isCompany) {
                $where .= " AND company_id = " . Helpers::companyId();
            }
            if ($userRole == Roles::$employee) {
                $where .= " AND (SELECT COUNT(G.id) AS count FROM groups AS G WHERE G.status = '" . $this->actStatus . "' AND G.deleted_at IS NULL AND G.id = '" . $userData->group_id . "' AND FIND_IN_SET(device.id, G.device_ids ) ) > 0 ";
            }

            $totalData = Device::whereRaw($where)->count();

            $dataList = Device::whereRaw($where)
                ->select(
                    'uuid', 'device_name', 'device_sn', 'type_of_facility', 'expire_date', 'created_at',
                    DB::raw("(SELECT first_name FROM users WHERE users.id = device.company_id) AS company_name"),
                    DB::raw("(SELECT time_zone FROM users WHERE users.id = device.company_id) AS company_time_zone")
                )
                ->orderBy('id', 'desc')
                ->limit($limit)->offset($start)
                ->get()->toArray();

            foreach ($dataList as $key => $value) {
                $dataList[$key]['index'] = ++$start;
                $timezone = new \DateTimeZone($value['company_time_zone']);
                $createdAt = new \DateTime($value['created_at'], $timezone);
                $expireAt = new \DateTime($value['expire_date'], $timezone);
                $dataList[$key]['created_date'] = $createdAt->format('d-m-Y');
                $dataList[$key]['expire_date'] = $expireAt->format('d-m-Y');
            }
        } catch (\Exception $exception) {
            Helpers::log('Sensor pagination exception');
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
        $checkPermission = Permissions::checkActionPermission('add_sensor');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $isCompany = Helpers::isCompany();
        $branchList = [];
        $companyList = [];
        if ($isCompany) {
            $companyId = Helpers::companyId();
            $branchList = Helpers::selectBoxBranchListByCompany($companyId);
        } else {
            $companyList = Helpers::selectBoxCompanyList();
        }
        $typeOfFacility = config('constants.type_of_facility');

        return view('backend.sensor.create', compact('isCompany', 'companyList', 'branchList', 'typeOfFacility'));
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
            $deviceName = $request->device_name;
            $deviceSN = $request->device_sn;
            $checkName = Device::where('device_name', $deviceName)->where('company_id', $companyId)->count();
            $checkImei = Device::where('device_sn', $deviceSN)->where('company_id', $companyId)->count();
            if ($checkName > 0) {
                return response()->json(['status' => 409, 'message' => 'This sensor name already exists.']);
            } elseif ($checkImei > 0) {
                return response()->json(['status' => 409, 'message' => 'This sensor serial no already exists.']);
            } else {

                $expireTime = config('constants.sensor_expire');
                $expiredDate = Carbon::now()->addMonths($expireTime);

                $branchId = $request->branch_id;
                $terminalId = $request->terminal_id;
                $createData = [
                    'uuid' => Helpers::getUuid(),
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'terminal_id' => $terminalId,
                    'group_id' => 0,
                    'device_name' => $deviceName,
                    'device_sn' => $deviceSN,
                    'type_of_facility' => $request->type_of_facility,
                    'expire_date' => $expiredDate,
                    'device_password' => $request->device_password,
                    'data_interval' => $request->data_interval,
                    'temp_adjustment' => $request->temp_adjustment,
                    'humidity_adjustment' => $request->humidity_adjustment,
                    'status' => $this->actStatus,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                $deviceData = Device::create($createData);

                $deviceDashboardData = [
                    "uuid" => Helpers::getUuid(),
                    "branch_id" => $branchId,
                    "terminal_id" => $terminalId,
                    "company_id" => $companyId,
                    "device_id" => $deviceData->id,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];
                DeviceDashboard::create($deviceDashboardData);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This sensor has been successfully created.", "redirect" => route('admin.sensor.index')]);
            }
        } catch (\Exception $exception) {
            Helpers::log('sensor create : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
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
        $checkPermission = Permissions::checkActionPermission('edit_sensor');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $sensorData = Device::where('uuid', $uuid)->first();
        if (!empty($sensorData)) {

            $isCompany = Helpers::isCompany();
            $branchList = [];
            $companyList = [];
            $sensorData->company_name = Helpers::companyName($sensorData->company_id);

            $branchData = Branches::where('id', $sensorData->branch_id)
                ->select('id', 'name')
                ->first();
            $sensorData->branch_name = $branchData->name;

            $terminalData = Terminals::where('id', $sensorData->terminal_id)
                ->select('id', 'name')
                ->first();
            $sensorData->gateway_name = $terminalData->name;


            $typeOfFacility = config('constants.type_of_facility');

            return view('backend.sensor.edit', compact('sensorData', 'isCompany', 'companyList', 'branchList', 'typeOfFacility'));
        } else {
            return redirect()->route('admin.sensor.index')->with('error', 'Ooops...Something went wrong. Please try again.');
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
            $deviceData = Device::where('uuid', $uuid)->select('id', 'company_id', 'branch_id')->first();
            if (!empty($deviceData)) {
                $deviceId = $deviceData->id;
                $companyId = $deviceData->company_id;
                $deviceName = $request->device_name;
                $deviceSN = $request->device_sn;

                $checkName = Device::where('device_name', $deviceName)
                    ->where('company_id', $companyId)
                    ->where('id', '!=', $deviceId)
                    ->count();

                $checkImei = Device::where('device_sn', $deviceSN)
                    ->where('company_id', $companyId)
                    ->where('id', '!=', $deviceId)
                    ->count();

                if ($checkName > 0) {
                    return response()->json(['status' => 409, 'message' => 'This sensor name already exists.']);
                } elseif ($checkImei > 0) {
                    return response()->json(['status' => 409, 'message' => 'This sensor serial no already exists.']);
                } else {

                    $updateData = [
                        'device_name' => $deviceName,
                        'device_sn' => $deviceSN,
                        'type_of_facility' => $request->type_of_facility,
                        'device_password' => $request->device_password,
                        'data_interval' => $request->data_interval,
                        'temp_adjustment' => $request->temp_adjustment,
                        'humidity_adjustment' => $request->humidity_adjustment,
                        'status' => $this->actStatus,
                        'updated_at' => Carbon::now(),
                    ];
                    Device::where('id', $deviceId)->update($updateData);

                    DB::commit();
                    return response()->json(["status" => 200, "message" => "This sensor has been successfully updated.", "redirect" => route('admin.sensor.index')]);
                }
            } else {
                return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
            }
        } catch (\Exception $exception) {
            Helpers::log('sensor update : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
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
        $checkPermission = Permissions::checkActionPermission('delete_sensor');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        return view('backend.sensor.delete', compact('uuid'));
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
            $deviceData = Device::where('uuid', $uuid)->select('id')->first();
            if (!empty($deviceData)) {
                $deviceId = $deviceData->id;
                $data = [
                    'status' => $this->delStatus,
                    'deleted_at' => Carbon::now()
                ];
                Device::where('id', $deviceId)->update($data);

                $deleteData = ['deleted_at' => Carbon::now()];
                DeviceDashboard::where('device_id', $deviceId)->update($deleteData);
                DeviceTemperatureLog::where('device_id', $deviceId)->update($deleteData);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This sensor has been successfully deleted.", "redirect" => route('admin.sensor.index')]);
            } else {
                return response()->json(['status' => 404, 'message' => 'Ooops...Something went wrong! Please try again.']);
            }
        } catch (\Exception $exception) {
            Helpers::log('sensor delete : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }

    /**
     * device list by company
     *
     * @param $company_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function listByCompany($company_id)
    {
        $deviceList = Helpers::selectBoxDeviceListByCompany($company_id);

        return response()->json(["status" => 200, "message" => "success", "data" => $deviceList]);
    }

    /**
     * device list by group
     *
     * @param $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function listByGroup($group_id)
    {
        $where = " status = '" . $this->actStatus . "' AND FIND_IN_SET(id ,(SELECT device_ids FROM `groups` WHERE id = $group_id AND status = '" . $this->actStatus . "' AND deleted_at IS NULL ))";
        $deviceList = Device::whereRaw($where)
            ->select('id', 'device_name')
            ->get()->toArray();

        return response()->json(["status" => 200, "message" => "success", "data" => $deviceList]);
    }
}
