<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReconciliationRun;
use App\Services\Admin\ReconciliationRunService;
use Illuminate\Http\Request;

class AdminReconciliationController extends Controller{

    private function getReconciliationTotalExceptions($user_id)
    {
        return ReconciliationRun::query()
            ->where('executed_by', $user_id)
            ->where('total_exceptions', '>', 0)
            ->count();
    }

    public function index(Request $request){

        $reconciliation_run_service = new ReconciliationRunService();
        $runs = $reconciliation_run_service->latest_run();

        $user_id = $request->session()->get('user_id');
        
        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions($user_id);    
        return view('admin.reconciliation_runs', compact('runs', 'total_unmatched_discrepancies'));
        }
        
    public function trigger_run(Request $request){
        $user_id = $request->session()->get('user_id');
        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions($user_id);    
        return view('admin.trigger_run', compact('total_unmatched_discrepancies'));
    }
}