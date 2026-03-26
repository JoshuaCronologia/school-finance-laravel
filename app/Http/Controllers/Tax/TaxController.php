<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\ApBill;
use App\Models\ApPayment;
use App\Models\ArCollection;
use App\Models\ArInvoice;
use App\Models\DisbursementPayment;
use App\Models\JournalEntryLine;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    /**
     * BIR 2307 - Certificate of Creditable Tax Withheld at Source.
     */
    public function bir2307(Request $request)
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        $query = ApPayment::with('vendor')
            ->where('status', 'posted')
            ->where('withholding_tax', '>', 0);

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('quarter')) {
            $quarter = (int) $request->quarter;
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $quarter * 3;
            $year = $request->input('year', now()->year);
            $query->whereYear('payment_date', $year)
                  ->whereMonth('payment_date', '>=', $startMonth)
                  ->whereMonth('payment_date', '<=', $endMonth);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderBy('payment_date')->get();

        $totalWithheld = $payments->sum('withholding_tax');

        return view('pages.tax.bir-2307', compact('payments', 'vendors', 'totalWithheld'));
    }

    public function generateBir2307(Request $request)
    {
        return $this->bir2307($request);
    }

    /**
     * BIR 1601-E - Monthly Expanded Withholding Tax Remittance.
     */
    public function bir1601e(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $payments = ApPayment::with('vendor')
            ->where('status', 'posted')
            ->where('withholding_tax', '>', 0)
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->orderBy('payment_date')
            ->get();

        // Group by tax type
        $byTaxType = $payments->groupBy(fn($p) => $p->vendor?->withholding_tax_type ?? 'EWT')
            ->map(function ($group, $type) {
                return [
                    'type' => $type,
                    'count' => $group->count(),
                    'taxable_amount' => $group->sum('gross_amount'),
                    'tax_withheld' => $group->sum('withholding_tax'),
                ];
            });

        $totalTaxWithheld = $payments->sum('withholding_tax');

        return view('pages.tax.bir-1601e', compact('payments', 'byTaxType', 'totalTaxWithheld', 'month', 'year'));
    }

    public function generateBir1601e(Request $request)
    {
        return $this->bir1601e($request);
    }

    /**
     * BIR 2550-M - Monthly VAT Declaration.
     */
    public function vat2550m(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Output VAT from sales/invoices
        $outputVat = JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereMonth('je.posting_date', $month)
            ->whereYear('je.posting_date', $year)
            ->where('coa.account_code', 'like', '2050%')
            ->sum(DB::raw('journal_entry_lines.credit - journal_entry_lines.debit'));

        // Input VAT from purchases/bills
        $inputVat = JournalEntryLine::join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->whereMonth('je.posting_date', $month)
            ->whereYear('je.posting_date', $year)
            ->where('coa.account_code', 'like', '1150%')
            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

        $vatPayable = $outputVat - $inputVat;

        // Sales summary
        $totalSales = ArInvoice::whereNotIn('status', ['cancelled', 'voided'])
            ->whereMonth('invoice_date', $month)
            ->whereYear('invoice_date', $year)
            ->sum('gross_amount');

        // Purchase summary
        $totalPurchases = ApBill::whereNotIn('status', ['cancelled', 'voided'])
            ->whereMonth('bill_date', $month)
            ->whereYear('bill_date', $year)
            ->sum('gross_amount');

        return view('pages.tax.vat-2550m', compact(
            'outputVat', 'inputVat', 'vatPayable',
            'totalSales', 'totalPurchases', 'month', 'year'
        ));
    }

    public function generateVat2550m(Request $request)
    {
        return $this->vat2550m($request);
    }

    /**
     * Alphalist - Quarterly Alphabetical List of Payees.
     */
    public function alphalist(Request $request)
    {
        $quarter = $request->input('quarter', ceil(now()->month / 3));
        $year = $request->input('year', now()->year);

        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        $payees = ApPayment::with('vendor')
            ->select(
                'vendor_id',
                DB::raw('SUM(gross_amount) as total_amount'),
                DB::raw('SUM(withholding_tax) as total_tax')
            )
            ->where('status', 'posted')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', '>=', $startMonth)
            ->whereMonth('payment_date', '<=', $endMonth)
            ->groupBy('vendor_id')
            ->get()
            ->sortBy(fn($p) => $p->vendor?->name);

        $totalAmount = $payees->sum('total_amount');
        $totalTax = $payees->sum('total_tax');

        return view('pages.tax.alphalist', compact('payees', 'totalAmount', 'totalTax', 'quarter', 'year'));
    }

    public function exportAlphalist(Request $request)
    {
        return $this->alphalist($request);
    }

    /**
     * Special Journals - Cash Receipts, Cash Disbursements, Sales, Purchases.
     */
    public function specialJournals(Request $request)
    {
        $journalType = $request->input('type', 'CRJ');
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $entries = JournalEntryLine::select(
                'journal_entry_lines.*',
                'je.entry_number', 'je.entry_date', 'je.posting_date',
                'je.reference_number', 'je.description as je_description',
                'coa.account_code', 'coa.account_name'
            )
            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'journal_entry_lines.account_id', '=', 'coa.id')
            ->where('je.status', 'posted')
            ->where('je.journal_type', $journalType)
            ->whereDate('je.posting_date', '>=', $dateFrom)
            ->whereDate('je.posting_date', '<=', $dateTo)
            ->orderBy('je.posting_date')
            ->orderBy('je.entry_number')
            ->get()
            ->groupBy('journal_entry_id');

        $totalDebit = $entries->flatten()->sum('debit');
        $totalCredit = $entries->flatten()->sum('credit');

        $journalTypes = [
            'CRJ' => 'Cash Receipts Journal',
            'CDJ' => 'Cash Disbursements Journal',
            'SJ' => 'Sales Journal',
            'PJ' => 'Purchases Journal',
            'GJ' => 'General Journal',
        ];

        return view('pages.tax.special-journals', compact(
            'entries', 'journalType', 'journalTypes',
            'totalDebit', 'totalCredit', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Check Writer utility.
     */
    public function checkWriter(Request $request)
    {
        $payments = DisbursementPayment::with('disbursement')
            ->where('payment_method', 'check')
            ->where('status', 'completed')
            ->latest('payment_date')
            ->paginate(20);

        return view('pages.tax.check-writer', compact('payments'));
    }

    public function printCheck(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:disbursement_payments,id',
        ]);

        $payment = DisbursementPayment::with('disbursement')->findOrFail($validated['payment_id']);

        return view('pages.tax.print-check', compact('payment'));
    }

    /**
     * BIR 0619-E - Monthly Remittance of Creditable Income Taxes Withheld (Expanded).
     */
    public function bir0619e(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $payments = DisbursementPayment::with('disbursement')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->get();

        $totalTaxBase = $payments->sum('gross_amount');
        $totalTaxWithheld = $payments->sum('withholding_tax');

        return view('pages.tax.bir-0619e', compact('payments', 'totalTaxBase', 'totalTaxWithheld', 'month', 'year'));
    }

    /**
     * BIR 0619-F - Monthly Remittance of Final Income Taxes Withheld.
     */
    public function bir0619f(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        return view('pages.tax.bir-0619f', compact('month', 'year'));
    }

    /**
     * BIR 1601-C - Monthly Remittance of Income Taxes Withheld on Compensation.
     */
    public function bir1601c(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        return view('pages.tax.bir-1601c', compact('month', 'year'));
    }

    /**
     * BIR 1601-EQ - Quarterly Remittance of Creditable Income Taxes Withheld (Expanded).
     */
    public function bir1601eq(Request $request)
    {
        $quarter = $request->input('quarter', ceil(now()->month / 3));
        $year = $request->input('year', now()->year);

        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        $payments = DisbursementPayment::with('disbursement')
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

    /**
     * BIR 1604-E - Annual Information Return of Creditable Income Taxes Withheld (Expanded).
     */
    public function bir1604e(Request $request)
    {
        $year = $request->input('year', now()->year);

        $payments = DisbursementPayment::with('disbursement')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->get();

        $totalTaxBase = $payments->sum('gross_amount');
        $totalTaxWithheld = $payments->sum('withholding_tax');
        $payeeCount = $payments->groupBy(fn($p) => $p->disbursement->payee_name ?? '')->count();

        return view('pages.tax.bir-1604e', compact('payments', 'totalTaxBase', 'totalTaxWithheld', 'payeeCount', 'year'));
    }

    /**
     * BIR 1604-CF - Annual Information Return of Income Tax Withheld on Compensation and Final.
     */
    public function bir1604cf(Request $request)
    {
        $year = $request->input('year', now()->year);

        return view('pages.tax.bir-1604cf', compact('year'));
    }

    /**
     * Alphalist - Quarterly (QAP).
     */
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

        $alphalist = $payments->groupBy(fn($p) => $p->disbursement->payee_name ?? 'Unknown')
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

    /**
     * Alphalist - Annual.
     */
    public function alphalistAnnual(Request $request)
    {
        $year = $request->input('year', now()->year);

        $payments = DisbursementPayment::with('disbursement.vendor')
            ->where('status', 'completed')
            ->where('withholding_tax', '>', 0)
            ->whereYear('payment_date', $year)
            ->orderBy('payment_date')
            ->get();

        $alphalist = $payments->groupBy(fn($p) => $p->disbursement->payee_name ?? 'Unknown')
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
}
