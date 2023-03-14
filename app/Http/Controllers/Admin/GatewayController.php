<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branches;
use App\Models\Helpers;
use App\Models\Permissions;
use App\Models\Terminals;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GatewayController extends Controller
{
    protected $actStatus = '';
    protected $delStatus = '';

    /**
     * GatewayController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
        $this->delStatus = Helpers::$delete;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = Permissions::checkActionPermission('view_gateway');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $isCompany = Helpers::isCompany();

        return view('backend.gateway.index', compact('isCompany'));
    }

    /**
     * paginate for gateway
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginate(Request $request)
    {
        $inputs = $request->all();
        $start = $inputs['start'];
        $limit = $inputs['length'];
        $gatewayList = [];
        $totalGateway = 0;
        try {

            $where = ["status" => $this->actStatus];

            $isCompany = Helpers::isCompany();
            if ($isCompany) {
                $where['company_id'] = Helpers::companyId();
            }

            $totalGateway = Terminals::where($where)->count();

            $gatewayList = Terminals::where($where)
                ->select(
                    'uuid', 'name', 'imei', 'receiver_type',
                    DB::raw("(SELECT name FROM branches WHERE branches.id = terminals.branch_id) AS branch_name"),
                    DB::raw("(SELECT first_name FROM users WHERE users.id = terminals.company_id) AS company_name")
                )
                ->orderBy('id', 'desc')
                ->limit($limit)->offset($start)
                ->get()->toArray();

            foreach ($gatewayList as $key => $value) {
                $gatewayList[$key]['index'] = ++$start;
            }
        } catch (\Exception $exception) {
            Helpers::log('Gateway pagination exception');
            Helpers::log($exception);
            $gatewayList = [];
            $totalGateway = 0;
        }
        $data = [
            "aaData" => $gatewayList,
            "iTotalDisplayRecords" => $totalGateway,
            "iTotalRecords" => $totalGateway,
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
        $checkPermission = Permissions::checkActionPermission('add_gateway');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        $isCompany = Helpers::isCompany();
        $branchList = [];
        $companyList = [];
        if ($isCompany) {
            $companyId = Helpers::companyId();
            $branchList = Helpers::selectBoxBranchListByCompany($companyId);
        } else {
            $companyList = Helpers::selectBoxCompanyList();
        }
        $terminalType = config('constants.terminal_type');

        return view('backend.gateway.create', compact('isCompany', 'companyList', 'branchList', 'terminalType'));
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
            $branchId = $request->branch_id;
            $name = $request->name;
            $imei = $request->imei;

            $checkName = Terminals::where('name', $name)->where('company_id', $companyId)->where('branch_id', $branchId)->count();
            $checkImei = Terminals::where('imei', $imei)->where('company_id', $companyId)->where('branch_id', $branchId)->count();
            if ($checkName > 0) {
                return response()->json(['status' => 409, 'message' => 'This gateway name already exists.']);
            } elseif ($checkImei > 0) {
                return response()->json(['status' => 409, 'message' => 'This gateway imei already exists.']);
            } else {
                $createData = [
                    'uuid' => Helpers::getUuid(),
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'department_id' => 0,
                    'name' => $name,
                    'imei' => $imei,
                    'receiver_type' => $request->receiver_type,
                    'password' => $request->password,
                    'remarks' => (!empty($request->remarks)) ? $request->remarks : '',
                    'status' => $this->actStatus,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
                Terminals::create($createData);
                DB::commit();
                return response()->json(["status" => 200, "message" => "This gateway has been successfully created.", "redirect" => route('admin.gateway.index')]);
            }
        } catch (\Exception $exception) {
            Helpers::log('gateway create : exception');
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($uuid)
    {
        $checkPermission = Permissions::checkActionPermission('edit_gateway');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }

        $gatewayData = Terminals::where('uuid', $uuid)->first();
        if (!empty($gatewayData)) {

            $isCompany = Helpers::isCompany();
            $branchList = [];
            $companyList = [];
            $companyId = $gatewayData->company_id;
            $companyList = Helpers::selectBoxCompanyList();
            $gatewayData->company_name = Helpers::companyName($companyId);
            $branchList = Helpers::selectBoxBranchListByCompany($companyId);
            $terminalType = config('constants.terminal_type');

            return view('backend.gateway.edit', compact('gatewayData', 'isCompany', 'companyList', 'branchList', 'terminalType'));
        } else {
            return redirect()->route('admin.gateway.index')->with('error', 'Ooops...Something went wrong. Please try again.');
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
            $terminalData = Terminals::where('uuid', $uuid)->select('id', 'company_id', 'branch_id')->first();
            if (!empty($terminalData)) {
                $terminalId = $terminalData->id;
                $companyId = $terminalData->company_id;
                $branchId = $request->branch_id;
                $name = $request->name;
                $imei = $request->imei;

                $checkName = Terminals::where('name', $name)
                    ->where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->where('id', '!=', $terminalId)
                    ->count();

                $checkImei = Terminals::where('imei', $imei)
                    ->where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->where('id', '!=', $terminalId)
                    ->count();

                if ($checkName > 0) {
                    return response()->json(['status' => 409, 'message' => 'This gateway name already exists.']);
                } elseif ($checkImei > 0) {
                    return response()->json(['status' => 409, 'message' => 'This gateway imei already exists.']);
                } else {
                    $updateData = [
                        'branch_id' => $branchId,
                        'name' => $name,
                        'imei' => $imei,
                        'receiver_type' => $request->receiver_type,
                        'password' => $request->password,
                        'remarks' => (!empty($request->remarks)) ? $request->remarks : '',
                        'updated_at' => Carbon::now()
                    ];
                    Terminals::where('id', $terminalId)->update($updateData);
                    DB::commit();
                    return response()->json(["status" => 200, "message" => "This gateway has been successfully updated.", "redirect" => route('admin.gateway.index')]);
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
        $checkPermission = Permissions::checkActionPermission('delete_gateway');
        if ($checkPermission == false) {
            return view('backend.access-denied');
        }
        return view('backend.gateway.delete', compact('uuid'));
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
            $terminalData = Terminals::where('uuid', $uuid)->select('id')->first();
            if (!empty($terminalData)) {
                $terminalId = $terminalData->id;
                $data = [
                    'status' => $this->delStatus,
                    'deleted_at' => Carbon::now()
                ];
                Terminals::where('id', $terminalId)->update($data);

                DB::commit();
                return response()->json(["status" => 200, "message" => "This gateway has been successfully deleted.", "redirect" => route('admin.gateway.index')]);
            } else {
                return response()->json(['status' => 404, 'message' => 'Ooops...Something went wrong! Please try again.']);
            }
        } catch (\Exception $exception) {
            Helpers::log('gateway delete : exception');
            Helpers::log($exception);
            DB::rollBack();
            return response()->json(['status' => 500, 'message' => 'Ooops...Something went wrong! Please contact to support team']);
        }
    }

    /**
     * gateway list by branch id
     *
     * @param $branch_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function listByBranch($branch_id)
    {
        $gatewayList = Helpers::selectBoxGatewayListByBranch($branch_id);

        return response()->json(["status" => 200, "message" => "success", "data" => $gatewayList]);
    }
}
