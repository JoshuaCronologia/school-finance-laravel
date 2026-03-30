<?php

namespace App\Http\Controllers\AP;

use App\Http\Controllers\Controller;
use App\Models\DisbursementPayment;
use App\Models\DisbursementRequest;
use App\Services\AuditService;
use App\Services\BudgetService;
use App\Services\NumberingService;
use App\Services\PostingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Payment Processing queue – approved disbursements waiting to be paid.
     */
    public function index(Request $request)
    {
        $query = DisbursementRequest::with('department', 'category')
            ->where('status', 'approved')
            ->whereDoesntHave('payment');

        // Filter by "approved as of" date
        if ($request->filled('as_of_date')) {
            $query->where('request_date', '<=', $request->as_of_date);
        }

        // Filter by payment method
        if ($request->filled('filter_method')) {
            $query->where('payment_method', $request->filter_method);
        }

        $readyForPayment = $query->oldest('request_date')->get();

        $totalReadyForPayment = $readyForPayment->sum('amount');

        // Get last check used for reference (audit trail / bank rec)
        $lastCheckUsed = DisbursementPayment::with('disbursement')
            ->where('payment_method', 'check')
            ->whereNotNull('check_number')
            ->where('status', 'completed')
            ->latest('id')
            ->first();

        // Suggest next check number
        $nextCheckNumber = $lastCheckUsed && $lastCheckUsed->check_number
            ? $this->incrementCheckNumber($lastCheckUsed->check_number)
            : null;

        return view('pages.ap.payment-processing', compact(
            'readyForPayment', 'totalReadyForPayment', 'lastCheckUsed', 'nextCheckNumber'
        ));
    }

    /**
     * Batch generate voucher numbers and check numbers for selected disbursements.
     */
    public function batchProcess(Request $request)
    {
        $validated = $request->validate([
            'disbursement_ids' => 'required|array|min:1',
            'disbursement_ids.*' => 'exists:disbursement_requests,id',
            'payment_date' => 'required|date',
            'bank_account' => 'required|string|max:100',
            'starting_check_number' => 'nullable|string|max:50',
        ]);

        $disbursements = DisbursementRequest::whereIn('id', $validated['disbursement_ids'])
            ->where('status', 'approved')
            ->whereDoesntHave('payment')
            ->oldest('request_date')
            ->get();

        if ($disbursements->isEmpty()) {
            return back()->with('error', 'No valid approved disbursements found for processing.');
        }

        try {
            $results = DB::transaction(function () use ($disbursements, $validated) {
                $batchResults = [];
                $bankAccount = $validated['bank_account'];

                // Track sequential check numbering for manual starting number
                $manualCheckSeq = null;
                if (!empty($validated['starting_check_number'])) {
                    $manualCheckSeq = $validated['starting_check_number'];
                }

                foreach ($disbursements as $disbursement) {
                    $whtRate = 0.02;
                    $wht = round((float) $disbursement->amount * $whtRate, 2);
                    $netAmount = (float) $disbursement->amount - $wht;

                    $voucherNumber = NumberingService::generate('PV');

                    // Generate check/reference number based on payment method
                    $checkNumber = null;
                    $refNumber = $voucherNumber;
                    $method = $disbursement->payment_method ?? 'check';

                    if ($method === 'check') {
                        if ($manualCheckSeq !== null) {
                            $checkNumber = $manualCheckSeq;
                            $manualCheckSeq = $this->incrementCheckNumber($manualCheckSeq);
                        } else {
                            $checkNumber = NumberingService::generate('CHK');
                        }
                        $refNumber = $checkNumber;
                    } elseif ($method === 'bank_transfer') {
                        $refNumber = NumberingService::generate('BT');
                    } elseif ($method === 'online') {
                        $refNumber = NumberingService::generate('OL');
                    }

                    $payment = DisbursementPayment::create([
                        'disbursement_id' => $disbursement->id,
                        'voucher_number' => $voucherNumber,
                        'payment_date' => $validated['payment_date'],
                        'payment_method' => $disbursement->payment_method ?? 'check',
                        'bank_account' => $bankAccount,
                        'check_number' => $checkNumber,
                        'reference_number' => $refNumber,
                        'gross_amount' => $disbursement->amount,
                        'withholding_tax' => $wht,
                        'net_amount' => $netAmount,
                        'status' => 'completed',
                        'created_by' => auth()->id(),
                    ]);

                    $disbursement->update(['status' => 'paid']);

                    // Move budget from committed to actual
                    if ($disbursement->budget_id) {
                        app(BudgetService::class)->recordActual($disbursement->budget_id, (float) $disbursement->amount);
                    }

                    // Post to GL
                    app(PostingService::class)->postDisbursement($payment);

                    app(AuditService::class)->log('payment', 'disbursement', $disbursement, null,
                        "Batch payment processed: {$payment->voucher_number}");

                    $batchResults[] = [
                        'voucher_number' => $voucherNumber,
                        'request_number' => $disbursement->request_number,
                        'payee_name' => $disbursement->payee_name,
                        'payment_method' => $method,
                        'bank_account' => $bankAccount,
                        'check_number' => $checkNumber,
                        'reference_number' => $refNumber,
                        'gross_amount' => (float) $disbursement->amount,
                        'withholding_tax' => $wht,
                        'net_amount' => $netAmount,
                    ];
                }

                return $batchResults;
            });

            return redirect()->route('ap.payment-processing')
                ->with('success', count($results) . ' payment(s) processed successfully.')
                ->with('batch_results', $results);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process batch: ' . $e->getMessage());
        }
    }

    /**
     * Supplier Payments – history of all processed payments.
     */
    public function payments(Request $request)
    {
        $query = DisbursementPayment::with('disbursement.department');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_number', 'ilike', "%{$search}%")
                  ->orWhereHas('disbursement', fn ($d) => $d->where('request_number', 'ilike', "%{$search}%")
                      ->orWhere('payee_name', 'ilike', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $payments = $query->latest('payment_date')->paginate(20)->withQueryString();

        $totalPaid = DisbursementPayment::where('status', 'completed')->sum('net_amount');
        $totalVoided = DisbursementPayment::where('status', 'voided')->sum('net_amount');

        return view('pages.ap.supplier-payments', compact('payments', 'totalPaid', 'totalVoided'));
    }

    public function processPayment(Request $request, DisbursementRequest $disbursement)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,bank_transfer,online',
            'bank_account' => 'nullable|string|max:100',
            'check_number' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'withholding_tax' => 'nullable|numeric|min:0',
        ]);

        if ($disbursement->status !== 'approved') {
            return back()->with('error', 'Only approved disbursements can be paid.');
        }

        try {
            $payment = DB::transaction(function () use ($disbursement, $validated) {
                $wht = $validated['withholding_tax'] ?? 0;
                $netAmount = (float) $disbursement->amount - $wht;

                $voucherNumber = NumberingService::generate('PV');

                // Auto-generate check/reference number if not provided
                $refNumber = $validated['reference_number'] ?? null;
                $checkNumber = $validated['check_number'] ?? null;
                if (empty($refNumber)) {
                    $refNumber = $voucherNumber;
                }
                if ($validated['payment_method'] === 'check' && empty($checkNumber)) {
                    $checkNumber = 'CHK-' . str_pad(DisbursementPayment::where('payment_method', 'check')->count() + 1, 5, '0', STR_PAD_LEFT);
                }

                $payment = DisbursementPayment::create([
                    'disbursement_id' => $disbursement->id,
                    'voucher_number' => $voucherNumber,
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'bank_account' => $validated['bank_account'] ?? null,
                    'check_number' => $checkNumber,
                    'reference_number' => $refNumber,
                    'gross_amount' => $disbursement->amount,
                    'withholding_tax' => $wht,
                    'net_amount' => $netAmount,
                    'status' => 'completed',
                    'created_by' => auth()->id(),
                ]);

                $disbursement->update(['status' => 'paid']);

                // Move budget from committed to actual
                if ($disbursement->budget_id) {
                    app(BudgetService::class)->recordActual($disbursement->budget_id, (float) $disbursement->amount);
                }

                // Post to GL
                app(PostingService::class)->postDisbursement($payment);

                app(AuditService::class)->log('payment', 'disbursement', $disbursement, null,
                    "Payment processed: {$payment->voucher_number}");
                \App\Services\NotificationService::paymentProcessed($payment);

                return $payment;
            });

            return redirect()->route('ap.payments.index')
                ->with('success', "Payment {$payment->voucher_number} processed successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    public function voidPayment(DisbursementPayment $payment)
    {
        if ($payment->status === 'voided') {
            return back()->with('error', 'Payment is already voided.');
        }

        try {
            DB::transaction(function () use ($payment) {
                $payment->update(['status' => 'voided']);
                $payment->disbursement->update(['status' => 'approved']);

                // Reverse budget: move from actual back to committed
                if ($payment->disbursement->budget_id) {
                    $budget = \App\Models\Budget::find($payment->disbursement->budget_id);
                    if ($budget) {
                        $budget->decrement('actual', (float) $payment->gross_amount);
                        $budget->increment('committed', (float) $payment->gross_amount);
                    }
                }

                app(AuditService::class)->log('void', 'disbursement_payment', $payment, null, 'Payment voided');
            });

            return back()->with('success', "Payment {$payment->voucher_number} voided.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to void payment: ' . $e->getMessage());
        }
    }

    /**
     * Increment a check number string (e.g., "CHK-2026-0005" → "CHK-2026-0006", or "12345" → "12346").
     */
    private function incrementCheckNumber(string $checkNumber): string
    {
        if (preg_match('/^(.+?)(\d+)$/', $checkNumber, $m)) {
            $prefix = $m[1];
            $num = $m[2];
            $next = str_pad((int) $num + 1, strlen($num), '0', STR_PAD_LEFT);
            return $prefix . $next;
        }

        return $checkNumber;
    }

    public function printVoucher(DisbursementPayment $payment)
    {
        $payment->load('disbursement.items', 'disbursement.department', 'disbursement.category', 'disbursement.approvals');

        $data = [
            'payment'   => $payment,
            'printedAt' => now()->format('F d, Y h:i A'),
            'printedBy' => auth()->user()->name ?? 'System',
        ];

        $pdf = Pdf::loadView('pages.ap.print-voucher', $data)
            ->setPaper('letter', 'portrait');

        return $pdf->download("PV-{$payment->voucher_number}.pdf");
    }
}
