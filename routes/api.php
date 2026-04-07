<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\DisbursementController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ChartOfAccountsController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TaxController;
use App\Http\Controllers\Api\AuditTrailController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically.
| Sanctum-protected routes require a valid API token.
|
*/

// -----------------------------------------------------------------
// Public / Auth
// -----------------------------------------------------------------
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// SSO Validation (called by SIS)
Route::post('/sso/validate', [\App\Http\Controllers\Api\SsoValidateController::class, 'validate']);

// -----------------------------------------------------------------
// Protected Routes
// -----------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/user', function (Request $request) { return $request->user(); });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);

    // Users management
    Route::apiResource('users', UserController::class);

    // =========================================================
    // Dashboard
    // =========================================================
    Route::get('/dashboard/finance', [DashboardController::class, 'finance']);
    Route::get('/dashboard/accounting', [DashboardController::class, 'accounting']);
    Route::get('/dashboard/widgets', [DashboardController::class, 'widgets']);

    // =========================================================
    // Budget
    // =========================================================
    Route::apiResource('budgets', BudgetController::class);
    Route::get('/budgets/summary/fiscal-year', [BudgetController::class, 'fiscalYearSummary']);
    Route::get('/budget-allocations', [BudgetController::class, 'allocations']);
    Route::post('/budget-allocations', [BudgetController::class, 'storeAllocation']);
    Route::put('/budget-allocations/{allocation}', [BudgetController::class, 'updateAllocation']);

    // =========================================================
    // Accounts Payable
    // =========================================================
    Route::apiResource('bills', BillController::class);
    Route::post('/bills/{bill}/approve', [BillController::class, 'approve']);
    Route::post('/bills/{bill}/reject', [BillController::class, 'reject']);
    Route::get('/bills/pending/approval', [BillController::class, 'pendingApproval']);

    Route::apiResource('disbursements', DisbursementController::class);
    Route::post('/disbursements/batch', [DisbursementController::class, 'batchProcess']);

    Route::apiResource('vendors', VendorController::class);
    Route::get('/vendors/{vendor}/transactions', [VendorController::class, 'transactions']);

    // =========================================================
    // Accounts Receivable
    // =========================================================
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send']);
    Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void']);

    Route::apiResource('collections', CollectionController::class);
    Route::post('/collections/{collection}/apply', [CollectionController::class, 'apply']);

    Route::apiResource('customers', CustomerController::class);
    Route::get('/customers/{customer}/statement', [CustomerController::class, 'statement']);
    Route::get('/customers/{customer}/aging', [CustomerController::class, 'aging']);

    Route::get('/ar/aging-report', [InvoiceController::class, 'agingReport']);

    // =========================================================
    // General Ledger
    // =========================================================
    Route::apiResource('chart-of-accounts', ChartOfAccountsController::class);
    Route::get('/chart-of-accounts/tree', [ChartOfAccountsController::class, 'tree']);

    Route::apiResource('journal-entries', JournalEntryController::class);
    Route::post('/journal-entries/{entry}/post', [JournalEntryController::class, 'post']);
    Route::post('/journal-entries/{entry}/reverse', [JournalEntryController::class, 'reverse']);
    Route::get('/journal-entries/recurring/list', [JournalEntryController::class, 'recurringList']);
    Route::post('/journal-entries/recurring', [JournalEntryController::class, 'storeRecurring']);

    Route::get('/ledger-inquiry', [ChartOfAccountsController::class, 'ledgerInquiry']);

    Route::get('/period-closing/status', [JournalEntryController::class, 'periodStatus']);
    Route::post('/period-closing/close', [JournalEntryController::class, 'closePeriod']);
    Route::post('/period-closing/reopen', [JournalEntryController::class, 'reopenPeriod']);

    // =========================================================
    // Reports
    // =========================================================
    Route::prefix('reports')->group(function () {
        Route::get('/trial-balance', [ReportController::class, 'trialBalance']);
        Route::get('/balance-sheet', [ReportController::class, 'balanceSheet']);
        Route::get('/income-statement', [ReportController::class, 'incomeStatement']);
        Route::get('/cash-flow', [ReportController::class, 'cashFlow']);
        Route::get('/general-ledger', [ReportController::class, 'generalLedger']);
        Route::get('/budget-vs-actual', [ReportController::class, 'budgetVsActual']);
        Route::get('/monthly-variance', [ReportController::class, 'monthlyVariance']);
    });

    // =========================================================
    // Tax & Compliance
    // =========================================================
    Route::prefix('tax')->group(function () {
        Route::get('/bir-2307', [TaxController::class, 'bir2307']);
        Route::post('/bir-2307/generate', [TaxController::class, 'generateBir2307']);
        Route::get('/bir-1601e', [TaxController::class, 'bir1601e']);
        Route::post('/bir-1601e/generate', [TaxController::class, 'generateBir1601e']);
        Route::get('/vat-2550m', [TaxController::class, 'vat2550m']);
        Route::post('/vat-2550m/generate', [TaxController::class, 'generateVat2550m']);
        Route::get('/alphalist', [TaxController::class, 'alphalist']);
        Route::get('/alphalist/export', [TaxController::class, 'exportAlphalist']);
    });

    // =========================================================
    // System
    // =========================================================
    Route::get('/audit-trail', [AuditTrailController::class, 'index']);
    Route::get('/audit-trail/export', [AuditTrailController::class, 'export']);

    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);
    Route::get('/settings/fiscal-year', [SettingsController::class, 'fiscalYear']);
    Route::put('/settings/fiscal-year', [SettingsController::class, 'updateFiscalYear']);
});
