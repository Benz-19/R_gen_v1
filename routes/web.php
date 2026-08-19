<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PagesController;
use Illuminate\Support\Facades\Route;


Route::get('/demo', function () {
    return view('demo');
});
Route::get('/', function () {
    return view('landing');
});

// login
Route::get('/login', [AuthController::class, 'loginPage']);

// pages
Route::get('/help-center', [PagesController::class, 'help_center']);
Route::get('/system-status', [PagesController::class, 'system_status']);
Route::get('/privacy', [PagesController::class, 'privacy']);