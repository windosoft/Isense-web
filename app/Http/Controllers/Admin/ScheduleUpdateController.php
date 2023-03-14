<?php

namespace App\Http\Controllers\Admin;

use App\Models\Device;
use App\Models\Helpers;
use App\Models\Permissions;
use App\Models\Roles;
use App\Models\ScheduleUpdate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleUpdateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->company = Roles::$company;
        $this->actStatus = Helpers::$active;
    }

    public function index()
    {
        $checkPermission = Permissions::checkActionPermission('view_schedule_update');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $isCompany = Helpers::isCompany();

        return view('backend.scheduleupdate.index', compact('isCompany'));
    }

    public function paginate(Request $request)
    {
        $inputs = $request->all();
        $start = $inputs['start'];
        $limit = $inputs['length'];
        $schedulesList = [];
        $totalSchedules = 0;
        try {

            $where = ["su_status" => 1];

            $isCompany = Helpers::isCompany();
            if ($isCompany) {
                $where['su_company_id'] = Helpers::companyId();
            }

            $totalSchedules = ScheduleUpdate::where($where)->count();

            $schedulesList = ScheduleUpdate::where($where)
                ->select(
                    'su_uuid', 'su_devices', 'su_type', 'su_week_day', 'su_time', 'su_email', 'su_status',
                    DB::raw("(SELECT first_name FROM users WHERE users.id = schedule_update.su_company_id) AS company_name")
                )
                ->orderBy('su_created_date', 'desc')
                ->limit($limit)->offset($start)
                ->get()->toArray();
            foreach ($schedulesList as $key => $value) {
                $deviceListArray = explode(',', $value['su_devices']);

                $getDevices = Device::whereIn('id', $deviceListArray)->get()->toArray();
                $AllDevices = [];
                foreach ($getDevices as $deviceData) {
                    $AllDevices[] = $deviceData['device_name'];
                }
                $scheduleType = '';
                $scheduleOn = '';
                $scheduleTime = date('H:i A', strtotime($value['su_time']));
                if ($value['su_type'] == 1) {
                    $scheduleType = 'Daily';
                    $scheduleOn = date('H:i A', strtotime($value['su_time']));
                } else if ($value['su_type'] == 2) {
                    $scheduleType = 'Weekly';
                    $scheduleOn = $value['su_week_day'];
                } else if ($value['su_type'] == 3) {
                    $scheduleType = 'Monthly';
                    $scheduleOn = '';
                    $strMonth = strtotime($scheduleOn);
                    if($strMonth > strtotime(date('d-mY'))){
                        $scheduleOn = date("d-m-Y", strtotime("+1 month", $strMonth));
                    }
                }
                $schedulesList[$key]['devices'] = $AllDevices;

                $schedulesList[$key]['scheduleType'] = $scheduleType;
                $schedulesList[$key]['scheduleOn'] = $scheduleOn;
                $schedulesList[$key]['scheduleTime'] = $scheduleTime;
                $schedulesList[$key]['index'] = ++$start;
            }
        } catch (\Exception $exception) {
            Helpers::log('Schedule Update pagination exception');
            Helpers::log($exception);
            $branchList = [];
            $totalBranch = 0;
        }
        $data = [
            "aaData" => $schedulesList,
            "iTotalDisplayRecords" => $totalSchedules,
            "iTotalRecords" => $totalSchedules,
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
        $checkPermission = Permissions::checkActionPermission('add_schedule_update');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $companyList = [];
        $isCompany = Helpers::isCompany();
        if ($isCompany == false) {
            $companyList = Helpers::selectBoxCompanyList();
        }
        $userData = Auth::user();
        $userRole = $userData->role_id;

        $where = "status = '" . $this->actStatus . "' ";
        if ($isCompany) {
            $where .= " AND company_id = " . Helpers::companyId();
        }
        if ($userRole == Roles::$employee) {
            $where .= " AND (SELECT COUNT(G.id) AS count FROM groups AS G WHERE G.status = '" . $this->actStatus . "' AND G.deleted_at IS NULL AND G.id = '" . $userData->group_id . "' AND FIND_IN_SET(device.id, G.device_ids ) ) > 0 ";
        }

        $sensorList = Device::whereRaw($where)
            ->select('*')
            ->orderBy('id', 'desc')
            ->get()->toArray();

        return view('backend.scheduleupdate.create', compact('companyList', 'isCompany', 'sensorList'));
    }

    public function getCompanySensor($company_id)
    {
        $deviceList = Helpers::selectBoxDeviceListByCompany($company_id);
        return response()->json(["status" => 200, "message" => "success", "data" => $deviceList]);
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

            $sensor_id = $request->sensor_id;
            $su_type = trim($request->su_type);
            $su_week_day = trim($request->su_week_day);
            //$su_month_date = trim($request->su_month_date);
            $su_time = trim($request->su_time);
            $su_email = trim($request->su_email);

            $createData = [
                "su_uuid" => Helpers::getUuid(),
                "su_company_id" => $companyId,
                "su_devices" => $sensor_id,
                "su_type" => $su_type,
                "su_week_day" => $su_week_day,
                "su_time" => $su_time,
                "su_email" => $su_email,
                "su_status" => 1,
                "su_created_date" => Carbon::now(),
                "su_created_by" => Auth::user()->id,
                "su_updated_date" => Carbon::now()
            ];
            ScheduleUpdate::create($createData);
            DB::commit();
            return response()->json(["status" => 200, "message" => "Schedule has been successfully created", "redirect" => route('admin.schedule.index')]);

        } catch (\Exception $exception) {
            Helpers::log('schedule create : exception');
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

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $checkPermission = Permissions::checkActionPermission('edit_schedule_update');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $scheduleData = ScheduleUpdate::where('su_uuid', $id)->first();
        if (!empty($scheduleData)) {

            $companyList = [];
            $isCompany = Helpers::isCompany();
            if ($isCompany == false) {
                $companyList = Helpers::selectBoxCompanyList();
            }
            $userData = Auth::user();
            $userRole = $userData->role_id;

            $where = "status = '" . $this->actStatus . "' AND company_id = '" . $scheduleData->su_company_id . "' ";
            $sensorList = Device::whereRaw($where)
                ->select('*')
                ->orderBy('id', 'desc')
                ->get()->toArray();


            return view('backend.scheduleupdate.edit', compact('scheduleData', 'isCompany', 'companyList', 'sensorList'));
        } else {
            return redirect()->route('backend.scheduleupdate.index')->with('error', 'Ooops...Something went wrong. Please try again.');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $scheduleData = ScheduleUpdate::where('su_uuid', $id)->select('su_id', 'su_company_id')->first();
            if (!empty($scheduleData)) {

                $companyId = 0;
                $isCompany = Helpers::isCompany();
                if ($isCompany) {
                    $companyId = Helpers::companyId();
                } else {
                    $companyId = $request->company_id;
                }

                $sensor_id = $request->sensor_id;
                $su_type = trim($request->su_type);
                $su_week_day = trim($request->su_week_day);
                //$su_month_date = trim($request->su_month_date);
                $su_time = trim($request->su_time);
                $su_email = trim($request->su_email);

                $updateData = [
                    "su_company_id" => $companyId,
                    "su_devices" => $sensor_id,
                    "su_type" => $su_type,
                    "su_week_day" => $su_week_day,
                    "su_time" => $su_time,
                    "su_email" => $su_email,
                    "su_status" => 1,
                    "su_updated_by" => Auth::user()->id,
                    "su_updated_date" => Carbon::now()
                ];
                ScheduleUpdate::where('su_uuid', $id)->update($updateData);
                DB::commit();
                return response()->json(["status" => 200, "message" => "Schedule has been successfully updated.", "redirect" => route('admin.schedule.index')]);

            } else {
                return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
            }
        } catch (\Exception $exception) {
            Helpers::log('Schedule update : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($su_uuid){
        $checkPermission = Permissions::checkActionPermission('delete_schedule_update');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        return view('backend.scheduleupdate.delete', compact('su_uuid'));
    }
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $scheduleData = ScheduleUpdate::where('su_uuid', $id)->select('su_id')->first();
            if (!empty($scheduleData)) {
                $suId = $scheduleData->su_id;
                $data = [
                    'su_status' => 0,
                    'su_updated_date' => Carbon::now(),
                    'su_updated_by' => Auth::user()->id
                ];
                ScheduleUpdate::where('su_id', $suId)->update($data);

                DB::commit();
                return response()->json(["status" => 200, "message" => "Scheduled report has been successfully deleted.", "redirect" => route('admin.schedule.index')]);
            } else {
                return response()->json(['status' => 404, 'message' => 'Ooops...Something went wrong! Please try again.']);
            }
        } catch (\Exception $exception) {
            Helpers::log('schedule report delete : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }
}
