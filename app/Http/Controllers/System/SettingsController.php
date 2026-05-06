<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Setting;
use App\Services\Users\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('key')->get()->pluck('value', 'key')->toArray();
        $periods = AccountingPeriod::orderBy('start_date', 'desc')->get();
        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('pages.system.settings', compact('settings', 'periods', 'users', 'roles'));
    }

    public function update(Request $request)
    {
        $section = $request->input('section');

        $sectionKeys = [
            'general'  => ['school_name', 'tin', 'address', 'phone', 'email'],
            'approval' => ['approval_level_1', 'approval_level_2', 'approval_level_3'],
            'budget'   => ['budget_policy', 'overspend_tolerance'],
            'tax'      => ['default_vat_rate', 'wht_professional', 'wht_rental', 'wht_services', 'wht_supplies', 'authorized_rep_name', 'authorized_rep_tin'],
            'numbering'=> ['prefix_dr', 'prefix_pv', 'prefix_or', 'prefix_je', 'prefix_bill', 'prefix_inv'],
        ];

        $keys = $sectionKeys[$section] ?? [];

        DB::transaction(function () use ($request, $keys) {
            foreach ($keys as $key) {
                $value = $request->input($key, '');
                $existing = Setting::where('key', $key)->first();
                $oldValue = optional($existing)->value;

                Setting::set($key, $value);

                if ($oldValue !== $value) {
                    app(AuditService::class)->log(
                        'update', 'settings',
                        Setting::where('key', $key)->first(),
                        ['value' => $oldValue],
                        "Setting '{$key}' changed"
                    );
                }
            }
        });

        Cache::forget('school_info');

        return redirect()->route('settings', ['tab' => $section])->with('success', 'Settings updated successfully.');
    }

    public function fiscalYear()
    {
        $periods = AccountingPeriod::orderBy('start_date', 'desc')->get();
        $currentYear = Setting::get('fiscal_year', now()->year);

        return view('pages.system.fiscal-year', compact('periods', 'currentYear'));
    }

    public function updateFiscalYear(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|string|max:20',
            'periods' => 'nullable|array',
            'periods.*.name' => 'required|string|max:100',
            'periods.*.start_date' => 'required|date',
            'periods.*.end_date' => 'required|date|after:periods.*.start_date',
        ]);

        DB::transaction(function () use ($validated) {
            Setting::set('fiscal_year', $validated['fiscal_year']);

            if (!empty($validated['periods'])) {
                foreach ($validated['periods'] as $period) {
                    AccountingPeriod::create([
                        'name' => $period['name'],
                        'school_year' => $validated['fiscal_year'],
                        'start_date' => $period['start_date'],
                        'end_date' => $period['end_date'],
                        'status' => 'open',
                    ]);
                }
            }
        });

        return redirect()->route('settings.fiscal-year')->with('success', 'Fiscal year updated.');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('settings', ['tab' => 'users'])->with('success', "User {$user->name} created.");
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$user->id}",
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('settings', ['tab' => 'users'])->with('success', "User {$user->name} updated.");
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('settings', ['tab' => 'users'])->with('success', 'User deleted.');
    }
}
