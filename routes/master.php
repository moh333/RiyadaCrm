<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Master Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" and "auth:master" middleware groups.
|
*/

Route::prefix('admin')->name('master.')->group(function () {
    Route::get('/', function () {
        return 'Master Admin Dashboard';
    })->name('dashboard');
});
