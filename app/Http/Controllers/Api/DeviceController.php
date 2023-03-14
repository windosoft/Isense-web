<?php

namespace App\Http\Controllers\Api;

use App\Models\DeviceTemperatureLog;
use App\Models\Helpers;
use App\Models\Notification;
use App\Models\Temperature;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    protected $actStatus = '';

    /**
     * DeviceController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
    }

    /**
     * Get Device history by device number
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deviceHistory(Request $request)
    {
        Helpers::log('device history list : start');
        try {
            $deviceSn = $request->device_sn;
            if (empty($deviceSn)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Device number must be required"]);
            } else {
                //DB::enableQueryLog();
                $date = date('Y-m-d');
                $where = "device_temperature_log.device_id  = (SELECT device.id FROM device WHERE device.device_sn = '$deviceSn' AND device.status = '" . $this->actStatus . "')";
                $where .= " AND Date(device_temperature_log.updated_at) = '$date' ";
                $deviceTempList = DeviceTemperatureLog::leftjoin('temperature AS T', function ($join) {
                    $join->on('T.device_id', '=', 'device_temperature_log.device_id');
                    $join->where('T.status', $this->actStatus);
                })->leftjoin('humidity AS H', function ($join) {
                    $join->on('H.device_id', '=', 'device_temperature_log.device_id');
                    $join->where('H.status', $this->actStatus);
                })->whereRaw($where)
                    ->select(
                        'device_temperature_log.*', 'T.low_temp_warning', 'T.high_temp_warning', 'T.low_temp_threshold',
                        'T.high_temp_threshold', 'H.warning_low_humidity_threshold', 'H.warning_high_humidity_threshold',
                        'H.low_humidity_threshold', 'H.high_humidity_threshold',
                        DB::raw("(SELECT time_zone FROM users AS U WHERE U.id = (SELECT company_id FROM device WHERE device.id = device_temperature_log.device_id)) AS company_time_zone")
                    )
                    ->orderBy('device_temperature_log.id', 'desc')
                    ->limit(200)
                    ->get()->toArray();
                //dd(DB::getQueryLog());
                foreach ($deviceTempList as $key => $value) {
                    $timezone = new \DateTimeZone($value['company_time_zone']);
                    $serverTime = new \DateTime($value['servertime'], $timezone);
                    $createdAt = new \DateTime($value['created_at'], $timezone);
                    $updatedAt = new \DateTime($value['updated_at'], $timezone);

                    $deviceTempList[$key]['servertime'] = $serverTime->format('d-m-Y / H:i');
                    /*$deviceTempList[$key]['created_at'] = $createdAt->format('d-m-Y / H:i');*/
                    $deviceTempList[$key]['created_at'] = $updatedAt->format('d-m-Y / H:i');
                    $deviceTempList[$key]['updated_at'] = $updatedAt->format('d-m-Y / H:i');

                    $temperatureColor = Notification::DISABLECOLOR;
                    $temperature = $value['temperature'];
                    $lowTempWarning = $value['low_temp_warning'];
                    $highTempWarning = $value['high_temp_warning'];
                    $lowTempThreshold = $value['low_temp_threshold'];
                    $highTempThreshold = $value['high_temp_threshold'];
                    if (!is_null($lowTempWarning) && !is_null($highTempWarning) && !is_null($lowTempThreshold) && !is_null($highTempThreshold)) {
                        if ($temperature >= $lowTempWarning && $temperature <= $highTempWarning) {
                            $temperatureColor = Notification::SUCCESSCOLOR;
                        } elseif (($temperature >= $lowTempThreshold && $temperature < $lowTempWarning) || ($temperature > $highTempWarning && $temperature <= $highTempThreshold)) {
                            $temperatureColor = Notification::WARNINGCOLOR;
                        } elseif ($temperature < $highTempWarning || $temperature > $highTempThreshold) {
                            $temperatureColor = Notification::DANGERCOLOR;
                        }
                    }
                    $deviceTempList[$key]['temperature_color'] = $temperatureColor;

                    $humidityColor = Notification::DISABLECOLOR;
                    $humidity = $value['humidity'];
                    $humidityLowWarning = $value['warning_low_humidity_threshold'];
                    $humidityHighWarning = $value['warning_high_humidity_threshold'];
                    $humidityLowThreshold = $value['low_humidity_threshold'];
                    $humidityHighThreshold = $value['high_humidity_threshold'];
                    if (!is_null($humidityLowWarning) && !is_null($humidityHighWarning) && !is_null($humidityLowThreshold) && !is_null($humidityHighThreshold)) {
                        if ($humidity >= $humidityLowWarning && $humidity <= $humidityHighWarning) {
                            $humidityColor = Notification::SUCCESSCOLOR;
                        } elseif (($humidity >= $humidityLowThreshold && $humidity < $humidityLowWarning) || ($humidity > $humidityHighWarning && $humidity <= $humidityHighThreshold)) {
                            $humidityColor = Notification::WARNINGCOLOR;
                        } elseif ($humidity < $humidityHighWarning || $humidity > $humidityHighThreshold) {
                            $humidityColor = Notification::DANGERCOLOR;
                        }
                    }
                    $deviceTempList[$key]['humidity_color'] = $humidityColor;
                }

                $response = Helpers::replaceNullWithEmptyString($deviceTempList);
                Helpers::log('device history list : finish');
                return response()->json(["status" => 200, "show" => false, "msg" => "success", "data" => $response]);
            }
        } catch (\Exception $exception) {
            Helpers::log('device history list : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }
}
