<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Helpers extends Model
{
    static $active = 'A';
    static $inactive = 'I';
    static $delete = 'D';
    const UNREAD = 'unread';
    const READ = 'read';

    static function getUuid()
    {
        $query = DB::select('select uuid() AS uuid');

        return $query[0]->uuid;
    }

    static function log($message)
    {
        Log::info($message);
    }

    static function slugify($string)
    {
        $string = utf8_encode($string);
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^a-z0-9- ]/i', '', $string);
        $string = str_replace(' ', '-', $string);
        $string = trim($string, '-');
        $string = strtolower($string);
        if (empty($string)) {
            return 'n-a';
        }
        return $string;
    }

    static function replaceNullWithEmptyString($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value))
                $array[$key] = self::replaceNullWithEmptyString($value);
            else {
                if (is_null($value))
                    $array[$key] = "";
            }
        }
        return $array;
    }

    static function randomString($n)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString . time();
    }

    static function deleteLogFile()
    {
        $currentDate = date('Y-m-d');
        $previousDate = date('Y-m-d', strtotime('-3 day', strtotime($currentDate)));
        $dir = base_path('storage/logs');
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    $array = ['.', '..', '.gitignore'];
                    if (!in_array($file, $array)) {
                        $fileName = basename($file, ".log");
                        $dateSplit = explode('laravel-', $fileName);
                        if (isset($dateSplit[1])) {
                            $date = $dateSplit[1];
                            if ($date <= $previousDate) {
                                $path = $dir . '/' . $file;
                                unlink($path);
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    static function userDetail($userId)
    {
        $userData = User::leftjoin('device AS D', 'D.id', 'users.device_id')
            ->leftjoin('terminals AS T', 'T.id', 'D.terminal_id')
            ->where('users.id', $userId)
            ->select('users.*', 'D.terminal_id', 'T.department_id', 'T.branch_id')
            ->first();

        $userDetail = [];
        if (!empty($userData)) {
            $userData->profile = url($userData->profile);
            $userDetail = $userData->toArray();
        }
        return $userDetail;
    }

    static function sendMailAdmin($data, $filename, $subject)
    {
        try {
            $fromEmail = config('constants.from_email');
            $fromName = config('constants.from_name');
            $toEmail = config('constants.admin_email');
            $subject = env('APP_NAME') . " - " . $subject;
            Mail::send($filename, ['data' => $data], function ($m) use ($fromName, $fromEmail, $subject, $toEmail) {
                $m->from($fromEmail, $fromName);
                $m->to($toEmail)->subject($subject);
            });

        } catch (\Exception $exception) {
            self::log($subject . ' : exception');
            self::log($exception);
        }
    }

    static function sendMailUser($data, $filename, $subject, $toEmail)
    {
        self::log('Sending emails to user');
        try {

            $fromEmail = config('constants.from_email');
            $fromName = config('constants.from_name');
            $subject = env('APP_NAME') . " - " . $subject;
            Mail::send($filename, ['data' => $data], function ($m) use ($fromName, $fromEmail, $subject, $toEmail) {
                $m->from($fromEmail, $fromName);
                $m->to($toEmail)->subject($subject);
            });
            self::log('----Logging Failures of email---------------');
            self::log(Mail::failures());
            if( count(Mail::failures()) > 0 ) {
                self::log(Mail::failures);
                /*foreach(Mail::failures as $email_address) {
                    echo "$email_address <br />";
                }*/

            } else {
                self::log("Mail sent successfully!");
            }

        } catch (\Exception $exception) {
            self::log($subject . ' : exception');
            self::log($exception);
        }
    }

    static function timeZoneList()
    {
        return timezone_identifiers_list();
    }

    static function isCompany()
    {
        $isCompany = false;
        $userRole = Auth::user()->role_id;
        if (Roles::$company == $userRole) {
            $isCompany = true;
        } elseif (Roles::$employee == $userRole) {
            $isCompany = true;
        }

        return $isCompany;
    }

    static function companyId()
    {
        $userData = Auth::user();
        $companyId = $userData->id;
        if ($userData->role_id == Roles::$employee) {
            $companyId = $userData->company_id;
        }
        return $companyId;
    }

    static function companyName($companyId)
    {
        $companyData = User::where('id', $companyId)
            ->select('first_name')->first();

        return $companyData->first_name;
    }

    static function selectBoxCompanyList()
    {
        $companyList = User::where('role_id', Roles::$company)
            ->where('status', self::$active)
            ->select('id', 'first_name', 'email')
            ->orderBy('first_name', 'ASC')
            ->get()->toArray();

        return $companyList;
    }

    static function selectBoxBranchListByCompany($companyId)
    {
        $branchList = Branches::where('company_id', $companyId)
            ->where('status', self::$active)
            ->select('id', 'name')
            ->get()->toArray();

        return $branchList;
    }

    static function selectBoxGatewayListByBranch($branch_id)
    {
        $gatewayList = Terminals::where('branch_id', $branch_id)
            ->where('status', self::$active)
            ->select('id', 'name')
            ->get()->toArray();

        return $gatewayList;
    }

    static function selectBoxDeviceListByCompany($companyId)
    {
        $userData = Auth::user();
        $active = self::$active;
        $where = "company_id = $companyId AND status = '$active' ";

        if ($userData->role_id == Roles::$employee) {
            $where .= " AND (SELECT COUNT(G.id) AS count FROM groups AS G WHERE G.status = '$active' AND G.deleted_at IS NULL AND G.id = '" . $userData->group_id . "' AND FIND_IN_SET(device.id, G.device_ids ) ) > 0 ";
        }

        $deviceList = Device::whereRaw($where)
            ->select('id', 'uuid', 'device_name')
            ->orderBy('device_name', 'ASC')
            ->get()->toArray();

        return $deviceList;
    }

    static function selectBoxGroupListByCompany($companyId)
    {
        $groupList = Groups::where('company_id', $companyId)
            ->where('status', self::$active)
            ->select('id', 'name')
            ->get()->toArray();

        return $groupList;
    }

    static function getDatesFromTimePeriod($timePeriod)
    {
        $startDate = '';
        $endDate = '';
        switch ($timePeriod) {
            case 'today':

                $today = date('Y-m-d');
                $startDate = $today;
                $endDate = $today;
                break;
            case 'yesterday':

                $yesterday = date('Y-m-d', strtotime("-1 days"));
                $startDate = $yesterday;
                $endDate = $yesterday;
                break;
            case 'this_month':
                $startDate = date('Y-m-d', strtotime('first day of this month'));
                $endDate = date('y-m-d');
                break;
            case 'last_month':
                $startDate = date('Y-m-d', strtotime("first day of last month"));
                $endDate = date('Y-m-d', strtotime("last day of last month"));
                break;
            default:

                break;
        }

        return ["start_date" => $startDate, "end_date" => $endDate];
    }

    static function alarmListByDevice($deviceId, $timezone)
    {
        $status = self::$active;
        $temperatureList = Temperature::where('status', $status)
            ->where('device_id', $deviceId)
            ->select('name', 'effective_start_date', 'effective_end_date', 'effective_start_time', 'effective_end_time')
            ->get()->toArray();
        foreach ($temperatureList as $key => $value) {
            $temperatureList[$key]['type'] = 'Temperature';

            $stateDate = new \DateTime($value['effective_start_date'], $timezone);
            $endDate = new \DateTime($value['effective_end_date'], $timezone);
            $startTime = new \DateTime($value['effective_start_time'], $timezone);
            $endTime = new \DateTime($value['effective_end_time'], $timezone);

            $temperatureList[$key]['date'] = $stateDate->format('d-m-Y') . " to " . $endDate->format('d-m-Y');
            $temperatureList[$key]['time'] = $startTime->format('H:i') . " to " . $endTime->format('H:i');
        }

        $humidityList = Humidity::where('status', $status)
            ->where('device_id', $deviceId)
            ->select('name', 'effective_start_date', 'effective_end_date', 'effective_start_time', 'effective_end_time')
            ->get()->toArray();
        foreach ($humidityList as $key => $value) {
            $humidityList[$key]['type'] = 'Humidity';

            $stateDate = new \DateTime($value['effective_start_date'], $timezone);
            $endDate = new \DateTime($value['effective_end_date'], $timezone);
            $startTime = new \DateTime($value['effective_start_time'], $timezone);
            $endTime = new \DateTime($value['effective_end_time'], $timezone);

            $humidityList[$key]['date'] = $stateDate->format('d-m-Y') . " to " . $endDate->format('d-m-Y');
            $humidityList[$key]['time'] = $startTime->format('H:i') . " to " . $endTime->format('H:i');
        }

        $data = array_merge($temperatureList, $humidityList);

        return $data;
    }

    static function deviceLogListByDevice($deviceId, $startDate, $endDate, $reportType, $timezone)
    {
        $where = "device_id = $deviceId";
        $reportTypeList = config('constants.report_type');
        $success = Notification::SUCCESS;
        if (!empty($reportType) && $reportTypeList[1] == $reportType) {
            $where .= " AND alerttype != '$success' ";
        }

        if (!empty($startDate) && !empty($endDate)) {
            $where .= " AND DATE(updated_at) BETWEEN '$startDate' AND '$endDate'";
        }

        $deviceLogList = DeviceTemperatureLog::whereRaw($where)
            ->select('temperature', 'humidity', 'device_color', 'updated_at')
            ->orderBy('updated_at', 'DESC')
            ->get()->toArray();

        foreach ($deviceLogList as $key => $value) {
            $createdAt = new \DateTime($value['updated_at'], $timezone);
            $deviceLogList[$key]['created_at'] = $createdAt->format('d-m-Y / H:i');
        }

        return $deviceLogList;
    }

    static function totalGroups()
    {
        $userData = Auth::user();
        $roleId = $userData->role_id;
        if (Roles::$employee == $roleId) {
            return 1;
        } else {
            $where = ["status" => self::$active];
            if (Roles::$company == $roleId) {
                $where['company_id'] = $userData->id;
            }
            return Groups::where($where)->count();
        }
    }

    static function todayNotification()
    {
        $userData = Auth::user();
        $companyRole = Roles::$company;
        $employeeRole = Roles::$employee;
        $actStatus = self::$active;
        $userId = $userData->id;
        $roleId = $userData->role_id;
        $where = "notification_detail.user_id IN (SELECT U1.id FROM users AS U1 WHERE U1.status = '" . $actStatus . "' AND  U1.role_id = '" . $companyRole . "' AND U1.deleted_at IS NULL)";
        if (in_array($roleId, [$companyRole, $employeeRole])) {
            $where = "notification_detail.user_id = " . $userId;
        }
        $toDay = Carbon::today();
        $count = NotificationDetail::join('notification AS N', 'N.uuid', '=', 'notification_detail.notification_uuid')
            ->whereRaw($where)
            ->whereDate('N.current_date_time', $toDay)
            ->count();

        return $count;
    }
    public static function sendSms($to,$sms){
        $apikey = config('constants.experttext_apikey');
        $apisecret = config('constants.experttext_apisecret');
        $experttext_user = config('constants.experttext_user');
        $experttext_sender = config('constants.experttext_sender');
        $url = 'https://www.experttexting.com/ExptRestApi/sms/json/Message/Send?username='.$experttext_user.'&api_key='.$apikey.'&api_secret='.$apisecret.'&from='.$experttext_sender.'&to='.$to.'&text='.$sms.'&type=text';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        self::log($url);
        self::log($result);
        return $result;
    }
}
