<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\ApBill;
use App\Models\ApPayment;
use App\Models\ArCollection;
use App\Models\ArInvoice;
use App\Models\DisbursementPayment;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Setting;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    /**
     * School info used by BIR forms.
     */
    private function schoolInfo(): array
    {
        return [
            'schoolTin' => Setting::where('key', 'school_tin')->value('value') ?? '000-000-000-000',
            'schoolName' => Setting::where('key', 'school_name')->value('value') ?? config('app.name'),
            'schoolAddress' => Setting::where('key', 'school_address')->value('value') ?? 'Manila, Philippines',
        ];
    }

    // =================================================================
    // BIR 2307 - Certificate of Creditable Tax Withheld at Source
    // =================================================================

    public function bir2307(Request $request)
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $selectedVendor = $request->filled('vendor_id') ? Vendor::find($request->vendor_id) : null;
        $quarter = $request->input('quarter', 'Q' . ceil(now()->month / 3));
        $year = $request->input('year', now()->year);

        $formData = null;
        $summary = collect();

        if ($selectedVendor) {
            $q = (int) str_replace('Q', '', $quarter);
            $startMonth = ($q - 1) * 3 + 1;
            $endMonth = $q * 3;

            $payments = ApPayment::with('vendor')
                ->where('status', 'posted')
                ->where('withholding_tax', '>', 0)
                ->where('vendor_id', $selectedVendor->id)
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', '>=', $startMonth)
                ->whereMonth('payment_date', '<=', $endMonth)
                ->orderBy('payment_date')
                ->get();

            // Monthly breakdown for the form
            $monthly = [];
            for ($m = $startMonth; $m <= $endMonth; $m++) {
                $monthPayments = $payments->filter(function ($p) { return (int) $p->payment_date->format('m') === $m; });
                $monthly[$m] = (object) [
                    'month' => $m,
                    'income_payment' => $monthPayments->sum('gross_amount'),
                    'tax_withheld' => $monthPayments->sum('withholding_tax'),
                ];
            }

            $formData = (object) [
                'vendor' => $selectedVendor,
                'monthly' => collect($monthly),
                'total_income' => $payments->sum('gross_amount'),
                'total_tax' => $payments->sum('withholding_tax'),
                'payments' => $payments,
            ];
        }

        // Summary of all vendors with withholding for the period
        $summaryQuery = ApPayment::with('vendor')
            ->select('vendor_id', DB::raw('SUM(gross_amount) as total_income'), DB::raw('SUM(withholding_tax) as total_tax'))
            ->where('status', 'posted')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->groupBy('vendor_id')
            ->get();

        $summary = $summaryQuery->map(function ($p) { return (object) [
            'vendor' => $p->vendor,
            'income_payment' => (float) $p->total_income,
            'tax_withheld' => (float) $p->total_tax,
        ]; });

        $totalWithheld = $summary->sum('tax_withheld');

        // Handle Excel/CSV export
        if ($request->input('export') === 'excel') {
            return $this->exportBir2307Csv($summary, $quarter, $year);
        }

        return view('pages.tax.bir-2307', array_merge(
            compact('vendors', 'selectedVendor', 'quarter', 'year', 'formData', 'summary', 'totalWithheld'),
            $this->schoolInfo()
        ));
    }

    private function exportBir2307Csv($summary, $quarter, $year)
    {
        $filename = "BIR-2307-{$quarter}-{$year}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($summary) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['TIN', 'Vendor Name', 'ATC', 'Income Payment', 'Tax Withheld']);

            foreach ($summary as $item) {
                fputcsv($file, [
                    $item->vendor->tin ?? '',
                    $item->vendor->name ?? '',
                    $item->vendor->withholding_tax_type ?? '',
                    number_format($item->income_payment, 2, '.', ''),
                    number_format($item->tax_withheld, 2, '.', ''),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['', 'TOTAL', '', number_format($summary->sum('income_payment'), 2, '.', ''), number_format($summary->sum('tax_withheld'), 2, '.', '')]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function generateBir2307(Request $request)
    {
        return $this->bir2307($request);
    }

    // =================================================================
    // BIR 1601-E - Monthly Expanded Withholding Tax Remittance
    // =================================================================

    public function bir1601e(Request $request)
    {
        $taxableMonth = $request->input('month', now()->format('Y-m'));
        $month = (int) date('m', strtotime($taxableMonth . '-01'));
        $year = (int) date('Y', strtotime($taxableMonth . '-01'));

        $payments = ApPayment::with('vendor')
            ->where('status', 'posted')
            ->where('withholding_tax', '>', 0)
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->orderBy('payment_date')
            ->get();

        // Group by ATC (tax type)
        $atcEntries = $payments->groupBy(function ($p) { return optional($p->vendor)->withholding_tax_type ?? 'EWT'; })
            ->map(function ($group, $type) {
                return (object) [
                    'atc' => $type,
                    'count' => $group->count(),
                    'taxable_amount' => $group->sum('gross_amount'),
                    'tax_withheld' => $group->sum('withholding_tax'),
                ];
            })->values();

        $totalTaxWithheld = $payments->sum('withholding_tax');
        $atcCodesUsed = $atcEntries->count();

        // Monthly trend (last 6 months)
        $monthlyTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $total = ApPayment::where('status', 'posted')
                ->where('withholding_tax', '>', 0)
                ->whereMonth('payment_date', $d->month)
                ->whereYear('payment_date', $d->year)
                ->sum('withholding_tax');
            $monthlyTrend->push((object) [
                'month' => $d->format('M Y'),
                'amount' => (float) $total,
            ]);
        }

        if ($request->filled('export')) {
            return $this->exportCsv("BIR-1601E-{$taxableMonth}.csv",
                ['ATC', 'No. of Payees', 'Taxable Amount', 'Tax Withheld'],
                $atcEntries->map(function ($e) { return [$e->atc, $e->count, $e->taxable_amount, $e->tax_withheld]; })
            );
        }

        return view('pages.tax.bir-1601e', compact(
            'taxableMonth', 'totalTaxWithheld', 'atcEntries', 'atcCodesUsed', 'monthlyTrend', 'payments'
        ));
    }

    public function generateBir1601e(Request $request)
    {
        return $this->bir1601e($request);
    }

    // =================================================================
    // BIR 2550-M - Monthly VAT Declaration
    // =================================================================

    public function vat2550m(Request $request)
    {
        $taxableMonth = $request->input('month', now()->format('Y-m'));
        $month = (int) date('m', strtotime($taxableMonth . '-01'));
        $year = (int) date('Y', strtotime($taxableMonth . '-01'));

        // Sales breakdown
        $taxableSales = (float) ArInvoice::whereNotIn('status', ['cancelled', 'voided'])
            ->whereMonth('invoice_date', $month)->whereYear('invoice_date', $year)
            ->where('tax_amount', '>', 0)->sum('gross_amount');

        $exemptSales = (float) ArInvoice::whereNotIn('status', ['cancelled', 'voided'])
            ->whereMonth('invoice_date', $month)->whereYear('invoice_date', $year)
            ->where('tax_amount', 0)->sum('gross_amount');

        $zeroRatedSales = 0;

        // VAT from GL
        $outputVat = (float) JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereMonth('je.posting_date', $month)->whereYear('je.posting_date', $year)
            ->where('coa.account_code', 'like', '2050%')
            ->sum(DB::raw('journal_entry_lines.credit - journal_entry_lines.debit'));

        $inputVat = (float) JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereMonth('je.posting_date', $month)->whereYear('je.posting_date', $year)
            ->where('coa.account_code', 'like', '1150%')
            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

        $vatPayable = $outputVat - $inputVat;

        $totalSales = $taxableSales + $exemptSales + $zeroRatedSales;
        $totalPurchases = (float) ApBill::whereNotIn('status', ['cancelled', 'voided'])
            ->whereMonth('bill_date', $month)->whereYear('bill_date', $year)->sum('gross_amount');

        // Revenue breakdown by account
        $revenueBreakdown = JournalEntryLine::select(
                'coa.account_name',
                DB::raw('SUM(journal_entry_lines.credit - journal_entry_lines.debit) as amount')
            )
            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('coa.account_type', 'revenue')
            ->where('je.status', 'posted')
            ->whereMonth('je.posting_date', $month)->whereYear('je.posting_date', $year)
            ->groupBy('coa.account_name')
            ->orderByDesc('amount')
            ->get();

        if ($request->filled('export')) {
            return $this->exportCsv("BIR-2550M-{$taxableMonth}.csv",
                ['Description', 'Amount'],
                collect([
                    ['Taxable Sales', $taxableSales], ['Exempt Sales', $exemptSales], ['Zero-Rated Sales', $zeroRatedSales],
                    ['Output VAT', $outputVat], ['Input VAT', $inputVat], ['VAT Payable', $vatPayable],
                ])
            );
        }

        return view('pages.tax.vat-2550m', compact(
            'taxableMonth', 'taxableSales', 'exemptSales', 'zeroRatedSales',
            'outputVat', 'inputVat', 'vatPayable', 'totalSales', 'totalPurchases', 'revenueBreakdown'
        ));
    }

    public function generateVat2550m(Request $request)
    {
        return $this->vat2550m($request);
    }

    // =================================================================
    // Alphalist of Payees (QAP & SAWT)
    // =================================================================

    public function alphalist(Request $request)
    {
        $quarter = $request->input('quarter', 'Q' . ceil(now()->month / 3));
        $year = $request->input('year', now()->year);

        $q = (int) str_replace('Q', '', $quarter);
        $startMonth = ($q - 1) * 3 + 1;
        $endMonth = $q * 3;

        $payments = ApPayment::with('vendor')
            ->where('status', 'posted')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', '>=', $startMonth)
            ->whereMonth('payment_date', '<=', $endMonth)
            ->get();

        $qapEntries = $payments->groupBy('vendor_id')->map(function ($group) {
            $vendor = $group->first()->vendor;
            return (object) [
                'tin' => $vendor->tin ?? '',
                'registered_name' => $vendor->name ?? '',
                'atc' => $vendor->withholding_tax_type ?? 'WE',
                'income_payment' => $group->sum('gross_amount'),
                'tax_withheld' => $group->sum('withholding_tax'),
            ];
        })->sortBy('registered_name')->values();

        $sawtEntries = collect(); // SAWT not yet implemented

        $totalPayees = $qapEntries->count();
        $totalIncome = $qapEntries->sum('income_payment');
        $totalTax = $qapEntries->sum('tax_withheld');

        if ($request->filled('export')) {
            return $this->exportCsv("Alphalist-{$quarter}-{$year}.csv",
                ['TIN', 'Registered Name', 'ATC', 'Income Payment', 'Tax Withheld'],
                $qapEntries->map(function ($e) { return [$e->tin, $e->registered_name, $e->atc, $e->income_payment, $e->tax_withheld]; })
            );
        }

        return view('pages.tax.alphalist', compact(
            'quarter', 'year', 'qapEntries', 'sawtEntries',
            'totalPayees', 'totalIncome', 'totalTax'
        ));
    }

    public function exportAlphalist(Request $request)
    {
        $request->merge(['export' => 'csv']);
        return $this->alphalist($request);
    }

    // =================================================================
    // Special Journals (BIR Books of Accounts)
    // =================================================================

    public function specialJournals(Request $request)
    {
        $dateFrom = $request->input('date_from') ?: now()->startOfYear()->toDateString();
        $dateTo = $request->input('date_to') ?: now()->toDateString();

        $loadEntries = function (string $type) use ($dateFrom, $dateTo) {
            return JournalEntryLine::select(
                    'journal_entry_lines.*',
                    'je.entry_number', 'je.entry_date', 'je.posting_date',
                    'je.reference_number', 'je.description as je_description',
                    'coa.account_code', 'coa.account_name'
                )
                ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
                ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
                ->where('je.status', 'posted')
                ->where('je.journal_type', $type)
                ->whereDate('je.posting_date', '>=', $dateFrom)
                ->whereDate('je.posting_date', '<=', $dateTo)
                ->orderBy('je.posting_date')
                ->orderBy('je.entry_number')
                ->get();
        };

        // Map to actual DB journal types (CRJ/CDJ/SJ/PJ don't exist in DB constraint)
        $cashReceipts = $loadEntries('revenue');
        $cashDisbursements = $loadEntries('expense');
        $salesJournal = $loadEntries('general');
        $purchasesJournal = $loadEntries('payroll');

        if ($request->filled('export')) {
            $journal = $request->input('journal', 'receipts');
            $map = ['receipts' => $cashReceipts, 'disbursements' => $cashDisbursements, 'sales' => $salesJournal, 'purchases' => $purchasesJournal];
            $entries = $map[$journal] ?? $cashReceipts;
            return $this->exportCsv("Special-Journal-{$journal}-{$dateFrom}.csv",
                ['Date', 'Entry #', 'Reference', 'Account Code', 'Account Name', 'Description', 'Debit', 'Credit'],
                $entries->map(function ($e) { return [$e->posting_date, $e->entry_number, $e->reference_number, $e->account_code, $e->account_name, $e->je_description, $e->debit, $e->credit]; })
            );
        }

        return view('pages.tax.special-journals', compact(
            'cashReceipts', 'cashDisbursements', 'salesJournal', 'purchasesJournal',
            'dateFrom', 'dateTo'
        ));
    }

    // =================================================================
    // Check Writer
    // =================================================================

    public function checkWriter(Request $request)
    {
        $checkPayments = DisbursementPayment::with('disbursement')
            ->where('payment_method', 'check')
            ->where('status', 'completed')
            ->latest('payment_date')
            ->paginate(20);

        // Pending = checks that have been generated but not yet printed (check_number exists)
        $pendingChecks = DisbursementPayment::where('payment_method', 'check')
            ->where('status', 'completed')
            ->whereNotNull('check_number')
            ->count();

        $totalAmount = DisbursementPayment::where('payment_method', 'check')
            ->where('status', 'completed')
            ->sum('net_amount');

        return view('pages.tax.check-writer', compact('checkPayments', 'pendingChecks', 'totalAmount'));
    }

    public function printCheck(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:disbursement_payments,id',
        ]);

        $payment = DisbursementPayment::with('disbursement.department')->findOrFail($validated['payment_id']);

        return view('pages.tax.print-check', compact('payment'));
    }

    // =================================================================
    // Other BIR Forms
    // =================================================================

    public function bir0619e(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $payments = DisbursementPayment::with('disbursement.vendor')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->get();

        $totalTaxBase = $payments->sum('gross_amount');
        $totalTaxWithheld = $payments->sum('withholding_tax');

        return view('pages.tax.bir-0619e', compact('payments', 'totalTaxBase', 'totalTaxWithheld', 'month', 'year'));
    }

    public function bir0619f(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        return view('pages.tax.bir-0619f', compact('month', 'year'));
    }

    public function bir1601c(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        return view('pages.tax.bir-1601c', compact('month', 'year'));
    }

    public function bir1601eq(Request $request)
    {
        $quarter = $request->input('quarter', ceil(now()->month / 3));
        $year = $request->input('year', now()->year);

        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        $payments = DisbursementPayment::with('disbursement.vendor')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', '>=', $startMonth)
            ->whereMonth('payment_date', '<=', $endMonth)
            ->get();

        $totalTaxBase = $payments->sum('gross_amount');
        $totalTaxWithheld = $payments->sum('withholding_tax');

        return view('pages.tax.bir-1601eq', compact('payments', 'totalTaxBase', 'totalTaxWithheld', 'quarter', 'year'));
    }

    public function bir1604e(Request $request)
    {
        $year = $request->input('year', now()->year);

        $payments = DisbursementPayment::with('disbursement.vendor')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->get();

        $totalTaxBase = $payments->sum('gross_amount');
        $totalTaxWithheld = $payments->sum('withholding_tax');
        $payeeCount = $payments->groupBy(function ($p) { return $p->disbursement->payee_name ?? ''; })->count();

        return view('pages.tax.bir-1604e', compact('payments', 'totalTaxBase', 'totalTaxWithheld', 'payeeCount', 'year'));
    }

    public function bir1604cf(Request $request)
    {
        $year = $request->input('year', now()->year);
        return view('pages.tax.bir-1604cf', compact('year'));
    }

    // =================================================================
    // Alphalist - Quarterly & Annual
    // =================================================================

    public function alphalistQuarterly(Request $request)
    {
        $quarter = $request->input('quarter', ceil(now()->month / 3));
        $year = $request->input('year', now()->year);

        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        $payments = DisbursementPayment::with('disbursement.vendor')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', '>=', $startMonth)
            ->whereMonth('payment_date', '<=', $endMonth)
            ->orderBy('payment_date')
            ->get();

        $alphalist = $payments->groupBy(function ($p) { return $p->disbursement->payee_name ?? 'Unknown'; })
            ->map(function ($group, $payeeName) {
                $first = $group->first();
                return (object) [
                    'payee_name' => $payeeName,
                    'tin' => $first->disbursement->vendor->tin ?? '',
                    'atc' => $first->disbursement->vendor->withholding_tax_type ?? '',
                    'income_payment' => $group->sum('gross_amount'),
                    'tax_withheld' => $group->sum('withholding_tax'),
                ];
            })->sortBy('payee_name')->values();

        return view('pages.tax.alphalist-quarterly', compact('alphalist', 'quarter', 'year'));
    }

    public function alphalistAnnual(Request $request)
    {
        $year = $request->input('year', now()->year);

        $payments = DisbursementPayment::with('disbursement.vendor')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->orderBy('payment_date')
            ->get();

        $alphalist = $payments->groupBy(function ($p) { return $p->disbursement->payee_name ?? 'Unknown'; })
            ->map(function ($group, $payeeName) {
                $first = $group->first();
                return (object) [
                    'payee_name' => $payeeName,
                    'tin' => $first->disbursement->vendor->tin ?? '',
                    'atc' => $first->disbursement->vendor->withholding_tax_type ?? '',
                    'income_payment' => $group->sum('gross_amount'),
                    'tax_withheld' => $group->sum('withholding_tax'),
                ];
            })->sortBy('payee_name')->values();

        return view('pages.tax.alphalist-annual', compact('alphalist', 'year'));
    }

    // =================================================================
    // Reusable CSV Export Helper
    // =================================================================

    private function exportCsv(string $filename, array $headers, $rows)
    {
        return response()->stream(function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, is_array($row) ? $row : (array) $row);
            }
            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
