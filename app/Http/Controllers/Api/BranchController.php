<?php

namespace App\Http\Controllers\Api;

use App\Models\Branches;
use App\Models\Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    protected $actStatus = '';

    /**
     * BranchController constructor.
     */
    public function __construct()
    {
        $this->actStatus = Helpers::$active;
    }

    /**
     * branch list by company id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function branchList(Request $request)
    {
        Helpers::log('branch list : start');
        try {
            $companyId = $request->company_id;

            if (empty($companyId)) {
                return response()->json(["status" => 422, "show" => true, "msg" => "Company id must be required"]);
            } else {

                $branchList = Branches::where('status', $this->actStatus)
                    ->where('company_id', $companyId)
                    ->select(
                        '*',
                        DB::raw('(SELECT CONCAT(first_name," ",last_name) AS name FROM users WHERE users.id = branches.company_id) AS companyName'),
                        DB::raw('(SELECT count(*) FROM terminals WHERE branch_id = branches.id AND status != "D") AS totalReceiver')
                    )
                    ->orderBy('id', 'DESC')
                    ->get()->toArray();

                $response = Helpers::replaceNullWithEmptyString($branchList);
                Helpers::log('branch list : finish');
                return response()->json(["status" => 200, "show" => false, "msg" => "success", "data" => $response]);
            }
        } catch (\Exception $exception) {
            Helpers::log('branch list : exception');
            Helpers::log($exception);
            return response()->json(["status" => 500, "show" => true, "msg" => "Ooops..Something went wrong. Please try again."]);
        }
    }
}
