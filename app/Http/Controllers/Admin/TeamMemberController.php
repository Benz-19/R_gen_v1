<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReconciliationRun;
use App\Models\User;
use App\Models\UserDetail;
use App\Services\Admin\AdminDasboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class TeamMemberController extends Controller
{
    private function getReconciliationTotalExceptions($user_id)
    {
        return ReconciliationRun::query()
            ->where('executed_by', $user_id)
            ->where('total_exceptions', '>', 0)
            ->count();
    }

    public function index(Request $request)
    {
        $admin_id = $request->session()->get('user_id');

        $user_management = new AdminDasboardService()
            ->userManagement($admin_id);

        $total_unmatched_discrepancies =
            $this->getReconciliationTotalExceptions($admin_id);

        $metrics = [
            'total_users' => count($user_management),
            'active_workspace' => 'Team Members',
        ];

        return view(
            '/admin/team_members',
            compact(
                'user_management',
                'metrics',
                'total_unmatched_discrepancies'
            )
        );
    }

    /**
     * Update username, email and administrator role.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $admin_id = $request->session()->get('user_id');

        /*
        * Prevent an administrator from modifying their own
        * administrative privileges through this screen.
        */
        if ((int) $user->id === (int) $admin_id) {
            return response()->json([
                'message' => 'You cannot modify your own administrator account from this screen.'
            ], 403);
        }

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'is_admin' => [
                'required',
                'boolean',
            ],
        ]);

        // users table
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->save();

        // user_details table
        UserDetail::where('user_id', $user->id)->update([
            'is_admin' => (bool) $validated['is_admin'],
        ]);

        return response()->json([
            'message' => 'User account updated successfully.',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'is_admin' => (bool) $validated['is_admin'],
                'account_status' => (bool) $user->account_status,
            ],
        ]);
    }

    /**
     * Activate a user account.
     */
    public function activate(Request $request, User $user): JsonResponse
    {
        $admin_id = $request->session()->get('user_id');

        if ((int) $user->id === (int) $admin_id) {
            return response()->json([
                'message' => 'Your own account does not need to be activated.'
            ], 422);
        }

        if ((bool) $user->account_status) {
            return response()->json([
                'message' => 'This account is already active.'
            ]);
        }

        $user->account_status = true;
        $user->save();

        return response()->json([
            'message' => 'User account activated successfully.',
            'account_status' => true,
        ]);
    }

    /**
     * Deactivate a user account.
     */
    public function deactivate(Request $request, User $user): JsonResponse
    {
        $admin_id = $request->session()->get('user_id');

        /*
         * Never allow the currently authenticated administrator
         * to disable their own account.
         */
        if ((int) $user->id === (int) $admin_id) {
            return response()->json([
                'message' => 'You cannot deactivate your own administrator account.'
            ], 403);
        }

        if (!(bool) $user->account_status) {
            return response()->json([
                'message' => 'This account is already inactive.'
            ]);
        }

        $user->account_status = false;
        $user->save();

        return response()->json([
            'message' => 'User account deactivated successfully.',
            'account_status' => false,
        ]);
    }

    /**
     * Send a password reset request to the selected user.
     */
    public function passwordReset(Request $request, User $user): JsonResponse
    {
        $status = Password::sendResetLink([
            'email' => $user->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Unable to send the password reset request.'
            ], 422);
        }

        return response()->json([
            'message' => 'Password reset request sent successfully.'
        ]);
    }

    /**
     * Permanently delete a user account.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $admin_id = $request->session()->get('user_id');

        /*
         * Prevent an administrator from deleting their own account.
         */
        if ((int) $user->id === (int) $admin_id) {
            return response()->json([
                'message' => 'You cannot delete your own administrator account.'
            ], 403);
        }

        $username = $user->username;

        $user->delete();

        return response()->json([
            'message' => "User account '{$username}' deleted successfully."
        ]);
    }
}