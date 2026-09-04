<?php

use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Auth\LoginAuthController;
use App\Http\Controllers\Auth\LogoutAuthController;
use App\Http\Controllers\Auth\RegisterAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PagesController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureWorkspaceAccess;
use Illuminate\Support\Facades\Route;


Route::get('/demo', function () {
    return view('demo');
});
Route::get('/', function () {
    return view('landing');
});

// Authentication
Route::get('/login', [LoginAuthController::class, 'loginPage'])->name('login');//login
Route::get('/register', [RegisterAuthController::class, 'registerPage']);
Route::post('/logout', [LogoutAuthController::class, 'logout']);

// Process Login
Route::post('/process-login', [LoginAuthController::class, 'login']);

// pages
Route::get('/help-center', [PagesController::class, 'help_center']);
Route::get('/system-status', [PagesController::class, 'system_status']);
Route::get('/privacy', [PagesController::class, 'privacy']);

// dashboard
Route::middleware(['auth', EnsureWorkspaceAccess::class])->group(function(){
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Admin
Route::middleware([EnsureUserIsAdmin::class])->group(function(){
    Route::get('/team-members', [TeamMemberController::class, 'index']);
});