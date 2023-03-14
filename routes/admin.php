<?php
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['namespace' => 'Admin'], function () {
    Route::get('login', 'AdminController@login')->name('login');
    Route::post('login', 'AdminController@loginPost')->name('login.post');

    Route::get('forgot-password', 'AdminController@forgotPassword')->name('forgot-password');
    Route::post('forgot-password', 'AdminController@forgotPasswordPost')->name('forgot-password.post');

    Route::get('reset-password/{token}', 'AdminController@resetPassword')->name('reset-password.token');
    Route::put('reset-password/{token}', 'AdminController@resetPasswordUpdate')->name('reset-password.update');

    Route::get('changeTheme/{themeOption}', 'AdminController@changeTheme')->name('changeTheme');
    Route::get('sendEmails', 'AdminController@sendEmails')->name('sendEmails');
    

    Route::get('403', function () {
        return view('backend.access-denied');
    })->name('403');

    Route::group(['middleware' => 'admin'], function () {
        Route::get('/', 'AdminController@masterDashboard')->name('masterdashboard');
        Route::get('/dashboard2', 'AdminController@index')->name('home');
        Route::get('/dashboard/getDoughnotDetails', 'AdminController@getDoughnotDetails')->name('getDoughnotDetails');
        Route::post('/dashboard/getLineChartData', 'AdminController@getLineChartData')->name('getLineChartData');
        Route::post('/dashboard/getLiveHistory', 'AdminController@getLiveHistory')->name('getLiveHistory');
        Route::get('/dashboard/getLiveNotification', 'AdminController@getLiveNotification')->name('getLiveNotification');
        Route::get('/dashboard/real-time-data', 'AdminController@dashboardRealTimeData')->name('dashboard.real-time-data');
        Route::post('/dashboard/sensor-history', 'AdminController@dashboardSensorHistory')->name('dashboard.sensor-history');
        Route::get('/logout', 'AdminController@logout')->name('logout');

        Route::get('/profile', 'AdminController@profile')->name('profile');
        Route::put('/profile', 'AdminController@profileUpdate')->name('profile.update');

        Route::put('roles/{uuid}/permissions', 'RoleController@permissionUpdate')->name('roles.permissions.update');
        Route::get('roles/{uuid}/permissions', 'RoleController@permissions')->name('roles.permissions');
        Route::resource('roles', 'RoleController');

        Route::get('company/{uuid}/delete', 'CompanyController@delete')->name('company.delete');
        Route::post('company-paginate', 'CompanyController@paginate')->name('company.paginate');
        Route::resource('company', 'CompanyController');

        Route::get('branches/{company_id}/list', 'BranchController@listByCompany')->name('branches.list.company');
        Route::get('branches/{uuid}/delete', 'BranchController@delete')->name('branches.delete');
        Route::post('branches-paginate', 'BranchController@paginate')->name('branches.paginate');
        Route::resource('branches', 'BranchController');

        Route::get('gateway/{branch_id}/list', 'GatewayController@listByBranch')->name('gateway.list.branch');
        Route::get('gateway/{uuid}/delete', 'GatewayController@delete')->name('gateway.delete');
        Route::post('gateway-paginate', 'GatewayController@paginate')->name('gateway.paginate');
        Route::resource('gateway', 'GatewayController');

        Route::get('sensor/{group_id}/group', 'SensorController@listByGroup')->name('sensor.list.group');
        Route::get('sensor/{company_id}/list', 'SensorController@listByCompany')->name('sensor.list.company');
        Route::get('sensor/{uuid}/delete', 'SensorController@delete')->name('sensor.delete');
        Route::post('sensor-paginate', 'SensorController@paginate')->name('sensor.paginate');
        Route::resource('sensor', 'SensorController');

        Route::get('group/{company_id}/list', 'GroupController@listByCompany')->name('group.list.company');
        Route::get('group/{uuid}/delete', 'GroupController@delete')->name('group.delete');
        Route::post('group-paginate', 'GroupController@paginate')->name('group.paginate');
        Route::resource('group', 'GroupController');

        Route::get('employee/{uuid}/delete', 'EmployeeController@delete')->name('employee.delete');
        Route::post('employee-paginate', 'EmployeeController@paginate')->name('employee.paginate');
        Route::resource('employee', 'EmployeeController');

        Route::get('temperatures/{uuid}/delete', 'TemperaturesController@delete')->name('temperatures.delete');
        Route::post('temperatures-paginate', 'TemperaturesController@paginate')->name('temperatures.paginate');
        Route::resource('temperatures', 'TemperaturesController');

        Route::get('humidity/{uuid}/delete', 'HumidityController@delete')->name('humidity.delete');
        Route::post('humidity-paginate', 'HumidityController@paginate')->name('humidity.paginate');
        Route::resource('humidity', 'HumidityController');

        Route::get('voltage/{uuid}/delete', 'VoltageController@delete')->name('voltage.delete');
        Route::post('voltage-paginate', 'VoltageController@paginate')->name('voltage.paginate');
        Route::resource('voltage', 'VoltageController');

        Route::get('offline/{uuid}/delete', 'OfflineController@delete')->name('offline.delete');
        Route::post('offline-paginate', 'OfflineController@paginate')->name('offline.paginate');
        Route::resource('offline', 'OfflineController');

        Route::delete('notification/multi-delete', 'NotificationController@multiDestroy')->name('notification.multi.destroy');
        Route::post('notification/multi-delete', 'NotificationController@multiDelete')->name('notification.multi.delete');
        Route::get('notification/{uuid}/delete', 'NotificationController@delete')->name('notification.delete');
        Route::post('notification-paginate', 'NotificationController@paginate')->name('notification.paginate');
        Route::resource('notification', 'NotificationController');

        Route::get('report/{device}', 'ReportController@index')->name('report.device');
        Route::post('report/download/csv', 'ReportController@downloadReportCSV')->name('report.download.csv');
        Route::post('report/download/pdf', 'ReportController@downloadReportPdf')->name('report.download.pdf');
        Route::post('report', 'ReportController@generateDeviceReport')->name('report.post');
        Route::get('report', 'ReportController@index')->name('report.index');

        Route::get('schedule/{uuid}/delete', 'ScheduleUpdateController@delete')->name('schedule.delete');
        Route::get('schedule/getsensor/{company_id}', 'ScheduleUpdateController@getCompanySensor')->name('schedule.getsensor');
        Route::post('schedule-paginate', 'ScheduleUpdateController@paginate')->name('schedule.paginate');
        Route::resource('schedule', 'ScheduleUpdateController');
    });
});
