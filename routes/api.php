<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'namespace' => 'Api'], function () {

    Route::get('/', function () {
        return response()->json(['status' => 200, "show" => false, "msg" => "welcome to api"]);
    });

    Route::get('role', 'ApiController@roleAccessList');
    Route::post('company/login', 'ApiController@login');
    Route::post('company/forgotPassword', 'ApiController@forgotPassword');
    Route::match(['get', 'post'], 'terminal/terminal-info', 'TerminalController@getTerminalInfo');
    Route::match(['get', 'post'], 'terminal/store-data', 'TerminalController@storeData');
    Route::match(['get', 'post'], 'terminal/direct', 'TerminalController@terminalDirect');
    Route::match(['get', 'post'], 'terminal/test', 'TerminalController@testConversion');
    Route::match(['get', 'post'], 'terminal/teltonica-direct', 'TerminalController@teltonicaDirect');
    Route::match(['get', 'post'], 'sendiosNote', 'ApiController@sendIOSNOtification');
    Route::match(['get', 'post'], 'testDashboardHistory', 'TerminalController@testDashboardHistory');

    Route::group(["middleware" => ["auth:api", "blockuser"]], function () {
        Route::post('companydashboard2', 'ApiController@companyDashboard');
        Route::post('branches/branchList', 'BranchController@branchList');
        Route::post('notificationfilter', 'NotificationController@notificationFilter');
        Route::post('receiver/receiverList', 'TerminalController@receiverList');
        Route::post('departments/departmentList', 'DepartmentController@departmentList');
        Route::post('device/realtime', 'NotificationController@realtimeDeviceData');
        Route::post('device/deviceHistory', 'DeviceController@deviceHistory');
        Route::post('company/profileUpdate', 'ApiController@profileUpdate');
        Route::post('company/profile', 'ApiController@profile');
        Route::post('company/changePassword', 'ApiController@changePassword');
        Route::post('notification/read-all', 'NotificationController@readAllNotification');
        Route::post('notification/read-single', 'NotificationController@singleNotificationRead');
        Route::post('notification/sound', 'NotificationController@notificationSound');
    });
});
