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

class BranchController extends Controller
{
    protected $actStatus = '';
    protected $delStatus = '';
    protected $company = '';

    /**
     * BranchController constructor.
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
        $checkPermission = Permissions::checkActionPermission('view_branch');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $isCompany = Helpers::isCompany();

        return view('backend.branches.index', compact('isCompany'));
    }

    /**
     * paginate for branch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginate(Request $request)
    {
        $inputs = $request->all();
        $start = $inputs['start'];
        $limit = $inputs['length'];
        $branchList = [];
        $totalBranch = 0;
        try {

            $where = ["status" => $this->actStatus];

            $isCompany = Helpers::isCompany();
            if ($isCompany) {
                $where['company_id'] = Helpers::companyId();
            }

            $totalBranch = Branches::where($where)->count();

            $branchList = Branches::where($where)
                ->select(
                    'uuid', 'name', 'email', 'phone', 'key_person',
                    DB::raw("(SELECT first_name FROM users WHERE users.id = branches.company_id) AS company_name")
                )
                ->orderBy('id', 'desc')
                ->limit($limit)->offset($start)
                ->get()->toArray();

            foreach ($branchList as $key => $value) {
                $branchList[$key]['index'] = ++$start;
            }
        } catch (\Exception $exception) {
            Helpers::log('Branch pagination exception');
            Helpers::log($exception);
            $branchList = [];
            $totalBranch = 0;
        }
        $data = [
            "aaData" => $branchList,
            "iTotalDisplayRecords" => $totalBranch,
            "iTotalRecords" => $totalBranch,
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
        $checkPermission = Permissions::checkActionPermission('add_branch');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $companyList = [];
        $isCompany = Helpers::isCompany();
        if ($isCompany == false) {
            $companyList = Helpers::selectBoxCompanyList();
        }

        return view('backend.branches.create', compact('companyList', 'isCompany'));
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
            $companyId = 0;
            $isCompany = Helpers::isCompany();
            if ($isCompany) {
                $companyId = Helpers::companyId();
            } else {
                $companyId = $request->company_id;
            }

            $name = trim($request->name);
            $email = trim($request->email);
            $checkName = Branches::where('name', $name)->where('company_id', $companyId)->count();
            $checkEmail = Branches::where('email', $email)->where('company_id', $companyId)->count();

            if ($checkName > 0) {
                return response()->json(['status' => 409, 'message' => 'This branch name already exists.']);
            } elseif ($checkEmail > 0) {
                return response()->json(['status' => 409, 'message' => 'This branch email already exists.']);
            } else {
                $createData = [
                    "uuid" => Helpers::getUuid(),
                    "company_id" => $companyId,
                    "name" => $name,
                    "phone" => $request->phone,
                    "email" => $email,
                    "key_person" => $request->key_person,
                    "address" => $request->address,
                    "status" => $this->actStatus,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];
                Branches::create($createData);
                DB::commit();
                return response()->json(["status" => 200, "message" => "This branch has been successfully created.", "redirect" => route('admin.branches.index')]);
            }
        } catch (\Exception $exception) {
            Helpers::log('branch create : exception');
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
        $checkPermission = Permissions::checkActionPermission('edit_branch');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $branchData = Branches::where('uuid', $uuid)->first();
        if (!empty($branchData)) {

            $companyList = [];
            $isCompany = Helpers::isCompany();
            $branchData->company_name = Helpers::companyName($branchData->company_id);
            return view('backend.branches.edit', compact('branchData', 'companyList', 'isCompany'));
        } else {
            return redirect()->route('admin.branches.index')->with('error', 'Ooops...Something went wrong. Please try again.');
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
            $branchData = Branches::where('uuid', $uuid)->select('id', 'company_id')->first();
            if (!empty($branchData)) {
                $branchId = $branchData->id;
                $companyId = $branchData->company_id;
                $name = trim($request->name);
                $email = trim($request->email);

                $checkName = Branches::where('name', $name)
                    ->where('company_id', $companyId)
                    ->where('id', '!=', $branchId)
                    ->count();

                $checkEmail = Branches::where('email', $email)
                    ->where('company_id', $companyId)
                    ->where('id', '!=', $branchId)
                    ->count();

                if ($checkName > 0) {
                    return response()->json(['status' => 409, 'message' => 'This branch name already exists.']);
                } elseif ($checkEmail > 0) {
                    return response()->json(['status' => 409, 'message' => 'This branch email already exists.']);
                } else {
                    $updateData = [
                        "name" => $name,
                        "phone" => $request->phone,
                        "email" => $email,
                        "key_person" => $request->key_person,
                        "address" => $request->address,
                        "status" => $this->actStatus,
                        "updated_at" => Carbon::now()
                    ];
                    Branches::where('id', $branchId)->update($updateData);
                    DB::commit();
                    return response()->json(["status" => 200, "message" => "This branch has been successfully updated.", "redirect" => route('admin.branches.index')]);
                }
            } else {
                return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
            }
        } catch (\Exception $exception) {
            Helpers::log('branch update : exception');
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
        $checkPermission = Permissions::checkActionPermission('delete_branch');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        return view('backend.branches.delete', compact('uuid'));
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
            $branchData = Branches::where('uuid', $uuid)->select('id')->first();
            if (!empty($branchData)) {
                $branchId = $branchData->id;
                $data = [
                    'status' => $this->delStatus,
                    'deleted_at' => Carbon::now()
                ];
                Branches::where('id', $branchId)->update($data);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This branch has been successfully deleted.", "redirect" => route('admin.branches.index')]);
            } else {
                return response()->json(['status' => 404, 'message' => 'Ooops...Something went wrong! Please try again.']);
            }
        } catch (\Exception $exception) {
            Helpers::log('branch delete : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }

    /**
     * branch list by company
     *
     * @param $company_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function listByCompany($company_id)
    {
        $branchList = Helpers::selectBoxBranchListByCompany($company_id);

        return response()->json(["status" => 200, "message" => "success", "data" => $branchList]);
    }
}
