<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReconciliationRun;
use App\Services\Admin\AdminDasboardService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller{

    private function getReconciliationTotalExceptions(){
        $runs = ReconciliationRun::query()->latest()->paginate();
        $exceptions = 0;
        foreach($runs as $run){
            if(!empty($run->total_exceptions) && $run->total_exceptions >0){
                $exceptions+=1;
            }
        }

        return $exceptions;
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

        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions();

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