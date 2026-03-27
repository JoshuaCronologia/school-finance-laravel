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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;

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
        Route::get('/budget-vs-actual/pdf', [BudgetController::class, 'budgetVsActualPdf'])->name('budget-vs-actual.pdf');
        Route::post('/planning', [BudgetController::class, 'storePlan'])->name('planning.store');
        Route::put('/planning/{plan}', [BudgetController::class, 'updatePlan'])->name('planning.update');
        Route::post('/planning/copy-previous', [BudgetController::class, 'copyFromPreviousYear'])->name('planning.copy-previous');
        Route::get('/planning/export', [BudgetController::class, 'export'])->name('planning.export');
        Route::post('/allocation', [BudgetController::class, 'storeAllocation'])->name('allocation.store');
        Route::post('/allocation/update', [BudgetController::class, 'updateAllocation'])->name('allocation.update');
        Route::get('/allocation/export', [BudgetController::class, 'export'])->name('allocation.export');
    });

    // =============================================================
    // Accounts Payable (AP)
    // =============================================================
    Route::prefix('ap')->name('ap.')->group(function () {
        Route::resource('bills', BillController::class);
        Route::post('/bills/{bill}/approve', [BillController::class, 'approve'])->name('bills.approve');
        Route::post('/bills/{bill}/post', [BillController::class, 'post'])->name('bills.post');
        Route::get('disbursements/export', [DisbursementController::class, 'export'])->name('disbursements.export');
        Route::resource('disbursements', DisbursementController::class);
        Route::post('/disbursements/{disbursement}/submit', [DisbursementController::class, 'submit'])->name('disbursements.submit');
        Route::get('/approval-queue', [ApprovalController::class, 'index'])->name('approval-queue');
        Route::post('/approval-queue/{disbursement}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval-queue/{disbursement}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
        Route::post('/approval-queue/{disbursement}/return', [ApprovalController::class, 'returnForRevision'])->name('approval.return');
        // Supplier / disbursement payments
        Route::get('/payment-processing', [PaymentController::class, 'index'])->name('payment-processing');
        Route::get('/supplier-payments', [PaymentController::class, 'payments'])->name('supplier-payments');
        Route::get('/payments', [PaymentController::class, 'payments'])->name('payments.index');
        Route::post('/payments/{disbursement}', [PaymentController::class, 'processPayment'])->name('payments.store');
        Route::post('/payments/{payment}/void', [PaymentController::class, 'voidPayment'])->name('payments.void');
        Route::get('/payments/{payment}/print', [PaymentController::class, 'printVoucher'])->name('payments.print');
        // Backward compatibility for older frontend code pointing here
        Route::post('/payment-processing/{disbursement}/process', [PaymentController::class, 'processPayment'])->name('payment.process');
    });

    // AP Aging per vendor
    Route::get('/ap/aging', [VendorController::class, 'aging'])->name('ap.aging');

    Route::resource('vendors', VendorController::class);

    // =============================================================
    // Accounts Receivable (AR)
    // =============================================================
    Route::prefix('ar')->name('ar.')->group(function () {
        Route::resource('invoices', InvoiceController::class);
        Route::resource('collections', CollectionController::class);
        Route::get('/collections/{collection}/print', [CollectionController::class, 'printReceipt'])->name('collections.print');
        Route::resource('customers', CustomerController::class);
        Route::get('/aging', [ARController::class, 'aging'])->name('aging');
        // Simple export placeholder: reuse aging view data for now
        Route::get('/aging/export', fn() => redirect()->route('ar.aging'))->name('aging.export');
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
        Route::get('/journal-entries-approval', [JournalEntryController::class, 'approvalQueue'])->name('journal-entries.approval');
        Route::post('/journal-entries/{journal_entry}/submit-approval', [JournalEntryController::class, 'submitForApproval'])->name('journal-entries.submit-approval');
        Route::post('/journal-entries/{journal_entry}/approve', [JournalEntryController::class, 'approve'])->name('journal-entries.approve');
        Route::post('/journal-entries/{journal_entry}/reject', [JournalEntryController::class, 'reject'])->name('journal-entries.reject');
        Route::post('/journal-entries/{journal_entry}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
        Route::post('/journal-entries/{journal_entry}/reverse', [JournalEntryController::class, 'reverse'])->name('journal-entries.reverse');
        Route::get('/journal-entries/{journal_entry}/print', [JournalEntryController::class, 'printVoucher'])->name('journal-entries.print');
        Route::get('/recurring', [JournalEntryController::class, 'recurring'])->name('recurring');
        Route::post('/recurring', [JournalEntryController::class, 'storeRecurring'])->name('recurring.store');
        Route::put('/recurring/{template}', [JournalEntryController::class, 'updateRecurring'])->name('recurring.update');
        Route::post('/recurring/{template}/generate', [JournalEntryController::class, 'generateRecurring'])->name('recurring.generate');
        Route::get('/bank-reconciliation', [\App\Http\Controllers\GL\BankReconciliationController::class, 'index'])->name('bank-reconciliation');
        Route::get('/bank-reconciliation/pdf', [\App\Http\Controllers\GL\BankReconciliationController::class, 'pdf'])->name('bank-reconciliation.pdf');
        Route::get('/ledger-inquiry', [GLController::class, 'ledgerInquiry'])->name('ledger-inquiry');
        Route::get('/period-closing', [PeriodClosingController::class, 'index'])->name('period-closing');
        Route::post('/period-closing/{period}/close', [PeriodClosingController::class, 'close'])->name('period-closing.close');
        Route::post('/period-closing/{period}/reopen', [PeriodClosingController::class, 'reopen'])->name('period-closing.reopen');
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
        Route::get('/expense-schedule', [ReportController::class, 'expenseSchedule'])->name('expense-schedule');
        Route::get('/general-journal', [ReportController::class, 'generalJournal'])->name('general-journal');
        Route::get('/cash-receipts-book', [ReportController::class, 'cashReceiptsBook'])->name('cash-receipts-book');
        Route::get('/cash-disbursements-book', [ReportController::class, 'cashDisbursementsBook'])->name('cash-disbursements-book');

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
        // BIR Forms
        Route::get('/bir-0619e', [TaxController::class, 'bir0619e'])->name('bir-0619e');
        Route::get('/bir-0619f', [TaxController::class, 'bir0619f'])->name('bir-0619f');
        Route::get('/bir-1601c', [TaxController::class, 'bir1601c'])->name('bir-1601c');
        Route::get('/bir-1601eq', [TaxController::class, 'bir1601eq'])->name('bir-1601eq');
        Route::get('/bir-1604e', [TaxController::class, 'bir1604e'])->name('bir-1604e');
        Route::get('/bir-1604cf', [TaxController::class, 'bir1604cf'])->name('bir-1604cf');
        Route::get('/alphalist-quarterly', [TaxController::class, 'alphalistQuarterly'])->name('alphalist-quarterly');
        Route::get('/alphalist-annual', [TaxController::class, 'alphalistAnnual'])->name('alphalist-annual');
    });

    // =============================================================
    // System
    // =============================================================
    Route::get('/user-access', [\App\Http\Controllers\System\UserAccessController::class, 'index'])->name('user-access');
    Route::post('/user-access', [\App\Http\Controllers\System\UserAccessController::class, 'storeUser'])->name('user-access.store');
    Route::put('/user-access/{user}', [\App\Http\Controllers\System\UserAccessController::class, 'updateUser'])->name('user-access.update');
    Route::put('/user-access/{user}/permissions', [\App\Http\Controllers\System\UserAccessController::class, 'updatePermissions'])->name('user-access.permissions');
    Route::delete('/user-access/{user}', [\App\Http\Controllers\System\UserAccessController::class, 'deleteUser'])->name('user-access.delete');

    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail');
    Route::get('/audit-trail/export', [AuditTrailController::class, 'export'])->name('audit-trail.export');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/fiscal-year', [SettingsController::class, 'fiscalYear'])->name('settings.fiscal-year');
    Route::put('/settings/fiscal-year', [SettingsController::class, 'updateFiscalYear'])->name('settings.fiscal-year.update');
    Route::post('/settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
    Route::put('/settings/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
    Route::delete('/settings/users/{user}', [SettingsController::class, 'deleteUser'])->name('settings.users.delete');

    Route::get('/api-docs', fn () => view('system.api-docs'))->name('api-docs');

    // =============================================================
    // Global Search
    // =============================================================
    Route::get('/search', SearchController::class)->name('search');

    // =============================================================
    // Notifications
    // =============================================================
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
});
