<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class RegisterService extends AuthService
{
    public function register(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'username'     => $data['fullName'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $workspaceId = null;
            $isAdmin = false;

            if ($data['accountType'] === 'organization') {
                $workspace = Workspace::where('name', $data['companyName'])->first();

                if ($workspace) {
                    // employee
                    if ($workspace->join_code !== $data['workspaceCode']) {
                        throw new Exception("Invalid Join Code for this organization.");
                    }
                    $workspaceId = $workspace->id;
                } else {
                    // admin
                    $isAdmin = true;
                    $workspace = Workspace::create([
                        'name'          => $data['companyName'],
                        'join_code'     => Workspace::generateUniqueCode(),
                        'user_id'       => $user->id,
                        'admin_user_id' => $user->id,
                        'is_verified' => true,
                    ]);
                    $workspaceId = $workspace->id;
                }
            }

            UserDetail::create([
                'user_id'             => $user->id,
                'workspace_id'        => $workspaceId,
                'account_type'        => $data['accountType'],
                'is_admin'            => $isAdmin,
                'primary_data_source' => $data['primaryDataSource'] ?? null,
                'selected_role'       => $data['selectedRole'] ?? null,
            ]);

            return $user->load('userDetail');
        });
    }

    public function verifyCompanyJoinCode(array $data): bool
    {
        return Workspace::where('name', $data['companyName'])->where('join_code', $data['joinCode'])->exists();
    }
}