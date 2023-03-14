<?php

namespace App\Http\Middleware;

use App\Models\Helpers;
use App\Models\Roles;
use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class BlockUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $isActive = 0;
        $userData = Auth::user();
        $actStatus = Helpers::$active;

        if ($userData->status == $actStatus) {
            $isActive = 1;

            if ($userData->role_id == Roles::$employee) {
                $checkCompany = User::where('id', $userData->company_id)
                    ->where('status', $actStatus)
                    ->count();
                if ($checkCompany == 0) {
                    $isActive = 0;
                }
            }
        }

        if ($request->is('api') || $request->is('api/*')) {
            if ($isActive == 1) {
                return $next($request);
            } else {
                return response()->json(["status" => 403, "show" => true, "msg" => "Sorry, your account has been blocked. Please contact to support team."]);
            }
        } else {
            if ($isActive == 1) {
                return $next($request);
            } else {
                Auth::logout();
                return redirect()->route('login');
            }
        }
    }
}
