<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\FeeAccountMapping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinanceFeeService
{
    protected static $connection = 'finance';

    /**
     * Check once per request whether the finance DB connection is usable.
     * Logs the first failure so prod can see why finance data is missing.
     */
    protected static $available = null;

    public static function isAvailable(): bool
    {
        if (static::$available !== null) {
            return static::$available;
        }

        if (!array_key_exists(static::$connection, config('database.connections', []))) {
            Log::warning('Finance DB connection not registered in config/database.php — check creds.json finance entry');
            return static::$available = false;
        }

        try {
            DB::connection(static::$connection)->getPdo();
            return static::$available = true;
        } catch (\Throwable $e) {
            Log::warning('Finance DB unavailable: ' . $e->getMessage());
            return static::$available = false;
        }
    }

    /**
     * Sync guard — syncNewFees() is expensive and idempotent within a request.
     */
    protected static $syncedThisRequest = false;

    /**
     * Bust all cached finance aggregates. Call after any collection/payment
     * posting or when an admin forces a refresh.
     */
    public static function flushCache(): void
    {
        Cache::forget('finance:current_sy');
        // Cache::flush(finance:*) is not universally supported, so keep keys predictable
        // and let admins clear via artisan cache:clear if needed.
    }

    /**
     * Finance parent group → accounting parent account code mapping.
     * Used by syncNewFees() to auto-map new fees.
     */
    protected static $groupDefaults = [
        'TUITION FEE'                  => '4010',
        'MISCELLANEOUS FEES'           => '4020',
        'LABORATORY FEES'              => '4030',
        'OTHER FEE'                    => '4100',
        'RESERVATION'                  => '4050',
        'DEVT/PUB/PAG-PER FAMILY FEE'  => '4020',
        'ADDED FEE'                    => '4100',
        'SURCHARGE/PENALTY'            => '4100',
        'INSTALLMENT FEE'              => '4100',
        'ADD ONS'                      => '4100',
        'AR - Parent'                  => '4100',
        'Migrated Arrears Parent'      => '4100',
        'TOP-UP FEES'                  => '4100',
        'Forwarded Arrears Parent'     => '4100',
        'DISCOUNTS'                    => '4200',
        'ADJUSTMENTS'                  => '4210',
        'REFUND'                       => '4220',
        'Overpayment Parent'           => '4230',
        'TESTING'                      => '4100',
    ];

    /**
     * Auto-sync: detect new fees in finance DB and create mappings + sub-accounts.
     * Call this before any report that uses mappings.
     *
     * No-ops if finance DB is unavailable or already ran this request.
     */
    public static function syncNewFees()
    {
        if (static::$syncedThisRequest || !static::isAvailable()) {
            return 0;
        }
        static::$syncedThisRequest = true;

        $existingIds = FeeAccountMapping::pluck('finance_fee_id')->toArray();

        $newFees = DB::connection(static::$connection)
            ->table('chart_of_accounts')
            ->whereNull('deleted_at')
            ->whereNotNull('parent_id')
            ->whereNotIn('id', $existingIds)
            ->get(['id', 'name', 'parent_id']);

        if ($newFees->isEmpty()) return 0;

        // Get finance parent names
        $financeParents = DB::connection(static::$connection)
            ->table('chart_of_accounts')
            ->whereNull('deleted_at')
            ->whereNull('parent_id')
            ->pluck('name', 'id');

        $counters = [];
        $created = 0;

        foreach ($newFees as $fee) {
            $parentName = $financeParents->get($fee->parent_id);
            if (!$parentName) continue;

            $acctCode = static::$groupDefaults[$parentName] ?? '4100';
            $acctParent = ChartOfAccount::where('account_code', $acctCode)->first();
            if (!$acctParent) continue;

            // Get or init counter
            if (!isset($counters[$acctCode])) {
                $lastSub = ChartOfAccount::where('account_code', 'like', $acctCode . '-%')
                    ->orderByDesc('account_code')->first();
                $counters[$acctCode] = $lastSub
                    ? (int) str_replace($acctCode . '-', '', $lastSub->account_code)
                    : 0;
            }

            $counters[$acctCode]++;
            $subCode = $acctCode . '-' . str_pad($counters[$acctCode], 3, '0', STR_PAD_LEFT);

            $account = ChartOfAccount::create([
                'account_code' => $subCode,
                'account_name' => $fee->name,
                'account_type' => $acctParent->account_type,
                'parent_id' => $acctParent->id,
                'normal_balance' => $acctParent->normal_balance,
                'is_active' => true,
                'is_postable' => true,
            ]);

            FeeAccountMapping::create([
                'finance_fee_id' => $fee->id,
                'finance_fee_name' => $fee->name,
                'account_id' => $account->id,
            ]);

            $created++;
        }

        return $created;
    }

    /**
     * Base query for fee distribution with joins.
     */
    protected static function baseQuery()
    {
        return DB::connection(static::$connection)
            ->table('transaction_fees_distribution as a')
            ->leftJoin('transaction_batch as b', 'a.transaction_batch_id', '=', 'b.id')
            ->leftJoin('chart_of_accounts as c', 'a.fee_id', '=', 'c.id')
            ->whereNull('b.voided_by')
            ->whereNull('b.deleted_at');
    }

    /**
     * Get all non-voided fees.
     */
    public static function all()
    {
        return static::baseQuery()
            ->select('b.receipt_number', 'c.name as fee_name', 'a.amount', 'b.total', 'b.date_paid')
            ->get();
    }

    /**
     * Get fees by school year (e.g., '2025').
     */
    public static function bySchoolYear($yearFrom)
    {
        return static::baseQuery()
            ->leftJoin('acad_sy_school_year as d', 'b.acad_sy_school_year_id', '=', 'd.id')
            ->select('b.receipt_number', 'c.name as fee_name', 'a.amount', 'b.total', 'b.date_paid', 'd.year_fr', 'd.year_to')
            ->where('d.year_fr', $yearFrom)
            ->get();
    }

    /**
     * Fee summary grouped by fee name (for dashboard cards & report).
     */
    public static function summaryByFee($yearFrom = null)
    {
        $query = static::baseQuery()
            ->leftJoin('acad_sy_school_year as d', 'b.acad_sy_school_year_id', '=', 'd.id')
            ->select('c.name as fee_name', DB::raw('COUNT(*) as txn_count'), DB::raw('SUM(a.amount) as total_amount'))
            ->groupBy('c.name')
            ->orderByDesc('total_amount');

        if ($yearFrom) {
            $query->where('d.year_fr', $yearFrom);
        }

        return $query->get();
    }

    /**
     * Dashboard totals: total collected, transaction count, top fees.
     */
    public static function dashboardSummary($yearFrom = null)
    {
        $query = static::baseQuery()
            ->leftJoin('acad_sy_school_year as d', 'b.acad_sy_school_year_id', '=', 'd.id');

        if ($yearFrom) {
            $query->where('d.year_fr', $yearFrom);
        }

        $totals = $query->selectRaw('COALESCE(SUM(a.amount), 0) as total_collected, COUNT(DISTINCT b.id) as txn_count')->first();

        $topFees = static::summaryByFee($yearFrom)->take(5);

        return (object) [
            'total_collected' => (float) $totals->total_collected,
            'txn_count' => (int) $totals->txn_count,
            'top_fees' => $topFees,
        ];
    }

    /**
     * Detailed transactions for report (paginated-ready).
     */
    public static function transactions($yearFrom = null, $feeName = null)
    {
        $query = static::baseQuery()
            ->leftJoin('acad_sy_school_year as d', 'b.acad_sy_school_year_id', '=', 'd.id')
            ->select(
                'b.receipt_number', 'c.name as fee_name', 'a.amount',
                'b.total', 'b.date_paid', 'd.year_fr', 'd.year_to'
            )
            ->orderByDesc('b.date_paid');

        if ($yearFrom) {
            $query->where('d.year_fr', $yearFrom);
        }
        if ($feeName) {
            $query->where('c.name', $feeName);
        }

        return $query;
    }

    /**
     * Get fee collections mapped to accounting revenue accounts (for Income Statement).
     * Groups by accounting account and sums amounts within date range.
     *
     * Cached 5 minutes per (from, to) pair.
     */
    public static function mappedRevenue($dateFrom, $dateTo)
    {
        if (!static::isAvailable()) {
            return collect();
        }

        $cacheKey = "finance:mapped_revenue:{$dateFrom}:{$dateTo}";
        return Cache::remember($cacheKey, 60, function () use ($dateFrom, $dateTo) {
            return static::computeMappedRevenue($dateFrom, $dateTo);
        });
    }

    protected static function computeMappedRevenue($dateFrom, $dateTo)
    {
        static::syncNewFees();

        $mappings = FeeAccountMapping::with('account')->get();

        if ($mappings->isEmpty()) {
            return collect();
        }

        // Get totals per finance fee_id within date range
        $feeTotals = static::baseQuery()
            ->whereBetween('b.date_paid', [$dateFrom, $dateTo])
            ->select('a.fee_id', DB::raw('SUM(a.amount) as total_amount'))
            ->groupBy('a.fee_id')
            ->get()
            ->keyBy('fee_id');

        // Group by accounting account
        $result = [];
        foreach ($mappings as $mapping) {
            $feeTotal = $feeTotals->get($mapping->finance_fee_id);
            if (!$feeTotal || $feeTotal->total_amount <= 0) {
                continue;
            }

            $accountId = $mapping->account_id;
            if (!isset($result[$accountId])) {
                $result[$accountId] = (object) [
                    'account_id' => $accountId,
                    'account_code' => $mapping->account->account_code,
                    'account_name' => $mapping->account->account_name,
                    'total_amount' => 0,
                ];
            }
            $result[$accountId]->total_amount += (float) $feeTotal->total_amount;
        }

        return collect(array_values($result));
    }

    /**
     * Get fee collections as virtual GL entries (for General Ledger report).
     * Returns collection grouped by account_id, each item looks like a JE line.
     *
     * Cached 5 minutes per (from, to, accountId) triple.
     */
    public static function glEntries($dateFrom, $dateTo, $accountId = null)
    {
        if (!static::isAvailable()) {
            return collect();
        }

        $cacheKey = "finance:gl_entries:{$dateFrom}:{$dateTo}:" . ($accountId ?? 'all');
        return Cache::remember($cacheKey, 60, function () use ($dateFrom, $dateTo, $accountId) {
            return static::computeGlEntries($dateFrom, $dateTo, $accountId);
        });
    }

    protected static function computeGlEntries($dateFrom, $dateTo, $accountId = null)
    {
        static::syncNewFees();

        $mappings = FeeAccountMapping::with('account')->get()->keyBy('finance_fee_id');

        if ($mappings->isEmpty()) {
            return collect();
        }

        // Filter mappings by account if specified
        $feeIds = $mappings->keys()->all();
        if ($accountId) {
            $filteredMappings = $mappings->where('account_id', $accountId);
            if ($filteredMappings->isEmpty()) {
                return collect();
            }
            $feeIds = $filteredMappings->keys()->all();
        }

        $rows = static::baseQuery()
            ->whereIn('a.fee_id', $feeIds)
            ->whereBetween('b.date_paid', [$dateFrom, $dateTo])
            ->select('b.date_paid', 'a.fee_id', DB::raw('SUM(a.amount) as total_amount'))
            ->groupBy('b.date_paid', 'a.fee_id')
            ->orderBy('b.date_paid')
            ->get();

        // Build virtual GL entries grouped by account_id
        $result = [];
        foreach ($rows as $row) {
            $mapping = $mappings->get($row->fee_id);
            if (!$mapping) continue;

            $acctId = $mapping->account_id;
            if (!isset($result[$acctId])) {
                $result[$acctId] = collect();
            }

            $result[$acctId]->push((object) [
                'account_id' => $acctId,
                'account_code' => $mapping->account->account_code,
                'account_name' => $mapping->account->account_name,
                'account_type' => $mapping->account->account_type,
                'normal_balance' => $mapping->account->normal_balance,
                'posting_date' => $row->date_paid,
                'entry_number' => 'FEE-' . $row->date_paid,
                'reference_number' => $mapping->finance_fee_name,
                'je_description' => $mapping->finance_fee_name . ' collections',
                'description' => $mapping->finance_fee_name,
                'debit' => 0,
                'credit' => round((float) $row->total_amount, 2),
                'journal_type' => 'finance',
            ]);
        }

        return collect($result);
    }

    /**
     * Get recent receipts/transactions (paginated).
     */
    public static function receipts($yearFrom = null, $search = null, $perPage = 20)
    {
        $query = DB::connection(static::$connection)
            ->table('transaction_batch as b')
            ->leftJoin('student_db as s', 'b.student_id', '=', 's.id')
            ->leftJoin('acad_sy_school_year as sy', 'b.acad_sy_school_year_id', '=', 'sy.id')
            ->whereNull('b.voided_by')
            ->whereNull('b.deleted_at')
            ->where('b.total', '>', 0)
            ->select(
                'b.id', 'b.receipt_number', 'b.total', 'b.date_paid',
                's.student_name', 's.student_number',
                'sy.year_fr', 'sy.year_to'
            );

        if ($yearFrom) {
            $query->where('sy.year_fr', $yearFrom);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('b.receipt_number', 'like', "%{$search}%")
                  ->orWhere('s.student_name', 'like', "%{$search}%")
                  ->orWhere('s.student_number', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('b.date_paid')->paginate($perPage);
    }

    /**
     * Get a single receipt with itemized fee breakdown.
     */
    public static function receiptDetail($batchId)
    {
        $receipt = DB::connection(static::$connection)
            ->table('transaction_batch as b')
            ->leftJoin('student_db as s', 'b.student_id', '=', 's.id')
            ->leftJoin('acad_sy_school_year as sy', 'b.acad_sy_school_year_id', '=', 'sy.id')
            ->where('b.id', $batchId)
            ->select(
                'b.id', 'b.receipt_number', 'b.total', 'b.date_paid', 'b.tendered', 'b.changed',
                's.student_name', 's.student_number', 's.control_number',
                'sy.year_fr', 'sy.year_to'
            )
            ->first();

        if (!$receipt) return null;

        $receipt->fees = DB::connection(static::$connection)
            ->table('transaction_fees_distribution as a')
            ->leftJoin('chart_of_accounts as c', 'a.fee_id', '=', 'c.id')
            ->where('a.transaction_batch_id', $batchId)
            ->select('c.name as fee_name', 'a.amount')
            ->orderByDesc('a.amount')
            ->get();

        return $receipt;
    }

    /**
     * Get available school years from finance DB.
     */
    public static function schoolYears()
    {
        return DB::connection(static::$connection)
            ->table('acad_sy_school_year')
            ->whereRaw("year_fr REGEXP '^[0-9]+$'")
            ->orderByDesc('year_fr')
            ->get(['id', 'year_fr', 'year_to']);
    }

    /**
     * Current school year label "YYYY-YYYY". Falls back to calendar-year heuristic
     * when the finance connection is unavailable. Cached for 5 minutes.
     */
    public static function currentSchoolYear(): string
    {
        return \Illuminate\Support\Facades\Cache::remember('finance:current_sy', 300, function () {
            try {
                $sy = DB::connection(static::$connection)
                    ->table('acad_sy_school_year')
                    ->whereRaw("year_fr REGEXP '^[0-9]+$'")
                    ->orderByDesc('year_fr')
                    ->first(['year_fr', 'year_to']);
                if ($sy) {
                    return $sy->year_fr . '-' . $sy->year_to;
                }
            } catch (\Throwable $e) {
                // fall through
            }
            $y = (int) now()->year;
            $month = (int) now()->month;
            if ($month >= 6) {
                return $y . '-' . ($y + 1);
            }
            return ($y - 1) . '-' . $y;
        });
    }
}
