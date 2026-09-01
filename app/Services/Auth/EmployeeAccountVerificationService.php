<?php

namespace App\Services\Auth;

use App\Models\Workspace;
use App\Models\WorkspaceUser;
use Exception;

/**
 * Handles business logic for verifying employee membership in organization workspaces.
 */
class EmployeeAccountVerificationService
{
    /**
     * Checks if a user has an active, verified workspace association.
     *
     * @param  array{user_id: int|string|null}  $credentials  An associative array containing the 'user_id' key.
     * @return bool True if a verified workspace entry exists for the user; false otherwise.
     */
    public function isVerified(array $credentials): bool
    {
        if (empty($credentials['user_id'])) {
            return false;
        }

        return WorkspaceUser::where('user_id', $credentials['user_id'])->where('is_verified', 1)->exists();
    }

    /**
     * Validates company join credentials and creates or updates the user's workspace association.
     *
     * @param  array{join_code: string, company_name: string, user_id: int|string}  $data  Payload containing organization join details and user identifier.
     * @return bool Returns true on successful association and verification.
     *
     * @throws Exception If no matching workspace is found for the given join code and company name.
     */
    public function verifyEmployee(array $data): bool
    {
        $workspace = Workspace::where('join_code', $data['join_code'])->where('name', $data['company_name'])->first();

        if (!$workspace) {
            throw new Exception("Invalid join code or company name for this organization. Contact your admin.");
        }

        WorkspaceUser::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'workspace_id' => $workspace->id,
            ],
            [
                'is_verified' => 1,
            ]
        );

        return true;
    }
}