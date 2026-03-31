<?php

namespace App\Http\Controllers;

use App\Models\BranchUser;
use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Session;

/**
 * Base controller for SSO-aware controllers.
 * Provides helpers for multi-database access patterns.
 */
abstract class BranchController extends Controller
{
    /**
     * Get the main accounting database connection name.
     */
    protected function accountingConnection(): string
    {
        return config('database.default'); // 'mysql' or 'pgsql'
    }

    /**
     * Get the branch SIS database connection name from session.
     * e.g., 'main_kto12', 'pcc_college'
     */
    protected function branchConnection(): string
    {
        $branchCode = strtolower(Session::get('branch_code', ''));
        $platform   = strtolower(Session::get('platform', 'kto12'));
        $connection = $branchCode . '_' . $platform;

        if (!config("database.connections.{$connection}")) {
            abort(500, "Branch connection [{$connection}] not configured.");
        }

        return $connection;
    }

    /**
     * Get the currently logged-in requester (User, Employee, or Student).
     */
    protected function getRequester()
    {
        $userInfo = Session::get('user_info');
        $userId   = Session::get('user_id');

        if (!$userInfo || !$userId) {
            return auth()->user(); // fallback to standard auth
        }

        $userType = $userInfo['user_type'] ?? null;

        if ($userType === User::class) {
            return User::find($userId);
        }

        if ($userType === Employee::class) {
            return Employee::on($this->branchConnection())->find($userId);
        }

        if ($userType === Student::class) {
            return Student::on($this->branchConnection())->find($userId);
        }

        return null;
    }

    /**
     * Get the BranchUser record for the current SSO user.
     */
    protected function getBranchUser(): ?BranchUser
    {
        $branchUserId = Session::get('user_info.branch_user_id');
        return $branchUserId ? BranchUser::find($branchUserId) : null;
    }

    /**
     * Check if the current session user has a specific permission.
     */
    protected function hasPermission(string $permission): bool
    {
        // Standard auth
        if (auth()->check()) {
            return auth()->user()->can($permission);
        }

        // SSO session
        return in_array($permission, Session::get('permissions', []));
    }
}
