<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Budget\BudgetController;
use App\Http\Controllers\AP\BillController;
use App\Http\Controllers\AP\DisbursementController;
use App\Http\Controllers\AP\ApprovalController;
use App\Http\Controllers\AP\PaymentController;
use App\Http\Controllers\AP\VendorController;
use App\Http\Controllers\AR\InvoiceController;
use App\Http\Controllers\AR\CollectionController;
use App\Http\Controllers\AR\CustomerController;
use App\Http\Controllers\AR\ARController;
use App\Http\Controllers\GL\ChartOfAccountsController;
use App\Http\Controllers\GL\JournalEntryController;
use App\Http\Controllers\GL\GLController;
use App\Http\Controllers\GL\PeriodClosingController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Tax\TaxController;
use App\Http\Controllers\System\AuditTrailController;
use App\Http\Controllers\System\SettingsController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// -----------------------------------------------------------------
// Authentication
// -----------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// -----------------------------------------------------------------
// Authenticated Routes
// -----------------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    // =============================================================
    // Overview / Dashboards
    // =============================================================
    Route::get('/', [DashboardController::class, 'finance'])->name('dashboard');
    Route::get('/accounting/dashboard', [DashboardController::class, 'accounting'])->name('accounting.dashboard');

    // =============================================================
    // Budget Module
    // =============================================================
    Route::prefix('budget')->name('budget.')->group(function () {
        Route::get('/dashboard', [BudgetController::class, 'dashboard'])->name('dashboard');
        Route::get('/planning', [BudgetController::class, 'planning'])->name('planning');
        Route::get('/allocation', [BudgetController::class, 'allocation'])->name('allocation');
        Route::post('/planning', [BudgetController::class, 'storePlan'])->name('planning.store');
        Route::put('/planning/{plan}', [BudgetController::class, 'updatePlan'])->name('planning.update');
        Route::post('/allocation', [BudgetController::class, 'storeAllocation'])->name('allocation.store');
        Route::put('/allocation/{allocation}', [BudgetController::class, 'updateAllocation'])->name('allocation.update');
    });

    // =============================================================
    // Accounts Payable (AP)
    // =============================================================
    Route::prefix('ap')->name('ap.')->group(function () {
        Route::resource('bills', BillController::class);
        Route::resource('disbursements', DisbursementController::class);
        Route::get('/approval-queue', [ApprovalController::class, 'index'])->name('approval-queue');
        Route::post('/approval-queue/{bill}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval-queue/{bill}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
        Route::get('/payment-processing', [PaymentController::class, 'index'])->name('payment-processing');
        Route::post('/payment-processing/process', [PaymentController::class, 'process'])->name('payment.process');
    });

    Route::resource('vendors', VendorController::class);

    // =============================================================
    // Accounts Receivable (AR)
    // =============================================================
    Route::prefix('ar')->name('ar.')->group(function () {
        Route::resource('invoices', InvoiceController::class);
        Route::resource('collections', CollectionController::class);
        Route::resource('customers', CustomerController::class);
        Route::get('/aging', [ARController::class, 'aging'])->name('aging');
        Route::get('/soa', [ARController::class, 'soa'])->name('soa');
        Route::get('/soa/{customer}', [ARController::class, 'soaDetail'])->name('soa.detail');
        Route::get('/soa/{customer}/pdf', [ARController::class, 'soaPdf'])->name('soa.pdf');
    });

    // =============================================================
    // General Ledger (GL)
    // =============================================================
    Route::prefix('gl')->name('gl.')->group(function () {
        Route::resource('accounts', ChartOfAccountsController::class);
        Route::resource('journal-entries', JournalEntryController::class);
        Route::get('/recurring', [JournalEntryController::class, 'recurring'])->name('recurring');
        Route::post('/recurring', [JournalEntryController::class, 'storeRecurring'])->name('recurring.store');
        Route::get('/ledger-inquiry', [GLController::class, 'ledgerInquiry'])->name('ledger-inquiry');
        Route::get('/period-closing', [PeriodClosingController::class, 'index'])->name('period-closing');
        Route::post('/period-closing/close', [PeriodClosingController::class, 'close'])->name('period-closing.close');
        Route::post('/period-closing/reopen', [PeriodClosingController::class, 'reopen'])->name('period-closing.reopen');
    });

    // =============================================================
    // Reports
    // =============================================================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/income-statement', [ReportController::class, 'incomeStatement'])->name('income-statement');
        Route::get('/cash-flow', [ReportController::class, 'cashFlow'])->name('cash-flow');
        Route::get('/general-ledger', [ReportController::class, 'generalLedger'])->name('general-ledger');
        Route::get('/budget-vs-actual', [ReportController::class, 'budgetVsActual'])->name('budget-vs-actual');
        Route::get('/monthly-variance', [ReportController::class, 'monthlyVariance'])->name('monthly-variance');

        // Export endpoints
        Route::get('/trial-balance/export/{format}', [ReportController::class, 'exportTrialBalance'])->name('trial-balance.export');
        Route::get('/balance-sheet/export/{format}', [ReportController::class, 'exportBalanceSheet'])->name('balance-sheet.export');
        Route::get('/income-statement/export/{format}', [ReportController::class, 'exportIncomeStatement'])->name('income-statement.export');
        Route::get('/cash-flow/export/{format}', [ReportController::class, 'exportCashFlow'])->name('cash-flow.export');
        Route::get('/general-ledger/export/{format}', [ReportController::class, 'exportGeneralLedger'])->name('general-ledger.export');
        Route::get('/budget-vs-actual/export/{format}', [ReportController::class, 'exportBudgetVsActual'])->name('budget-vs-actual.export');
    });

    // =============================================================
    // Tax & Compliance (Philippine BIR)
    // =============================================================
    Route::prefix('tax')->name('tax.')->group(function () {
        Route::get('/bir-2307', [TaxController::class, 'bir2307'])->name('bir-2307');
        Route::get('/bir-2307/generate', [TaxController::class, 'generateBir2307'])->name('bir-2307.generate');
        Route::get('/bir-1601e', [TaxController::class, 'bir1601e'])->name('bir-1601e');
        Route::get('/bir-1601e/generate', [TaxController::class, 'generateBir1601e'])->name('bir-1601e.generate');
        Route::get('/vat-2550m', [TaxController::class, 'vat2550m'])->name('vat-2550m');
        Route::get('/vat-2550m/generate', [TaxController::class, 'generateVat2550m'])->name('vat-2550m.generate');
        Route::get('/alphalist', [TaxController::class, 'alphalist'])->name('alphalist');
        Route::get('/alphalist/export', [TaxController::class, 'exportAlphalist'])->name('alphalist.export');
        Route::get('/special-journals', [TaxController::class, 'specialJournals'])->name('special-journals');
        Route::get('/check-writer', [TaxController::class, 'checkWriter'])->name('check-writer');
        Route::post('/check-writer/print', [TaxController::class, 'printCheck'])->name('check-writer.print');
    });

    // =============================================================
    // System
    // =============================================================
    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail');
    Route::get('/audit-trail/export', [AuditTrailController::class, 'export'])->name('audit-trail.export');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/fiscal-year', [SettingsController::class, 'fiscalYear'])->name('settings.fiscal-year');
    Route::put('/settings/fiscal-year', [SettingsController::class, 'updateFiscalYear'])->name('settings.fiscal-year.update');

    Route::get('/api-docs', fn () => view('system.api-docs'))->name('api-docs');
});
