<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branches;
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

class CompanyController extends Controller
{
    protected $actStatus = '';
    protected $delStatus = '';
    protected $company = '';

    /**
     * RoleController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
        $this->delStatus = Helpers::$delete;
        $this->company = Roles::$company;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = Permissions::checkActionPermission('view_company');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        return view('backend.company.index');
    }

    /**
     * Pagination for company
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginate(Request $request)
    {
        $inputs = $request->all();
        $start = $inputs['start'];
        $limit = $inputs['length'];
        $companyList = [];
        $totalCompany = 0;
        try {
            $totalCompany = User::where('role_id', $this->company)
                ->where('status', $this->actStatus)
                ->count();

            $companyList = User::where('role_id', $this->company)
                ->where('status', $this->actStatus)
                ->select(
                    'id', 'uuid', 'first_name', 'last_name', 'email', 'phone', 'time_zone',
                    DB::raw("(SELECT COUNT(id) AS count FROM branches WHERE branches.company_id = users.id AND branches.status = '" . $this->actStatus . "' AND branches.deleted_at IS NULL) AS branches"),
                    DB::raw("(SELECT COUNT(id) AS count FROM terminals WHERE terminals.company_id = users.id AND terminals.status = '" . $this->actStatus . "' AND terminals.deleted_at IS NULL) AS terminals"),
                    DB::raw("(SELECT COUNT(id) AS count FROM device WHERE device.company_id = users.id AND device.status = '" . $this->actStatus . "' AND device.deleted_at IS NULL) AS devices")
                )
                ->orderBy('id', 'desc')
                ->limit($limit)->offset($start)
                ->get()->toArray();

            foreach ($companyList as $key => $value) {
                $companyList[$key]['index'] = ++$start;
                $companyList[$key]['name'] = $value['first_name'] . " " . $value['last_name'];
            }
        } catch (\Exception $exception) {
            Helpers::log('Company pagination exception');
            Helpers::log($exception);
            $companyList = [];
            $totalCompany = 0;
        }
        $data = [
            "aaData" => $companyList,
            "iTotalDisplayRecords" => $totalCompany,
            "iTotalRecords" => $totalCompany,
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
        $checkPermission = Permissions::checkActionPermission('add_company');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $timeZoneList = Helpers::timeZoneList();
        return view('backend.company.create', compact('timeZoneList'));
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
            $email = $request->email;
            $phone = $request->phone;

            $checkEmail = User::where('email', $email)->count();
            $checkPhone = User::where('phone', $phone)->count();
            if ($checkEmail > 0) {
                return response()->json(["status" => 409, "message" => "This email address already exist."]);
            } elseif ($checkPhone > 0) {
                return response()->json(["status" => 409, "message" => "This phone number already exist."]);
            } else {
                $password = $request->password;
                $firstName = $request->first_name;
                $userType = User::$company;

                $createData = [
                    'uuid' => Helpers::getUuid(),
                    'role_id' => $this->company,
                    "first_name" => $firstName,
                    "last_name" => '',
                    "email" => $email,
                    "phone" => $phone,
                    "password" => Hash::make($password),
                    "user_type" => $userType,
                    "time_zone" => $request->time_zone,
                    "address" => $request->address,
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
                    "username" => $firstName,
                    "email" => $email,
                    "password" => $password,
                    "login_url" => route('admin.login'),
                    "type" => $userType,
                ];
                Helpers::sendMailUser($mailData, 'emails.registration', 'Create Account', $email);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This company has been successfully created.", "redirect" => route('admin.company.index')]);
            }
        } catch (\Exception $exception) {
            Helpers::log('company create : exception');
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
        $checkPermission = Permissions::checkActionPermission('edit_company');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $companyData = User::where('uuid', $uuid)->where('role_id', $this->company)->first();
        if (!empty($companyData)) {
            $timeZoneList = Helpers::timeZoneList();
            return view('backend.company.edit', compact('companyData', 'timeZoneList'));
        } else {
            return redirect()->route('admin.company.index')->with('error', 'Ooops...Something went wrong. Please try again.');
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
            $email = $request->email;
            $phone = $request->phone;

            $companyData = User::where('uuid', $uuid)->where('role_id', $this->company)->select('id')->first();
            if (!empty($companyData)) {
                $companyId = $companyData->id;

                $checkEmail = User::where('email', $email)->where('id', '!=', $companyId)->count();
                $checkPhone = User::where('phone', $phone)->where('id', '!=', $companyId)->count();
                if ($checkEmail > 0) {
                    return response()->json(["status" => 409, "message" => "This email address already exist."]);
                } elseif ($checkPhone > 0) {
                    return response()->json(["status" => 409, "message" => "This phone number already exist."]);
                } else {
                    $updateData = [
                        "first_name" => $request->first_name,
                        "last_name" => '',
                        "email" => $email,
                        "phone" => $phone,
                        "time_zone" => $request->time_zone,
                        "address" => $request->address,
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

                    User::where('id', $companyId)->update($updateData);

                    DB::commit();
                    return response()->json(["status" => 200, "message" => "This company has been successfully updated.", "redirect" => route('admin.company.index')]);
                }
            } else {
                return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please try again.']);
            }
        } catch (\Exception $exception) {
            Helpers::log('company update : exception');
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
        $checkPermission = Permissions::checkActionPermission('delete_company');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        return view('backend.company.delete', compact('uuid'));
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
            $companyData = User::where('uuid', $uuid)->where('role_id', $this->company)->select('id')->first();
            if (!empty($companyData)) {
                $companyId = $companyData->id;
                $data = [
                    'status' => $this->delStatus,
                    'deleted_at' => Carbon::now()
                ];

                Branches::where('company_id', $companyId)->update($data);

                User::where('id', $companyId)->update($data);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This company has been successfully deleted.", "redirect" => route('admin.company.index')]);
            } else {
                return response()->json(['status' => 404, 'message' => 'Ooops...Something went wrong! Please try again.']);
            }
        } catch (\Exception $exception) {
            Helpers::log('company delete : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }
}
