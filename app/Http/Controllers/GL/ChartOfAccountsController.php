<?php

namespace App\Http\Controllers\GL;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\ChartOfAccount;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ChartOfAccountsController extends Controller
{
    public function index(Request $request)
    {
        $query = ChartOfAccount::with('parent', 'children');

        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_code', 'like', "%{$search}%")
                  ->orWhere('account_name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Show hierarchy: parent accounts first, then children
        $accounts = $query->orderBy('account_code')->paginate(50);

        $parentAccounts = ChartOfAccount::whereNull('parent_id')
            ->orWhere('is_postable', false)
            ->orderBy('account_code')
            ->get();

        $campuses = Campus::all();
        $accountTypes = ['asset', 'liability', 'equity', 'revenue', 'expense'];

        return view('pages.gl.accounts.index', compact('accounts', 'parentAccounts', 'campuses', 'accountTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'normal_balance' => 'required|in:debit,credit',
            'fs_group' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'is_postable' => 'boolean',
            'campus_id' => 'nullable|exists:campuses,id',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_postable'] = $validated['is_postable'] ?? true;

        $account = ChartOfAccount::create($validated);

        app(AuditService::class)->log('create', 'chart_of_accounts', $account, null, 'Account created');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'account' => $account, 'message' => 'Account created.']);
        }

        return redirect()->route('gl.accounts.index')->with('success', 'Account created successfully.');
    }

    public function update(Request $request, ChartOfAccount $account)
    {
        $validated = $request->validate([
            'account_code' => "required|string|max:20|unique:chart_of_accounts,account_code,{$account->id}",
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'normal_balance' => 'required|in:debit,credit',
            'fs_group' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'is_postable' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $oldValues = $account->toArray();
        $account->update($validated);

        app(AuditService::class)->log('update', 'chart_of_accounts', $account, $oldValues, 'Account updated');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'account' => $account, 'message' => 'Account updated.']);
        }

        return redirect()->route('gl.accounts.index')->with('success', 'Account updated successfully.');
    }
}
