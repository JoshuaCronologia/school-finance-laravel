<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Setting;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('category')->orderBy('key')->get()->groupBy('category');

        $periods = AccountingPeriod::orderBy('start_date', 'desc')->get();

        return view('pages.system.settings', compact('settings', 'periods'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['settings'] as $setting) {
                $existing = Setting::where('key', $setting['key'])->first();
                $oldValue = $existing?->value;

                Setting::set($setting['key'], $setting['value'] ?? '');

                if ($oldValue !== ($setting['value'] ?? '')) {
                    app(AuditService::class)->log(
                        'update', 'settings',
                        Setting::where('key', $setting['key'])->first(),
                        ['value' => $oldValue],
                        "Setting '{$setting['key']}' changed"
                    );
                }
            }
        });

        return redirect()->route('settings')->with('success', 'Settings updated successfully.');
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
}
