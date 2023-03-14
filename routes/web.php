<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Auth::routes();

Route::get('front', function () {
    return redirect()->route('admin.home');
})->name('home');

Route::get('front-login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return "Cleared!";
});

Route::get('/migrate', function () {
    return Artisan::call('migrate');
});

Route::get('/migrate/pro', function () {
    return Artisan::call('migrate --force');
});
