<?php
namespace App\Http\Controllers\Admin;

use App\Models\ReconciliationRun;
use App\Services\ReconciliationService;
use App\Services\Workspace\WorkspaceService;
use Illuminate\Http\Request;

class AdminUnmatchedDiscrepancies{

    private function getWorkspaceId($user_id){
        return (new WorkspaceService)->get_workspace_id($user_id);
    }

    private function getReconciliationTotalExceptionsByTeam($workspace_id)
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

    public function index(Request $request)
    {
        $user_id = $request->session()->get('user_id');
        $workspace_id = $this->getWorkspaceId($user_id);

        $runs = ReconciliationRun::query()
            ->where('workspace_id', $workspace_id)
            ->where('total_exceptions', '>', 0)
            ->latest()
            ->paginate(20);

        $total_unmatched_discrepancies = $this->totalExceptions($runs);
        $reconciliation_executed_by = $this->getReconciliationExecutedBy($workspace_id);
        $total_unmatched_discrepancies_by_team = $this->getReconciliationTotalExceptionsByTeam($workspace_id);
        $total_unmatched_discrepancies_by_user = $this->getReconciliationTotalExceptionsByUser($user_id);

        return view(
            'admin.unmatched_discrepancies',
            compact('runs', 'total_unmatched_discrepancies', 'reconciliation_executed_by', 
                    'total_unmatched_discrepancies_by_team', 'total_unmatched_discrepancies_by_user')
        );
    }

    
    private function totalExceptions($runs){
        $exceptions = 0;
        foreach($runs as $run){
            if(!empty($run->total_exceptions) && $run->total_exceptions >0){
                $exceptions+=1;
            }
        }

        return $exceptions;
    }
}