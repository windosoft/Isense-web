<?php

namespace App\Http\Controllers\Api;

use App\Models\DeviceDashboard;
use App\Models\Helpers;
use App\Models\Notification;
use App\Models\NotificationDetail;
use App\Models\Roles;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    protected $actStatus = '';
    protected $unread = '';
    protected $read = '';
    protected $employee = 0;

    /**
     * BranchController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
        $this->unread = Helpers::UNREAD;
        $this->read = Helpers::READ;
        $this->employee = Roles::$employee;
    }

    /**
     * Notification list
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function notificationFilter(Request $request)
    {
        Helpers::log('notification filter : start');
        try {
            $companyId = $request->company_id;
            if (empty($companyId)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Company id must be required"]);
            } else {
                $userId = Auth::user()->id;
                $condition = [
                    "notification_detail.user_id" => $userId,
                    'N.company_id' => $companyId,
                    'D.status' => $this->actStatus
                ];
                if (isset($request->type) && empty($request->type)) {
                    $condition['N.alerttype'] = $request->type;
                }
                if (isset($request->terminal_id) && empty($request->terminal_id)) {
                    $condition['N.terminal_id'] = $request->terminal_id;
                }
                if (isset($request->device_id) && empty($request->device_id)) {
                    $condition['N.device_id'] = $request->device_id;
                }

                $toDay = Carbon::today();
                $notificationList = NotificationDetail::join('notification AS N', 'N.uuid', '=', 'notification_detail.notification_uuid')
                    ->join('device AS D', 'N.device_id', '=', 'D.id')
                    ->whereDate('N.current_date_time', $toDay)
                    ->where($condition)
                    ->select(
                        'notification_detail.uuid', 'N.company_id', 'N.terminal_id', 'N.device_id', 'N.temperature', 'N.temperature',
                        'N.offline', 'N.voltage', 'N.notification', 'N.current_date_time', 'N.notification_for',
                        'N.alerttype', 'N.device_color', 'notification_detail.read_status', 'N.status', 'notification_detail.created_at',
                        'notification_detail.updated_at', 'D.device_name',
                        DB::raw("(SELECT time_zone FROM users AS U WHERE U.id = (SELECT company_id FROM device WHERE device.id = D.id)) AS company_time_zone")
                    )
                    ->orderby('notification_detail.created_at', 'DESC')
                    ->limit(200)
                    ->get()->toArray();

                foreach ($notificationList as $key => $value) {
                    $alertType = $value['alerttype'];
                    $alertName = Notification::DISABLE_NAME;
                    if ($alertType == Notification::WARNING) {
                        $alertName = Notification::WARNING_NAME;
                    } elseif ($alertType == Notification::SUCCESS) {
                        $alertName = Notification::SUCCESS_NAME;
                    } elseif ($alertType == Notification::DANGER) {
                        $alertName = Notification::DANGER_NAME;
                    }
                    $notificationList[$key]['alert_name'] = $alertName;

                    $timezone = new \DateTimeZone($value['company_time_zone']);
                    $createdAt = new \DateTime($value['created_at'], $timezone);
                    $notificationList[$key]['created_at'] = $createdAt->format('d-m-Y / H:i');
                }

                $response = Helpers::replaceNullWithEmptyString($notificationList);
                Helpers::log('notification filter : finish');
                return response()->json(["status" => 200, "show" => false, "msg" => "success", "data" => $response]);
            }
        } catch (\Exception $exception) {
            Helpers::log('notification filter : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * Real time device data by company id, type, terminal id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function realtimeDeviceData(Request $request)
    {
        Helpers::log('Real time device data : start');
        try {
            $companyId = $request->company_id;
            if (empty($companyId)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Company id must be required"]);
            } else {
                $where = "device_dashboard.company_id = $companyId AND D.status = '" . $this->actStatus . "' AND D.deleted_at IS NULL";
                if (isset($request->type) && !empty($request->type)) {
                    $where .= " AND device_dashboard.temperature_alert = '" . $request->type . "'";
                }
                if (isset($request->terminal_id) && !empty($request->terminal_id)) {
                    $where .= " AND device_dashboard.terminal_id = '" . $request->terminal_id . "'";
                }
                $userData = Auth::user();
                if ($userData->role_id == $this->employee) {
                    $where .= " AND (SELECT COUNT(G.id) AS count FROM groups AS G WHERE G.status = '" . $this->actStatus . "' AND G.deleted_at IS NULL AND G.id = '" . $userData->group_id . "' AND FIND_IN_SET(D.id, G.device_ids ) ) > 0 ";
                }

                $realDeviceList = DeviceDashboard::join('device AS D', 'D.id', '=', 'device_dashboard.device_id')
                    ->select(
                        'D.*',
                        'device_dashboard.*',
                        'device_dashboard.voltage as vbv',
                        'device_dashboard.last_seen as servertime',
                        DB::raw("(SELECT time_zone FROM users WHERE users.id = device_dashboard.company_id) AS company_time_zone"),
                        DB::raw("(SELECT updated_at FROM device_temperature_log WHERE device_temperature_log.device_id = device_dashboard.device_id ORDER BY id DESC LIMIT 1) AS log_last_time")
                    )
                    ->whereRaw($where)
                    ->orderBy('device_dashboard.id', 'DESC')
                    ->get()->toArray();
                $currentTime = strtotime(date('H:i:s'));

                foreach ($realDeviceList as $key => $realData) {
                    $serverTime = date("Y-m-d H:i:s", strtotime($realData['updated_at']));
                    $foundTotalDiffMinute = (int)round(((abs($currentTime - strtotime($serverTime)) / 3600) * 60));
                    $dataInterval = (int)$realData['data_interval'];

                    $isGray = true;
                    if ($dataInterval > $foundTotalDiffMinute) {
                        $isGray = false;
                    }
                    $realDeviceList[$key]['showGreyColor'] = $isGray;

                    $timezone = new \DateTimeZone($realData['company_time_zone']);
                    $createdAt = new \DateTime($realData['created_at'], $timezone);
                    $updatedAt = new \DateTime($realData['log_last_time'], $timezone);
                    $lastSeen = new \DateTime($realData['last_seen'], $timezone);
                    $serverTime = new \DateTime($realData['servertime'], $timezone);

                    $realDeviceList[$key]['created_at'] = $createdAt->format('d-m-Y / H:i');
                    //$realDeviceList[$key]['updated_at'] = $updatedAt->format('d-m-Y / H:i');
                    $realDeviceList[$key]['updated_at'] = date('d-m-Y / H:i',strtotime($realData['log_last_time']));
                    $realDeviceList[$key]['last_seen'] = $lastSeen->format('d-m-Y / H:i');
                    $realDeviceList[$key]['servertime'] = $serverTime->format('d-m-Y / H:i');

                    $realDeviceList[$key]['offline_color'] = (empty($realData['offline_color'])) ? Notification::DISABLECOLOR : $realData['offline_color'];
                    $realDeviceList[$key]['offline_alert'] = (empty($realData['offline_alert'])) ? Notification::DISABLE : $realData['offline_alert'];
                    $realDeviceList[$key]['voltage_color'] = (empty($realData['voltage_color'])) ? Notification::DISABLECOLOR : $realData['voltage_color'];
                    $realDeviceList[$key]['voltage_alert'] = (empty($realData['voltage_alert'])) ? Notification::DISABLE : $realData['voltage_alert'];
                }

                $response = Helpers::replaceNullWithEmptyString($realDeviceList);
                Helpers::log('Real time device data : finish');
                return response()->json(["status" => 200, "show" => false, "msg" => "success", "data" => $response]);
            }
        } catch (\Exception $exception) {
            Helpers::log('Real time device data : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * Read all notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function readAllNotification(Request $request)
    {
        DB::beginTransaction();
        Helpers::log('read all notification : start');
        try {
            $companyId = $request->company_id;
            if (empty($companyId)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Company id must be required"]);
            } else {
                $userId = Auth::user()->id;

                $updateData = [
                    "read_status" => $this->read,
                    "deleted_at" => Carbon::now()
                ];
                NotificationDetail::where('user_id', $userId)->update($updateData);

                Helpers::log('read all notification : finish');
                DB::commit();
                return response()->json(["status" => 200, "show" => true, "msg" => "All notification has been successfully cleared."]);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('read all notification : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * Read single notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function singleNotificationRead(Request $request)
    {
        DB::beginTransaction();
        Helpers::log('read single notification : start');
        try {
            $notificationUuid = $request->notification_uuid;
            if (empty($notificationUuid)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "notification must be required"]);
            } else {
                $userId = Auth::user()->id;

                $updateData = [
                    "read_status" => $this->read,
                    "updated_at" => Carbon::now()
                ];
                NotificationDetail::where('user_id', $userId)
                    ->where('uuid', $notificationUuid)
                    ->update($updateData);

                Helpers::log('read single notification : finish');
                DB::commit();
                return response()->json(["status" => 200, "show" => false, "msg" => "success"]);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('read single notification : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * notification sound on off
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function notificationSound(Request $request)
    {
        DB::beginTransaction();
        try {
            $sound = 0;
            if (!empty($request->sound)) {
                $sound = 1;
            }
            $userId = Auth::user()->id;

            User::where('id', $userId)->update(["notify_sound" => $sound]);

            DB::commit();
            return response()->json(["status" => 200, "show" => false, "msg" => "success", "notify_sound" => $sound]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('notification sound : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }
}
