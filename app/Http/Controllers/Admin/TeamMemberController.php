<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReconciliationRun;
use App\Services\Admin\AdminDasboardService;
use Illuminate\Http\Request;

class TeamMemberController extends Controller{

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
        $admin_id = $request->session()->get('user_id');
        $user_management = new AdminDasboardService()->userManagement($admin_id);

        $total_unmatched_discrepancies = $this->getReconciliationTotalExceptions();
        $metrics = [
            'total_users' => count($user_management),
            'active_workspace' => 'Team Members',
        ];
        return view('/admin/team_members', compact('user_management', 'metrics', 'total_unmatched_discrepancies'));
    }
}