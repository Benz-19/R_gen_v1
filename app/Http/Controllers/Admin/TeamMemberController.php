<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDasboardService;
use Illuminate\Http\Request;

class TeamMemberController extends Controller{

    public function index(Request $request){
        $admin_id = $request->session()->get('user_id');
        $user_management = new AdminDasboardService()->userManagement($admin_id);

        $metrics = [
            'total_users' => count($user_management),
            'active_workspace' => 'Team Members',
        ];
        return view('/admin/team_members', compact('user_management', 'metrics'));
    }
}