<?php
namespace App\Http\Controllers\Employee;

use App\Models\ReconciliationRun;
use Illuminate\Http\Request;

class EmployeeUnmatchedDiscrepancies{

    public function index(Request $request)
    {
        $user_id = $request->session()->get('user_id');

        $runs = ReconciliationRun::query()
            ->where('executed_by', $user_id)
            ->where('total_exceptions', '>', 0)
            ->latest()
            ->paginate(20);

        $total_unmatched_discrepancies = $this->totalExceptions($runs);

        return view(
            'employee.unmatched_discrepancies',
            compact('runs', 'total_unmatched_discrepancies')
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