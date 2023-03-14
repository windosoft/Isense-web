<?php

namespace App\Http\Controllers\Api;

use App\Models\Access;
use App\Models\Device;
use App\Models\DeviceDashboard;
use App\Models\Helpers;
use App\Models\Notification;
use App\Models\NotificationDetail;
use App\Models\PasswordResets;
use App\Models\Permissions;
use App\Models\Roles;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    protected $actStatus = '';
    protected $unread = '';
    protected $read = '';

    /**
     * ApiController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
        $this->unread = Helpers::UNREAD;
        $this->read = Helpers::READ;
    }

    /**
     * Get all role access list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function roleAccessList()
    {
        Helpers::log('Role access list : start');
        try {

            $roleList = Roles::where('id', '!=', Roles::$admin)
                ->select('id AS role_id', 'name AS role_name')
                ->get()->toArray();

            foreach ($roleList as $key => $role) {
                $roleId = $role['role_id'];
                $permissionList = Permissions::select(
                    'id', 'name',
                    DB::raw("(SELECT IF(SUM(1) = 1,1,0) AS count FROM role_permissions WHERE permissions_id = permissions.id AND roles_id = " . $roleId . ") AS is_permission")
                )->get()->toArray();
                $myAccess = [];
                if (!empty($permissionList)) {
                    foreach ($permissionList as $value) {
                        $permissionName = $value['name'];
                        $isPermission = $value['is_permission'];
                        $myAccess[$permissionName] = (string)$isPermission;
                    }
                }
                $roleList[$key]['access'] = (object)$myAccess;
            }

            $response = Helpers::replaceNullWithEmptyString($roleList);

            Helpers::log('Role access list : finish');
            return response()->json(["status" => 200, "show" => false, "refreshing_time" => 120, "msg" => "success", "data" => $response]);
        } catch (\Exception $exception) {
            Helpers::log('Role access list : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * Company login
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        DB::beginTransaction();
        Helpers::log('Company login : start');
        try {
            $email = $request->email;
            $password = $request->password;
            $deviceToken = $request->device_token;
            $deviceType = $request->device_type;
            $mobileDeviceId = $request->mobile_device_id;

            if (empty($email)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Email address must be required"]);
            } elseif (empty($password)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Password must be required"]);
            } elseif (empty($deviceToken)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Device token must be required"]);
            } elseif (empty($deviceType)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Device type must be required"]);
            } elseif (empty($mobileDeviceId)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Device id must be required"]);
            } else {

                $credentials = [
                    'email' => $email,
                    'password' => $password,
                    'status' => Helpers::$active,
                ];
                if (Auth::attempt($credentials)) {
                    $userDetail = Auth::user();
                    if ($userDetail->role_id == Roles::$admin) {
                        Auth::logout();
                        return response()->json(["status" => 404, 'show' => true, "msg" => "username or password is wrong."]);
                    } else {

                        User::where('device_token',$deviceToken)->update(['device_token'=>'']);

                        $userId = $userDetail->id;
                        $updateData = [
                            'api_token' => Helpers::randomString(60),
                            'device_type' => $deviceType,
                            'device_token' => $deviceToken,
                            'mobile_device_id' => $mobileDeviceId,
                            'updated_at' => Carbon::now(),
                        ];
                        User::where('id', $userId)->update($updateData);

                        $userData = Helpers::userDetail($userId);
                        $response = Helpers::replaceNullWithEmptyString($userData);
                        DB::commit();
                        Helpers::log('Company login : finish');
                        return response()->json(["status" => 200, "show" => false, "msg" => "success", "data" => $response]);
                    }
                } else {
                    DB::rollBack();
                    Helpers::log('Company login : 404');
                    return response()->json(["status" => 404, 'show' => true, "msg" => "username or password is wrong."]);
                }
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('Company login : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * Dashboard page api
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyDashboard(Request $request)
    {
        DB::beginTransaction();
        Helpers::log('Company dashboard : start');
        try {
            $companyId = $request->company_id;

            if (empty($companyId)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Company id must be required"]);
            } else {

                $currentTime = strtotime(date('H:i:s'));
                $deviceDashboardData = DeviceDashboard::join('device AS D', 'D.id', '=', 'device_dashboard.device_id')
                    ->where('device_dashboard.company_id', $companyId)
                    ->where('D.status', $this->actStatus)
                    ->select('device_dashboard.device_id', 'device_dashboard.updated_at', 'D.data_interval', 'D.device_sn')
                    ->groupBy('device_dashboard.device_id')
                    ->get()->toArray();
                if (!empty($deviceDashboardData)) {
                    foreach ($deviceDashboardData as $device) {
                        $updatedAt = $device['updated_at'];
                        $serverTime = date("Y-m-d H:i:s", strtotime($updatedAt));
                        $foundTotalDiffMinute = (int)round(((abs($currentTime - strtotime($serverTime)) / 3600) * 60));
                        $dataInterval = (int)$device['data_interval'];

                        if ($dataInterval < $foundTotalDiffMinute) {
                            $updateData = [
                                "temperature_color" => Notification::DISABLECOLOR,
                                "temperature_alert" => Notification::DISABLE,
                                "humidity_color" => Notification::DISABLECOLOR,
                                "humidity_alert" => Notification::DISABLECOLOR,
                                "offline_color" => Notification::DISABLECOLOR,
                                "offline_alert" => Notification::DISABLECOLOR,
                                "voltage_color" => Notification::DISABLECOLOR,
                                "voltage_alert" => Notification::DISABLECOLOR,
                                "last_seen" => $updatedAt,
                            ];
                            DeviceDashboard::where("device_id", $device['device_id'])
                                ->where('company_id', $companyId)
                                ->update($updateData);
                        }
                    }
                }

                $toDay = Carbon::today();
                $where = "company_id = $companyId AND device_id IN (SELECT id FROM device WHERE status = '" . $this->actStatus . "')";
                $totalWarning = DeviceDashboard::whereDate('updated_at', $toDay)
                    ->whereRaw($where)
                    ->where('temperature_alert', Notification::WARNING)
                    ->count();
                $totalDanger = DeviceDashboard::whereDate('updated_at', $toDay)
                    ->whereRaw($where)
                    ->where('temperature_alert', Notification::DANGER)
                    ->count();
                $totalSuccess = DeviceDashboard::whereDate('updated_at', $toDay)
                    ->whereRaw($where)
                    ->where('temperature_alert', Notification::SUCCESS)
                    ->count();
                $totalDisable = DeviceDashboard::whereDate('updated_at', $toDay)
                    ->whereRaw($where)
                    ->where('temperature_alert', Notification::DISABLE)
                    ->count();
                $totalSensor = Device::where('company_id', $companyId)
                    ->where('status', $this->actStatus)
                    ->count();

                $userId = Auth::user()->id;
                $where = "N.company_id = $companyId AND notification_detail.read_status = '" . $this->unread . "' ";
                $where .= " AND N.device_id IN (SELECT D.id FROM device AS D WHERE D.status = '" . $this->actStatus . "' AND D.deleted_at IS NULL)";
                $where .= " AND notification_detail.user_id = " . $userId;

                $totalAlert = NotificationDetail::join('notification AS N', 'N.uuid', '=', 'notification_detail.notification_uuid')
                    ->whereDate('N.current_date_time', Carbon::today())
                    ->whereRaw($where)
                    ->count();
                $dashboardList = [
                    'total_alert' => $totalAlert,
                    'total_sensor' => $totalSensor,
                    'types' => [
                        ['total' => $totalWarning, 'color' => Notification::WARNINGCOLOR, 'type' => Notification::WARNING, 'name' => Notification::WARNING_NAME],
                        ['total' => $totalDanger, 'color' => Notification::DANGERCOLOR, 'type' => Notification::DANGER, 'name' => Notification::DANGER_NAME],
                        ['total' => $totalSuccess, 'color' => Notification::SUCCESSCOLOR, 'type' => Notification::SUCCESS, 'name' => Notification::SUCCESS_NAME],
                        ['total' => $totalDisable, 'color' => Notification::DISABLECOLOR, 'type' => Notification::DISABLE, 'name' => Notification::DISABLE_NAME]
                    ]
                ];

                $response = Helpers::replaceNullWithEmptyString($dashboardList);
                Helpers::log('Company dashboard : finish');
                DB::commit();
                return response()->json(["status" => 200, "show" => false, "msg" => "success", "data" => $response]);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('Company dashboard : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * Forgot password by email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        DB::beginTransaction();
        try {
            $email = $request->email;
            if (empty($email)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Email address must be required"]);
            } else {

                $userData = User::where('email', $email)
                    ->where('status', $this->actStatus)
                    ->where('role_id', '!=', Roles::$admin)
                    ->select('first_name', 'last_name')
                    ->first();

                if (empty($userData)) {
                    return response()->json(["status" => 404, "show" => true, "msg" => "The email address does not exist"]);
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

                    //$email = 'no-reply@isenseonline.com';
                    Helpers::sendMailUser($data, 'emails.forgot-password', 'Reset Password', $email);

                    DB::commit();
                    return response()->json(["status" => 200, "show" => true, "msg" => "We have e-mailed your password reset link!"]);
                }
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('forgot password : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * Profile update
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profileUpdate(Request $request)
    {
        DB::beginTransaction();
        Helpers::log('profile update : start');
        try {
            $id = $request->id;
            $firstName = $request->first_name;
            $lastName = $request->last_name;
            $email = $request->email;
            $phone = $request->phone;

            if (empty($id)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "id must be required"]);
            } elseif (empty($firstName)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "First name must be required"]);
            } elseif (empty($lastName)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Last name must be required"]);
            } elseif (empty($email)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Email address must be required"]);
            } elseif (empty($phone)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Phone must be required"]);
            } else {
                $userId = Auth::user()->id;
                $userEmail = User::where('email', $email)->where('id', '!=', $userId)->count();
                $userMobile = User::where('phone', $phone)->where('id', '!=', $userId)->count();

                if ($userEmail > 0) {
                    return response()->json(["status" => 409, "show" => true, "msg" => "This email already exist."]);
                } elseif ($userMobile > 0) {
                    return response()->json(["status" => 409, "show" => true, "msg" => "This phone already exist."]);
                } else {
                    $updateData = [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $phone,
                        'email' => $email,
                        'updated_at' => Carbon::now()
                    ];

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

                    $userData = Helpers::userDetail($userId);
                    $response = Helpers::replaceNullWithEmptyString($userData);
                    DB::commit();
                    return response()->json(["status" => 200, "show" => true, "msg" => "Your Profile has been successfully updated.", "data" => $response]);
                }
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('profile update : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * Get user profile detail
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        Helpers::log('get profile : start');
        try {
            $id = $request->id;
            if (empty($id)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "id must be required"]);
            } else {
                $userId = Auth::user()->id;
                $userData = Helpers::userDetail($userId);
                $response = Helpers::replaceNullWithEmptyString($userData);
                return response()->json(["status" => 200, "show" => false, "msg" => "success", "data" => $response]);
            }
        } catch (\Exception $exception) {
            Helpers::log('profile update : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }

    /**
     * change password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        DB::beginTransaction();
        Helpers::log('change password update : start');
        try {
            $id = $request->id;
            $oldPassword = $request->old_password;
            $newPassword = $request->password;
            $passwordConfirm = $request->password_confirm;

            if (empty($id)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "id must be required"]);
            } elseif (empty($oldPassword)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "old password must be required"]);
            } elseif (empty($newPassword)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "new password must be required"]);
            } elseif (empty($passwordConfirm)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "confirm password must be required"]);
            } elseif ($newPassword != $passwordConfirm) {
                return response()->json(["status" => 422, "show" => true, "msg" => "password and confirm password does not match"]);
            } else {
                $userData = Auth::user();
                if (Hash::check($oldPassword, $userData->password)) {
                    $userId = $userData->id;
                    $updateData = [
                        'password' => Hash::make($newPassword),
                        'updated_at' => Carbon::now()
                    ];
                    User::where('id', $userId)->update($updateData);

                    DB::commit();
                    return response()->json(["status" => 200, "show" => true, "msg" => "Your password has been successfully updated."]);
                } else {
                    return response()->json(['status' => 404, 'show' => true, 'msg' => 'Your old password does not match']);
                }
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Helpers::log('change password update : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }
    public function sendIOSNOtification(){
        $iosNotification = [
                "aps" => [
                    "alert" => [
                        "title" => 'Hey Bharat,',
                        "body" => 'Hope you are doing well.',
                        "type" => "",
                        "link" => "",
                    ],
                    "custom_data" => [
                        'device_name' => '',
                        'user_id' => '',
                        'notification_id' => 0,
                        'device_sn' => '',
                        'company_id' => '',
                        'terminal_id' => '',
                        'alerttype' => '',
                        'device_color' => '',
                        'temperature' => '',
                        'humidity' => '',
                        'offline' => '',
                        'voltage' => '',
                        'notification' => '',
                        'uuid' => '',
                        'is_sound_play' => ''
                    ],
                    "sound" => '',
                ]
        ];

        $deviceToken = '7331CA3E078DAFB0C5EBF72EE49AE07F45F7B742CDBE4169FEE61D4FDAAA6291';
        $body = $iosNotification;
        $environment = env('APP_ENV');
        $result = [];

        // certificate file path
        $pushCertFile = base_path() . config('constants.ios_pem');
        $passphrase = ''; //p12 certificate password

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $pushCertFile);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        if ($environment == 'production') {
            $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errStr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        } else {
            $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errStr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
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
