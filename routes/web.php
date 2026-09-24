<?php

use App\Http\Controllers\Reconciliation\ReconciliationController;
use App\Http\Controllers\Admin\AdminReconciliationController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Auth\LoginAuthController;
use App\Http\Controllers\Auth\LogoutAuthController;
use App\Http\Controllers\Auth\RegisterAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\Reconciliation\UnmatchedDiscrepanciesController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureWorkspaceAccess;
use Illuminate\Support\Facades\Route;

Route::get('/demo', function () { return view('demo'); });

Route::get('/', function () { return view('landing'); });

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginAuthController::class, 'loginPage'])->name('login');

Route::get('/register', [RegisterAuthController::class, 'registerPage']);

Route::post('/logout', [LogoutAuthController::class, 'logout']);

Route::post('/process-login', [LoginAuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Pages
|--------------------------------------------------------------------------
*/

Route::get('/help-center', [PagesController::class, 'help_center']);

Route::get('/system-status', [PagesController::class, 'system_status']);

Route::get('/privacy', [PagesController::class, 'privacy']);

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware([EnsureUserIsAdmin::class])->group(function () {

    Route::get('/team-members', [TeamMemberController::class, 'index']);

    Route::get('/execute-recon-runs', [AdminReconciliationController::class, 'index'])->name('admin.reconciliation-runs');

    Route::get('/trigger-run', [AdminReconciliationController::class, 'trigger_run'])->name('admin.trigger-run');
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', EnsureWorkspaceAccess::class])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
     * Reconciliation index
     */
    Route::get('/reconciliation-runs', [ReconciliationController::class, 'index'])->name('reconciliation-runs.index');

    /*
     * Reconciliation index
     */
    Route::get('/unmatched-discrepancies', [UnmatchedDiscrepanciesController::class, 'index'])->name('unmatched.discrepancies.index');

    /*
     * Start reconciliation
     */
    Route::post('/reconciliation-runs/execute', [ReconciliationController::class, 'execute'])->name('reconciliation.runs.execute');

    /*
     * IMPORTANT:
     *
     * These MUST use {run}, not {id},
     * because the controller receives:
     *
     * ReconciliationRun $run
     */
    Route::get('/reconciliation-runs/{run}/status', [ReconciliationController::class, 'checkStatus'])->name('reconciliation.runs.status');

    Route::get('/reconciliation-runs/{run}/results', [ReconciliationController::class, 'getResults'])->name('reconciliation.runs.results');

    Route::get('/reconciliation-runs/{run}/export/{type}', [ReconciliationController::class, 'exportFile'])->name('reconciliation.runs.export');
});


// Admin
// Route::middleware([EnsureUserIsAdmin::class])->group(function(){
//     Route::get('/team-members', [TeamMemberController::class, 'index']);
//     Route::get('/reconciliation-runs', [AdminReconciliationController::class, 'index'])->name('admin.reconcilaition-runs');
//     Route::get('/trigger-run', [AdminReconciliationController::class, 'trigger_run'])->name('admin.trigger-run');
// });



// Route::middleware(['auth', EnsureWorkspaceAccess::class])->group(function () {
//     // dashboard
//     Route::get('/dashboard', [DashboardController::class, 'index'])
//         ->name('dashboard');
//     // 1. Route to receive the form, execute Python ML engine, and save results
//     Route::post('/reconciliation-runs/execute', [ReconciliationController::class, 'execute'])
//         ->name('reconciliation.runs.execute');

//     // 2. API Route to fetch structured JSON analysis results for dynamic frontend rendering
//     Route::get('/reconciliation-runs/{id}/results', [ReconciliationController::class, 'getResults'])
//         ->name('reconciliation.runs.results');
        
//     // 3. (Optional) API Route to download output CSV files (matches, unmatched_a, unmatched_b)
//     Route::get('/reconciliation-runs/{id}/export/{type}', [ReconciliationController::class, 'exportFile'])
//         ->name('reconciliation.runs.export');
// });