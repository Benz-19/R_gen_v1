<?php
namespace App\Http\Controllers\Reconciliation;

use App\Http\Controllers\Controller;
use App\Models\ReconciliationRun;
use Illuminate\Http\Request;

class UnmatchedDiscrepanciesController extends Controller{

    public function index(Request $request){
        $runs = ReconciliationRun::query()->latest()->paginate(20);

        $total_unmatched_discrepancies = $this->totalExceptions($runs);

        return view('admin.unmatched_discrepancies', compact('runs', 'total_unmatched_discrepancies'));
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