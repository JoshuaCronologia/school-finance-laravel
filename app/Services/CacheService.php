<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Clear dashboard and related caches when financial data changes.
     * Call this after any create/update/delete on budgets, bills, invoices, payments, JEs, etc.
     */
    public static function clearFinancialCaches()
    {
        $keys = [
            'dashboard:finance',
            'dashboard:finance:disbursements',
            'dashboard:accounting',
            'dashboard:accounting:recent_jes',
            'api:finance_dashboard',
            'api:accounting_dashboard',
            'budget:dashboard:all',
            'ar:aging',
            'disbursement:form_data',
            'departments:active',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Clear budget dashboard per-department caches
        // These have dynamic keys, so use pattern-based clearing
        // For file cache driver, we just clear the known ones
    }

    /**
     * Clear AP-specific caches (vendor aging, etc.)
     */
    public static function clearAPCaches()
    {
        Cache::forget('ap:aging:' . now()->toDateString());
    }
}
