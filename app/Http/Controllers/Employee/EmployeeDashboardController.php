<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Auth\EmployeeAccountVerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\ReconciliationRun;


/**
 * Controls routing and views for the main employee dashboard interface.
 */
class EmployeeDashboardController extends Controller
{
    private function getReconciliationTotalExceptions($user_id)
    {
        return ReconciliationRun::query()
            ->where('executed_by', $user_id)
            ->where('total_exceptions', '>', 0)
            ->count();
    }

    private function getTotalReconciled($user_id){
        return ReconciliationRun::query()->where('executed_by', $user_id)->count();
    }
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

        $runs = $this->renderReconcilaitonRun($userId);


        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions($userId);

        return view('employee.dashboard', compact('isVerified', 'runs', 'total_unmatched_discrepancies'));
    }

    public function renderReconcilaitonRun($userId){
            $runs = ReconciliationRun::query()
            ->where('executed_by', $userId)
            ->latest()
            ->paginate(5);
            
            return $runs;
    }
}