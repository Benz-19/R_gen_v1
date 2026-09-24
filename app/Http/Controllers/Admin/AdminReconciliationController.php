<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReconciliationRun;
use App\Services\Admin\ReconciliationRunService;
use Illuminate\Http\Request;

class AdminReconciliationController extends Controller{

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

        $reconciliation_run_service = new ReconciliationRunService();
        $runs = $reconciliation_run_service->latest_run();

        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions();    
        return view('admin.reconciliation_runs', compact('runs', 'total_unmatched_discrepancies'));
    }

    public function trigger_run(Request $request){
        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions();    
        return view('admin.trigger_run', compact('total_unmatched_discrepancies'));
    }
}