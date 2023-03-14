<?php

namespace App\Http\Controllers\Admin;

use App\Models\Helpers;
use App\Models\Humidity;
use App\Models\Permissions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HumidityController extends Controller
{
    protected $actStatus = '';
    protected $delStatus = '';

    /**
     * HumidityController constructor.
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
        $checkPermission = Permissions::checkActionPermission('view_humidity');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $isCompany = Helpers::isCompany();

        return view('backend.humidity.index', compact('isCompany'));
    }

    /**
     * paginate for humidity
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

            $totalData = Humidity::where($where)->count();

            $dataList = Humidity::where($where)
                ->select(
                    'uuid', 'name', 'warning_low_humidity_threshold', 'warning_high_humidity_threshold',
                    'low_humidity_threshold', 'high_humidity_threshold', 'dramatic_changes_value',
                    'effective_start_date', 'effective_end_date', 'effective_start_time', 'effective_end_time',
                    DB::raw("(SELECT first_name FROM users AS U WHERE U.id = humidity.company_id) AS company_name"),
                    DB::raw("(SELECT time_zone FROM users AS U WHERE U.id = humidity.company_id) AS company_time_zone"),
                    DB::raw("(SELECT device_name FROM device WHERE device.id = humidity.device_id) AS device_name")
                )
                ->orderBy('id', 'desc')
                ->limit($limit)->offset($start)
                ->get()->toArray();

            foreach ($dataList as $key => $value) {
                $dataList[$key]['index'] = ++$start;

                $event = "L W : " . $value['warning_low_humidity_threshold'] . "% <br>";
                $event .= "H W : " . $value['warning_high_humidity_threshold'] . "% <br>";
                $event .= "L T : " . $value['low_humidity_threshold'] . "% <br>";
                $event .= "H T : " . $value['high_humidity_threshold'] . "% <br>";
                $event .= "C : " . $value['dramatic_changes_value'] . "%";
                $dataList[$key]['event'] = $event;

                $timezone = new \DateTimeZone($value['company_time_zone']);
                $startDate = new \DateTime($value['effective_start_date'], $timezone);
                $endDate = new \DateTime($value['effective_end_date'], $timezone);

                $startTime = new \DateTime($value['effective_start_time'], $timezone);
                $endTime = new \DateTime($value['effective_end_time'], $timezone);

                $dataList[$key]['effective_date'] = $startDate->format('d-m-Y') . " - " . $endDate->format('d-m-Y');
                $dataList[$key]['effective_time'] = $startTime->format('H:i') . " - " . $endTime->format('H:i');
            }
        } catch (\Exception $exception) {
            Helpers::log('humidity pagination exception');
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
        $checkPermission = Permissions::checkActionPermission('add_humidity');
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

        $dayList = config('constants.day_list');
        return view('backend.humidity.create', compact('isCompany', 'companyList', 'deviceList', 'dayList'));
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
            $name = $request->name;
            $deviceId = $request->device_id;

            $checkName = Humidity::where('name', $name)->where('company_id', $companyId)->count();
            $checkDevice = Humidity::where('device_id', $deviceId)->where('company_id', $companyId)->count();
            if ($checkName > 0) {
                return response()->json(["status" => 409, "message" => "This humidity name already exist."]);
            } elseif ($checkDevice > 0) {
                return response()->json(["status" => 409, "message" => "This sensor already exist."]);
            } else {

                $dateEnable = 0;
                if ($request->has('effective_date_enable')) {
                    $dateEnable = 1;
                }

                $repeat = '';
                if ($request->has('repeat')) {
                    $repeat = implode(',', $request->repeat);
                }

                $timeEnable = 0;
                if ($request->has('effective_time_enable')) {
                    $timeEnable = 1;
                }

                $createData = [
                    "uuid" => Helpers::getUuid(),
                    "company_id" => $companyId,
                    "group_id" => 0,
                    "device_id" => $deviceId,
                    "name" => $name,
                    "warning_low_humidity_threshold" => $request->warning_low_humidity_threshold,
                    "warning_high_humidity_threshold" => $request->warning_high_humidity_threshold,
                    "low_humidity_threshold" => $request->low_humidity_threshold,
                    "high_humidity_threshold" => $request->high_humidity_threshold,
                    "dramatic_changes_value" => $request->dramatic_changes_value,
                    "effective_start_date" => $request->effective_start_date,
                    "effective_end_date" => $request->effective_end_date,
                    "effective_date_enable" => $dateEnable,
                    "repeat" => $repeat,
                    "effective_start_time" => $request->effective_start_time,
                    "effective_end_time" => $request->effective_end_time,
                    "effective_time_enable" => $timeEnable,
                    "status" => $this->actStatus,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ];
                Humidity::create($createData);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This humidity has been successfully created.", "redirect" => route('admin.humidity.index')]);
            }
        } catch (\Exception $exception) {
            Helpers::log('Humidity create : exception');
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
        $checkPermission = Permissions::checkActionPermission('edit_humidity');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $humidityData = Humidity::where('uuid', $uuid)->first();
        if (!empty($humidityData)) {

            $isCompany = Helpers::isCompany();
            $companyList = [];
            $companyId = $humidityData->company_id;
            $humidityData->company_name = Helpers::companyName($companyId);
            $deviceList = Helpers::selectBoxDeviceListByCompany($companyId);

            $humidityData->repeat_day = explode(',', $humidityData->repeat);

            $dayList = config('constants.day_list');
            return view('backend.humidity.edit', compact('humidityData', 'isCompany', 'companyList', 'deviceList', 'dayList'));
        } else {
            return redirect()->route('admin.humidity.index')->with('error', 'Ooops...Something went wrong. Please try again.');
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
            $humidityData = Humidity::where('uuid', $uuid)->select('id', 'company_id')->first();
            if (!empty($humidityData)) {
                $companyId = $humidityData->company_id;
                $humidityId = $humidityData->id;
                $name = $request->name;
                $deviceId = $request->device_id;

                $checkName = Humidity::where('name', $name)
                    ->where('company_id', $companyId)
                    ->where('id', '!=', $humidityId)
                    ->count();

                $checkDevice = Humidity::where('device_id', $deviceId)
                    ->where('company_id', $companyId)
                    ->where('id', '!=', $humidityId)
                    ->count();

                if ($checkName > 0) {
                    return response()->json(["status" => 409, "message" => "This humidity name already exist."]);
                } elseif ($checkDevice > 0) {
                    return response()->json(["status" => 409, "message" => "This sensor already exist."]);
                } else {

                    $dateEnable = 0;
                    if ($request->has('effective_date_enable')) {
                        $dateEnable = 1;
                    }

                    $repeat = '';
                    if ($request->has('repeat')) {
                        $repeat = implode(',', $request->repeat);
                    }

                    $timeEnable = 0;
                    if ($request->has('effective_time_enable')) {
                        $timeEnable = 1;
                    }

                    $updateData = [
                        "device_id" => $deviceId,
                        "name" => $name,
                        "warning_low_humidity_threshold" => $request->warning_low_humidity_threshold,
                        "warning_high_humidity_threshold" => $request->warning_high_humidity_threshold,
                        "low_humidity_threshold" => $request->low_humidity_threshold,
                        "high_humidity_threshold" => $request->high_humidity_threshold,
                        "dramatic_changes_value" => $request->dramatic_changes_value,
                        "effective_start_date" => $request->effective_start_date,
                        "effective_end_date" => $request->effective_end_date,
                        "effective_date_enable" => $dateEnable,
                        "repeat" => $repeat,
                        "effective_start_time" => $request->effective_start_time,
                        "effective_end_time" => $request->effective_end_time,
                        "effective_time_enable" => $timeEnable,
                        "status" => $this->actStatus,
                        "updated_at" => Carbon::now(),
                    ];
                    Humidity::where('id', $humidityId)->update($updateData);

                    DB::commit();
                    return response()->json(["status" => 200, "message" => "This humidity has been successfully updated.", "redirect" => route('admin.humidity.index')]);
                }
            } else {
                return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
            }
        } catch (\Exception $exception) {
            Helpers::log('Humidity update : exception');
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
        $checkPermission = Permissions::checkActionPermission('delete_humidity');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        return view('backend.humidity.delete', compact('uuid'));
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
            $humidityData = Humidity::where('uuid', $uuid)->select('id')->first();
            if (!empty($humidityData)) {
                $humidityId = $humidityData->id;
                $data = [
                    'status' => $this->delStatus,
                    'deleted_at' => Carbon::now()
                ];
                Humidity::where('id', $humidityId)->update($data);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This humidity has been successfully deleted.", "redirect" => route('admin.humidity.index')]);
            } else {
                return response()->json(['status' => 404, 'message' => 'Ooops...Something went wrong! Please try again.']);
            }
        } catch (\Exception $exception) {
            Helpers::log('humidity delete : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }
}
