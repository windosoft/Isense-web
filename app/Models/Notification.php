<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = "notification";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'company_id', 'terminal_id', 'device_id', 'temperature', 'humidity', 'offline', 'voltage',
        'notification', 'current_date_time', 'notification_for', 'alerttype', 'device_color', 'read_status',
        'status', 'created_at', 'updated_at'
    ];

    const WARNING = 'WARNING';
    const DANGER = 'DANGER';
    const SUCCESS = 'SUCCESS';
    const DISABLE = 'DISABLE';

    const WARNING_NAME = 'Caution';
    const DANGER_NAME = 'Danger';
    const SUCCESS_NAME = 'Normal';
    const DISABLE_NAME = 'Inactive';

    const ALERT = 'ALERT';

    const DANGERCOLOR = '#FB1717';   // RED
    const WARNINGCOLOR = '#FBC217';  // YELLOW
    const SUCCESSCOLOR = '#87C253';  // GREEN
    const DISABLECOLOR = '#B4B8C0';  // GREY

    const ALERTTYPES = ['temperature', 'humidity', 'offline', 'voltage'];

    static function sendNotification($data)
    {
        $actStatus = Helpers::$active;
        $companyId = $data['company_id'];
        $deviceId = $data['device_id'];
        $deviceName = $data['device_name'];
        $deviceSN = $data['device_sn'];
        unset($data['device_name']);
        unset($data['device_sn']);
        $android = config('constants.android');

        $companyData = User::where('id', $companyId)
            ->where('status', $actStatus)
            ->where('role_id', Roles::$company)
            ->select('id', 'device_token', 'device_type', 'notify_sound')
            ->first();

        if (!empty($companyData)) {
            $companyData = $companyData->toArray();
            $androidDevice = [];
            $iosDevice = [];
            $deviceToken = $companyData['device_token'];
            $uuid = Helpers::getUuid();

            $data['uuid'] = $uuid;
            self::create($data);

            $notificationUuid = Helpers::getUuid();
            $createData = [
                'uuid' => $notificationUuid,
                'notification_uuid' => $uuid,
                'user_id' => $companyId,
                'read_status' => 'unread',
            ];
            NotificationDetail::create($createData);

            $androidNotification = [
                "title" => ucfirst($data['notification_for']),
                "body" => $data['notification'],
                'message' => $data['notification'],
                'device_name' => $deviceName,
                'user_id' => $companyId,
                'notification_id' => 0,
                'uuid' => $notificationUuid,
                'device_sn' => $deviceSN,
                'company_id' => $companyId,
                'terminal_id' => $data['terminal_id'],
                'alerttype' => $data['alerttype'],
                'device_color' => $data['device_color'],
                'temperature' => $data['temperature'],
                'humidity' => $data['humidity'],
                'offline' => $data['offline'],
                'voltage' => $data['voltage'],
                'badge' => 1,
                'notification_count' => 1,
                'is_sound_play' => $companyData['notify_sound']
            ];

            $sound = 'default';
            if ($companyData['notify_sound'] == 1) {
                $sound = 'bell';
            }

            $iosNotification = [
                "aps" => [
                    "alert" => [
                        "title" => ucfirst($data['notification_for']),
                        "body" => $data['notification'],
                        "type" => "",
                        "link" => "",
                    ],
                    "custom_data" => [
                        'device_name' => $deviceName,
                        'user_id' => $companyId,
                        'notification_id' => 0,
                        'device_sn' => $deviceSN,
                        'company_id' => $companyId,
                        'terminal_id' => $data['terminal_id'],
                        'alerttype' => $data['alerttype'],
                        'device_color' => $data['device_color'],
                        'temperature' => $data['temperature'],
                        'humidity' => $data['humidity'],
                        'offline' => $data['offline'],
                        'voltage' => $data['voltage'],
                        'notification' => $data['notification'],
                        'uuid' => $notificationUuid,
                        'is_sound_play' => $companyData['notify_sound']
                    ],
                    "sound" => $sound,
                ]
            ];

            if ($companyData['device_type'] == $android) {
                $androidDevice[] = $deviceToken;

                self::androidSend([$deviceToken], $androidNotification);
            } else {
                $iosDevice[] = $deviceToken;
                self::iosSend([$deviceToken], $iosNotification);
            }

            $employeeRole = Roles::$employee;
            $where = " company_id = '$companyId' AND status = '$actStatus' AND role_id = '$employeeRole' ";
            $where .= " AND (SELECT COUNT(G.id) AS count FROM groups AS G WHERE G.status = '$actStatus' AND G.deleted_at IS NULL AND G.id = users.group_id AND FIND_IN_SET($deviceId, G.device_ids ) ) > 0";
            $companyEmployeeList = User::whereRaw($where)
                ->select('id', 'device_token', 'device_type', 'notify_sound')
                ->get()->toArray();
            foreach ($companyEmployeeList as $key => $value) {
                $employeeId = $value['id'];
                $notifySound = $value['notify_sound'];
                $uid = Helpers::getUuid();
                $createData = [
                    'uuid' => $uid,
                    'notification_uuid' => $uuid,
                    'user_id' => $employeeId,
                    'read_status' => 'unread',
                ];
                NotificationDetail::create($createData);

                $token = $value['device_token'];
                if ($value['device_type'] == $android) {
                    $androidNotification['user_id'] = $employeeId;
                    $androidNotification['uuid'] = $uid;
                    $androidNotification['is_sound_play'] = $notifySound;
                    self::androidSend([$token], $androidNotification);
                } else {

                    $iosNotification['aps']['custom_data']['user_id'] = $employeeId;
                    $iosNotification['aps']['custom_data']['uuid'] = $uid;
                    $iosNotification['aps']['custom_data']['is_sound_play'] = $notifySound;
                    $sound = 'default';
                    if ($notifySound == 1) {
                        $sound = 'bell';
                    }
                    $iosNotification['aps']['sound'] = $sound;
                    self::iosSend([$token], $iosNotification);
                }
            }
        }
    }

    static function androidSend($deviceToken, $notification)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $serverKey = config('constants.android_key');

        $fields = ["data" => $notification];
        if (is_array($deviceToken)) {
            $fields['registration_ids'] = $deviceToken;
        } else {
            $fields['to'] = $deviceToken;
        }

        $headers = [
            'Content-Type:application/json',
            'Authorization:key=' . $serverKey
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    static function iosSend($deviceToken, $body)
    {
        $environment = env('APP_ENV');
        $result = [];
        Helpers::log('Sending IOS Notification : ');
        Helpers::log($deviceToken);
        // certificate file path
        $pushCertFile = base_path() . config('constants.ios_pem');
        $passphrase = ''; //p12 certificate password

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $pushCertFile);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        if ($environment == 'production') {
            $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errStr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        } else {
            $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errStr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        }

        $payload = json_encode($body);
        if (is_array($deviceToken)) {
            foreach ($deviceToken as $token) {
                try {
                    $msg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
                    $result[] = fwrite($fp, $msg, strlen($msg));
                } catch (\Exception $exception) {
                    //
                }
            }
        } else {
            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
            $result[] = fwrite($fp, $msg, strlen($msg));
        }
        fclose($fp);

        return $result;
    }
}
