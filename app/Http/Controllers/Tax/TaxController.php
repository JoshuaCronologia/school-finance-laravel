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
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    /**
     * School info used by BIR forms.
     */
    private function schoolInfo(): array
    {
        return Cache::remember('school_info', 600, function () {
            $keys = ['school_tin', 'school_name', 'school_address', 'authorized_rep_name', 'authorized_rep_tin'];
            $rows = Setting::whereIn('key', $keys)->pluck('value', 'key');

            return [
                'schoolTin'   => $rows->get('school_tin', '000-000-000-000'),
                'schoolName'  => $rows->get('school_name', config('app.name')),
                'schoolAddress' => $rows->get('school_address', 'Manila, Philippines'),
                'authRepName' => $rows->get('authorized_rep_name', ''),
                'authRepTin'  => $rows->get('authorized_rep_tin', ''),
            ];
        });
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

            // Query AP payments (from AP bills flow)
            $apPayments = ApPayment::with('vendor', 'taxCode')
                ->where('status', 'posted')
                ->where('withholding_tax', '>', 0)
                ->where('vendor_id', $selectedVendor->id)
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', '>=', $startMonth)
                ->whereMonth('payment_date', '<=', $endMonth)
                ->get();

            // Query disbursement payments (from disbursement request flow)
            $disbPayments = DisbursementPayment::with('disbursement.vendor', 'taxCode')
                ->whereHas('disbursement', function ($dq) use ($selectedVendor) {
                    $dq->where('payee_id', $selectedVendor->id);
                })
                ->where('status', 'completed')
                ->where('withholding_tax', '>', 0)
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', '>=', $startMonth)
                ->whereMonth('payment_date', '<=', $endMonth)
                ->get();

            // Normalize both into a common flat collection
            $normalized = collect();
            foreach ($apPayments as $p) {
                $normalized->push((object)[
                    'payment_date'   => $p->payment_date,
                    'gross_amount'   => (float)$p->gross_amount,
                    'withholding_tax'=> (float)$p->withholding_tax,
                    'atc'            => optional($p->taxCode)->bir_atc ?? optional($p->vendor)->withholding_tax_type ?? 'EWT',
                ]);
            }
            foreach ($disbPayments as $p) {
                $normalized->push((object)[
                    'payment_date'   => $p->payment_date,
                    'gross_amount'   => (float)$p->gross_amount,
                    'withholding_tax'=> (float)$p->withholding_tax,
                    'atc'            => optional($p->taxCode)->bir_atc ?? optional($p->disbursement->vendor)->withholding_tax_type ?? 'EWT',
                ]);
            }

            $sm = $startMonth;
            $atcEntries = $normalized->groupBy('atc')->map(function ($group, $atc) use ($sm) {
                $m1 = $group->filter(function ($p) use ($sm) { return (int)$p->payment_date->format('m') === $sm; })->sum('gross_amount');
                $m2 = $group->filter(function ($p) use ($sm) { return (int)$p->payment_date->format('m') === ($sm + 1); })->sum('gross_amount');
                $m3 = $group->filter(function ($p) use ($sm) { return (int)$p->payment_date->format('m') === ($sm + 2); })->sum('gross_amount');
                return (object)[
                    'atc'   => $atc,
                    'm1'    => (float)$m1,
                    'm2'    => (float)$m2,
                    'm3'    => (float)$m3,
                    'total' => (float)($m1 + $m2 + $m3),
                    'tax'   => (float)$group->sum('withholding_tax'),
                ];
            })->values();

            $formData = (object)[
                'vendor'       => $selectedVendor,
                'atcEntries'   => $atcEntries,
                'total_income' => $normalized->sum('gross_amount'),
                'total_tax'    => $normalized->sum('withholding_tax'),
            ];
        }

        // Summary — combine ap_payments + disbursement_payments per vendor
        $apSummary = ApPayment::with('vendor', 'taxCode')
            ->select('vendor_id', 'tax_code_id',
                DB::raw('SUM(gross_amount) as total_income'),
                DB::raw('SUM(withholding_tax) as total_tax'))
            ->where('status', 'posted')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->groupBy('vendor_id', 'tax_code_id')
            ->get()
            ->map(function ($p) {
                return (object)[
                    'vendor'        => $p->vendor,
                    'atc'           => optional($p->taxCode)->bir_atc ?? optional($p->vendor)->withholding_tax_type ?? '',
                    'income_payment'=> (float)$p->total_income,
                    'tax_withheld'  => (float)$p->total_tax,
                ];
            });

        $disbSummary = DisbursementPayment::with('disbursement.vendor', 'taxCode')
            ->select('tax_code_id',
                DB::raw('SUM(gross_amount) as total_income'),
                DB::raw('SUM(withholding_tax) as total_tax'),
                DB::raw('(SELECT payee_id FROM disbursement_requests WHERE id = disbursement_payments.disbursement_id LIMIT 1) as vendor_id'))
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->groupBy('disbursement_id', 'tax_code_id')
            ->get()
            ->filter(function ($p) { return $p->disbursement && $p->disbursement->vendor; })
            ->map(function ($p) {
                return (object)[
                    'vendor'        => $p->disbursement->vendor,
                    'atc'           => optional($p->taxCode)->bir_atc ?? optional($p->disbursement->vendor)->withholding_tax_type ?? '',
                    'income_payment'=> (float)$p->total_income,
                    'tax_withheld'  => (float)$p->total_tax,
                ];
            });

        $summary = $apSummary->merge($disbSummary)
            ->groupBy(function ($item) { return optional($item->vendor)->id ?? 0; })
            ->map(function ($group) {
                return (object)[
                    'vendor'        => $group->first()->vendor,
                    'atc'           => $group->first()->atc,
                    'income_payment'=> $group->sum('income_payment'),
                    'tax_withheld'  => $group->sum('tax_withheld'),
                ];
            })
            ->filter(function ($item) { return $item->vendor !== null; })
            ->sortBy(function ($item) { return optional($item->vendor)->name; })
            ->values();

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
                    $item->atc ?? '',
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
        (new AuditService)->logActivity('exported', 'tax', 'Generated BIR 2307');

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

        $payments = ApPayment::with('vendor', 'taxCode')
            ->where('status', 'posted')
            ->where('withholding_tax', '>', 0)
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->orderBy('payment_date')
            ->get();

        // Group by ATC — prefer tax_code.bir_atc, fall back to vendor.withholding_tax_type
        $atcEntries = $payments->groupBy(function ($p) {
            return optional($p->taxCode)->bir_atc ?? optional($p->vendor)->withholding_tax_type ?? 'EWT';
        })
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
        (new AuditService)->logActivity('exported', 'tax', 'Generated BIR 1601-E');

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
            ->where('coa.account_code', 'like', '2210%')
            ->sum(DB::raw('journal_entry_lines.credit - journal_entry_lines.debit'));

        $inputVat = (float) JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereMonth('je.posting_date', $month)->whereYear('je.posting_date', $year)
            ->where('coa.account_code', 'like', '2220%')
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

        $schoolRdo = Setting::where('key', 'school_rdo')->value('value') ?? '';

        return view('pages.tax.vat-2550m', array_merge(
            compact(
                'taxableMonth', 'taxableSales', 'exemptSales', 'zeroRatedSales',
                'outputVat', 'inputVat', 'vatPayable', 'totalSales', 'totalPurchases', 'revenueBreakdown',
                'schoolRdo'
            ),
            $this->schoolInfo()
        ));
    }

    public function generateVat2550m(Request $request)
    {
        (new AuditService)->logActivity('exported', 'tax', 'Generated VAT 2550M');

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

        $payments = ApPayment::with('vendor', 'taxCode')
            ->where('status', 'posted')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', '>=', $startMonth)
            ->whereMonth('payment_date', '<=', $endMonth)
            ->get();

        $allMonthNames = [
            1 => 'January', 2 => 'February', 3 => 'March',
            4 => 'April',   5 => 'May',       6 => 'June',
            7 => 'July',    8 => 'August',    9 => 'September',
            10 => 'October', 11 => 'November', 12 => 'December',
        ];
        $monthNames = [
            $allMonthNames[$startMonth],
            $allMonthNames[$startMonth + 1],
            $allMonthNames[$endMonth],
        ];

        $sm = $startMonth;
        $qapEntries = $payments->groupBy('vendor_id')->map(function ($group) use ($sm) {
            $vendor = $group->first()->vendor;
            $atc = optional($group->first()->taxCode)->bir_atc
                ?? optional($vendor)->withholding_tax_type
                ?? 'WE';
            $m1 = $group->filter(function ($p) use ($sm) { return (int)$p->payment_date->format('m') === $sm; })->sum('gross_amount');
            $m2 = $group->filter(function ($p) use ($sm) { return (int)$p->payment_date->format('m') === ($sm + 1); })->sum('gross_amount');
            $m3 = $group->filter(function ($p) use ($sm) { return (int)$p->payment_date->format('m') === ($sm + 2); })->sum('gross_amount');
            return (object)[
                'tin'            => $vendor->tin ?? '',
                'registered_name'=> $vendor->name ?? '',
                'atc'            => $atc,
                'm1_income'      => (float)$m1,
                'm2_income'      => (float)$m2,
                'm3_income'      => (float)$m3,
                'income_payment' => (float)$group->sum('gross_amount'),
                'tax_withheld'   => (float)$group->sum('withholding_tax'),
            ];
        })->sortBy('registered_name')->values();

        $sawtEntries = collect(); // SAWT not yet implemented

        $totalPayees = $qapEntries->count();
        $totalIncome = $qapEntries->sum('income_payment');
        $totalTax = $qapEntries->sum('tax_withheld');

        if ($request->filled('export')) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\AlphalistQuarterlyExport($qapEntries, $monthNames, $quarter, $year, 'registered_name'),
                "Alphalist-QAP-{$quarter}-{$year}.xlsx"
            );
        }

        return view('pages.tax.alphalist', compact(
            'quarter', 'year', 'qapEntries', 'sawtEntries',
            'totalPayees', 'totalIncome', 'totalTax', 'monthNames'
        ));
    }

    public function exportAlphalist(Request $request)
    {
        (new AuditService)->logActivity('exported', 'tax', 'Exported Alphalist');

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
        $filter = $request->input('filter', 'all');

        // Sync: auto-create issued_checks for disbursement payments that don't have one
        $unsyncedPayments = DisbursementPayment::where('payment_method', 'check')
            ->where('status', 'completed')
            ->whereNotNull('check_number')
            ->whereNotIn('id', \App\Models\IssuedCheck::whereNotNull('disbursement_payment_id')->pluck('disbursement_payment_id'))
            ->get();

        foreach ($unsyncedPayments as $p) {
            $bankAcct = \App\Models\BankAccount::first();
            if ($bankAcct) {
                \App\Models\IssuedCheck::create([
                    'bank_account_id' => $bankAcct->id,
                    'check_date' => $p->payment_date,
                    'check_number' => $p->check_number,
                    'payee' => $p->disbursement->payee_name ?? 'Unknown',
                    'amount' => $p->net_amount,
                    'status' => 'outstanding',
                    'disbursement_payment_id' => $p->id,
                ]);
            }
        }

        // Query issued_checks directly (includes both disbursement-linked AND manually added)
        $query = \App\Models\IssuedCheck::with('bankAccount')
            ->orderByDesc('check_date');

        if ($filter === 'ic') {
            $query->where('status', 'outstanding');
        } elseif ($filter === 'cc') {
            $query->where('status', 'cleared');
        }

        $checks = $query->paginate(20);

        $totalIssued = \App\Models\IssuedCheck::where('status', 'outstanding')->count();
        $totalCleared = \App\Models\IssuedCheck::where('status', 'cleared')->count();
        $totalAmount = \App\Models\IssuedCheck::whereIn('status', ['outstanding', 'cleared'])->sum('amount');

        return view('pages.tax.check-writer', compact(
            'checks', 'totalIssued', 'totalCleared', 'totalAmount', 'filter'
        ));
    }

    public function batchClearChecks(Request $request)
    {
        $validated = $request->validate([
            'check_ids' => 'required|array|min:1',
            'check_ids.*' => 'exists:issued_checks,id',
        ]);

        $count = \App\Models\IssuedCheck::whereIn('id', $validated['check_ids'])
            ->where('status', 'outstanding')
            ->update([
                'status' => 'cleared',
                'cleared_date' => now()->toDateString(),
            ]);

        return back()->with('success', "{$count} check(s) marked as cleared.");
    }

    public function printCheck(Request $request)
    {
        (new AuditService)->logActivity('exported', 'tax', 'Printed check');

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

        $payments = DisbursementPayment::with('disbursement.vendor', 'taxCode')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->get();

        $totalTaxBase = $payments->sum('gross_amount');
        $totalTaxWithheld = $payments->sum('withholding_tax');

        $schoolRdo = Setting::where('key', 'school_rdo')->value('value') ?? '';

        return view('pages.tax.bir-0619e', array_merge(
            compact('payments', 'totalTaxBase', 'totalTaxWithheld', 'month', 'year', 'schoolRdo'),
            $this->schoolInfo()
        ));
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
        $year  = $request->input('year',  now()->year);

        $schoolName    = Setting::where('key', 'school_name')->value('value')    ?? config('app.name');
        $schoolTin     = Setting::where('key', 'school_tin')->value('value')     ?? '';
        $schoolAddress = Setting::where('key', 'school_address')->value('value') ?? '';
        $schoolRdo     = Setting::where('key', 'school_rdo')->value('value')     ?? '';

        // All zero until payroll module is wired up
        $data = [
            'item14' => 0, // Total Amount of Compensation
            'item15' => 0, // Non-Taxable/Exempt Compensation
            'item16' => 0, // Statutory Minimum Wage (MWEs)
            'item17' => 0, // Holiday/OT/Night Diff/Hazard Pay (MWEs)
            'item18' => 0, // 13th Month Pay & Other Benefits
            'item19' => 0, // De Minimis Benefits
            'item20' => 0, // SSS/GSIS/PhilHealth/HDMF/Union Dues (employee share)
            'item21' => 0, // Other Non-Taxable Compensation
        ];

        $data['item22'] = $data['item15'] + $data['item16'] + $data['item17']
                        + $data['item18'] + $data['item19'] + $data['item20'] + $data['item21'];
        $data['item23'] = max(0, $data['item14'] - $data['item22']);
        $data['item24'] = 0; // Taxable comp not subject to WTax (≤250K/year earners)
        $data['item25'] = 0; // Total Taxes Withheld
        $data['item26'] = 0; // Adjustment from previous month(s)
        $data['item27'] = $data['item25'] + $data['item26'];
        $data['item28'] = 0; // Tax remitted in previously filed return
        $data['item29'] = 0; // Other remittances made
        $data['item30'] = $data['item28'] + $data['item29'];
        $data['item31'] = $data['item27'] - $data['item30'];
        $data['item32'] = 0; // Surcharge
        $data['item33'] = 0; // Interest
        $data['item34'] = 0; // Compromise
        $data['item35'] = $data['item32'] + $data['item33'] + $data['item34'];
        $data['item36'] = $data['item31'] + $data['item35'];

        return view('pages.tax.bir-1601c', compact(
            'month', 'year', 'data',
            'schoolName', 'schoolTin', 'schoolAddress', 'schoolRdo'
        ));
    }

    public function bir1601eq(Request $request)
    {
        $quarter = $request->input('quarter', ceil(now()->month / 3));
        $year = $request->input('year', now()->year);

        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        $payments = DisbursementPayment::with('disbursement.vendor', 'taxCode')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', '>=', $startMonth)
            ->whereMonth('payment_date', '<=', $endMonth)
            ->get();

        $totalTaxBase = $payments->sum('gross_amount');
        $totalTaxWithheld = $payments->sum('withholding_tax');

        $atcEntries = $payments->groupBy(function ($p) {
            return optional($p->taxCode)->bir_atc
                ?? optional($p->disbursement->vendor)->withholding_tax_type
                ?? 'EWT';
        })->map(function ($group, $atc) {
            return (object) [
                'atc' => $atc,
                'count' => $group->count(),
                'taxable_amount' => $group->sum('gross_amount'),
                'tax_withheld' => $group->sum('withholding_tax'),
            ];
        })->values();

        $taxCredits = 0;
        $netTaxDue = $totalTaxWithheld - $taxCredits;
        $surcharge = 0;
        $interest = 0;
        $compromise = 0;
        $totalAmountDue = $netTaxDue + $surcharge + $interest + $compromise;
        $schoolRdo = Setting::where('key', 'school_rdo')->value('value') ?? '';

        return view('pages.tax.bir-1601eq', array_merge(
            compact(
                'payments', 'totalTaxBase', 'totalTaxWithheld', 'quarter', 'year',
                'atcEntries', 'taxCredits', 'netTaxDue',
                'surcharge', 'interest', 'compromise', 'totalAmountDue', 'schoolRdo'
            ),
            $this->schoolInfo()
        ));
    }

    public function bir1604e(Request $request)
    {
        $year = $request->input('year', now()->year);

        $payments = DisbursementPayment::with('disbursement.vendor', 'taxCode')
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
        $quarter = (int) $request->input('quarter', ceil(now()->month / 3));
        $year = $request->input('year', now()->year);

        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        $allMonthNames = [
            1=>'January',2=>'February',3=>'March',4=>'April',
            5=>'May',6=>'June',7=>'July',8=>'August',
            9=>'September',10=>'October',11=>'November',12=>'December',
        ];
        $monthNames = [$allMonthNames[$startMonth], $allMonthNames[$startMonth+1], $allMonthNames[$endMonth]];

        $payments = DisbursementPayment::with('disbursement.vendor', 'taxCode')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', '>=', $startMonth)
            ->whereMonth('payment_date', '<=', $endMonth)
            ->orderBy('payment_date')
            ->get();

        $sm = $startMonth;
        $alphalist = $payments->groupBy(function ($p) { return $p->disbursement->payee_name ?? 'Unknown'; })
            ->map(function ($group, $payeeName) use ($sm) {
                $first = $group->first();
                $atc = optional($first->taxCode)->bir_atc
                    ?? optional($first->disbursement->vendor)->withholding_tax_type
                    ?? '';
                $m1 = $group->filter(function ($p) use ($sm) { return (int)$p->payment_date->format('m') === $sm; })->sum('gross_amount');
                $m2 = $group->filter(function ($p) use ($sm) { return (int)$p->payment_date->format('m') === ($sm+1); })->sum('gross_amount');
                $m3 = $group->filter(function ($p) use ($sm) { return (int)$p->payment_date->format('m') === ($sm+2); })->sum('gross_amount');
                return (object)[
                    'payee_name'     => $payeeName,
                    'tin'            => optional($first->disbursement->vendor)->tin ?? '',
                    'atc'            => $atc,
                    'm1_income'      => (float)$m1,
                    'm2_income'      => (float)$m2,
                    'm3_income'      => (float)$m3,
                    'income_payment' => (float)$group->sum('gross_amount'),
                    'tax_withheld'   => (float)$group->sum('withholding_tax'),
                ];
            })->sortBy('payee_name')->values();

        if ($request->input('export') === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\AlphalistQuarterlyExport($alphalist, $monthNames, $quarter, $year),
                "Alphalist-Q{$quarter}-{$year}.xlsx"
            );
        }

        return view('pages.tax.alphalist-quarterly', compact('alphalist', 'quarter', 'year', 'monthNames'));
    }

    public function alphalistAnnual(Request $request)
    {
        $year = $request->input('year', now()->year);

        $payments = DisbursementPayment::with('disbursement.vendor', 'taxCode')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->orderBy('payment_date')
            ->get();

        $alphalist = $payments->groupBy(function ($p) { return $p->disbursement->payee_name ?? 'Unknown'; })
            ->map(function ($group, $payeeName) {
                $first = $group->first();
                $atc = optional($first->taxCode)->bir_atc
                    ?? optional($first->disbursement->vendor)->withholding_tax_type
                    ?? '';
                $q = function ($qNum) use ($group) {
                    $sm = ($qNum - 1) * 3 + 1;
                    return $group->filter(function ($p) use ($sm) {
                        $m = (int)$p->payment_date->format('m');
                        return $m >= $sm && $m <= ($sm + 2);
                    })->sum('gross_amount');
                };
                return (object)[
                    'payee_name'     => $payeeName,
                    'tin'            => optional($first->disbursement->vendor)->tin ?? '',
                    'atc'            => $atc,
                    'q1_income'      => (float)$q(1),
                    'q2_income'      => (float)$q(2),
                    'q3_income'      => (float)$q(3),
                    'q4_income'      => (float)$q(4),
                    'income_payment' => (float)$group->sum('gross_amount'),
                    'tax_withheld'   => (float)$group->sum('withholding_tax'),
                ];
            })->sortBy('payee_name')->values();

        if ($request->input('export') === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\AlphalistAnnualExport($alphalist, $year),
                "Alphalist-Annual-{$year}.xlsx"
            );
        }

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
