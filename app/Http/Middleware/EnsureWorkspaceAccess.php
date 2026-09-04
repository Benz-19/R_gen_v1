<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admins bypass workspace checks entirely
        if ($user?->userDetail?->is_admin) {
            return $next($request);
        }

        // Allow unverified employees to view the dashboard modal and submit the verification API
        
        if (($request->isMethod('GET') && $request->is('dashboard')) || $request->is('api/v1/verify-employee-account')){
            return $next($request);
        }

        // Bypass check for Individual Users (they manage their own workspace)
        if ($user->userDetail->account_type === 'individual') {
            return $next($request);
        }

        // Check if employee belongs to any verified workspace
        $hasAccess = $user->workspaces()
            ->where('workspace_user.is_verified', 1)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'You do not have active access to this workspace');
        }

        return $next($request);
    }
}
