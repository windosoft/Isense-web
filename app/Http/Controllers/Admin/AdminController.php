<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branches;
use App\Models\Device;
use App\Models\DeviceDashboard;
use App\Models\DeviceTemperatureLog;
use App\Models\Groups;
use App\Models\Helpers;
use App\Models\Humidity;
use App\Models\Notification;
use App\Models\NotificationDetail;
use App\Models\Offline;
use App\Models\PasswordResets;
use App\Models\Roles;
use App\Models\ScheduleUpdateLog;
use App\Models\Temperature;
use App\Models\Terminals;
use App\Models\Voltage;
use App\User;
use Carbon\Carbon;
use http\Cookie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use PDF;

class AdminController extends Controller
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
        $this->company = Roles::$company;
        $this->employee = Roles::$employee;
    }

    /**
     * Dashboard view
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function index(Request $request)
    {

        $userData = Auth::user();
        $userId = $userData->id;
        $roleId = $userData->role_id;
        $companyRole = Roles::$company;
        $employeeRole = Roles::$employee;
        /*if ($roleId == $employeeRole) {

            $toDay = Carbon::today();
            $companyId = $userData->company_id;
            $sWhere = "company_id = $companyId";
            $sWhere .= " AND (SELECT COUNT(G.id) AS count FROM groups AS G WHERE G.status = '" . $this->actStatus . "' AND G.deleted_at IS NULL AND G.id = '" . $userData->group_id . "' AND FIND_IN_SET(device_dashboard.device_id, G.device_ids ) ) > 0 ";
            $totalWarning = DeviceDashboard::whereRaw($sWhere)
                ->whereDate('updated_at', $toDay)
                ->where('temperature_alert', Notification::WARNING)
                ->count();
            $totalDanger = DeviceDashboard::whereRaw($sWhere)
                ->whereDate('updated_at', $toDay)
                ->where('temperature_alert', Notification::DANGER)
                ->count();
            $totalSuccess = DeviceDashboard::whereRaw($sWhere)
                ->whereDate('updated_at', $toDay)
                ->where('temperature_alert', Notification::SUCCESS)
                ->count();
            $totalDisable = DeviceDashboard::whereRaw($sWhere)
                ->whereDate('updated_at', $toDay)
                ->where('temperature_alert', Notification::DISABLE)
                ->count();

            $where = "device.status = '" . $this->actStatus . "' ";
            $where .= " AND device.company_id = " . Helpers::companyId();
            $where .= " AND (SELECT COUNT(G.id) AS count FROM groups AS G WHERE G.status = '" . $this->actStatus . "' AND G.deleted_at IS NULL AND G.id = '" . $userData->group_id . "' AND FIND_IN_SET(device.id, G.device_ids ) ) > 0 ";
            $sensorList = Device::join('device_dashboard AS DD', 'DD.device_id', '=', 'device.id')
                ->whereRaw($where)
                ->select(
                    'device.id', 'device.device_name', 'device.created_at', 'DD.temperature', 'DD.humidity',
                    'DD.temperature_color', 'DD.temperature_alert',
                    DB::raw("(SELECT time_zone FROM users WHERE users.id = device.company_id) AS company_time_zone")
                )
                ->orderBy('device.id', 'desc')
                ->get()->toArray();
            foreach ($sensorList as $key => $value) {
                $timezone = new \DateTimeZone($value['company_time_zone']);
                $createdAt = new \DateTime($value['created_at'], $timezone);
                $sensorList[$key]['created_at'] = $createdAt->format('d-m-Y / H:i');

                $statusName = '';
                $tempAlert = $value['temperature_alert'];
                if ($tempAlert == Notification::SUCCESS) {
                    $statusName = Notification::SUCCESS_NAME;
                } elseif ($tempAlert == Notification::DANGER) {
                    $statusName = Notification::DANGER_NAME;
                } elseif ($tempAlert == Notification::WARNING) {
                    $statusName = Notification::WARNING_NAME;
                } elseif ($tempAlert == Notification::DISABLE) {
                    $statusName = Notification::DISABLE_NAME;
                }
                $sensorList[$key]['status_name'] = $statusName;
            }

            if ($totalDisable == 0 && $totalSuccess == 0 && $totalWarning == 0 && $totalDanger == 0) {
                $totalDisable = count($sensorList);
            }

            $notificationList = NotificationDetail::join('notification AS N', 'N.uuid', '=', 'notification_detail.notification_uuid')
                ->where('notification_detail.user_id', $userId)
                ->whereDate('N.current_date_time', $toDay)
                ->select('N.notification')
                ->orderby('notification_detail.created_at', 'DESC')
                ->limit(5)
                ->get()->toArray();

            $colorList = [
                [
                    'color' => Notification::DISABLECOLOR,
                    'name' => Notification::DISABLE_NAME,
                    "count" => $totalDisable
                ],
                [
                    'color' => Notification::SUCCESSCOLOR,
                    'name' => Notification::SUCCESS_NAME,
                    "count" => $totalSuccess
                ],
                [
                    'color' => Notification::WARNINGCOLOR,
                    'name' => Notification::WARNING_NAME,
                    "count" => $totalWarning
                ],
                [
                    'color' => Notification::DANGERCOLOR,
                    'name' => Notification::DANGER_NAME,
                    "count" => $totalDanger
                ]
            ];

            $dashboard = [
                'full_name' => $userData->fullname,
                'sensor_list' => $sensorList,
                'notification_list' => $notificationList,
                'color_list' => $colorList,
            ];
            return view('backend.employee.dashboard', compact('dashboard'));
        } else*/
        if ($roleId == $companyRole || $roleId == $employeeRole) {
            /* Company or employee dashboard */
            $companyId = Helpers::companyId();
            $deviceList = Helpers::selectBoxDeviceListByCompany($companyId);

            $notificationData = NotificationDetail::where('read_status', Helpers::UNREAD)
                ->where('user_id', $userId)
                ->select(DB::raw('COUNT(uuid) AS count'))
                ->first();

            $notificationCount = 0;
            if (!empty($notificationData)) {
                if (!empty($notificationData->count)) {
                    $notificationCount = $notificationData->count;
                }
            }
            if ($notificationCount > 99) {
                $notificationCount = '99+';
            }

            $dashboard = [
                'device_list' => $deviceList,
                'notification_count' => $notificationCount
            ];
            return view('backend.company.dashboard', compact('dashboard'));
        } else {

            /* Admin Dashboard */
            $companyList = User::where('role_id', $companyRole)
                ->where('status', $this->actStatus)
                ->select(
                    'id', 'uuid', 'first_name', 'email',
                    DB::raw("(SELECT COUNT(id) AS count FROM branches WHERE branches.company_id = users.id AND branches.status = '" . $this->actStatus . "' AND branches.deleted_at IS NULL) AS branches"),
                    DB::raw("(SELECT COUNT(id) AS count FROM terminals WHERE terminals.company_id = users.id AND terminals.status = '" . $this->actStatus . "' AND terminals.deleted_at IS NULL) AS terminals"),
                    DB::raw("(SELECT COUNT(id) AS count FROM device WHERE device.company_id = users.id AND device.status = '" . $this->actStatus . "' AND device.deleted_at IS NULL) AS devices")
                )
                ->orderBy('id', 'desc')
                ->limit(5)->get()->toArray();

            $deviceList = Device::where('status', $this->actStatus)
                ->select(
                    'id', 'uuid', 'device_name', 'created_at',
                    DB::raw("(SELECT first_name FROM users WHERE users.id = device.company_id) AS company_name"),
                    DB::raw("(SELECT time_zone FROM users WHERE users.id = device.company_id) AS company_time_zone")
                )
                ->orderBy('id', 'desc')
                ->limit(5)->get()->toArray();

            foreach ($deviceList as $key => $value) {
                $timezone = new \DateTimeZone($value['company_time_zone']);
                $myDateTime = new \DateTime($value['created_at'], $timezone);
                $deviceList[$key]['created_at'] = $myDateTime->format('d-m-Y / H:i');
            }

            $dashboard = [
                'company' => User::where('role_id', $companyRole)->count(),
                'branch' => Branches::count(),
                'gateway' => Terminals::count(),
                'sensor' => Device::count(),
                'group' => Groups::count(),
                'employee' => User::where('role_id', $employeeRole)->count(),
                'temperatures' => Temperature::count(),
                'humidity' => Humidity::count(),
                'voltage' => Voltage::count(),
                'offline' => Offline::count(),
                'company_list' => $companyList,
                'sensor_list' => $deviceList,
            ];

            return view('backend.dashboard', compact('dashboard'));
        }
    }

    public function masterDashboard(Request $request)
    {
        $userData = Auth::user();
        $userId = $userData->id;
        $roleId = $userData->role_id;
        $companyRole = Roles::$company;
        $employeeRole = Roles::$employee;
        $companyId = Helpers::companyId();
        if($roleId > 1){
            $where = 'device_dashboard.company_id = ' . $companyId;
            if ($userData->role_id == Roles::$employee) {
                $groupId = $userData->group_id;
                $where .= " AND FIND_IN_SET(device_dashboard.device_id,(SELECT device_ids FROM groups WHERE id = $groupId))";
            }

            $deviceDashboardList = DeviceDashboard::whereRaw($where)
                ->select(
                    'id', 'device_id', 'temperature_alert', 'humidity_alert', 'updated_at',
                    DB::raw("(SELECT data_interval FROM device WHERE device.id = device_dashboard.device_id) AS data_interval"),
                    DB::raw("(SELECT device_name FROM device WHERE device.id = device_dashboard.device_id) AS device_name")
                )
                ->get()->toArray();
            $totalSuccess = 0;
            $totalDanger = 0;
            $totalWarning = 0;
            $totalDisable = 0;
            foreach ($deviceDashboardList as $deviceData) {
                if ($deviceData['temperature_alert'] = Notification::DISABLE) {
                    $totalDisable++;
                }
                if ($deviceData['temperature_alert'] = Notification::WARNING) {
                    $totalWarning++;
                }
                if ($deviceData['temperature_alert'] = Notification::SUCCESS) {
                    $totalSuccess++;
                }
                if ($deviceData['temperature_alert'] = Notification::DANGER) {
                    $totalDanger++;
                }
            }
            $totalSensor = count($deviceDashboardList);
            $totalSensor = sprintf("%02d", $totalSensor);
            return view('backend.company.dashboard2', compact('deviceDashboardList', 'totalSuccess', 'totalDanger', 'totalWarning', 'totalDisable', 'totalSensor'));
        }
        else{

                $userData = Auth::user();
                $userId = $userData->id;
                $roleId = $userData->role_id;
                $companyRole = Roles::$company;
                $employeeRole = Roles::$employee;
                if ($roleId == $companyRole || $roleId == $employeeRole) {
                    /* Company or employee dashboard */
                    $companyId = Helpers::companyId();
                    $deviceList = Helpers::selectBoxDeviceListByCompany($companyId);

                    $notificationData = NotificationDetail::where('read_status', Helpers::UNREAD)
                        ->where('user_id', $userId)
                        ->select(DB::raw('COUNT(uuid) AS count'))
                        ->first();

                    $notificationCount = 0;
                    if (!empty($notificationData)) {
                        if (!empty($notificationData->count)) {
                            $notificationCount = $notificationData->count;
                        }
                    }
                    if ($notificationCount > 99) {
                        $notificationCount = '99+';
                    }

                    $dashboard = [
                        'device_list' => $deviceList,
                        'notification_count' => $notificationCount
                    ];
                    return view('backend.company.dashboard', compact('dashboard'));
                } else {

                    /* Admin Dashboard */
                    $companyList = User::where('role_id', $companyRole)
                        ->where('status', $this->actStatus)
                        ->select(
                            'id', 'uuid', 'first_name', 'email',
                            DB::raw("(SELECT COUNT(id) AS count FROM branches WHERE branches.company_id = users.id AND branches.status = '" . $this->actStatus . "' AND branches.deleted_at IS NULL) AS branches"),
                            DB::raw("(SELECT COUNT(id) AS count FROM terminals WHERE terminals.company_id = users.id AND terminals.status = '" . $this->actStatus . "' AND terminals.deleted_at IS NULL) AS terminals"),
                            DB::raw("(SELECT COUNT(id) AS count FROM device WHERE device.company_id = users.id AND device.status = '" . $this->actStatus . "' AND device.deleted_at IS NULL) AS devices")
                        )
                        ->orderBy('id', 'desc')
                        ->limit(5)->get()->toArray();

                    $deviceList = Device::where('status', $this->actStatus)
                        ->select(
                            'id', 'uuid', 'device_name', 'created_at',
                            DB::raw("(SELECT first_name FROM users WHERE users.id = device.company_id) AS company_name"),
                            DB::raw("(SELECT time_zone FROM users WHERE users.id = device.company_id) AS company_time_zone")
                        )
                        ->orderBy('id', 'desc')
                        ->limit(5)->get()->toArray();

                    foreach ($deviceList as $key => $value) {
                        $timezone = new \DateTimeZone($value['company_time_zone']);
                        $myDateTime = new \DateTime($value['created_at'], $timezone);
                        $deviceList[$key]['created_at'] = $myDateTime->format('d-m-Y / H:i');
                    }

                    $dashboard = [
                        'company' => User::where('role_id', $companyRole)->count(),
                        'branch' => Branches::count(),
                        'gateway' => Terminals::count(),
                        'sensor' => Device::count(),
                        'group' => Groups::count(),
                        'employee' => User::where('role_id', $employeeRole)->count(),
                        'temperatures' => Temperature::count(),
                        'humidity' => Humidity::count(),
                        'voltage' => Voltage::count(),
                        'offline' => Offline::count(),
                        'company_list' => $companyList,
                        'sensor_list' => $deviceList,
                    ];

                    return view('backend.dashboard', compact('dashboard'));
                }
        }
        
    }

    public function getDoughnotDetails(Request $request)
    {
        $userData = Auth::user();
        $userId = $userData->id;
        $roleId = $userData->role_id;
        $companyRole = Roles::$company;
        $employeeRole = Roles::$employee;
        $companyId = Helpers::companyId();

        $where = 'device_dashboard.company_id = ' . $companyId;
        if ($userData->role_id == Roles::$employee) {
            $groupId = $userData->group_id;
            $where .= " AND FIND_IN_SET(device_dashboard.device_id,(SELECT device_ids FROM groups WHERE id = $groupId))";
        }

        $deviceDashboardList = DeviceDashboard::whereRaw($where)
            ->select(
                'id', 'temperature_alert', 'humidity_alert', 'updated_at',
                DB::raw("(SELECT data_interval FROM device WHERE device.id = device_dashboard.device_id) AS data_interval")
            )
            ->get()->toArray();
        $totalSuccess = 0;
        $totalDanger = 0;
        $totalWarning = 0;
        $totalDisable = 0;
        foreach ($deviceDashboardList as $deviceData) {
            if ($deviceData['temperature_alert'] == Notification::DISABLE) {
                $totalDisable++;
            }
            if ($deviceData['temperature_alert'] == Notification::WARNING) {
                $totalWarning++;
            }
            if ($deviceData['temperature_alert'] == Notification::SUCCESS) {
                $totalSuccess++;
            }
            if ($deviceData['temperature_alert'] == Notification::DANGER) {
                $totalDanger++;
            }
        }
        return response()->json(["status" => 200, "totalDisable" => $totalDisable, 'totalWarning' => $totalWarning, 'totalSuccess' => $totalSuccess, 'totalDanger' => $totalDanger]);
    }

    function getWeek($week, $year)
    {
        $dto = new \DateTime();
        $result['start'] = $dto->setISODate($year, $week, 0)->format('Y-m-d');
        $result['end'] = $dto->setISODate($year, $week, 6)->format('Y-m-d');
        return $result;
    }

    public function getLineChartData(Request $request)
    {
        $inputs = $request->all();
        if ($inputs['getSelectedDate'] == 'today') {
            $dateStartFrom = date('Y-m-d') . ' 00:00:00';
            $dateStartTo = date('Y-m-d') . ' 23:59:59';
            //$dateStartFrom = '2020-08-29 00:00:00';
            //$dateStartTo = '2020-08-29 23:59:59';

            $query = "SELECT date(`servertime`) dateDay, 2*floor(date_format(`servertime`,'%H')/2) hoursOn, avg(`temperature`) as temperature_avg, avg(`humidity`) as humidity_avg FROM `device_temperature_log` WHERE `device_id` = '" . $inputs['getSelectedMachine'] . "' AND (`servertime` BETWEEN '" . $dateStartFrom . "' AND '" . $dateStartTo . "') GROUP BY DATE(`servertime`), 2 * FLOOR(DATE_FORMAT(`servertime`, '%H') / 2)";
            $deviceDashboard = DB::select($query);
            $dataLabels = [
                '02 AM', '04 AM', '06 AM', '08 AM', '10 AM', '11 AM', '12 AM ', '02 PM', '04 PM', '06 PM', '08 PM ', '10 PM', '12 PM'
            ];
            $data = [
                'labels' => $dataLabels,
                'dashboardData' => $deviceDashboard
            ];
            echo json_encode($data);
        } else if ($inputs['getSelectedDate'] == 'week') {
            $dateStartFrom = strtotime('monday this week');
            $dateStartTo = strtotime('sunday this week');

            //$dateStartFrom = '2020-08-01 00:00:00';
            //$dateStartTo = '2020-08-07 23:59:59';
            $query = "SELECT DAYNAME(`servertime`) as DAY,servertime, AVG(`temperature`) AS temperature_avg, AVG(`humidity`) AS humidity_avg FROM device_temperature_log WHERE `device_id` = '" . $inputs['getSelectedMachine'] . "' AND(`servertime` BETWEEN '" . $dateStartFrom . "' AND '" . $dateStartTo . "') GROUP BY DAYNAME(`servertime`) ORDER BY servertime ASC";
            $deviceDashboard = DB::select($query);
            $dataLabels = [
                'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'
            ];
            $data = [
                'labels' => $dataLabels,
                'dashboardData' => $deviceDashboard
            ];
            echo json_encode($data);
        } else if ($inputs['getSelectedDate'] == 'month') {
            $monthStart = date('01-m-Y');
            $monthEnd = date('t');
            //$monthEnd = 31;
            $ListDates = [];
            //$ListDates[]=date('Y-m-01');
            for ($ml = 1; $ml < $monthEnd; $ml++) {
                $checkMl = $ml + 4;
                if ($checkMl < $monthEnd) {
                    $whatsDay = sprintf('%02d', $ml);
                    $createDate = date('Y-m-') . $whatsDay;

                    $add5Days = date('Y-m-d', strtotime($createDate . ' + 4 days'));
                    $ListDates[] = [
                        'from_date' => $createDate . ' 00:00:00',
                        'to_date' => $add5Days . ' 23:59:59',
                    ];
                    //$ListDates[] = $add5Days;
                    $ml += +4;
                }
            }
            if ($ListDates[count($ListDates) - 1]['to_date'] != date('Y-m-t')) {
                $ListDates[] = [
                    'from_date' => date('Y-m-d', strtotime($ListDates[count($ListDates) - 1]['to_date'])) . ' 00:00:00',
                    'to_date' => date('Y-m-t') . ' 23:59:59',
                ];
            }

            $finalTemmpHumidity = [];
            foreach ($ListDates as $listDateItems) {
                $query = "SELECT AVG(`temperature`) AS temperature_avg, AVG(`humidity`) AS humidity_avg FROM device_temperature_log WHERE `device_id` = '" . $inputs['getSelectedMachine'] . "' AND(`servertime` BETWEEN '" . $listDateItems['from_date'] . "' AND '" . $listDateItems['to_date'] . "') ";
                $deviceDashboard = DB::select($query)[0];
                if ($deviceDashboard->temperature_avg == '') {
                    $deviceDashboard->temperature_avg = 0.00;
                };
                if ($deviceDashboard->humidity_avg == '') {
                    $deviceDashboard->humidity_avg = 0.00;
                };
                $finalTemmpHumidity[] = [
                    'date' => $listDateItems['from_date'],
                    'temperature_avg' => $deviceDashboard->temperature_avg,
                    'humidity_avg' => $deviceDashboard->humidity_avg,
                ];
            }
            $labels = [];
            foreach ($finalTemmpHumidity as $itemTempHum) {
                $labels[] = date('d-M', strtotime($itemTempHum['date']));
            }
            $data = [
                'labels' => $labels,
                'dashboardData' => $finalTemmpHumidity
            ];
            echo json_encode($data);
        } else if ($inputs['getSelectedDate'] == 'year') {
            $listDates = [];
            for ($yi = 1; $yi <= 12; $yi++) {
                $firstDate = date('Y-' . $yi . '-01');
                $lastDate = date('Y-m-t', strtotime($firstDate));
                $listDates[] = [
                    'from_date' => $firstDate,
                    'to_date' => $lastDate
                ];
            }

            $finalTemmpHumidity = [];
            foreach ($listDates as $listDateItems) {
                $query = "SELECT AVG(`temperature`) AS temperature_avg, AVG(`humidity`) AS humidity_avg FROM device_temperature_log WHERE `device_id` = '" . $inputs['getSelectedMachine'] . "' AND(`servertime` BETWEEN '" . $listDateItems['from_date'] . "' AND '" . $listDateItems['to_date'] . "') ";
                $deviceDashboard = DB::select($query)[0];
                if ($deviceDashboard->temperature_avg == '') {
                    $deviceDashboard->temperature_avg = 0.00;
                };
                if ($deviceDashboard->humidity_avg == '') {
                    $deviceDashboard->humidity_avg = 0.00;
                };
                $finalTemmpHumidity[] = [
                    'date' => $listDateItems['from_date'],
                    'temperature_avg' => $deviceDashboard->temperature_avg,
                    'humidity_avg' => $deviceDashboard->humidity_avg,
                ];
            }
            $labels = [];
            foreach ($finalTemmpHumidity as $itemTempHum) {
                $labels[] = date('M', strtotime($itemTempHum['date']));
            }
            $data = [
                'labels' => $labels,
                'dashboardData' => $finalTemmpHumidity
            ];
            echo json_encode($data);


        }
    }

    public function getLiveHistory(Request $request)
    {
        $inputs = $request->all();
        $dateOfLive = $inputs['dataDate'];
        if ($dateOfLive == '') {
            $dateOfLive = date('Y-m-d');
        } else {
            $dateOfLive = date('Y-m-d', strtotime($dateOfLive));
        }

        $userData = Auth::user();
        $userId = $userData->id;
        $roleId = $userData->role_id;
        $companyRole = Roles::$company;
        $employeeRole = Roles::$employee;
        $companyId = Helpers::companyId();

        $where = 'device_dashboard.company_id = ' . $companyId;
        if ($userData->role_id == Roles::$employee) {
            $groupId = $userData->group_id;
            $where .= " AND FIND_IN_SET(device_dashboard.device_id,(SELECT device_ids FROM groups WHERE id = $groupId))";
        }

        $deviceDashboardList = DeviceDashboard::whereRaw($where)
            ->select(
                'id', 'device_id', 'temperature_alert', 'humidity_alert', 'updated_at',
                DB::raw("(SELECT data_interval FROM device WHERE device.id = device_dashboard.device_id) AS data_interval"),
                DB::raw("(SELECT device_name FROM device WHERE device.id = device_dashboard.device_id) AS device_name")
            )
            ->get()->toArray();

        $deviceList = [];
        foreach ($deviceDashboardList as $deviceDashboardListData) {
            $deviceList[] = $deviceDashboardListData['device_id'];
        }
        if(!empty($deviceList)){
            $query = "SELECT `device_temperature_log`.*, `DV`.`device_name`, `T`.`low_temp_warning`, `T`.`high_temp_warning`, `T`.`low_temp_threshold`, `T`.`high_temp_threshold`, `H`.`warning_low_humidity_threshold`, `H`.`warning_high_humidity_threshold`, `H`.`low_humidity_threshold`, `H`.`high_humidity_threshold`,(SELECT time_zone FROM users AS U WHERE U.id =(SELECT company_id FROM device WHERE device.id = device_temperature_log.device_id)) AS company_time_zone FROM `device_temperature_log` JOIN `device` AS `DV` ON `DV`.`id` = `device_temperature_log`.`device_id` LEFT JOIN `temperature` AS `T` ON `T`.`device_id` = `device_temperature_log`.`device_id` AND `T`.`status` = 'A' LEFT JOIN `humidity` AS `H` ON `H`.`device_id` = `device_temperature_log`.`device_id` AND `H`.`status` = 'A' WHERE `DV`.`status` = 'A'  AND  `DV`.`id` IN (" . implode(',', $deviceList) . ") AND DATE(device_temperature_log.updated_at) = '" . $dateOfLive . "' AND `device_temperature_log`.`deleted_at` IS NULL ORDER BY `device_temperature_log`.`id` DESC LIMIT 100";
            
        }
        else{
            $query = "SELECT `device_temperature_log`.*, `DV`.`device_name`, `T`.`low_temp_warning`, `T`.`high_temp_warning`, `T`.`low_temp_threshold`, `T`.`high_temp_threshold`, `H`.`warning_low_humidity_threshold`, `H`.`warning_high_humidity_threshold`, `H`.`low_humidity_threshold`, `H`.`high_humidity_threshold`,(SELECT time_zone FROM users AS U WHERE U.id =(SELECT company_id FROM device WHERE device.id = device_temperature_log.device_id)) AS company_time_zone FROM `device_temperature_log` JOIN `device` AS `DV` ON `DV`.`id` = `device_temperature_log`.`device_id` LEFT JOIN `temperature` AS `T` ON `T`.`device_id` = `device_temperature_log`.`device_id` AND `T`.`status` = 'A' LEFT JOIN `humidity` AS `H` ON `H`.`device_id` = `device_temperature_log`.`device_id` AND `H`.`status` = 'A' WHERE `DV`.`status` = 'A'  AND DATE(device_temperature_log.updated_at) = '" . $dateOfLive . "' AND `device_temperature_log`.`deleted_at` IS NULL ORDER BY `device_temperature_log`.`id` DESC LIMIT 100";
        
        }
        $deviceTempList = DB::select($query);
        foreach ($deviceTempList as $key => $value) {
            $timezone = new \DateTimeZone($value->company_time_zone);
            $serverTime = new \DateTime($value->servertime, $timezone);
            $createdAt = new \DateTime($value->created_at, $timezone);
            $updatedAt = new \DateTime($value->updated_at, $timezone);

            $deviceTempList[$key]->servertime = $serverTime->format('d-m-Y / H:i A');
            /*$deviceTempList[$key]['created_at'] = $createdAt->format('d-m-Y / H:i');*/
            $deviceTempList[$key]->created_at = $updatedAt->format('d-m-Y / H:i A');
            $deviceTempList[$key]->updated_at = $updatedAt->format('d-m-Y / H:i');

            $temperatureColor = Notification::DISABLECOLOR;
            $temperatureType = 'DISABLED';
            $temperature = $value->temperature;
            $lowTempWarning = $value->low_temp_warning;
            $highTempWarning = $value->high_temp_warning;
            $lowTempThreshold = $value->low_temp_threshold;
            $highTempThreshold = $value->high_temp_threshold;
            if (!is_null($lowTempWarning) && !is_null($highTempWarning) && !is_null($lowTempThreshold) && !is_null($highTempThreshold)) {
                if ($temperature >= $lowTempWarning && $temperature <= $highTempWarning) {
                    $temperatureColor = Notification::SUCCESSCOLOR;
                    $temperatureType = 'SUCCESS';
                } elseif (($temperature >= $lowTempThreshold && $temperature < $lowTempWarning) || ($temperature > $highTempWarning && $temperature <= $highTempThreshold)) {
                    $temperatureColor = Notification::WARNINGCOLOR;
                    $temperatureType = 'WARNING';
                } elseif ($temperature < $highTempWarning || $temperature > $highTempThreshold) {
                    $temperatureColor = Notification::DANGERCOLOR;
                    $temperatureType = 'DANGER';
                }
            }
            $deviceTempList[$key]->temperature_color = $temperatureColor;
            $deviceTempList[$key]->temperature_type = $temperatureType;

            $humidityColor = Notification::DISABLECOLOR;
            $humidityType = 'DISABLED';
            $humidity = $value->humidity;
            $humidityLowWarning = $value->warning_low_humidity_threshold;
            $humidityHighWarning = $value->warning_high_humidity_threshold;
            $humidityLowThreshold = $value->low_humidity_threshold;
            $humidityHighThreshold = $value->high_humidity_threshold;
            if (!is_null($humidityLowWarning) && !is_null($humidityHighWarning) && !is_null($humidityLowThreshold) && !is_null($humidityHighThreshold)) {
                if ($humidity >= $humidityLowWarning && $humidity <= $humidityHighWarning) {
                    $humidityColor = Notification::SUCCESSCOLOR;
                    $humidityType = 'SUCCESS';
                } elseif (($humidity >= $humidityLowThreshold && $humidity < $humidityLowWarning) || ($humidity > $humidityHighWarning && $humidity <= $humidityHighThreshold)) {
                    $humidityColor = Notification::WARNINGCOLOR;
                    $humidityType = 'WARNING';
                } elseif ($humidity < $humidityHighWarning || $humidity > $humidityHighThreshold) {
                    $humidityColor = Notification::DANGERCOLOR;
                    $humidityType = 'DANGER';
                }
            }
            $deviceTempList[$key]->humidity_color = $humidityColor;
            $deviceTempList[$key]->humidity_type = $humidityType;
        }

        $Table = '<table class="table" id="liveHistoryData"><thead><tr><th>Room Name</th><th>Date/Time</th><th>Temperature</th><th>Status</th></tr></thead><tbody>';
        foreach ($deviceTempList as $deviceTempListData) {
            $Table .= '<tr>';
            $Table .= '<td>' . $deviceTempListData->device_name . '</td>';
            $Table .= '<td>' . $deviceTempListData->servertime . '</td>';
            $temp = '<span class="liveTempBlock"><img src="' . asset('backend/images/temperatures-white.png') . '" class="img-responsive historyTempInfoBlockImg" alt=""><span class="liveTemp">' . $deviceTempListData->temperature . '</span></span>';
            $humidity = '<span class="liveHumidityBlock"><img src="' . asset('backend/images/humidity-white.png') . '" class="img-responsive humidityHistoryIcon" alt=""><span class="liveHumidity">' . $deviceTempListData->humidity . '</span></span>';
            $Table .= '<td>' . $temp . ' ' . $humidity . '</td>';
            $status = '';
            if ($deviceTempListData->temperature_type == 'DISABLED') {
                $status = '<span class="disabledSquare"></span>Inactive';
            } else if ($deviceTempListData->temperature_type == 'SUCCESS') {
                $status = '<span class="successSquare"></span>Normal';
            } else if ($deviceTempListData->temperature_type == 'WARNING') {
                $status = '<span class="warningSquare"></span>Caution';
            } else if ($deviceTempListData->temperature_type == 'DANGER') {
                $status = '<span class="dangerSquare"></span>Danger';
            }
            $Table .= '<td>' . $status . '</td>';
            $Table .= '</tr>';
        }
        $Table .= '</tbody></table>';
        $data = [
            'deviceDashboardLive' => $deviceTempList,
            'tableData' => $Table,
        ];


        echo json_encode($data);
    }

    public function getLiveNotification(Request $request)
    {
        $isCompany = Helpers::isCompany();
        $userData = Auth::user();
        $userId = $userData->id;
        $roleId = $userData->role_id;
        $where = "notification_detail.user_id IN (SELECT U1.id FROM users AS U1 WHERE U1.status = '" . $this->actStatus . "' AND  U1.role_id = '" . $this->company . "' AND U1.deleted_at IS NULL)";
        if (in_array($roleId, [$this->company, $this->employee])) {
            $where = "notification_detail.user_id = " . $userId;
        }

        $notificationList = NotificationDetail::join('notification AS N', 'N.uuid', '=', 'notification_detail.notification_uuid')
            ->whereRaw($where)
            ->select(
                'notification_detail.uuid', 'notification_detail.read_status', 'notification_detail.created_at',
                'N.notification', 'notification_for', 'N.temperature', 'N.humidity', 'N.alerttype',
                DB::raw("(SELECT CONCAT(first_name,' ',last_name) AS name FROM users AS U WHERE U.id = notification_detail.user_id) AS company_name"),
                DB::raw("(SELECT time_zone AS name FROM users AS U WHERE U.id = N.company_id) AS company_time_zone"),
                DB::raw("(SELECT device_name AS device_name FROM device AS D WHERE D.id = N.device_id) AS device_name")
            )
            ->orderby('notification_detail.created_at', 'DESC')
            ->limit(50)
            ->get()->toArray();

        foreach ($notificationList as $key => $value) {

            $timezone = new \DateTimeZone($value['company_time_zone']);
            $createdAt = new \DateTime($value['created_at'], $timezone);
            $notificationList[$key]['created_date'] = $createdAt->format('d-m-Y / H:i A');
        }
        $alertItems = '';
        foreach ($notificationList as $notificationListData) {
            $alertItems .= '<div class="col-md-12 alertItemBox">';
            $alertItems .= '<div class="alertItemMachine">' . $notificationListData['device_name'] . '</div>';
            $alertItems .= '<div class="alertItemContent">' . $notificationListData['notification'] . '</div>';
            $temp = '<span class="liveTempBlock"><img src="' . asset('backend/images/temperatures-white.png') . '" class="img-responsive historyTempInfoBlockImg" alt=""><span class="liveTempNotification">' . $notificationListData['temperature'] . '</span></span>';
            $humidity = '<span class="liveHumidityBlock"><img src="' . asset('backend/images/humidity-white.png') . '" class="img-responsive humidityHistoryIcon" alt=""><span class="liveHumidityNotification">' . $notificationListData['humidity'] . '</span></span>';
            $alertItems .= '<div class="alertItemTemp">' . $temp . ' ' . $humidity . '</div>';
            $alertItems .= '<div class="alertItemDate">' . $notificationListData['created_date'] . '</div>';
            $alertItems .= '</div>';
        }
        echo $alertItems;
    }

    public function login()
    {
        if (!empty(auth()->user())) {
            return redirect()->route('admin.home');
        }

        return view('backend.login');
    }

    /**
     * Admin login post
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginPost(Request $request)
    {
        try {

            $credentials = [
                'email' => $request->email,
                'password' => $request->password,
            ];
            $rememberMe = $request->has('remember') ? true : false;

            if (Auth::attempt($credentials)) {
                return response()->json(['status' => 200, 'message' => 'Successfully login!', 'redirect' => route('admin.masterdashboard')]);
            }
            return response()->json(['status' => 500, 'message' => 'Username or password is wrong.']);
        } catch (\Exception $exception) {
            Helpers::log('admin login post : exception');
            Helpers::log($exception);
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }

    /**
     * Admin logout
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Helpers::log('admin logout : start');
        try {
            Auth::logout();
            Session::flush();
            Helpers::log('admin logout : finish');
            return redirect()->route('admin.login');
        } catch (\Exception $exception) {
            Helpers::log('admin logout : exception');
            Helpers::log($exception);
            return redirect()->route('admin.home');
        }
    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function profile()
    {
        $userData = Auth::user();

        $timeZoneList = [];
        if ($userData->role_id == Roles::$company) {
            $timeZoneList = Helpers::timeZoneList();
        }

        return view('backend.profile', compact('userData', 'timeZoneList'));
    }

    /**
     * user profile update
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profileUpdate(Request $request)
    {
        DB::beginTransaction();
        try {
            $userData = Auth::user();
            $userId = $userData->id;
            $email = $request->email;
            $phone = $request->phone;

            $checkEmail = User::where('email', $email)->where('id', '!=', $userId)->count();
            $checkPhone = User::where('phone', $phone)->where('id', '!=', $userId)->count();
            if ($checkEmail > 0) {
                return response()->json(["status" => 409, "message" => "This email address already exist."]);
            } elseif ($checkPhone > 0) {
                return response()->json(["status" => 409, "message" => "This phone number already exist."]);
            } else {
                $updateData = [
                    "first_name" => $request->first_name,
                    "email" => $email,
                ];

                if ($userData->role_id == Roles::$company) {
                    $updateData['time_zone'] = $request->time_zone;
                } else {
                    $updateData['last_name'] = $request->last_name;
                }

                if (isset($request->password) && !empty($request->password)) {
                    $updateData['password'] = Hash::make($request->password);
                }
                if ($request->hasFile('profile')) {
                    $folder = $this->createDirectory('users');
                    if ($file = $request->file('profile')) {
                        $extension = $file->getClientOriginalExtension();
                        $fileName = time() . '.' . $extension;
                        $file->move("$folder/", $fileName);
                        chmod($folder . '/' . $fileName, 0777);
                        $updateData['profile'] = 'uploads/users/' . $fileName;
                    }
                }
                User::where('id', $userId)->update($updateData);
                DB::commit();
                return response()->json(["status" => 200, "message" => "Your profile information has been successfully updated.", "redirect" => route('admin.profile')]);
            }
        } catch (\Exception $exception) {
            Helpers::log('profile update : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }

    /**
     * Forgot password page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function forgotPassword()
    {
        if (!empty(auth()->user())) {
            return redirect()->route('admin.home');
        }
        return view('backend.password.forgot');
    }

    /**
     * Forgot password send mail
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPasswordPost(Request $request)
    {
        DB::beginTransaction();
        try {
            $email = $request->email;
            $userData = User::where('email', $email)->select('first_name', 'last_name')->first();

            if (empty($userData)) {
                return response()->json(["status" => 404, "message" => "You enter email address is wrong."]);
            } else {
                PasswordResets::where('email', $email)->delete();
                $token = Helpers::randomString(32);
                $insertData = [
                    'email' => $email,
                    'token' => $token,
                    'created_at' => Carbon::now(),
                ];
                PasswordResets::create($insertData);

                $data = [
                    "username" => $userData->first_name . " " . $userData->last_name,
                    "url" => route('admin.reset-password.token', $token),
                ];

                Helpers::sendMailUser($data, 'emails.forgot-password', 'Reset Password', $email);

                DB::commit();
                return response()->json(["status" => 200, "message" => "We have e-mailed your password reset link!"]);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('forgot password : exception');
            Helpers::log($exception);
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }

    /**
     * Reset password page
     *
     * @param $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function resetPassword($token)
    {
        if (!empty(auth()->user())) {
            return redirect()->route('admin.home');
        }
        return view('backend.password.reset', compact('token'));
    }

    /**
     * reset password update by token
     *
     * @param Request $request
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPasswordUpdate(Request $request, $token)
    {
        DB::beginTransaction();
        try {
            $resetData = PasswordResets::where('token', $token)->first();

            if (empty($resetData)) {
                return response()->json(["status" => 404, "message" => "Your password reset link has expired"]);
            } else {
                $email = $resetData->email;
                $userData = User::where('email', $email)->select('id')->first();
                if (empty($userData)) {
                    return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
                } else {

                    PasswordResets::where('email', $email)->delete();

                    $userId = $userData->id;
                    $data = [
                        'password' => Hash::make($request->password),
                        'updated_at' => Carbon::now()
                    ];
                    User::where('id', $userId)->update($data);

                    DB::commit();
                    return response()->json(["status" => 200, "message" => "Your password has been reset successfully! Please wait...", "redirect" => route('admin.login')]);
                }
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('reset password : exception');
            Helpers::log($exception);
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }

    /**
     * Company dashboard real time data list
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardRealTimeData(Request $request)
    {
        DB::beginTransaction();
        try {
            $html = '<div class="text-center col-md-12"><h3 class="text-danger">Ooops...Something went wrong. Please contact to support team.</h3></div>';
            $isMusicPlay = 0;
            $disableColor = Notification::DISABLECOLOR;
            $disable = Notification::DISABLE;
            $cookieName = \Illuminate\Support\Facades\Cookie::get('theme-selected');
            $userData = Auth::user();
            $companyId = Helpers::companyId();
            $currentTime = strtotime(date('H:i:s'));

            $where = 'device_dashboard.company_id = ' . $companyId;
            if ($request->has('device') && !empty($request->device) && $request->device != 'all') {
                $where .= ' AND device_dashboard.device_id = ' . $request->device;
            } else {
                if ($userData->role_id == Roles::$employee) {
                    $groupId = $userData->group_id;
                    $where .= " AND FIND_IN_SET(device_dashboard.device_id,(SELECT device_ids FROM groups WHERE id = $groupId))";
                }
            }

            $deviceDashboardList = DeviceDashboard::whereRaw($where)
                ->select(
                    'id', 'updated_at',
                    DB::raw("(SELECT data_interval FROM device WHERE device.id = device_dashboard.device_id) AS data_interval")
                )
                ->get()->toArray();

            if (!empty($deviceDashboardList)) {
                foreach ($deviceDashboardList as $value) {
                    $serverTime = date("Y-m-d H:i:s", strtotime($value['updated_at']));
                    $foundTotalDiffMinute = (int)round(((abs($currentTime - strtotime($serverTime)) / 3600) * 60));
                    $dataInterval = (int)$value['data_interval'];
                    if ($dataInterval < $foundTotalDiffMinute) {
                        $updateData = [
                            "temperature_color" => $disableColor,
                            "temperature_alert" => $disable,
                            "humidity_color" => $disableColor,
                            "humidity_alert" => $disable,
                            "offline_color" => $disableColor,
                            "offline_alert" => $disable,
                            "voltage_color" => $disableColor,
                            "voltage_alert" => $disable,
                            "last_seen" => Carbon::now(),
                        ];
                        DeviceDashboard::where('id', $value['id'])->update($updateData);
                    }
                }

                $latestDeviceDashboardList = DeviceDashboard::join('device AS D', 'D.id', '=', 'device_dashboard.device_id')
                    ->whereRaw($where)
                    ->select(
                        'device_dashboard.*', 'D.device_name', 'D.data_interval', 'D.uuid AS device_uuid',
                        DB::raw("(SELECT T.uuid FROM temperature AS T WHERE T.device_id = D.id AND T.status = '" . $this->actStatus . "' AND T.deleted_at IS NULL) AS temperature_uuid "),
                        DB::raw("(SELECT H.uuid FROM humidity AS H WHERE H.device_id = D.id AND H.status = '" . $this->actStatus . "' AND H.deleted_at IS NULL) AS humidity_uuid "),
                        DB::raw("(SELECT time_zone FROM users WHERE users.id = device_dashboard.company_id) AS company_time_zone"),
                        DB::raw("(SELECT updated_at FROM device_temperature_log WHERE device_temperature_log.device_id = device_dashboard.device_id ORDER BY id DESC LIMIT 1) AS log_last_time")
                    )
                    ->get()->toArray();

                if (!empty($latestDeviceDashboardList)) {
                    $html = '';
                    foreach ($latestDeviceDashboardList as $value) {
                        $serverTime = date("Y-m-d H:i:s", strtotime($value['updated_at']));
                        $foundTotalDiffMinute = (int)round(((abs($currentTime - strtotime($serverTime)) / 3600) * 60));
                        $dataInterval = (int)$value['data_interval'];
                        $temperatureColor = Notification::DISABLECOLOR;
                        $humidityColor = Notification::DISABLECOLOR;
                        $voltageColor = "";

                        $notifyDanger = Notification::DANGER;
                        if (($value['temperature_alert'] == $notifyDanger) || ($value['humidity_alert'] == $notifyDanger)) {
                            $isMusicPlay = 1;
                        }

                        if ($dataInterval > $foundTotalDiffMinute) {
                            //$voltageColor = $value['voltage_color'];  // gre fixed as pr desing  default card color gray
                            $temperatureColor = $value['temperature_color'];
                            $humidityColor = $value['humidity_color'];
                            //Show only if voltage in Danger mode
                            if ($value['voltage_alert'] == $notifyDanger) {
                                $voltageColor = $value['voltage_color'];
                                $isMusicPlay = 1;
                            }
                        }

                        // when both disabled then all re disabled
                        if ($temperatureColor == $disableColor && $humidityColor == $disableColor) {
                            //$voltageColor = $disableColor;
                            $isMusicPlay = 0;
                        }

                        $temperature = 0;
                        if ($value['temperature']) {
                            $temperature = $value['temperature'];
                        }
                        $humidity = 0;
                        if ($value['humidity']) {
                            $humidity = $value['humidity'];
                        }

                        $tempUrl = '#';
                        if ($value['temperature_uuid']) {
                            $tempUrl = route('admin.temperatures.edit', $value['temperature_uuid']);
                        }
                        $humidityUrl = '#';
                        if ($value['humidity_uuid']) {
                            $humidityUrl = route('admin.humidity.edit', $value['humidity_uuid']);
                        }
                        $updatedAt = '';
                        //print_r($value);exit;
                        /*if ($value['log_last_time']) {

                            $timezone = new \DateTimeZone($value['company_time_zone']);
                            $logLastTime = new \DateTime($value['log_last_time'], $timezone);
                            $updatedAt = $logLastTime->format('d-m-Y / H:i');
                        }*/
                        $updatedAt = date('d-m-Y / H:i', strtotime($value['log_last_time']));

                        $temperaturesHistory = '';
                        $temperaturesLog = DeviceTemperatureLog::where('device_id', $value['device_id'])
                            ->select('temperature')
                            ->orderBy('id', 'DESC')
                            ->limit(20)
                            ->get()->toArray();
                        $temperaturesLog = array_reverse($temperaturesLog, 'temperature');
                        $idx = 0;
                        foreach ($temperaturesLog as $log) {
                            $temperaturesHistory .= $log['temperature'];
                            if (count($temperaturesLog) != ($idx + 1)) {
                                $temperaturesHistory .= ',';
                            }
                            $idx++;
                        }

                        $html .= '<div class="col-md-4">';
                        $html .= '<div class="box">';
                        $html .= '<div class="box-header">';
                        $html .= '<span class="time pull-left"><i class="fa fa-clock-o"></i> ' . $updatedAt . '</span>';
                        $html .= '<span class="voltage pull-right">' . $value['rssi'] . ' / ' . $value['voltage'] . '</span>';
                        $html .= '</div>';
                        $html .= '<div class="box-body">';
                        $html .= '<h2>' . $value['device_name'] . '</h2>';
                        $html .= '<div class="temp-hum">';
                        $html .= '<div class="alarm temp">';
                        $html .= '<a class="' . $value['temperature_alert'] . 'Temp" href="' . $tempUrl . '" style="background: linear-gradient(to right, ' . $temperatureColor . ', #ffffff);border: 1px solid ' . $temperatureColor . ';">';
                        if ($cookieName == 'lightMode' || $cookieName == '') {
                            $html .= '<img src="' . url('backend/images/temperatures.png') . '" alt="temperatures">';
                        } else {
                            $html .= '<img src="' . url('backend/images/temperatures-white.png') . '" alt="temperatures">';
                        }

                        $html .= '<span>' . $temperature . ' ℃</span>';
                        $html .= '</a>';
                        $html .= '</div>';
                        $html .= '<div class="alarm hum">';
                        $html .= '<a class="' . $value['humidity_alert'] . 'Humidity" href="' . $humidityUrl . '" style="background: linear-gradient(to right, ' . $humidityColor . ', #ffffff);border: 1px solid ' . $humidityColor . ';">';
                        if ($cookieName == 'lightMode' || $cookieName == '') {
                            $html .= '<img src="' . url('backend/images/humidity.png') . '" alt="humidity">';
                        } else {
                            $html .= '<img src="' . url('backend/images/humidity-white.png') . '" alt="humidity">';
                        }
                        $html .= '<span>' . $humidity . ' %</span>';
                        $html .= '</a>';
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '<a href="' . route('admin.report.device', $value['device_uuid']) . '" class="link">Overall activity</a>';
                        $html .= '<div class="sparkline" data-type="line" data-spot-Radius="0" data-highlight-Spot-Color="#f39c12" data-highlight-Line-Color="" data-min-Spot-Color="#f56954" data-max-Spot-Color="#00a65a" data-spot-Color="#39CCCC" data-offset="90" data-width="100%" data-height="100px" data-line-Width="2" data-line-Color="#00acff" data-fill-Color="rgba(0, 170, 255, 0.08)">';
                        $html .= $temperaturesHistory;
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                    }
                } else {
                    $html = '<div class="text-center col-md-12"><h3>No Device Available.</h3></div>';
                }

                DB::commit();
            } else {
                $html = '<div class="text-center col-md-12"><h3>No Device Available.</h3></div>';
            }
            return response()->json(["status" => 200, "message" => "Latest temperature has been successfully generated.", "is_music_play" => $isMusicPlay, "html" => $html]);
        } catch (\Exception $exception) {
            Helpers::log('dashboard real time data : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(["status" => 500, "message" => "Latest temperature generating failed", "is_music_play" => 0, "html" => $html]);
        }
    }

    public function dashboardSensorHistory(Request $request)
    {
        try {
            $sensor = $request->sensor;
            $day = $request->day;
            $isTemp = $request->is_temp;

            $date = date('Y-m-d');
            if ($day == 'yesterday') {
                $date = date('Y-m-d', strtotime("-1 days"));
            }
            $hours = [0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22];

            $sensorHistory = [];
            foreach ($hours as $hour) {
                $where = "device_id = $sensor AND DATE(created_at) = '$date' AND HOUR(created_at) = $hour";
                $logData = DeviceTemperatureLog::whereRaw($where)
                    ->select('id', 'temperature', 'humidity')
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->first();
                $value = 0;
                if (!empty($logData)) {
                    $value = $logData->temperature;
                    if ($isTemp == 'false') {
                        $value = $logData->humidity;
                    }
                }
                $sensorHistory[] = $value;
            }
            return response()->json(["status" => 200, "message" => "success", "data" => $sensorHistory]);
        } catch (\Exception $exception) {
            Helpers::log('dashboard sensor history data : exception');
            Helpers::log($exception);
            $sensorHistory = [];
            return response()->json(["status" => 500, "message" => "Oops...Something went wrong.", "data" => $sensorHistory]);
        }
    }

    public function changeTheme(Request $request)
    {
        $themeOption = $request->themeOption;
        $response = new \Illuminate\Http\RedirectResponse(url('/'));
        $response->withCookie(cookie()->forever('theme-selected', $themeOption));
        return $response;
    }
    function getDateRangeForWeek($date){
        $dateTime = new \DateTime($date);
        $monday = clone $dateTime->modify(('Sunday' == $dateTime->format('l')) ? 'Monday last week' : 'Monday this week');
        $sunday = clone $dateTime->modify('Sunday this week');
        return ['monday'=>$monday->format("Y-m-d"), 'sunday'=>$sunday->format("Y-m-d")];
    }
    public function sendEmails(Request $request)
    {
        Helpers::log('Send email cronjob has been fired '.date('d-m-Y H:i:s'));
        $dateToday = date('Y-m-d');
        $timeFromNow = date('H') . ":00:00";
        $timeToNow = date('H') . ":59:59";

        $fromDate = date('Y-m-d')." 00:00:00";
        $toDate = date('Y-m-d')." 23:59:59";

        $queryGetSchedules = 'SELECT schedule_update.*,schedule_update_log.sul_id FROM schedule_update LEFT JOIN schedule_update_log ON schedule_update_log.su_id = schedule_update.su_id AND schedule_update_log.sul_date = "' . $dateToday . '" WHERE su_status = 1 AND su_time BETWEEN "' . $timeFromNow . '" AND "' . $timeToNow . '" ';
        $GetSchedules = DB::select($queryGetSchedules);

        foreach ($GetSchedules as $scheduleData) {
            $goAhead = 0;
            $subjectReportType = '';
            if($scheduleData->su_type == 1 && $scheduleData->sul_id <= 0){
                $goAhead = 1;
                $subjectReportType = 'Daily ';
            }
            else if($scheduleData->su_type == 2 && $scheduleData->sul_id <= 0){
                $subjectReportType = 'Weekly ';
                $weekDates = $this->getDateRangeForWeek(date('Y-m-d'));
                $fromDate = $weekDates['monday'].' 00:00:00';
                $toDate = $weekDates['sunday'].' 23:59:59';

                $getWeekDay = $scheduleData->su_week_day;
                $checkCurrentDay  = date('l');
                if($checkCurrentDay == ucfirst($getWeekDay)){
                    $goAhead = 1;
                }
            }
            else if($scheduleData->su_type == 3 && $scheduleData->sul_id <= 0){
                $subjectReportType = 'Yearly ';
                $fromDate = date('Y-m-01').' 00:00:00';
                $toDate = date('Y-m-t').' 23:59:59';

                $getMonthDate = $scheduleData->su_month_date;
                $checkCurrentDate  = date('d');
                if($getMonthDate == $checkCurrentDate){
                    $goAhead = 1;
                }
            }
            if ($goAhead == 1) {
                $deviceData = Device::dataById($scheduleData->su_devices);
                $deviceData = $deviceData->toArray();
                $timezone = new \DateTimeZone($deviceData['company_time_zone']);
                $deviceLogList = Helpers::deviceLogListByDevice($scheduleData->su_devices, $fromDate, $toDate, 'all', $timezone);
                $todayDateTime = new \DateTime(date('Y-m-d H:i:s'), $timezone);
                $currentDateTime = $todayDateTime->format('d-m-Y / H:i');

                $data = [
                    'current_datetime' => $currentDateTime,
                    'start_date' => date('d-m-Y', strtotime($fromDate)),
                    'end_date' => date('d-m-Y', strtotime($toDate)),
                    'device_detail' => $deviceData,
                    'device_log_list' => $deviceLogList
                ];

                ini_set("pcre.backtrack_limit", "5000000");

                $todayDate = new \DateTime(date('Y-m-d'), $timezone);
                $fileName = $deviceData['device_name'] . " " . $todayDate->format('d-m-Y') . ".pdf";
                $path = public_path('/uploads/' . $fileName);
                $pdf = PDF::loadView('emails.sensor-update-pdf', $data);
                $fromEmail = config('constants.from_email');
                $fromName = config('constants.from_name');
                $subject = env('APP_NAME') . " - " .$deviceData['device_name'].' '.$subjectReportType.' Sensor Update';
                //$toEmail = 'kashyap.waytoweb@gmail.com';
                $toEmail = $scheduleData->su_email;
                $emailData = [
                    'deviceData'=>$deviceData
                ];
                Mail::send('emails.sensor-update', ['data' => $emailData], function ($m) use ($pdf,$fileName,$fromName, $fromEmail, $subject, $toEmail) {
                    $m->from($fromEmail, 'i-Sense');
                    $m->to($toEmail)->subject($subject);
                    $m->attachData($pdf->output(), $fileName);
                });


                $insertLog = [
                    'sul_uuid'=>Helpers::getUuid(),
                    'su_id' => $scheduleData->su_id,
                    'sul_email' => $toEmail,
                    'sul_date' => date('Y-m-d'),
                    'sul_time' => $scheduleData->su_time,
                    'sul_log_date'=>date('Y-m-d H:i:s')
                ];
                ScheduleUpdateLog::insert($insertLog);

                /*$latestDeviceDashboardList = DeviceDashboard::whereIn('device_id', $deviceIds)->join('device AS D', 'D.id', '=', 'device_dashboard.device_id')
                    ->select(
                        'device_dashboard.*', 'D.device_name', 'D.data_interval', 'D.uuid AS device_uuid',
                        DB::raw("(SELECT T.uuid FROM temperature AS T WHERE T.device_id = D.id AND T.status = '" . $this->actStatus . "' AND T.deleted_at IS NULL) AS temperature_uuid "),
                        DB::raw("(SELECT H.uuid FROM humidity AS H WHERE H.device_id = D.id AND H.status = '" . $this->actStatus . "' AND H.deleted_at IS NULL) AS humidity_uuid "),
                        DB::raw("(SELECT time_zone FROM users WHERE users.id = device_dashboard.company_id) AS company_time_zone"),
                        DB::raw("(SELECT updated_at FROM device_temperature_log WHERE device_temperature_log.device_id = device_dashboard.device_id ORDER BY id DESC LIMIT 1) AS log_last_time")
                    )
                    ->get()->toArray();
                $datai = 0;
                foreach ($latestDeviceDashboardList as $latestDashboards) {
                    $updatedAt = date('d-m-Y / H:i', strtotime($latestDashboards['log_last_time']));
                    $latestDeviceDashboardList[$datai]['show_updated_time'] = $updatedAt;
                    $datai++;
                }

                $data = [
                    "latestDeviceDashboardList" => $latestDeviceDashboardList,
                ];*/
            }

        }
    }
}

