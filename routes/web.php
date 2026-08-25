<?php

use App\Http\Controllers\Auth\LoginAuthController;
use App\Http\Controllers\Auth\RegisterAuthController;
use App\Http\Controllers\PagesController;
use Illuminate\Support\Facades\Route;


Route::get('/demo', function () {
    return view('demo');
});
Route::get('/', function () {
    return view('landing');
});

// Authentication
Route::get('/login', [LoginAuthController::class, 'loginPage']);//login
Route::get('/register', [RegisterAuthController::class, 'registerPage']);


// pages
Route::get('/help-center', [PagesController::class, 'help_center']);
Route::get('/system-status', [PagesController::class, 'system_status']);
Route::get('/privacy', [PagesController::class, 'privacy']);