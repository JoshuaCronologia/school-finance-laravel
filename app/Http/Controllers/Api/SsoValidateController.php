<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BranchUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SsoValidateController extends Controller
{
    /**
     * Validate an SSO request from the SIS.
     *
     * POST /api/sso/validate
     *
     * Expected payload:
     * {
     *   "parent_id": "EMP-001",
     *   "parent_type": "employee",   // "employee" or "student"
     *   "branch_code": "main",
     *   "platform": "kto12",         // "kto12" or "college"
     *   "name": "Juan Dela Cruz",
     *   "email": "juan@school.edu.ph",
     *   "token": "shared-secret"
     * }
     */
    public function validate(Request $request)
    {
        $validated = $request->validate([
            'parent_id'   => 'required|string',
            'parent_type' => 'required|in:employee,student',
            'branch_code' => 'required|string',
            'platform'    => 'required|in:kto12,college',
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'token'       => 'required|string',
        ]);

        // Verify shared secret
        $ssoSecret = config('app.sso_secret', env('SSO_SHARED_SECRET', ''));
        if (empty($ssoSecret) || $validated['token'] !== $ssoSecret) {
            return response()->json(['valid' => false, 'reason' => 'invalid_token'], 401);
        }

        // Map parent_type to model class
        $parentTypeMap = [
            'employee' => \App\Models\Employee::class,
            'student'  => \App\Models\Student::class,
        ];
        $parentType = $parentTypeMap[$validated['parent_type']];

        // Check if user has a BranchUser record (whitelist)
        $branchUser = BranchUser::where('parent_id', $validated['parent_id'])
            ->where('parent_type', $parentType)
            ->where('branch_code', $validated['branch_code'])
            ->first();

        if (!$branchUser) {
            return response()->json([
                'valid'  => false,
                'reason' => 'no_access',
                'message' => 'User does not have access to the Accounting system.',
            ]);
        }

        if (!$branchUser->is_active) {
            return response()->json([
                'valid'  => false,
                'reason' => 'deactivated',
                'message' => 'User access has been deactivated.',
            ]);
        }

        // Generate one-time login token (60-second TTL)
        $loginToken = Str::random(64);
        Cache::put("sso_login:{$loginToken}", [
            'branch_user_id' => $branchUser->id,
            'branch_code'    => $validated['branch_code'],
            'platform'       => $validated['platform'],
            'name'           => $validated['name'],
            'email'          => $validated['email'] ?? $branchUser->email,
        ], now()->addSeconds(60));

        $loginUrl = url("/branch-login?token={$loginToken}");

        return response()->json([
            'valid'     => true,
            'login_url' => $loginUrl,
            'user'      => [
                'id'          => $branchUser->id,
                'name'        => $branchUser->name,
                'permissions' => $branchUser->getPermissionNames()->toArray(),
            ],
        ]);
    }
}
