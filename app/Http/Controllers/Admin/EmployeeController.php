<?php

namespace App\Http\Controllers\Admin;

use App\Models\Helpers;
use App\Models\Permissions;
use App\Models\Roles;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    protected $actStatus = '';
    protected $delStatus = '';
    protected $employee = '';

    /**
     * EmployeeController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
        $this->delStatus = Helpers::$delete;
        $this->employee = Roles::$employee;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = Permissions::checkActionPermission('view_employee');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $isCompany = Helpers::isCompany();

        return view('backend.employee.index', compact('isCompany'));
    }

    /**
     * paginate for employee
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
            $whereRaw = "status = '" . $this->actStatus . "' AND role_id = " . $this->employee;
            $isCompany = Helpers::isCompany();
            if ($isCompany) {
                $whereRaw .= " AND company_id = " . Helpers::companyId();
            }
            if ($userData->role_id == Roles::$employee) {
                $whereRaw .= " AND id != " . $userData->id;
            }

            $totalData = User::whereRaw($whereRaw)->count();

            $dataList = User::whereRaw($whereRaw)
                ->select(
                    'uuid', 'first_name', 'last_name', 'email', 'phone',
                    DB::raw("(SELECT first_name FROM users AS U WHERE U.id = users.company_id) AS company_name"),
                    DB::raw("(SELECT name FROM groups WHERE groups.id = users.group_id) AS group_name")
                )
                ->orderBy('id', 'desc')
                ->limit($limit)->offset($start)
                ->get()->toArray();

            foreach ($dataList as $key => $value) {
                $dataList[$key]['index'] = ++$start;
                $dataList[$key]['full_name'] = $value['first_name'] . " " . $value['last_name'];
            }
        } catch (\Exception $exception) {
            Helpers::log('employee pagination exception');
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
        $checkPermission = Permissions::checkActionPermission('add_employee');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $isCompany = Helpers::isCompany();
        $companyList = [];
        $groupList = [];
        if ($isCompany) {
            $companyId = Helpers::companyId();
            $groupList = Helpers::selectBoxGroupListByCompany($companyId);
        } else {
            $companyList = Helpers::selectBoxCompanyList();
        }

        return view('backend.employee.create', compact('isCompany', 'companyList', 'groupList'));
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
            $phone = $request->phone;
            $email = $request->email;

            $checkEmail = User::where('email', $email)->count();
            $checkPhone = User::where('phone', $phone)->count();
            if ($checkEmail > 0) {
                return response()->json(["status" => 409, "message" => "This email address already exist."]);
            } elseif ($checkPhone > 0) {
                return response()->json(["status" => 409, "message" => "This phone number already exist."]);
            } else {
                $password = $request->password;
                $firstName = $request->first_name;
                $lastName = $request->last_name;
                $userType = User::$employee;

                $companyId = 0;
                $isCompany = Helpers::isCompany();
                if ($isCompany) {
                    $companyId = Helpers::companyId();
                } else {
                    $companyId = $request->company_id;
                }

                $createData = [
                    'uuid' => Helpers::getUuid(),
                    'role_id' => $this->employee,
                    "company_id" => $companyId,
                    "group_id" => $request->group_id,
                    "first_name" => $firstName,
                    "last_name" => $lastName,
                    "email" => $email,
                    "phone" => $phone,
                    "password" => Hash::make($password),
                    "user_type" => $userType,
                    "time_zone" => $request->time_zone,
                    "address" => '',
                    "status" => $this->actStatus,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];

                if ($request->hasFile('profile')) {
                    $folder = $this->createDirectory('users');
                    if ($file = $request->file('profile')) {
                        $extension = $file->getClientOriginalExtension();
                        $fileName = time() . '.' . $extension;
                        $file->move("$folder/", $fileName);
                        chmod($folder . '/' . $fileName, 0777);
                        $createData['profile'] = 'uploads/users/' . $fileName;
                    }
                }

                User::create($createData);

                $mailData = [
                    "username" => $firstName . " " . $lastName,
                    "email" => $email,
                    "password" => $password,
                    "login_url" => route('admin.login'),
                    "type" => $userType,
                ];
                Helpers::sendMailUser($mailData, 'emails.registration', 'Create Account', $email);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This employee has been successfully created.", "redirect" => route('admin.employee.index')]);
            }

        } catch (\Exception $exception) {
            Helpers::log('employee create : exception');
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
        $checkPermission = Permissions::checkActionPermission('edit_employee');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $employeeData = User::where('uuid', $uuid)->where('role_id', $this->employee)->first();
        if (!empty($employeeData)) {

            $isCompany = Helpers::isCompany();
            $companyList = [];
            $companyId = $employeeData->company_id;
            $groupList = Helpers::selectBoxGroupListByCompany($companyId);

            $employeeData->company_name = Helpers::companyName($companyId);

            return view('backend.employee.edit', compact('employeeData', 'isCompany', 'companyList', 'groupList'));
        } else {
            return redirect()->route('admin.employee.index')->with('error', 'Ooops...Something went wrong. Please try again.');
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
            $phone = $request->phone;
            $email = $request->email;

            $employeeData = User::where('uuid', $uuid)
                ->where('role_id', $this->employee)
                ->select('id')->first();
            if (!empty($employeeData)) {
                $employeeId = $employeeData->id;

                $checkEmail = User::where('email', $email)->where('id', '!=', $employeeId)->count();
                $checkPhone = User::where('phone', $phone)->where('id', '!=', $employeeId)->count();
                if ($checkEmail > 0) {
                    return response()->json(["status" => 409, "message" => "This email address already exist."]);
                } elseif ($checkPhone > 0) {
                    return response()->json(["status" => 409, "message" => "This phone number already exist."]);
                } else {

                    $updateData = [
                        "group_id" => $request->group_id,
                        "first_name" => $request->first_name,
                        "last_name" => $request->last_name,
                        "email" => $email,
                        "phone" => $phone,
                        "time_zone" => '',
                        "address" => '',
                        "status" => $this->actStatus,
                        "updated_at" => Carbon::now()
                    ];

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
                    User::where('id', $employeeId)->update($updateData);

                    DB::commit();
                    return response()->json(["status" => 200, "message" => "This employee has been successfully updated.", "redirect" => route('admin.employee.index')]);
                }
            } else {
                return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
            }
        } catch (\Exception $exception) {
            Helpers::log('employee create : exception');
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
        $checkPermission = Permissions::checkActionPermission('delete_employee');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        return view('backend.employee.delete', compact('uuid'));
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
            $employeeData = User::where('uuid', $uuid)->where('role_id', $this->employee)->select('id')->first();
            if (!empty($employeeData)) {
                $employeeId = $employeeData->id;
                $data = [
                    'status' => $this->delStatus,
                    'deleted_at' => Carbon::now()
                ];
                User::where('id', $employeeId)->update($data);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This employee has been successfully deleted.", "redirect" => route('admin.employee.index')]);
            } else {
                return response()->json(['status' => 404, 'message' => 'Ooops...Something went wrong! Please try again.']);
            }
        } catch (\Exception $exception) {
            Helpers::log('employee delete : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }
}
