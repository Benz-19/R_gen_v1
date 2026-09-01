<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\API\V1\Auth\EmployeeAccountVerificationAPIAuthController;
use App\Http\Controllers\API\V1\Auth\RegisterAPIAuthController;
use App\Http\Controllers\ReconciliationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/v1/reconcile/match-demo', [ReconciliationController::class, 'match']);


// VERSION 1
Route::prefix('v1')->middleware(['web'])->group(function () {
    // USER REGISTRATION
    Route::post('/send-code', [RegisterAPIAuthController::class, 'sendVerificationCode']);
    Route::post('/verify-registration-code', [RegisterAPIAuthController::class, 'verifyRegistrationCode']);
    Route::post('/verify-company-join-code', [RegisterAPIAuthController::class, 'verifyCompanyJoinCode']);
    Route::post('/verify-employee-account', [EmployeeAccountVerificationAPIAuthController::class, 'verifyEmployee']);
    Route::post('/check-company', [RegisterAPIAuthController::class, 'checkCompany']);
    Route::post('/register', [RegisterAPIAuthController::class, 'registerAPI']);
});

