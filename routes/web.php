<?php

use App\Http\Controllers\Reconciliation\ReconciliationController;
use App\Http\Controllers\Admin\AdminReconciliationController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Auth\LoginAuthController;
use App\Http\Controllers\Auth\LogoutAuthController;
use App\Http\Controllers\Auth\RegisterAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\EmployeeReconciliationController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\Reconciliation\UnmatchedDiscrepanciesController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureWorkspaceAccess;
use Illuminate\Support\Facades\Route;

Route::get('/demo', function () {
    return view('demo');
});

Route::get('/', function () {
    return view('landing');
});

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

    /*
     * Team member management
     *
     * These routes are intentionally inside the admin middleware group
     * because only administrators should be able to modify team accounts.
     */
    Route::prefix('/admin/users')->name('admin.users.')->group(function () {

        Route::put('/{user}', [TeamMemberController::class, 'update'])->name('update');

        Route::post('/{user}/activate', [TeamMemberController::class, 'activate'])->name('activate');

        Route::post('/{user}/deactivate', [TeamMemberController::class, 'deactivate'])->name('deactivate');

        Route::post('/{user}/password-reset', [TeamMemberController::class, 'passwordReset'])->name('password-reset');

        Route::delete('/{user}', [TeamMemberController::class, 'destroy'])->name('destroy');

    });

});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', EnsureWorkspaceAccess::class])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
     * Reconciliation index
     *
     * Both administrators and employees use the same URL.
     *
     * ReconciliationController@user determines which controller
     * should handle the request and which view should be displayed.
     */
    Route::get('/reconciliation-runs', [ReconciliationController::class, 'user'])
        ->name('reconciliation-runs.index');

    /*
     * Unmatched discrepancies
     */
    Route::get('/unmatched-discrepancies', [UnmatchedDiscrepanciesController::class, 'index'])
        ->name('unmatched.discrepancies.index');

    /*
     * Start reconciliation
     *
     * This remains available through the authenticated user routes.
     */
    Route::post('/reconciliation-runs/execute', [ReconciliationController::class, 'execute'])
        ->name('reconciliation.runs.execute');

    /*
     * IMPORTANT:
     *
     * These MUST use {run}, not {id},
     * because the controller receives:
     *
     * ReconciliationRun $run
     */
    Route::get('/reconciliation-runs/{run}/status', [ReconciliationController::class, 'checkStatus'])
        ->name('reconciliation.runs.status');

    Route::get('/reconciliation-runs/{run}/results', [ReconciliationController::class, 'getResults'])
        ->name('reconciliation.runs.results');

    Route::get('/reconciliation-runs/{run}/export/{type}', [ReconciliationController::class, 'exportFile'])
        ->name('reconciliation.runs.export');

    /*
     * Execute reconciliation runs page
     *
     * Both administrators and employees can access the same URL.
     *
     * ReconciliationController@executeUser determines which controller
     * should handle the request and therefore which view is displayed.
     */
    Route::get('/execute-recon-runs', [ReconciliationController::class, 'executeUser'])
        ->name('reconciliation.execute');

    /*
     * Trigger reconciliation run page
     *
     * Both administrators and employees can access the same URL.
     *
     * ReconciliationController@triggerUser determines which controller
     * should handle the request and therefore which view is displayed.
     */
    Route::get('/trigger-run', [ReconciliationController::class, 'triggerUser'])
        ->name('reconciliation.trigger');
});

/*
|--------------------------------------------------------------------------
| Employee Routes
|--------------------------------------------------------------------------
*/

/*
 * Employee-specific routes can be added here when employees have
 * functionality that does not share the same URL as administrators.
 *
 * The shared reconciliation routes above do not need separate
 * employee routes because ReconciliationController handles the
 * role-based dispatching.
 */