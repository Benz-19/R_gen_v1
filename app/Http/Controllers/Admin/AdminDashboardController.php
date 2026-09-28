<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReconciliationRun;
use App\Services\Admin\AdminDasboardService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller{

    private function getReconciliationTotalExceptions($user_id)
    {
        return ReconciliationRun::query()
            ->where('executed_by', $user_id)
            ->where('total_exceptions', '>', 0)
            ->count();
    }

    public function index(Request $request){
        $metrics = [
            'total_users' => count($this->userManagement($request)),
            'active_workspace' => 'default organization',
            'pending_exceptions' => 0,
            'total_reconciled' => 20,
        ];

        $user_management = $this->userManagement($request);
        $runs = $this->renderReconcilaitonRun();

        $user_id = $request->session()->get('user_id');

        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions($user_id);

        return view('admin.dashboard', compact('metrics', 'user_management', 'runs', 'total_unmatched_discrepancies'));
    }

    public function userManagement(Request $request){
        $admin_id = $request->session()->get('user_id');
        $user_management = new AdminDasboardService()->userManagement($admin_id);

        return $user_management;
    }

    public function renderReconcilaitonRun(){
            $runs = ReconciliationRun::query()
            ->latest()
            ->paginate(5);
            
            return $runs;
    }
}