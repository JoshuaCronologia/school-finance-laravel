<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Users\BranchUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class BranchLoginController extends Controller
{
    /**
     * SSO login via one-time token.
     *
     * GET /branch-login?token={token}
     */
    public function login(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('no-access')->with('error', 'Missing login token.');
        }

        // Retrieve and consume the one-time token
        $cacheKey  = "sso_login:{$token}";
        $tokenData = Cache::pull($cacheKey);

        if (!$tokenData) {
            return redirect()->route('no-access')->with('error', 'Login token expired or invalid.');
        }

        // Load BranchUser
        $branchUser = BranchUser::find($tokenData['branch_user_id']);

        if (!$branchUser || !$branchUser->is_active) {
            return redirect()->route('no-access')->with('error', 'Access denied.');
        }

        // Build user info for session
        $permissions = $branchUser->getPermissionNames()->toArray();

        $userInfo = [
            'branch_user_id' => $branchUser->id,
            'user_type'      => $branchUser->parent_type,
            'name'           => $branchUser->name,
            'fname'          => $tokenData['name'],
            'email'          => $tokenData['email'] ?? $branchUser->email,
        ];

        // Set session variables
        Session::put('user_id', $branchUser->parent_id);
        Session::put('branch_code', $tokenData['branch_code']);
        Session::put('platform', ucfirst($tokenData['platform'])); // 'Kto12' or 'College'
        Session::put('permissions', $permissions);
        Session::put('user_info', $userInfo);
        Session::put('is_sso', true);

        return redirect('/');
    }

    /**
     * SSO logout — clear session and redirect.
     */
    public function logout(Request $request)
    {
        Session::flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'You have been logged out.');
    }

    /**
     * Alternative: Direct branch login without one-time token.
     *
     * GET /branch-login/{user_type}/{branch_code}/{hashed_id}
     */
    public function directLogin(string $userType, string $branchCode, string $hashedId)
    {
        // Find the BranchUser by matching md5 of parent_id
        $branchUser = BranchUser::where('branch_code', $branchCode)
            ->where('is_active', true)
            ->get()
            ->first(fn ($bu) => md5($bu->parent_id) === $hashedId);

        if (!$branchUser) {
            return redirect()->route('no-access')->with('error', 'User not found or no access.');
        }

        $permissions = $branchUser->getPermissionNames()->toArray();

        Session::put('user_id', $branchUser->parent_id);
        Session::put('branch_code', $branchCode);
        Session::put('platform', 'Kto12'); // default, can be passed as param
        Session::put('permissions', $permissions);
        Session::put('user_info', [
            'branch_user_id' => $branchUser->id,
            'user_type'      => $branchUser->parent_type,
            'name'           => $branchUser->name,
            'email'          => $branchUser->email,
        ]);
        Session::put('is_sso', true);

        return redirect('/');
    }
}
