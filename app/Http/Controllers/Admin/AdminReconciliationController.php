<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReconciliationRun;
use App\Services\Admin\ReconciliationRunService;
use App\Services\ReconciliationService;
use App\Services\Workspace\WorkspaceService;
use Illuminate\Http\Request;

class AdminReconciliationController extends Controller{

    private function getWorkspaceId($user_id){
        return (new WorkspaceService)->get_workspace_id($user_id);
    }

    private function getReconciliationExecutedBy($workspace_id){
        return (new ReconciliationService)->ReconciliationExecutedBy($workspace_id);
    }   

    private function getReconciliationTotalExceptions($user_id)
    {
        return ReconciliationRun::query()
            ->where('executed_by', $user_id)
            ->where('total_exceptions', '>', 0)
            ->count();
    }

    public function index(Request $request){

        $user_id = $request->session()->get('user_id');
        $workspace_id = $this->getWorkspaceId($user_id);

        $reconciliation_run_service = new ReconciliationRunService();
        $runs = $reconciliation_run_service->latest_run_all_tenant($workspace_id);
        
        $reconciliation_executed_by = $this->getReconciliationExecutedBy($workspace_id);


        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions($user_id);    
        return view('admin.reconciliation_runs', compact('runs', 'total_unmatched_discrepancies', 'reconciliation_executed_by'));
    }
        
    public function trigger_run(Request $request){
        $user_id = $request->session()->get('user_id');
        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions($user_id);    
        return view('admin.trigger_run', compact('total_unmatched_discrepancies'));
    }
}