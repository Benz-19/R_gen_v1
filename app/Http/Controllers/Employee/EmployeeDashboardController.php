<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Auth\EmployeeAccountVerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Controls routing and views for the main employee dashboard interface.
 */
class EmployeeDashboardController extends Controller
{
    /**
     * Renders the primary employee dashboard view.
     *
     * Checks the user's workspace verification status and passes state flags to the view 
     * to conditionally toggle the UI modal barrier.
     *
     * @param  Request  $request  The incoming HTTP request instance holding session data.
     * @return View The rendered 'employee.dashboard' Blade view.
     */
    public function index(Request $request): View
    {
        $userId = $request->session()->get('user_id');
        $verificationService = new EmployeeAccountVerificationService();

        $isVerified = $verificationService->isVerified(['user_id' => $userId]);

        return view('employee.dashboard', [
            'is_verified' => $isVerified,
        ]);
    }
}