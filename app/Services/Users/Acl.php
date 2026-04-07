<?php

namespace App\Services\Users;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class Acl
{
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_USER = 'user';

    const PERMISSION_BUDGET              = 'budget';
    const PERMISSION_ACCOUNTS_PAYABLE    = 'accounts payable';
    const PERMISSION_ACCOUNTS_RECEIVABLE = 'accounts receivable';
    const PERMISSION_GENERAL_LEDGER      = 'general ledger';
    const PERMISSION_REPORTS             = 'reports';
    const PERMISSION_TAX                 = 'tax';

    public static function permissions(array $exclusives = [])
    {
        try {
            $class = new \ReflectionClass(__CLASS__);
            $constants = $class->getConstants();
            $permissions = Arr::where($constants, function ($value, $key) use ($exclusives) {
                return !in_array($value, $exclusives) && Str::startsWith($key, 'PERMISSION_');
            });

            return array_values($permissions);
        } catch (\ReflectionException $exception) {
            return [];
        }
    }

    public static function roles()
    {
        try {
            $class = new \ReflectionClass(__CLASS__);
            $constants = $class->getConstants();
            $roles = Arr::where($constants, function ($value, $key) {
                return Str::startsWith($key, 'ROLE_');
            });

            return array_values($roles);
        } catch (\ReflectionException $exception) {
            return [];
        }
    }
}
