<?php

namespace App\Http\Controllers\AP;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\DisbursementApproval;
use App\Models\DisbursementRequest;
use App\Services\AuditService;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index()
    {
        $pendingApprovals = DisbursementRequest::with(['department', 'category', 'items'])
            ->where('status', 'pending')
            ->latest('request_date')
            ->paginate(20);

        // Attach budget utilization info
        $pendingApprovals->getCollection()->transform(function ($dr) {
            if ($dr->budget_id) {
                $budget = Budget::find($dr->budget_id);
                if ($budget) {
                    $dr->budget_info = [
                        'name' => $budget->budget_name,
                        'annual' => $budget->annual_budget,
                        'committed' => $budget->committed,
                        'actual' => $budget->actual,
                        'remaining' => $budget->annual_budget - $budget->committed - $budget->actual,
                        'utilization' => $budget->annual_budget > 0
                            ? round((($budget->committed + $budget->actual) / $budget->annual_budget) * 100, 1)
                            : 0,
                    ];
                }
            }
            return $dr;
        });

        $totalPendingAmount = DisbursementRequest::where('status', 'pending')->sum('amount');

        return view('pages.ap.approval-queue', compact('pendingApprovals', 'totalPendingAmount'));
    }

    public function approve(Request $request, DisbursementRequest $disbursement)
    {
        $validated = $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        if ($disbursement->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        DB::transaction(function () use ($disbursement, $validated) {
            DisbursementApproval::create([
                'disbursement_id' => $disbursement->id,
                'approver_role' => auth()->user()->role ?? 'approver',
                'approver_name' => auth()->user()->name,
                'action' => 'approved',
                'comments' => $validated['comments'] ?? null,
                'acted_at' => now(),
            ]);

            $disbursement->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            app(AuditService::class)->log('approve', 'disbursement', $disbursement, null, 'Disbursement approved');
        });

        return back()->with('success', "Disbursement {$disbursement->request_number} approved.");
    }

    public function reject(Request $request, DisbursementRequest $disbursement)
    {
        $validated = $request->validate([
            'comments' => 'required|string|max:1000',
        ]);

        if ($disbursement->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        DB::transaction(function () use ($disbursement, $validated) {
            DisbursementApproval::create([
                'disbursement_id' => $disbursement->id,
                'approver_role' => auth()->user()->role ?? 'approver',
                'approver_name' => auth()->user()->name,
                'action' => 'rejected',
                'comments' => $validated['comments'],
                'acted_at' => now(),
            ]);

            $disbursement->update(['status' => 'rejected']);

            // Release committed budget
            if ($disbursement->budget_id) {
                app(BudgetService::class)->releaseCommitment($disbursement->budget_id, (float) $disbursement->amount);
            }

            app(AuditService::class)->log('reject', 'disbursement', $disbursement, null, "Rejected: {$validated['comments']}");
        });

        return back()->with('success', "Disbursement {$disbursement->request_number} rejected.");
    }

    public function returnForRevision(Request $request, DisbursementRequest $disbursement)
    {
        $validated = $request->validate([
            'comments' => 'required|string|max:1000',
        ]);

        if ($disbursement->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be returned for revision.');
        }

        DB::transaction(function () use ($disbursement, $validated) {
            DisbursementApproval::create([
                'disbursement_id' => $disbursement->id,
                'approver_role' => auth()->user()->role ?? 'approver',
                'approver_name' => auth()->user()->name,
                'action' => 'returned',
                'comments' => $validated['comments'],
                'acted_at' => now(),
            ]);

            $disbursement->update(['status' => 'returned']);

            // Release committed budget
            if ($disbursement->budget_id) {
                app(BudgetService::class)->releaseCommitment($disbursement->budget_id, (float) $disbursement->amount);
            }

            app(AuditService::class)->log('return', 'disbursement', $disbursement, null, "Returned: {$validated['comments']}");
        });

        return back()->with('success', "Disbursement {$disbursement->request_number} returned for revision.");
    }
}
