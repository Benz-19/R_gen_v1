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
            SELECT u.username, u.email, ud.is_admin, ud.selected_role,
            CASE 
                WHEN ud.is_admin = 1 THEN 1
                ELSE COALESCE(wu.is_verified, 0) 
            END AS account_status
            FROM users as u
            LEFT JOIN user_details as ud ON ud.user_id = u.id
            LEFT JOIN workspace_user as wu ON wu.user_id = u.id
            LEFT JOIN workspaces as ws ON ws.id = wu.workspace_id;
        ');
        
    }
} 