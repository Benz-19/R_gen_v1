<?php

namespace App\Services\Workspace;

use Illuminate\Support\Facades\DB;

class WorkspaceService
{
    public function get_workspace_id($user_id)
    {
        $result = DB::selectOne("
            SELECT DISTINCT COALESCE(wu.workspace_id, ws.id) AS workspace_id
            FROM workspaces ws
            LEFT JOIN workspace_user wu ON ws.id = wu.workspace_id AND wu.user_id = :user_id1
            WHERE ws.admin_user_id = :user_id2 OR wu.user_id = :user_id3
            LIMIT 1
        ", [
            'user_id1' => $user_id,
            'user_id2' => $user_id,
            'user_id3' => $user_id,
        ]);

        return $result ? $result->workspace_id : null;
    }
}