<?php

namespace App\Http\Controllers\Admin;

use App\Models\Device;
use App\Models\Helpers;
use App\Models\Permissions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PDF;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $actStatus = '';
    protected $delStatus = '';

    /**
     * NotificationController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
        $this->delStatus = Helpers::$delete;
    }

    /**
     * Display a listing of the resource.
     *
     * @param null $device
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($device = null)
    {
        $checkPermission = Permissions::checkActionPermission('view_report');
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
        $timePeriod = config('constants.time_period');
        $reportType = config('constants.report_type');

        $report = [
            "is_company" => $isCompany,
            "company_list" => $companyList,
            "device_list" => $deviceList,
            "time_period" => $timePeriod,
            "report_type" => $reportType,
            "device" => $device,
        ];

        return view('backend.report.index', compact('report'));
    }

    /**
     * Generate device report
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateDeviceReport(Request $request)
    {
        try {
            $deviceId = $request->device_id;
            $reportType = $request->report_type;
            $timePeriod = $request->time_period;
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            if (!empty($timePeriod) && $timePeriod != 'custom') {
                $dates = Helpers::getDatesFromTimePeriod($timePeriod);
                $startDate = $dates['start_date'];
                $endDate = $dates['end_date'];
            }

            $deviceData = Device::dataById($deviceId);

            if (!empty($deviceData)) {

                $deviceData = $deviceData->toArray();

                $timezone = new \DateTimeZone($deviceData['company_time_zone']);
                $alarmList = Helpers::alarmListByDevice($deviceId, $timezone);
                //DB::enableQueryLog();
                $deviceLogList = Helpers::deviceLogListByDevice($deviceId, $startDate, $endDate, $reportType, $timezone);
                //print_r(DB::getQueryLog());exit;
                $todayDateTime = new \DateTime(date('Y-m-d H:i:s'), $timezone);
                $currentDateTime = $todayDateTime->format('d-m-Y / H:i');

                $data = [
                    'current_datetime' => $currentDateTime,
                    'start_date' => date('d-m-Y', strtotime($startDate)),
                    'end_date' => date('d-m-Y', strtotime($endDate)),
                    'device_detail' => $deviceData,
                    'alarm_list' => $alarmList,
                    'device_log_list' => $deviceLogList
                ];
                return response()->json(["status" => 200, "message" => "this device report has been successfully generated.", "data" => $data]);
            } else {
                return response()->json(["status" => 500, "message" => "Ooops...Something went wrong. Please try again."]);
            }
        } catch (\Exception $exception) {
            Helpers::log('Generate device report : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "message" => "Ooops...Something went wrong. Please try again."]);
        }
    }

    /**
     * Download pdf report
     *
     * @param Request $request
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function downloadReportPdf(Request $request)
    {
        try {
            $deviceId = $request->device_id;
            $reportType = $request->report_type;
            $timePeriod = $request->time_period;
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            if (!empty($timePeriod) && $timePeriod != 'custom') {
                $dates = Helpers::getDatesFromTimePeriod($timePeriod);
                $startDate = $dates['start_date'];
                $endDate = $dates['end_date'];
            }

            $deviceData = Device::dataById($deviceId);

            if (!empty($deviceData)) {

                $deviceData = $deviceData->toArray();
                $timezone = new \DateTimeZone($deviceData['company_time_zone']);

                $alarmList = Helpers::alarmListByDevice($deviceId, $timezone);
                $deviceLogList = Helpers::deviceLogListByDevice($deviceId, $startDate, $endDate, $reportType, $timezone);

                $todayDateTime = new \DateTime(date('Y-m-d H:i:s'), $timezone);
                $currentDateTime = $todayDateTime->format('d-m-Y / H:i');

                $data = [
                    'current_datetime' => $currentDateTime,
                    'start_date' => date('d-m-Y', strtotime($startDate)),
                    'end_date' => date('d-m-Y', strtotime($endDate)),
                    'device_detail' => $deviceData,
                    'alarm_list' => $alarmList,
                    'device_log_list' => $deviceLogList
                ];
                ini_set("pcre.backtrack_limit", "5000000");
                $pdf = PDF::loadView('backend.report.create-pdf', $data);

                $todayDate = new \DateTime(date('Y-m-d'), $timezone);
                $fileName = $deviceData['device_name'] . " " . $todayDate->format('d-m-Y') . ".pdf";
                return $pdf->stream($fileName);
            } else {
                return redirect()->route('admin.report.index')->with("error", "Ooops...Something went wrong. Please try again.");
            }
        } catch (\Exception $exception) {
            Helpers::log('Download device report pdf : exception');
            Helpers::log($exception);
            return redirect()->route('admin.report.index')->with("error", "Ooops...Something went wrong. Please try again.");
        }
    }

    /**
     * Download csv report
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadReportCSV(Request $request)
    {
        try {
            $deviceId = $request->device_id;
            $reportType = $request->report_type;
            $timePeriod = $request->time_period;
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            if (!empty($timePeriod) && $timePeriod != 'custom') {
                $dates = Helpers::getDatesFromTimePeriod($timePeriod);
                $startDate = $dates['start_date'];
                $endDate = $dates['end_date'];
            }

            $deviceData = Device::dataById($deviceId);

            if (!empty($deviceData)) {

                $deviceData = $deviceData->toArray();
                $timezone = new \DateTimeZone($deviceData['company_time_zone']);

                $deviceLogList = Helpers::deviceLogListByDevice($deviceId, $startDate, $endDate, $reportType, $timezone);

                $todayDateTime = new \DateTime(date('Y-m-d H:i:s'), $timezone);
                $currentDateTime = $todayDateTime->format('d-m-Y / H:i');

                $deviceName = $deviceData['device_name'];
                $selectedArray = array('Temperature', 'Humidity', 'Time');


                $todayDate = new \DateTime(date('Y-m-d'), $timezone);
                $fileName = $deviceName . " " . $todayDate->format('d-m-Y') . ".csv";

                $deviceInfo = [$deviceName, $currentDateTime];

                header('Content-Type: text/csv; charset=utf-8');
                Header('Content-Type: application/force-download');
                header('Content-Disposition: attachment; filename=' . $fileName . '');
                $output = fopen('php://output', 'w');
                fputcsv($output, $deviceInfo);
                fputcsv($output, $selectedArray);
                foreach ($deviceLogList as $value) {
                    unset($value['device_color']);

                    fputcsv($output, $value);
                }
                fclose($output);
            } else {
                return redirect()->route('admin.report.index')->with("error", "Ooops...Something went wrong. Please try again.");
            }
        } catch (\Exception $exception) {
            Helpers::log('Download device report csv : exception');
            Helpers::log($exception);
            return redirect()->route('admin.report.index')->with("error", "Ooops...Something went wrong. Please try again.");
        }
    }
}
