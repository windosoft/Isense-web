<?php

namespace App\Http\Controllers\Admin;

use App\Models\Helpers;
use App\Models\NotificationDetail;
use App\Models\Permissions;
use App\Models\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    protected $actStatus = '';
    protected $delStatus = '';
    protected $company = 0;
    protected $employee = 0;

    /**
     * NotificationController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
        $this->delStatus = Helpers::$delete;
        $this->company = Roles::$company;
        $this->employee = Roles::$employee;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function index()
    {
        $checkPermission = Permissions::checkActionPermission('view_message_center');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
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
                'N.notification', 'notification_for',
                DB::raw("(SELECT CONCAT(first_name,' ',last_name) AS name FROM users AS U WHERE U.id = notification_detail.user_id) AS company_name"),
                DB::raw("(SELECT time_zone AS name FROM users AS U WHERE U.id = N.company_id) AS company_time_zone")
            )
            ->orderby('notification_detail.created_at', 'DESC')
            ->limit(200)
            ->get()->toArray();

        foreach ($notificationList as $key => $value) {

            $timezone = new \DateTimeZone($value['company_time_zone']);
            $createdAt = new \DateTime($value['created_at'], $timezone);
            $notificationList[$key]['created_date'] = $createdAt->format('d-m-Y / H:i');
        }

        return view('backend.notification.index', compact('isCompany', 'notificationList'));
    }

    /**
     * paginate for notification
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
            $userData = Auth::user();
            $userId = $userData->id;
            $roleId = $userData->role_id;
            $where = "notification_detail.user_id IN (SELECT U1.id FROM users AS U1 WHERE U1.status = '" . $this->actStatus . "' AND  U1.role_id = '" . $this->company . "' AND U1.deleted_at IS NULL)";
            if (in_array($roleId, [$this->company, $this->employee])) {
                $where = "notification_detail.user_id = " . $userId;
            }

            $totalData = NotificationDetail::join('notification AS N', 'N.uuid', '=', 'notification_detail.notification_uuid')
                ->whereRaw($where)
                ->count();

            $dataList = NotificationDetail::join('notification AS N', 'N.uuid', '=', 'notification_detail.notification_uuid')
                ->whereRaw($where)
                ->select(
                    'notification_detail.uuid', 'notification_detail.read_status', 'notification_detail.created_at',
                    'N.notification', 'notification_for',
                    DB::raw("(SELECT CONCAT(first_name,' ',last_name) AS name FROM users AS U WHERE U.id = notification_detail.user_id) AS company_name"),
                    DB::raw("(SELECT time_zone AS name FROM users AS U WHERE U.id = N.company_id) AS company_time_zone")
                )
                ->orderby('notification_detail.created_at', 'DESC')
                ->limit($limit)->offset($start)
                ->get()->toArray();

            foreach ($dataList as $key => $value) {
                $dataList[$key]['index'] = ++$start;

                $timezone = new \DateTimeZone($value['company_time_zone']);
                $createdAt = new \DateTime($value['created_at'], $timezone);
                $dataList[$key]['created_date'] = $createdAt->format('d-m-Y / H:i');
            }
        } catch (\Exception $exception) {
            Helpers::log('notification pagination exception');
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($uuid)
    {
        DB::beginTransaction();

        $updateData = [
            "read_status" => Helpers::READ,
            "updated_at" => Carbon::now(),
        ];
        NotificationDetail::where('uuid', $uuid)->update($updateData);
        DB::commit();

        $notificationData = NotificationDetail::join('notification AS N', 'N.uuid', '=', 'notification_detail.notification_uuid')
            ->where('notification_detail.uuid', $uuid)
            ->select(
                'notification_detail.created_at', 'N.notification', 'N.notification_for', 'N.alerttype',
                DB::raw("(SELECT device_name FROM device AS d WHERE d.id = N.device_id) AS device_name"),
                DB::raw("(SELECT name FROM terminals AS d WHERE d.id = N.terminal_id) AS terminal_name")
            )->first();

        return view('backend.notification.show', compact('notificationData'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Show the form for deleting the specified resource.
     *
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function delete($uuid)
    {
        return view('backend.notification.delete', compact('uuid'));
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
            NotificationDetail::where('uuid', $uuid)->delete();

            DB::commit();
            return response()->json(["status" => 200, "message" => "This notification has been successfully deleted.", "redirect" => route('admin.notification.index')]);
        } catch (\Exception $exception) {
            Helpers::log('offline delete : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }

    /**
     * Show the form for multi deleting the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function multiDelete(Request $request)
    {
        $ids = $request->ids;
        return view('backend.notification.multi-delete', compact('ids'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function multiDestroy(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->ids as $value) {
                NotificationDetail::where('uuid', $value)->delete();
            }

            DB::commit();
            return response()->json(["status" => 200, "message" => "This notification has been successfully deleted.", "redirect" => route('admin.notification.index')]);
        } catch (\Exception $exception) {
            Helpers::log('offline delete : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }
}
