<?php

namespace App\Http\Controllers\Api;

use App\Models\DashboardUpdateHistory;
use App\Models\Device;
use App\Models\DeviceDashboard;
use App\Models\DeviceTemperatureLog;
use App\Models\Helpers;
use App\Models\Humidity;
use App\Models\Notification;
use App\Models\Offline;
use App\Models\Temperature;
use App\Models\Terminals;
use App\Models\Voltage;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TerminalController extends Controller
{
    protected $actStatus = '';

    /**
     * TerminalController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
    }

    /**
     * Receiver list by company id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiverList(Request $request)
    {
        Helpers::log('receiver list : start');
        try {
            $companyId = $request->company_id;

            if (empty($companyId)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Company id must be required"]);
            } else {

                $receiverList = Terminals::where('company_id', $companyId)
                    ->where('status', $this->actStatus)
                    ->select('id', 'name')
                    ->orderBy('id', 'DESC')
                    ->get();
                $color = [
                    ['name' => Notification::WARNING, 'code' => Notification::WARNINGCOLOR],
                    ['name' => Notification::DANGER, 'code' => Notification::DANGERCOLOR],
                    ['name' => Notification::SUCCESS, 'code' => Notification::SUCCESSCOLOR],
                    ['name' => Notification::DISABLE, 'code' => Notification::DISABLECOLOR]
                ];
                $data = ['receiver' => $receiverList, 'color' => $color];
                $response = Helpers::replaceNullWithEmptyString($data);
                Helpers::log('receiver list : finish');
                return response()->json(["status" => 200, "show" => false, "msg" => "success", "data" => $response]);
            }
        } catch (\Exception $exception) {
            Helpers::log('receiver list : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * Method use for save data from the sensor
     *
     * $notificationType[0] = temperature
     * $notificationType[1] = humidity
     * $notificationType[2] = offline
     * $notificationType[3] = voltage
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeData(Request $request)
    {
        try {
            $sensorData = $request->json()->all();

            Helpers::log('Sensor List : getTerminalInfo');
            Helpers::log($sensorData);

            if (is_array($sensorData) && !empty($sensorData)) {
                if (isset($sensorData['TagList_info']) && !empty($sensorData['TagList_info'])) {

                    $tagListInfo = $sensorData['TagList_info'];
                    $serverTime = $sensorData['ServerTime'];
                    $rtcTime = date('Y-m-d H:i:s', strtotime($sensorData['RTC']));

                    foreach ($tagListInfo as $key => $tagList) {
                        $deviceSN = $tagList['SN'];

                        $deviceData = Device::where('status', $this->actStatus)
                            ->where('device_sn', $deviceSN)
                            ->select('id')
                            ->first();

                        if (!empty($deviceData)) {
                            $where = "id = (SELECT company_id FROM device WHERE device_sn = '$deviceSN' AND status = '" . $this->actStatus . "' LIMIT 1 )";
                            $companyData = User::whereRaw($where)->select('id', 'time_zone')->first();
                            $rtcTimeWithZone = $rtcTime;
                            if (!empty($companyData)) {
                                if (isset($companyData->time_zone) && !empty($companyData->time_zone)) {
                                    $rtcTimeWithZone = Carbon::createFromFormat('Y-m-d H:i:s', $rtcTime, 'UTC')->setTimezone($companyData->time_zone);
                                }
                            }

                            $deviceId = $deviceData->id;
                            $temperature = $tagList['Temperature'];
                            $humidity = $tagList['Humidity'];
                            $vbv = $tagList['VBV'];
                            $rssi = $tagList['RSSI'];

                            $deviceTempLogCreate = [
                                "uuid" => Helpers::getUuid(),
                                "device_id" => $deviceId,
                                "device_sn" => $deviceSN,
                                "temperature" => $temperature,
                                "humidity" => $humidity,
                                "rssi" => $rssi,
                                "vbv" => $vbv,
                                "is_low_voltage" => $tagList['IsLowVoltage'],
                                "servertime" => $serverTime,
                                "created_at" => $rtcTimeWithZone,
                                "updated_at" => Carbon::now()
                            ];
                            $deviceTempLogData = DeviceTemperatureLog::create($deviceTempLogCreate);
                            $deviceTempLogId = $deviceTempLogData->id;

                            $deviceDashboardUpdate = [
                                "temperature" => $temperature,
                                "humidity" => $humidity,
                                "offline" => 0,
                                "voltage" => $vbv . ' V',
                                "rssi" => $rssi . ' dBm',
                                "last_seen" => $serverTime,
                                "updated_at" => Carbon::now()
                            ];
                            DeviceDashboard::where("device_id", $deviceId)->update($deviceDashboardUpdate);
                            $sensorInfo = [
                                "Temperature" => $temperature,
                                "Humidity" => $humidity,
                                "SN" => $deviceSN,
                                "VBV" => $vbv,
                                "device_id" => $deviceId,
                                "server_time" => $serverTime,
                                "rtc" => $rtcTimeWithZone,
                                "RSSI" => $rssi,
                                "IsLowVoltage" => $tagList['IsLowVoltage'],
                                "last_log_id" => $deviceTempLogId,
                            ];
                            //$sensorInfo = array_merge($tagList, $array);

                            $notificationFor = config('constants.notification_for');
                            $isNotNotify = true;

                            /* Temperature notification */

                            $temperatureData = Temperature::getDataByDevice($deviceSN);

                            if (!empty($temperatureData)) {
                                $isNotNotify = false;
                                $sensorInfo['notification_for'] = $notificationFor[0];
                                $this->generateAlarmNotification($temperatureData->toArray(), $sensorInfo);
                            }

                            $humidityData = Humidity::getDataByDevice($deviceSN);
                            if (!empty($humidityData)) {
                                $isNotNotify = false;
                                $sensorInfo['notification_for'] = $notificationFor[1];
                                $this->generateAlarmNotification($humidityData->toArray(), $sensorInfo);
                            }

                            $offlineData = Offline::getDataByDevice($deviceSN);
                            if (!empty($offlineData)) {
                                $isNotNotify = false;
                                $sensorInfo['notification_for'] = $notificationFor[2];
                                $this->generateAlarmNotification($offlineData->toArray(), $sensorInfo);
                            }

                            $voltageData = Voltage::getDataByDevice($deviceSN);
                            if (!empty($voltageData)) {
                                $isNotNotify = false;
                                $sensorInfo['notification_for'] = $notificationFor[3];
                                $this->generateAlarmNotification($voltageData->toArray(), $sensorInfo);
                            }

                            if ($isNotNotify) {
                                // Helpers::log("This Device $deviceSN is not found for notification");
                            } else {
                                // Helpers::log("This Device $deviceSN is found for notification");
                            }
                        } else {
                            //Helpers::log("Device $deviceSN not found");
                        }
                    }

                    return response()->json(["status" => 200, "show" => false, "msg" => "success"]);
                } else {
                    $data = ['flag' => 'tag'];
                    Helpers::sendMailAdmin($data, 'emails.sensor-error', 'Sensor update error');
                    Helpers::log('terminal info : tag list info');
                    return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
                }
            } else {
                $data = ['flag' => 'sensor'];
                Helpers::sendMailAdmin($data, 'emails.sensor-error', 'Sensor update error');
                Helpers::log('terminal info : something wrong');
                return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
            }
        } catch (\Exception $exception) {
            Helpers::log('terminal info : exception');
            Helpers::log($exception);
            $data = ['flag' => 'sensor'];
            Helpers::sendMailAdmin($data, 'emails.sensor-error', 'Sensor update error');
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    public function getTerminalInfo(Request $request)
    {
        try {
            $sensorData = $request->json()->all();

            Helpers::log('Sensor List : getTerminalInfo');
            Helpers::log($sensorData);

            if (is_array($sensorData) && !empty($sensorData)) {
                if (isset($sensorData['TagList_info']) && !empty($sensorData['TagList_info'])) {

                    $tagListInfo = $sensorData['TagList_info'];
                    $serverTime = $sensorData['ServerTime'];
                    $rtcTime = date('Y-m-d H:i:s', strtotime($sensorData['RTC']));

                    foreach ($tagListInfo as $key => $tagList) {
                        $deviceSN = $tagList['SN'];

                        $deviceData = Device::where('status', $this->actStatus)
                            ->where('device_sn', $deviceSN)
                            ->select('id','temp_adjustment','humidity_adjustment')
                            ->first();

                        if (!empty($deviceData)) {
                            $where = "id = (SELECT company_id FROM device WHERE device_sn = '$deviceSN' AND status = '" . $this->actStatus . "' LIMIT 1 )";
                            $companyData = User::whereRaw($where)->select('id', 'time_zone')->first();
                            $rtcTimeWithZone = $rtcTime;
                            if (!empty($companyData)) {
                                if (isset($companyData->time_zone) && !empty($companyData->time_zone)) {
                                    $rtcTimeWithZone = Carbon::createFromFormat('Y-m-d H:i:s', $rtcTime, 'UTC')->setTimezone($companyData->time_zone);
                                }
                            }

                            $deviceId = $deviceData->id;
                            $temperature = $tagList['Temperature'];
                            $humidity = $tagList['Humidity'];
                            $vbv = $tagList['VBV'];
                            $rssi = $tagList['RSSI'];

                            $adjustmentTemp = $deviceData->temp_adjustment;
                            $adjustmentHumidity = $deviceData->humidity_adjustment;
                            if($adjustmentTemp != '0'){
                                $temperature = $temperature+$adjustmentTemp;
                            }
                            if($adjustmentHumidity != '0'){
                                $humidity = $humidity+$adjustmentHumidity;
                            }


                            $deviceTempLogCreate = [
                                "uuid" => Helpers::getUuid(),
                                "device_id" => $deviceId,
                                "device_sn" => $deviceSN,
                                "temperature" => $temperature,
                                "humidity" => $humidity,
                                "rssi" => $rssi,
                                "vbv" => $vbv,
                                "is_low_voltage" => $tagList['IsLowVoltage'],
                                "servertime" => $serverTime,
                                "created_at" => $rtcTimeWithZone,
                                "updated_at" => Carbon::now()
                            ];
                            $deviceTempLogData = DeviceTemperatureLog::create($deviceTempLogCreate);
                            $deviceTempLogId = $deviceTempLogData->id;

                            $deviceDashboardUpdate = [
                                "temperature" => $temperature,
                                "humidity" => $humidity,
                                "offline" => 0,
                                "voltage" => $vbv . ' V',
                                "rssi" => $rssi . ' dBm',
                                "last_seen" => $serverTime,
                                "updated_at" => Carbon::now()
                            ];
                            DeviceDashboard::where("device_id", $deviceId)->update($deviceDashboardUpdate);
                            $sensorInfo = [
                                "Temperature" => $temperature,
                                "Humidity" => $humidity,
                                "SN" => $deviceSN,
                                "VBV" => $vbv,
                                "device_id" => $deviceId,
                                "server_time" => $serverTime,
                                "rtc" => $rtcTimeWithZone,
                                "RSSI" => $rssi,
                                "IsLowVoltage" => $tagList['IsLowVoltage'],
                                "last_log_id" => $deviceTempLogId,
                            ];
                            //$sensorInfo = array_merge($tagList, $array);

                            $notificationFor = config('constants.notification_for');
                            $isNotNotify = true;

                            /* Temperature notification */

                            $temperatureData = Temperature::getDataByDevice($deviceSN);
                            if (!empty($temperatureData)) {
                                $isNotNotify = false;
                                $sensorInfo['notification_for'] = $notificationFor[0];
                                $this->generateAlarmNotification($temperatureData->toArray(), $sensorInfo);
                            }

                            $humidityData = Humidity::getDataByDevice($deviceSN);
                            if (!empty($humidityData)) {
                                $isNotNotify = false;
                                $sensorInfo['notification_for'] = $notificationFor[1];
                                $this->generateAlarmNotification($humidityData->toArray(), $sensorInfo);
                            }

                            $offlineData = Offline::getDataByDevice($deviceSN);
                            if (!empty($offlineData)) {
                                $isNotNotify = false;
                                $sensorInfo['notification_for'] = $notificationFor[2];
                                $this->generateAlarmNotification($offlineData->toArray(), $sensorInfo);
                            }

                            $voltageData = Voltage::getDataByDevice($deviceSN);
                            if (!empty($voltageData)) {
                                $isNotNotify = false;
                                $sensorInfo['notification_for'] = $notificationFor[3];
                                $this->generateAlarmNotification($voltageData->toArray(), $sensorInfo);
                            }

                            if ($isNotNotify) {
                                // Helpers::log("This Device $deviceSN is not found for notification");
                            } else {
                                // Helpers::log("This Device $deviceSN is found for notification");
                            }
                        } else {
                            //Helpers::log("Device $deviceSN not found");
                        }
                    }

                    return response()->json(["status" => 200, "show" => false, "msg" => "success"]);
                } else {
                    $data = ['flag' => 'tag'];
                    Helpers::sendMailAdmin($data, 'emails.sensor-error', 'Sensor update error');
                    Helpers::log('terminal info : tag list info');
                    return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
                }
            } else {
                $data = ['flag' => 'sensor'];
                Helpers::sendMailAdmin($data, 'emails.sensor-error', 'Sensor update error');
                Helpers::log('terminal info : something wrong');
                return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
            }
        } catch (\Exception $exception) {
            Helpers::log('terminal info : exception');
            Helpers::log($exception);
            $data = ['flag' => 'sensor'];
            Helpers::sendMailAdmin($data, 'emails.sensor-error', 'Sensor update error');
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * 'TEMPRATUREHUMIDITY' for Temprature & Humidity
     * 'OFFLINEVOLTAGE' for Offline & Voltage
     *
     * i.e. temperature is  30
     * ORANGE  is setting to limit it will consider
     * LT: Low-temperature threshold: 32
     * HT: High-temperature threshold: 38
     *
     * RED is means danger
     * WLT: Low-temperature warning threshold: 30
     * WHT: High-temperature warning threshold: 40
     *
     * Case 1. Temperature is 30 then color would be Green ,
     *
     * Case 2. Temperature is 42 then color would be RED
     * Case 5. Temperature is 29 then color would be RED
     *
     * Case 3. Temperature is 31 then color would be ORANGE
     * Case 4. Temperature is 39 then color would be ORANGE
     *
     * 'danger' => '#FB0009'
     * 'warning' => '#FDB70B'
     * 'success' => '#3EA33F'
     * 'disable' => '#1659BA'
     *
     * How it will work. offline and voltage
     * 1. Offline Alarm
     * For the offline, we set min number of minutes if it is out of range that means we need to create Offline Alarm notification.
     * Offline case need to check out of range.
     *
     * 2. Voltage Alarm
     * For the Voltage alarm we need to check a minimum of received voltage on that time we need to create alarm.
     * In the voltage, alarm need to check below of range
     *
     * $notificationType[0] = temperature
     * $notificationType[1] = humidity
     * $notificationType[2] = offline
     * $notificationType[3] = voltage
     *
     * @param $deviceInfo
     * @param $sensorInfo
     */
    public function generateAlarmNotification($deviceInfo, $sensorInfo)
    {
        DB::beginTransaction();
        try {
            Helpers::log('Here to generate alarm');

            $deviceTemperature = $sensorInfo['Temperature'];
            $deviceHumidity = $sensorInfo['Humidity'];
            $deviceSN = $sensorInfo['SN'];
            $deviceVoltage = (float)$sensorInfo['VBV'];
            $notificationFor = $sensorInfo['notification_for'];
            $deviceId = $sensorInfo['device_id'];
            $serverTime = date("Y-m-d H:i:s", strtotime($sensorInfo['server_time']));
            $rtcTime = date("Y-m-d H:i:s", strtotime($sensorInfo['rtc']));
            $today = strtotime(date('Y-m-d'));
            $currentTime = strtotime(date('H:i:s'));

            $deviceName = $deviceInfo['d_device_name'];
            $alertTypeName = $deviceInfo['name'];
            $alertTypeId = $deviceInfo['id'];

            $effectiveStartDate = $deviceInfo['effective_start_date'];
            $effectiveEndDate = $deviceInfo['effective_end_date'];
            $effectiveStartTime = $deviceInfo['effective_start_time'];
            $effectiveEndTime = $deviceInfo['effective_end_time'];

            $startDate = strtotime($effectiveStartDate);
            $endDate = strtotime($effectiveEndDate);
            $startTime = strtotime($effectiveStartTime);
            $endTime = strtotime($effectiveEndTime);
            $repeat = explode(",", $deviceInfo['repeat']);
            $foundTotalDiffMinute = 0;

            /* if not today then */
            $alertColor = "";
            $alertTypeStatus = "";

            $notificationType = config('constants.notification_for');
            $alarmDisabledTime = config('constants.alarm_disabled_time');

            /**
             * if date current date is in between
             * check is enable or disable and if current date is enable ($getDeviceAlramInfo->effective_date_enable == '1') &&
             */

            if (($today >= $startDate && $today <= $endDate) && (in_array(lcfirst(date('l')), $repeat) && in_array('enable', $repeat)) && ($currentTime >= $startTime && $currentTime <= $endTime)) {
                $notificationMessage = "";

                /* only for temperature and humidity */
                if ($notificationFor == $notificationType[0] || $notificationFor == $notificationType[1]) {
                    $lowTempWarning = $deviceInfo['low_temp_warning'];
                    $highTempWarning = $deviceInfo['high_temp_warning'];

                    $lowTempThreshold = $deviceInfo['low_temp_threshold'];
                    $highTempThreshold = $deviceInfo['high_temp_threshold'];

                    /* get latest data from the log table */
                    $temperature = ($notificationFor == $notificationType[0]) ? $deviceTemperature : $deviceHumidity;

                    /**
                     * NOTE : PLEASE DO NOT TOUCH BELOW CODE WITHOUT PERMISSION
                     * GRAY Color ServerTime
                     */
                    $alertShine = "℃ ";
                    $temperatureReal = $deviceTemperature;
                    if ($notificationFor == $notificationType[1]) {
                        $alertShine = "% ";
                        $temperatureReal = $deviceHumidity;
                    }
                    $notificationMessage = ' “' . $deviceName . '”[' . $deviceSN . '] ' . ucfirst($notificationFor) . ' is ' . $temperatureReal . $alertShine;
                    $toTime = strtotime(date("Y-m-d H:i:s"));
                    $fromTime = strtotime($serverTime);
                    /* difference time in minutes */
                    $alertColor = Notification::DISABLECOLOR;
                    $alertTypeStatus = Notification::DISABLE;

                    if (round(abs($toTime - $fromTime) / 60, 2) >= $alarmDisabledTime) {
                        $alertColor = Notification::DISABLECOLOR;
                        $alertTypeStatus = Notification::DISABLE;

                    } elseif ($temperature >= $lowTempWarning && $temperature <= $highTempWarning) {
                        $alertColor = Notification::SUCCESSCOLOR;
                        $alertTypeStatus = Notification::SUCCESS;

                    } elseif (($temperature >= $lowTempThreshold && $temperature < $lowTempWarning) || ($temperature > $highTempWarning && $temperature <= $highTempThreshold)) {
                        $alertColor = Notification::WARNINGCOLOR;
                        $alertTypeStatus = Notification::WARNING;
                        if (($temperature >= $lowTempThreshold && $temperature < $lowTempWarning)) {
                            $notificationMessage .= ' Low ' . ucfirst($notificationFor) . ' alarm! ';
                        } else if (($temperature > $highTempWarning && $temperature <= $highTempThreshold)) {
                            $notificationMessage .= ' High ' . ucfirst($notificationFor) . ' alarm! ';
                        }
                    } elseif ($temperature < $highTempWarning || $temperature > $highTempThreshold) {
                        $alertColor = Notification::DANGERCOLOR;
                        $alertTypeStatus = Notification::DANGER;
                        if ($temperature < $highTempWarning) {
                            $notificationMessage .= ' Low ' . ucfirst($notificationFor) . ' Threshold alarm! ';
                        } else if ($temperature > $highTempThreshold) {
                            $notificationMessage .= ' High ' . ucfirst($notificationFor) . ' Threshold alarm! ';
                        }

                    }

                    $updateData = [
                        "updated_at" => Carbon::now()
                    ];
                    if ($notificationFor == $notificationType[0]) {
                        $updateData['temperature_color'] = $alertColor;
                        $updateData['temperature_alert'] = $alertTypeStatus;
                        $updateData['temperature'] = $sensorInfo['Temperature'];
                    } else {
                        $updateData['humidity_color'] = $alertColor;
                        $updateData['humidity_alert'] = $alertTypeStatus;
                        $updateData['humidity'] = $sensorInfo['Humidity'];
                    }
                    DeviceDashboard::where("device_id", $deviceId)->update($updateData);

                    $notificationMessage .= ' Alarm plan:“' . $alertTypeName . '”(ID:' . $alertTypeId . ') Start Date: ' . date('d-m-Y', strtotime($effectiveStartDate)) . ' End Date: ' . date('d-m-Y', strtotime($effectiveEndDate)) . ', TIME: ' . date('H:i', strtotime($effectiveStartTime)) . '~' . date('H:i', strtotime($effectiveEndTime)) . '.';

                }


                /* only for offline and voltage */
                if ($notificationFor == $notificationType[2] || $notificationFor == $notificationType[3]) {
                    if ($notificationFor == $notificationType[2]) {
                        $foundTotalDiffMinute = (int)round(((abs($currentTime - strtotime($serverTime)) / 3600) * 60));
                        $offlineLimit = (int)$deviceInfo['offline_time'];
                        if ($offlineLimit < $foundTotalDiffMinute) {
                            $alertColor = Notification::DISABLECOLOR; //"#1659BA"; //gray
                            $alertTypeStatus = Notification::DISABLE;

                            $updateData = [
                                "offline_color" => $alertColor,
                                'offline' => $foundTotalDiffMinute,
                                "updated_at" => Carbon::now()
                            ];
                            DeviceDashboard::where("device_id", $deviceId)->update($updateData);
                            $notificationMessage = ' “' . $deviceName . '”[' . $deviceSN . '] ' . ucfirst($notificationFor) . '(' . $foundTotalDiffMinute . 'Minutes) Alarm plan: “' . $alertTypeName . '(ID:' . $alertTypeId . ')” Start Date: ' . date('d-m-Y', strtotime($effectiveStartDate)) . ' End Date: ' . date('d-m-Y', strtotime($effectiveEndDate)) . ', TIME: ' . date('H:i', strtotime($effectiveStartTime)) . '~' . date('H:i', strtotime($effectiveEndTime)) . '.';
                        }
                    }

                    if ($notificationFor == $notificationType[3]) {
                        $lowVoltageValue = (float)$deviceInfo['low_voltage_value'];
                        if ($lowVoltageValue > $deviceVoltage) {
                            $alertColor = Notification::DANGERCOLOR;
                            $alertTypeStatus = Notification::DANGER;

                            $updateData = [
                                "voltage_color" => $alertColor,
                                'voltage_alert' => $alertTypeStatus,
                                'voltage' => $deviceVoltage . ' V',
                                "updated_at" => Carbon::now()
                            ];
                            DeviceDashboard::where("device_id", $deviceId)->update($updateData);
                            $notificationMessage = ' “' . $deviceName . '”[' . $deviceSN . '] ' . ucfirst($notificationFor) . '(' . $deviceVoltage . 'V) Alarm plan:“' . $alertTypeName . '(ID:' . $alertTypeId . ')” Start Date: ' . date('d-m-Y', strtotime($effectiveStartDate)) . ' End Date: ' . date('d-m-Y', strtotime($effectiveEndDate)) . ', TIME: ' . date('H:i', strtotime($effectiveStartTime)) . '~' . date('H:i', strtotime($effectiveEndTime)) . '.';
                        }
                    }
                }
                /* notification save & send */
                if($notificationFor == 'temperature'){
                    $this->dashboardHistoryUpdate($deviceInfo['d_company_id'], $deviceInfo['d_device_id'], $alertTypeStatus, $deviceTemperature, $deviceHumidity, $foundTotalDiffMinute, $deviceVoltage);
                    if($alertTypeStatus == Notification::DANGER){
                        $this->sendSMSUpdate($deviceInfo['d_company_id'], $deviceInfo['d_device_id'], $alertTypeStatus, $deviceTemperature, $deviceHumidity, $foundTotalDiffMinute, $deviceVoltage);
                    }
                }
                if (!empty($notificationMessage) && $alertTypeStatus != Notification::SUCCESS) {
                    $notificationItems = [
                        'company_id' => $deviceInfo['d_company_id'],
                        'terminal_id' => $deviceInfo['d_terminal_id'],
                        'device_id' => $deviceInfo['d_device_id'],
                        'device_name' => $deviceName,
                        'device_sn' => $deviceSN,
                        'temperature' => $deviceTemperature,
                        'humidity' => $deviceHumidity,
                        'offline' => $foundTotalDiffMinute,
                        'voltage' => $deviceVoltage,
                        'notification' => $notificationMessage,
                        'current_date_time' => Carbon::now(),
                        'notification_for' => $notificationFor,
                        'alerttype' => $alertTypeStatus,
                        'device_color' => $alertColor,
                    ];
                    Notification::sendNotification($notificationItems);
                }

                if ($alertTypeStatus != Notification::SUCCESS) {

                    $deviceTempLogUpdate = [
                        "device_id" => $deviceId,
                        "device_sn" => $deviceSN,
                        "temperature" => $deviceTemperature,
                        "humidity" => $deviceHumidity,
                        "rssi" => $sensorInfo['RSSI'],
                        "vbv" => $deviceVoltage,
                        "is_low_voltage" => $sensorInfo['IsLowVoltage'],
                        "notification_for" => $notificationFor,
                        "alerttype" => $alertTypeStatus,
                        "device_color" => $alertColor,
                        "servertime" => $serverTime,
                        "created_at" => $rtcTime,
                        "updated_at" => Carbon::now(),
                    ];
                    DeviceTemperatureLog::where('id', $sensorInfo['last_log_id'])->update($deviceTempLogUpdate);
                }

            }
            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('generate alarm notification exception');
            Helpers::log($sensorInfo);
            Helpers::log($exception);
        }
    }

    /**
     * Method use for save data from the api sensor
     *
     * $notificationType[0] = temperature
     * $notificationType[1] = humidity
     * $notificationType[2] = offline
     * $notificationType[3] = voltage
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function terminalDirect(Request $request)
    {
        //
        DB::beginTransaction();
        try {
            $sensorList = $request->json()->all();
            Helpers::log('Sensor List');
            Helpers::log($sensorList);

            foreach ($sensorList as $key => $value) {
                if (isset($value['timestamp']) && isset($value['mac']) && isset($value['rssi']) && isset($value['temperature']) && isset($value['humidity'])) {
                    $rtcTime = date('Y-m-d H:i:s', strtotime($value['timestamp']));
                    $serverTime = $value['timestamp'];
                    $deviceSN = $value['mac'];

                    $deviceData = Device::where('status', $this->actStatus)
                        ->where('device_sn', $deviceSN)
                        ->select('id','temp_adjustment','humidity_adjustment')
                        ->first();

                    if (!empty($deviceData)) {
                        $where = "id = (SELECT company_id FROM device WHERE device_sn = '$deviceSN' AND status = '" . $this->actStatus . "' LIMIT 1 )";
                        $companyData = User::whereRaw($where)->select('id', 'time_zone')->first();
                        $rtcTimeWithZone = $rtcTime;
                        if (!empty($companyData)) {
                            if (isset($companyData->time_zone) && !empty($companyData->time_zone)) {
                                $rtcTimeWithZone = Carbon::createFromFormat('Y-m-d H:i:s', $rtcTime, 'UTC')->setTimezone($companyData->time_zone);
                            }
                        }

                        $deviceId = $deviceData->id;
                        $temperature = $value['temperature'];
                        $humidity = $value['humidity'];
                        $vbv = 0;
                        $rssi = $value['rssi'];

                        $adjustmentTemp = $deviceData->temp_adjustment;
                        $adjustmentHumidity = $deviceData->humidity_adjustment;
                        if($adjustmentTemp != '0'){
                            $temperature = $temperature+$adjustmentTemp;
                        }
                        if($adjustmentHumidity != '0'){
                            $humidity = $humidity+$adjustmentHumidity;
                        }

                        $deviceTempLogCreate = [
                            "uuid" => Helpers::getUuid(),
                            "device_id" => $deviceId,
                            "device_sn" => $deviceSN,
                            "temperature" => $temperature,
                            "humidity" => $humidity,
                            "rssi" => $rssi,
                            "vbv" => $vbv,
                            "is_low_voltage" => 0,
                            "servertime" => $serverTime,
                            "created_at" => $rtcTimeWithZone,
                            "updated_at" => Carbon::now()
                        ];
                        $deviceTempLogData = DeviceTemperatureLog::create($deviceTempLogCreate);
                        $deviceTempLogId = $deviceTempLogData->id;

                        $deviceDashboardUpdate = [
                            "temperature" => $temperature,
                            "humidity" => $humidity,
                            "offline" => 0,
                            "voltage" => $vbv . ' V',
                            "rssi" => $rssi . ' dBm',
                            "last_seen" => $serverTime,
                            "updated_at" => Carbon::now()
                        ];
                        DeviceDashboard::where("device_id", $deviceId)->update($deviceDashboardUpdate);

                        $sensorInfo = [
                            "Temperature" => $temperature,
                            "Humidity" => $humidity,
                            "SN" => $deviceSN,
                            "VBV" => $vbv,
                            "device_id" => $deviceId,
                            "server_time" => $serverTime,
                            "rtc" => $rtcTimeWithZone,
                            "RSSI" => $rssi,
                            "IsLowVoltage" => 0,
                            "last_log_id" => $deviceTempLogId,
                        ];

                        $notificationFor = config('constants.notification_for');
                        $isNotNotify = true;

                        /* Temperature notification */
                        $temperatureData = Temperature::getDataByDevice($deviceSN);
                        if (!empty($temperatureData)) {
                            $isNotNotify = false;
                            $sensorInfo['notification_for'] = $notificationFor[0];
                            $this->generateAlarmNotification($temperatureData->toArray(), $sensorInfo);

                        }

                        $humidityData = Humidity::getDataByDevice($deviceSN);
                        if (!empty($humidityData)) {
                            $isNotNotify = false;
                            $sensorInfo['notification_for'] = $notificationFor[1];
                            $this->generateAlarmNotification($humidityData->toArray(), $sensorInfo);
                        }

                        if ($isNotNotify) {
                            Helpers::log("API - This Device $deviceSN is not found for notification");
                        }
                    } else {
                        Helpers::log("API - Device $deviceSN not found");
                    }
                } else if (isset($value['timestamp']) && isset($value['mac']) && isset($value['rssi']) && isset($value['rawData'])) {
                    $rtcTime = date('Y-m-d H:i:s', strtotime($value['timestamp']));
                    $serverTime = $value['timestamp'];
                    $deviceSN = $value['mac'];


                    $deviceData = Device::where('status', $this->actStatus)
                        ->where('device_sn', $deviceSN)
                        ->select('id','temp_adjustment','humidity_adjustment')
                        ->first();

                    if (!empty($deviceData)) {
                        $where = "id = (SELECT company_id FROM device WHERE device_sn = '$deviceSN' AND status = '" . $this->actStatus . "' LIMIT 1 )";
                        $companyData = User::whereRaw($where)->select('id', 'time_zone')->first();
                        $rtcTimeWithZone = $rtcTime;
                        if (!empty($companyData)) {
                            if (isset($companyData->time_zone) && !empty($companyData->time_zone)) {
                                $rtcTimeWithZone = Carbon::createFromFormat('Y-m-d H:i:s', $rtcTime, 'UTC')->setTimezone($companyData->time_zone);
                            }
                        }

                        $deviceId = $deviceData->id;
                        $rawDatass = $value['rawData'];

                        $cutdata = str_split($rawDatass, 2);
                        $humidity_cut = hexdec($cutdata[13]);
                        $x_cut = $cutdata[8] . $cutdata[7]; //gives 0AC9
                        $c_cut = hexdec($x_cut);
                        if ($c_cut > 32767) {
                            $negativeVal = intval(65535) - intval($c_cut);
                            $temp_cut = $negativeVal / 100;
                            $temp_cut = '-' . $temp_cut;
                        } else {
                            $temp_cut = $c_cut / 100;
                        }
                        /*Helpers::log("------------------------$deviceSN-------------------------");
                        Helpers::log("Raw Data : ".$value['rawData']);
                        Helpers::log("CUT OF 8  ".hexdec($cutdata[8]));
                        Helpers::log("CUT OF 7 ".hexdec($cutdata[7]));
                        Helpers::log("Mix cut ".hexdec($x_cut));
                        Helpers::log("------------------------$deviceSN-------------------------");*/
                        //gives 2761
                        /*foreach ($cutdata as $cutter) {
                            Helpers::log("API - humidity_cut ".hexdec($cutter));
                        }
                        */


                        $temperature = $temp_cut;
                        $humidity = $humidity_cut;

                        $vbv = 0;
                        $rssi = $value['rssi'];

                        $adjustmentTemp = $deviceData->temp_adjustment;
                        $adjustmentHumidity = $deviceData->humidity_adjustment;
                        if($adjustmentTemp != '0'){
                            $temperature = $temperature+$adjustmentTemp;
                        }
                        if($adjustmentHumidity != '0'){
                            $humidity = $humidity+$adjustmentHumidity;
                        }

                        $deviceTempLogCreate = [
                            "uuid" => Helpers::getUuid(),
                            "device_id" => $deviceId,
                            "device_sn" => $deviceSN,
                            "temperature" => $temperature,
                            "humidity" => $humidity,
                            "rssi" => $rssi,
                            "vbv" => $vbv,
                            "is_low_voltage" => 0,
                            "servertime" => $serverTime,
                            "created_at" => $rtcTimeWithZone,
                            "updated_at" => Carbon::now()
                        ];
                        $deviceTempLogData = DeviceTemperatureLog::create($deviceTempLogCreate);
                        $deviceTempLogId = $deviceTempLogData->id;

                        $deviceDashboardUpdate = [
                            "temperature" => $temperature,
                            "humidity" => $humidity,
                            "offline" => 0,
                            "voltage" => $vbv . ' V',
                            "rssi" => $rssi . ' dBm',
                            "last_seen" => $serverTime,
                            "updated_at" => Carbon::now()
                        ];
                        DeviceDashboard::where("device_id", $deviceId)->update($deviceDashboardUpdate);

                        $sensorInfo = [
                            "Temperature" => $temperature,
                            "Humidity" => $humidity,
                            "SN" => $deviceSN,
                            "VBV" => $vbv,
                            "device_id" => $deviceId,
                            "server_time" => $serverTime,
                            "rtc" => $rtcTimeWithZone,
                            "RSSI" => $rssi,
                            "IsLowVoltage" => 0,
                            "last_log_id" => $deviceTempLogId,
                        ];

                        $notificationFor = config('constants.notification_for');
                        $isNotNotify = true;

                        /* Temperature notification */
                        $temperatureData = Temperature::getDataByDevice($deviceSN);

                        if (!empty($temperatureData)) {
                            $isNotNotify = false;
                            $sensorInfo['notification_for'] = $notificationFor[0];
                            $this->generateAlarmNotification($temperatureData->toArray(), $sensorInfo);

                        }

                        $humidityData = Humidity::getDataByDevice($deviceSN);
                        if (!empty($humidityData)) {
                            $isNotNotify = false;
                            $sensorInfo['notification_for'] = $notificationFor[1];
                            $this->generateAlarmNotification($humidityData->toArray(), $sensorInfo);
                        }

                        if ($isNotNotify) {
                            Helpers::log("API - This Device $deviceSN is not found for notification");
                        }
                    } else {
                        Helpers::log("API - Device $deviceSN not found");
                    }
                } else {
                    Helpers::log("invalid array");
                }
            }

            DB::commit();
            return response()->json(["status" => 200, "show" => true, "msg" => "success"]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('generate alarm notification exception by api');
        }
    }

    public function testConversion(Request $request)
    {
        $rawdata = '02010605166E2AADF904166F2A370D09502052485420393030333531';
        $cutdata = str_split($rawdata, 2);
        foreach ($cutdata as $cuter) {
            echo "<pre>";
            print_r(hexdec($cuter));
            echo "</pre>";
        }

    }

    public function teltonicaDirect(Request $request)
    {
        DB::beginTransaction();
        try {
            $sensorList = $request->json()->all();
            Helpers::log('Sensor List');
            Helpers::log($sensorList);
            $readings = $sensorList['readings'];
            foreach ($readings as $sensorData) {
                if (isset($sensorData['time']) && isset($sensorData['sensor']) && isset($sensorData['temperature']) && isset($sensorData['humidity'])) {
                    $rtcTime = date('Y-m-d H:i:s', strtotime($sensorData['time']));
                    $serverTime = $sensorData['time'];
                    $deviceSN = $sensorData['sensor'];


                    $deviceData = Device::where('status', $this->actStatus)
                        ->where('device_sn', $deviceSN)
                        ->select('id','temp_adjustment','humidity_adjustment')
                        ->first();

                    if (!empty($deviceData)) {
                        $where = "id = (SELECT company_id FROM device WHERE device_sn = '$deviceSN' AND status = '" . $this->actStatus . "' LIMIT 1 )";
                        $companyData = User::whereRaw($where)->select('id', 'time_zone')->first();
                        $rtcTimeWithZone = $rtcTime;
                        if (!empty($companyData)) {
                            if (isset($companyData->time_zone) && !empty($companyData->time_zone)) {
                                $rtcTimeWithZone = Carbon::createFromFormat('Y-m-d H:i:s', $rtcTime, 'UTC')->setTimezone($companyData->time_zone);
                            }
                        }

                        $deviceId = $deviceData->id;
                        $temperature = $sensorData['temperature'];
                        $humidity = $sensorData['humidity'];
                        $vbv = $sensorData['external_volts'];
                        $rssi = '';

                        $adjustmentTemp = $deviceData->temp_adjustment;
                        $adjustmentHumidity = $deviceData->humidity_adjustment;
                        if($adjustmentTemp != '0'){
                            $temperature = $temperature+$adjustmentTemp;
                        }
                        if($adjustmentHumidity != '0'){
                            $humidity = $humidity+$adjustmentHumidity;
                        }

                        $deviceTempLogCreate = [
                            "uuid" => Helpers::getUuid(),
                            "device_id" => $deviceId,
                            "device_sn" => $deviceSN,
                            "temperature" => $temperature,
                            "humidity" => $humidity,
                            "rssi" => $rssi,
                            "vbv" => $vbv,
                            "is_low_voltage" => 0,
                            "servertime" => $serverTime,
                            "created_at" => $rtcTimeWithZone,
                            "updated_at" => Carbon::now()
                        ];
                        $deviceTempLogData = DeviceTemperatureLog::create($deviceTempLogCreate);
                        $deviceTempLogId = $deviceTempLogData->id;

                        $deviceDashboardUpdate = [
                            "temperature" => $temperature,
                            "humidity" => $humidity,
                            "offline" => 0,
                            "voltage" => $vbv . ' V',
                            "rssi" => $rssi . ' dBm',
                            "last_seen" => $serverTime,
                            "updated_at" => Carbon::now()
                        ];
                        DeviceDashboard::where("device_id", $deviceId)->update($deviceDashboardUpdate);

                        $sensorInfo = [
                            "Temperature" => $temperature,
                            "Humidity" => $humidity,
                            "SN" => $deviceSN,
                            "VBV" => $vbv,
                            "device_id" => $deviceId,
                            "server_time" => $serverTime,
                            "rtc" => $rtcTimeWithZone,
                            "RSSI" => $rssi,
                            "IsLowVoltage" => 0,
                            "last_log_id" => $deviceTempLogId,
                        ];

                        $notificationFor = config('constants.notification_for');
                        $isNotNotify = true;

                        /* Temperature notification */
                        $temperatureData = Temperature::getDataByDevice($deviceSN);
                        if (!empty($temperatureData)) {
                            $isNotNotify = false;
                            $sensorInfo['notification_for'] = $notificationFor[0];
                            $this->generateAlarmNotification($temperatureData->toArray(), $sensorInfo);
                        }

                        $humidityData = Humidity::getDataByDevice($deviceSN);
                        if (!empty($humidityData)) {
                            $isNotNotify = false;
                            $sensorInfo['notification_for'] = $notificationFor[1];
                            $this->generateAlarmNotification($humidityData->toArray(), $sensorInfo);
                        }

                        if ($isNotNotify) {
                            Helpers::log("API - This Device $deviceSN is not found for notification");
                        }
                    } else {
                        Helpers::log("API - Device $deviceSN not found");
                    }
                } else {
                    Helpers::log("invalid array");
                }
            }
            DB::commit();
            return response()->json(["status" => 200, "show" => true, "msg" => "success"]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('generate alarm notification exception by api');
        }
    }

    public function testDashboardHistory()
    {
        $companyId = 2;
        $device_id = 20;
        $alertTypeStatus = 'DANGER';
        $deviceTemperature = '48.00';
        $deviceHumidity = '55.00';
        $foundTotalDiffMinute = '0';
        $deviceVoltage = '10';

        $this->dashboardHistoryUpdate($companyId, $device_id, $alertTypeStatus, $deviceTemperature, $deviceHumidity, $foundTotalDiffMinute, $deviceVoltage);
    }

    public function dashboardHistoryUpdate($company_id, $device_id, $alertTypeStatus, $deviceTemperature, $deviceHumidity, $foundTotalDiffMinute, $deviceVoltage)
    {
        /*$checkEntry = DashboardUpdateHistory::where('dh_company_id', $company_id)->where('dh_device_id', $device_id)->first();

        $isSendMail = 0;
        $isMakeEntry = 0;
        if (!empty($checkEntry)) {
            if ($checkEntry->dh_sensor_status != $alertTypeStatus) {
                $isSendMail = 1;
            }
        } else {
            $isSendMail = 1;
            $isMakeEntry = 1;
        }
        $getCompanyAcc = User::where('id',$company_id)->first();
        if(!empty($getCompanyAcc)){
            $device = Device::where('id', $device_id)->first();
            if ($isSendMail == 1) {
                $alertcolor = '#CCC';
                if ($alertTypeStatus == 'DANGER') {
                    $alertcolor = '#ff0000';
                } else if ($alertTypeStatus == 'WARNING') {
                    $alertcolor = '#ffff00';
                } else if ($alertTypeStatus == 'SUCCESS') {
                    $alertcolor = '#008000';
                }
                Helpers::log('Sending email for ' . $device->device_name . ' - ' . $alertTypeStatus);
                $data = [
                    'device' => $device,
                    'alert' => $alertTypeStatus,
                    'alertcolor' => $alertcolor,
                    'temp' => $deviceTemperature,
                    'humidity' => $deviceHumidity,
                    'offline' => $foundTotalDiffMinute,
                    'voltage' => $deviceVoltage,
                ];
                $fromEmail = config('constants.from_email');
                $fromName = config('constants.from_name');
                $subject = env('APP_NAME') . " - " . $device->device_name . ' Falls in to ' . $alertTypeStatus . ' state';
                //$toEmail = 'kashyap.waytoweb@gmail.com';
                $toEmail = $getCompanyAcc->email;
                $dResult = view('emails.sensorChangeUpdate',compact('data'));
                echo $dResult;

                if ($isMakeEntry == 1) {
                    $insertLog = [
                        'dh_uuid' => Helpers::getUuid(),
                        'dh_company_id' => $company_id,
                        'dh_device_id' => $device_id,
                        'dh_sensor_status' => $alertTypeStatus,
                        'dh_sensor_temp' => $deviceTemperature,
                        'dh_sensor_humidity' => $deviceHumidity,
                        'dh_last_updated' => Carbon::now()
                    ];
                    DashboardUpdateHistory::insert($insertLog);
                } else {
                    $updateLog = [
                        'dh_sensor_status' => $alertTypeStatus,
                        'dh_sensor_temp' => $deviceTemperature,
                        'dh_sensor_humidity' => $deviceHumidity,
                        'dh_last_updated' => Carbon::now()
                    ];
                    DashboardUpdateHistory::where('dh_company_id', $company_id)->where('dh_device_id', $device_id)->update($updateLog);
                }

                Mail::send('emails.sensorChangeUpdate', ['data' => $data], function ($m) use ($fromName, $fromEmail, $subject, $toEmail) {
                    $m->from($fromEmail, 'i-Sense');
                    $m->to($toEmail)->subject($subject);
                });
                if (Mail::failures()) {
                    Helpers::log('Mail Sending Error');
                    Helpers::log(Mail::failures());
                }
            } else {
                Helpers::log('Not Sending email for ' . $device->device_name . ' - ' . $alertTypeStatus);
            }
        }
        else{
            Helpers::log('Not Sending email for because company not identified');
        }*/
    }
    public function sendSMSUpdate($company_id, $device_id, $alertTypeStatus, $deviceTemperature, $deviceHumidity, $foundTotalDiffMinute, $deviceVoltage){
        Helpers::log('Sending SMS For Danger Device Fall');
        $getCompanyAcc = User::where('id',$company_id)->first();
        if(!empty($getCompanyAcc)){
            $device = Device::where('id', $device_id)->first();
            if(!empty($device)){
                $name = $getCompanyAcc->first_name.' '.$getCompanyAcc->last_name;
                $msg = 'Hello ' . $name . ', Your sensor '.$device->device_name.' ('.$device->device_sn.') is falling in Danger State';
                $encodeMsg = urlencode(utf8_encode($msg));
                $encodeMsg = str_replace('+', '%20', $encodeMsg);
                $numberRec = $getCompanyAcc->phone;
                $numberRec = '+919898955075';
                $numberRec = str_replace('+', '', $numberRec);
                $checkSms = Helpers::sendSms($numberRec, $encodeMsg);
                $msgResponse = json_decode($checkSms);
                Helpers::log($msgResponse);
            }
        }

    }
}
