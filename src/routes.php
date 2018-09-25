<?php

use Illuminate\Support\Facades\Route;

Route::get('{any}', function ($any = '') {
    return response()
        ->view('system::frontend');
})->where('any', '.*');