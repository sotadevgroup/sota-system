<?php

use Illuminate\Support\Facades\Route;
use Sota\System\Controllers\AuthController;

Route::middleware('api')
    ->prefix('api/auth')
    ->group(function () {

        Route::post('login', '\\'.AuthController::class.'@login');
        Route::post('logout', '\\'.AuthController::class.'@logout');
        Route::post('refresh', '\\'.AuthController::class.'@refresh');
        Route::post('user', '\\'.AuthController::class.'@user');

    }
);

Route::get('{any}', function ($any = '') {
    return response()
        ->view('system::frontend');
})->where('any', '.*');