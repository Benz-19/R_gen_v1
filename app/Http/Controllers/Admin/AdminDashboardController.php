<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDasboardService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller{

    public function index(Request $request){
        $metrics = [
            'total_users' => count($this->userManagement($request)),
            'active_workspace' => 'default organization',
            'pending_exceptions' => 0,
            'total_reconciled' => 20,
        ];

        $user_management = $this->userManagement($request);

        return view('admin.dashboard', compact('metrics', 'user_management'));
    }

    public function userManagement(Request $request){
        $admin_id = $request->session()->get('user_id');
        $user_management = new AdminDasboardService()->userManagement($admin_id);

        return $user_management;
    }
}