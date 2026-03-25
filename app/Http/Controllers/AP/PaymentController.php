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
    public function index()
    {
        $readyForPayment = DisbursementRequest::with('department', 'category')
            ->where('status', 'approved')
            ->whereDoesntHave('payment')
            ->latest('request_date')
            ->get();

        $payments = DisbursementPayment::with('disbursement.department')
            ->latest('payment_date')
            ->paginate(20);

        $totalReadyForPayment = $readyForPayment->sum('amount');

        // Placeholder; replace with real bank accounts when model exists
        $bankAccounts = [];

        return view('pages.ap.payment-processing', compact(
            'readyForPayment', 'payments', 'totalReadyForPayment', 'bankAccounts'
        ));
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

                $payment = DisbursementPayment::create([
                    'disbursement_id' => $disbursement->id,
                    'voucher_number' => NumberingService::generate('PV'),
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'bank_account' => $validated['bank_account'] ?? null,
                    'check_number' => $validated['check_number'] ?? null,
                    'reference_number' => $validated['reference_number'] ?? null,
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
