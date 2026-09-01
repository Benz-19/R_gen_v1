<?php
namespace App\Services\Admin;

use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

class AdminDasboardService{

    public function userManagement($admin_id){
        $workspace = Workspace::where('user_id', $admin_id)->first();

        if(!$workspace){
            return [];
        }

        return DB::select('
            SELECT ws.*, u.name, u.email, ud.selected_role 
            FROM `workspaces` as ws 
            JOIN users as u ON u.id = ws.user_id
            JOIN user_details as ud ON ud.user_id = ws.user_id
            WHERE u.id = ? and ws.join_code = ?
        ', [$admin_id, $workspace->join_code]);
        
    }
} 