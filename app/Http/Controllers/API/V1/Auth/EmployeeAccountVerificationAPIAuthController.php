<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\EmployeeAccountVerificationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles API authentication and account verification requests for employees.
 */
class EmployeeAccountVerificationAPIAuthController extends Controller
{
    /**
     * Handles employee workspace verification via API requests.
     *
     * Validates incoming request data, verifies active session state, and invokes 
     * the verification service to associate the employee with a workspace.
     *
     * @param  Request  $request  The incoming HTTP request containing session and validation inputs.
     * @return JsonResponse JSON payload indicating success (200), unauthenticated state (401), or validation/business logic error (422).
     */
    public function verifyEmployee(Request $request): JsonResponse
    {
        $userId = $request->session()->get('user_id');

        if (!$userId) {
            return response()->json([
                'state' => false,
                'message' => 'Unauthenticated session.'
            ], 401);
        }

        $validated = $request->validate([
            'join_code' => 'required|string',
            'company_name' => 'required|string',
        ]);

        $validated['user_id'] = $userId;

        try {
            $verifyService = new EmployeeAccountVerificationService();
            $verifyService->verifyEmployee($validated);

            return response()->json([
                'state' => true,
                'message' => 'Account was verified successfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'state' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}