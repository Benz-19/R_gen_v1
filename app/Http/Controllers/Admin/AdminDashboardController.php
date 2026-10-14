<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReconciliationRun;
use App\Services\Admin\AdminDasboardService;
use App\Services\ReconciliationService;
use App\Services\Workspace\WorkspaceService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller{

    private function getWorkspaceId($user_id){
        return (new WorkspaceService)->get_workspace_id($user_id);
    }

    private function getReconciliationTotalExceptions($workspace_id)
    {
        return (new ReconciliationService)->getReconciliationTotalExceptions($workspace_id);
    }

    private function getReconciliationTotalExceptionsByUser($user_id)
    {
        return (new ReconciliationService)->getReconciliationTotalExceptionsByUser($user_id);
    }

    private function getTotalReconciled($workspace_id){
        return (new ReconciliationService)->getTotalReconciled($workspace_id);
    }

    private function getTotalReconciledByUser($user_id){
        return (new ReconciliationService)->getTotalReconciledByUser($user_id);
    }

    private function getReconciliationExecutedBy($workspace_id){
        return (new ReconciliationService)->ReconciliationExecutedBy($workspace_id);
    }

    public function index(Request $request){

        $user_id = $request->session()->get('user_id');
        $workspace_id = $this->getWorkspaceId($user_id);


        $metrics = [
            'total_users' => count($this->userManagement($request)),
            'active_workspace' => 'default organization',
            'total_reconciled_by_user' => $this->getTotalReconciledByUser($user_id) ?? 0,
            'total_reconciled_by_team' => $this->getTotalReconciled($workspace_id) ?? 0,
            'total_exeptions_caught_by_team' => $this->getReconciliationTotalExceptions($workspace_id) ?? 0,
            'total_exeptions_caught_by_user' => $this->getReconciliationTotalExceptionsByUser($user_id) ?? 0,
        ];

        $user_management = $this->userManagement($request);
        $runs = $this->renderReconcilaitonRun($workspace_id);


        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions($workspace_id);
        $reconciliation_executed_by = $this->getReconciliationExecutedBy($workspace_id);

        return view('admin.dashboard', compact('metrics', 'user_management', 'runs', 'total_unmatched_discrepancies', 'reconciliation_executed_by'));
    }

    public function userManagement(Request $request){
        $admin_id = $request->session()->get('user_id');
        $user_management = new AdminDasboardService()->userManagement($admin_id);

        return $user_management;
    }

    public function renderReconcilaitonRun($workspace_id){
            $runs = ReconciliationRun::query()
            ->where('workspace_id', $workspace_id)
            ->latest()
            ->paginate(5);
            
            return $runs;
    }
}