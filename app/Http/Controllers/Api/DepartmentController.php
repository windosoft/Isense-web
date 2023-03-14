<?php

namespace App\Http\Controllers\Api;

use App\Models\Departments;
use App\Models\Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    protected $actStatus = '';

    /**
     * DepartmentController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
    }

    /**
     * Department list by branch id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function departmentList(Request $request)
    {
        Helpers::log('department list : start');
        try {
            $branchId = $request->branch_id;

            if (empty($branchId)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Branch id must be required"]);
            } else {

                $departmentList = Departments::where('status', $this->actStatus)
                    ->where('branch_id', $branchId)
                    ->select(
                        '*',
                        DB::raw('(SELECT name FROM branches WHERE branches.id = departments.branch_id) AS branchName'),
                        DB::raw('(SELECT count(*) FROM terminals WHERE department_id = departments.id AND terminals.status = "' . $this->actStatus . '") AS totalTerminal')
                    )
                    ->orderBy('id', 'DESC')
                    ->get()->toArray();

                $response = Helpers::replaceNullWithEmptyString($departmentList);
                Helpers::log('department list : finish');
                return response()->json(["status" => 200, "show" => false, "msg" => "success", "data" => $response]);
            }
        } catch (\Exception $exception) {
            Helpers::log('department list : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }
}
