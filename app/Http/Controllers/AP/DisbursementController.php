<?php

namespace App\Http\Controllers\AP;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\ChartOfAccount;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\DisbursementItem;
use App\Models\DisbursementRequest;
use App\Models\ExpenseCategory;
use App\Models\RecurringDisbursementItem;
use App\Models\RecurringDisbursementTemplate;
use App\Models\Vendor;
use App\Services\AuditService;
use App\Services\BudgetService;
use App\Services\NumberingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DisbursementController extends Controller
{
    public function index(Request $request)
    {
        $query = DisbursementRequest::with('department', 'category', 'payment');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('request_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('request_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('payee_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $disbursements = $query->latest('request_date')->paginate(20);

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $drStats = DisbursementRequest::selectRaw("
            COALESCE(SUM(CASE WHEN status = 'pending_approval' THEN amount END), 0) as total_pending,
            COALESCE(SUM(CASE WHEN status = 'approved' THEN amount END), 0) as total_approved
        ")->first();
        $totalPending = (float) $drStats->total_pending;
        $totalApproved = (float) $drStats->total_approved;

        return view('pages.ap.disbursements.index', compact(
            'disbursements', 'departments', 'totalPending', 'totalApproved'
        ));
    }

    public function create()
    {
        return view('pages.ap.disbursements.create', $this->formData());
    }

    /**
     * Recurring Disbursements page — browse past disbursements to memorize/copy as new.
     */
    public function recurring(Request $request)
    {
        // Only show approved+ disbursements (draft/pending not eligible for memorizing)
        $query = DisbursementRequest::with('department', 'category')
            ->whereIn('status', ['approved', 'paid'])
            ->orderByDesc('created_at')
            ->latest('request_date');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('payee_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $disbursements = $query->paginate(20);

        return view('pages.ap.disbursements.recurring', compact('disbursements'));
    }

    public function storeRecurringDr(Request $request)
    {
        $validated = $request->validate([
            'template_name'       => 'required|string|max:255',
            'frequency'           => 'required|in:monthly,quarterly,semi-annually,annually',
            'start_date'          => 'required|date',
            'end_date'            => 'nullable|date|after:start_date',
            'description'         => 'nullable|string',
            'payee_type'          => 'nullable|string',
            'payee_id'            => 'nullable|integer',
            'payee_name'          => 'nullable|string|max:255',
            'department_id'       => 'nullable|exists:departments,id',
            'category_id'         => 'nullable|exists:expense_categories,id',
            'cost_center_id'      => 'nullable|exists:cost_centers,id',
            'project'             => 'nullable|string|max:255',
            'budget_id'           => 'nullable|exists:budgets,id',
            'payment_method'      => 'nullable|string|max:50',
            'amount'              => 'required|numeric|min:0.01',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0',
            'items.*.unit_cost'   => 'required|numeric|min:0',
            'items.*.amount'      => 'required|numeric|min:0',
            'items.*.account_id'  => 'nullable|exists:chart_of_accounts,id',
        ]);

        DB::transaction(function () use ($validated) {
            $template = RecurringDisbursementTemplate::create([
                'template_name'  => $validated['template_name'],
                'frequency'      => $validated['frequency'],
                'start_date'     => $validated['start_date'],
                'end_date'       => $validated['end_date'] ?? null,
                'description'    => $validated['description'] ?? null,
                'payee_type'     => $validated['payee_type'] ?? null,
                'payee_id'       => $validated['payee_id'] ?? null,
                'payee_name'     => $validated['payee_name'] ?? null,
                'department_id'  => $validated['department_id'] ?? null,
                'category_id'    => $validated['category_id'] ?? null,
                'cost_center_id' => $validated['cost_center_id'] ?? null,
                'project'        => $validated['project'] ?? null,
                'budget_id'      => $validated['budget_id'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'amount'         => $validated['amount'],
                'auto_create'    => false,
                'is_active'      => true,
            ]);

            foreach ($validated['items'] as $item) {
                RecurringDisbursementItem::create([
                    'template_id' => $template->id,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_cost'   => $item['unit_cost'],
                    'amount'      => $item['amount'],
                    'account_id'  => $item['account_id'] ?? null,
                    'remarks'     => $item['remarks'] ?? null,
                ]);
            }
        });

        return redirect()->route('ap.disbursements.recurring')
            ->with('success', 'Recurring disbursement template saved.');
    }

    public function generateRecurringDr(RecurringDisbursementTemplate $template)
    {
        if (!$template->is_active) {
            return back()->with('error', 'Template is inactive.');
        }

        $dr = DB::transaction(function () use ($template) {
            $template->load('items.account');

            $dr = DisbursementRequest::create([
                'request_number' => NumberingService::generate('DR', 'disbursement_requests', 'request_number'),
                'request_date'   => now()->toDateString(),
                'due_date'       => null,
                'payee_type'     => $template->payee_type,
                'payee_id'       => $template->payee_id,
                'payee_name'     => $template->payee_name,
                'description'    => $template->description ?? "Generated from: {$template->template_name}",
                'amount'         => $template->amount,
                'department_id'  => $template->department_id,
                'category_id'    => $template->category_id,
                'cost_center_id' => $template->cost_center_id,
                'project'        => $template->project,
                'budget_id'      => $template->budget_id,
                'payment_method' => $template->payment_method,
                'status'         => 'draft',
                'requested_by'   => auth()->id(),
            ]);

            foreach ($template->items as $item) {
                DisbursementItem::create([
                    'disbursement_id' => $dr->id,
                    'description'     => $item->description,
                    'quantity'        => $item->quantity,
                    'unit_cost'       => $item->unit_cost,
                    'amount'          => $item->amount,
                    'account_id'      => $item->account_id,
                    'account_code'    => $item->account_code ?? ($item->account ? $item->account->account_code : null),
                    'tax_code_id'     => $item->tax_code_id,
                    'tax_code'        => $item->tax_code,
                    'remarks'         => $item->remarks,
                ]);
            }

            $template->update(['last_generated_date' => now()]);

            app(AuditService::class)->log('create', 'disbursement_request', $dr, null,
                "Generated from recurring template: {$template->template_name}");

            return $dr;
        });

        return back()->with('success', "Disbursement {$dr->request_number} generated from template \"{$template->template_name}\".");
    }

    public function updateRecurringDr(Request $request, RecurringDisbursementTemplate $template)
    {
        $validated = $request->validate([
            'is_active'   => 'sometimes|boolean',
            'auto_create' => 'sometimes|boolean',
        ]);

        $template->update($validated);

        return back()->with('success', 'Template updated.');
    }

    /**
     * Memorize — copy an existing disbursement as a new draft.
     * Preserves all fields: payee, department, items, tax codes, remarks, attachments, description.
     */
    public function memorizeDisbursement(DisbursementRequest $disbursement)
    {
        $disbursement->load('items');

        $newDr = DB::transaction(function () use ($disbursement) {
            $dr = DisbursementRequest::create([
                'request_number' => \App\Services\NumberingService::generate('DR', 'disbursement_requests', 'request_number'),
                'request_date' => now(),
                'due_date' => $disbursement->due_date,
                'payee_type' => $disbursement->payee_type,
                'payee_id' => $disbursement->payee_id,
                'payee_name' => $disbursement->payee_name,
                'description' => $disbursement->description,
                'amount' => $disbursement->amount,
                'department_id' => $disbursement->department_id,
                'category_id' => $disbursement->category_id,
                'cost_center_id' => $disbursement->cost_center_id,
                'project' => $disbursement->project,
                'budget_id' => $disbursement->budget_id,
                'payment_method' => $disbursement->payment_method,
                'attachments' => $disbursement->attachments, // carry over attachment references
                'status' => 'draft',
                'requested_by' => auth()->id(),
            ]);

            foreach ($disbursement->items as $item) {
                \App\Models\DisbursementItem::create([
                    'disbursement_id' => $dr->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity ?? 1,
                    'unit_cost' => $item->unit_cost ?? $item->amount,
                    'amount' => $item->amount,
                    'account_id' => $item->account_id,
                    'account_code' => $item->account_code,
                    'tax_code_id' => $item->tax_code_id,
                    'tax_code' => $item->tax_code,
                    'remarks' => $item->remarks,
                ]);
            }

            return $dr;
        });

        return redirect()->route('ap.disbursements.show', $newDr)
            ->with('success', "Memorized {$disbursement->request_number} as new draft {$newDr->request_number}. Click Edit if you need to modify any details.");
    }

    public function edit(DisbursementRequest $disbursement)
    {
        if ($disbursement->status !== 'draft') {
            return redirect()->route('ap.disbursements.show', $disbursement)
                ->with('error', 'Only draft requests can be edited.');
        }

        $disbursement->load('items');

        return view('pages.ap.disbursements.create', array_merge(
            $this->formData(),
            ['disbursement' => $disbursement]
        ));
    }

    private function formData(): array
    {
        return Cache::remember('disbursement:form_data', 300, function () {
            $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
            $departments = Department::where('is_active', true)->orderBy('name')->get();
            $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
            $costCenters = CostCenter::where('is_active', true)->get();
            $accounts = ChartOfAccount::active()->postable()->where('account_type', 'expense')
                ->orderBy('account_code')->get();
            $budgets = Budget::with('department', 'category')
                ->where('status', 'active')
                ->get()
                ->map(function ($b) {
                    $b->remaining = $b->annual_budget - $b->committed - $b->actual;
                    return $b;
                });

            return compact('vendors', 'departments', 'categories', 'costCenters', 'accounts', 'budgets');
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payee_type' => 'required|in:vendor,employee,other',
            'payee_id' => 'nullable|integer',
            'payee_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'nullable|exists:expense_categories,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'project' => 'nullable|string|max:100',
            'payment_method' => 'nullable|in:cash,check,bank_transfer,online',
            'description' => 'required|string',
            'budget_id' => 'nullable|exists:budgets,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.account_code' => 'nullable|string|max:20',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        $totalAmount = collect($validated['items'])->sum('amount');

        // Budget check
        if (!empty($validated['budget_id'])) {
            $budgetCheck = app(BudgetService::class)->checkBudget($validated['budget_id'], $totalAmount);
            if ($budgetCheck['isOverBudget']) {
                return back()->withInput()->with('error', "Budget exceeded. Remaining: PHP " . number_format($budgetCheck['remaining'], 2));
            }
        }

        try {
            $disbursement = DB::transaction(function () use ($validated, $totalAmount) {
                $disbursement = DisbursementRequest::create([
                    'request_number' => NumberingService::generate('DR', 'disbursement_requests', 'request_number'),
                    'request_date' => $validated['request_date'],
                    'due_date' => $validated['due_date'] ?? null,
                    'payee_type' => $validated['payee_type'],
                    'payee_id' => $validated['payee_id'] ?? null,
                    'payee_name' => $validated['payee_name'],
                    'department_id' => $validated['department_id'],
                    'category_id' => $validated['category_id'] ?? null,
                    'cost_center_id' => $validated['cost_center_id'] ?? null,
                    'project' => $validated['project'] ?? null,
                    'amount' => $totalAmount,
                    'payment_method' => $validated['payment_method'],
                    'description' => $validated['description'],
                    'budget_id' => $validated['budget_id'] ?? null,
                    'status' => 'draft',
                    'requested_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    DisbursementItem::create([
                        'disbursement_id' => $disbursement->id,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'amount' => $item['amount'],
                        'account_code' => $item['account_code'] ?? null,
                    ]);
                }

                // Store attachments (skip on Vercel — read-only filesystem)
                if (isset($validated['attachments']) && is_writable(storage_path('app/public'))) {
                    $attachmentPaths = [];
                    foreach ($validated['attachments'] as $file) {
                        $path = $file->store("disbursements/{$disbursement->id}", 'public');
                        $attachmentPaths[] = [
                            'filename' => $file->getClientOriginalName(),
                            'path' => $path,
                            'size' => $file->getSize(),
                        ];
                    }
                    $disbursement->update(['attachments' => json_encode($attachmentPaths)]);
                }

                app(AuditService::class)->log('create', 'disbursement', $disbursement, null, 'Disbursement request created');

                return $disbursement;
            });

            return redirect()->route('ap.disbursements.show', $disbursement)
                ->with('success', 'Disbursement request created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create disbursement: ' . $e->getMessage());
        }
    }

    public function update(Request $request, DisbursementRequest $disbursement)
    {
        if ($disbursement->status !== 'draft') {
            return back()->with('error', 'Only draft requests can be edited.');
        }

        $validated = $request->validate([
            'request_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payee_type' => 'required|in:vendor,employee,other',
            'payee_id' => 'nullable|integer',
            'payee_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'nullable|exists:expense_categories,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'project' => 'nullable|string|max:100',
            'payment_method' => 'nullable|in:cash,check,bank_transfer,online',
            'description' => 'required|string',
            'budget_id' => 'nullable|exists:budgets,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.account_code' => 'nullable|string|max:20',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'remove_attachments' => 'nullable|array',
        ]);

        $totalAmount = collect($validated['items'])->sum('amount');

        try {
            DB::transaction(function () use ($validated, $totalAmount, $disbursement, $request) {
                $oldValues = $disbursement->toArray();

                $disbursement->update([
                    'request_date' => $validated['request_date'],
                    'due_date' => $validated['due_date'] ?? null,
                    'payee_type' => $validated['payee_type'],
                    'payee_id' => $validated['payee_id'] ?? null,
                    'payee_name' => $validated['payee_name'],
                    'department_id' => $validated['department_id'],
                    'category_id' => $validated['category_id'] ?? null,
                    'cost_center_id' => $validated['cost_center_id'] ?? null,
                    'project' => $validated['project'] ?? null,
                    'amount' => $totalAmount,
                    'payment_method' => $validated['payment_method'],
                    'description' => $validated['description'],
                    'budget_id' => $validated['budget_id'] ?? null,
                ]);

                // Replace line items
                $disbursement->items()->delete();
                foreach ($validated['items'] as $item) {
                    DisbursementItem::create([
                        'disbursement_id' => $disbursement->id,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'amount' => $item['amount'],
                        'account_code' => $item['account_code'] ?? null,
                        'tax_code' => $item['tax_code'] ?? null,
                        'remarks' => $item['remarks'] ?? null,
                    ]);
                }

                // Handle attachments
                $existing = is_array($disbursement->attachments) ? $disbursement->attachments
                    : (json_decode($disbursement->attachments ?? '[]', true) ?: []);

                // Remove selected
                if (!empty($validated['remove_attachments'])) {
                    $existing = array_values(array_diff_key($existing, array_flip($validated['remove_attachments'])));
                }

                // Add new uploads
                if (isset($validated['attachments']) && is_writable(storage_path('app/public'))) {
                    foreach ($validated['attachments'] as $file) {
                        $path = $file->store("disbursements/{$disbursement->id}", 'public');
                        $existing[] = [
                            'filename' => $file->getClientOriginalName(),
                            'path' => $path,
                            'size' => $file->getSize(),
                        ];
                    }
                }

                $disbursement->update(['attachments' => json_encode($existing)]);

                app(AuditService::class)->log('update', 'disbursement', $disbursement, $oldValues, 'Disbursement request updated');
            });

            return redirect()->route('ap.disbursements.show', $disbursement)
                ->with('success', 'Disbursement request updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update disbursement: ' . $e->getMessage());
        }
    }

    public function show(DisbursementRequest $disbursement)
    {
        $disbursement->load([
            'department', 'category', 'costCenter',
            'items', 'approvals', 'payment',
        ]);

        $budgetInfo = null;
        if ($disbursement->budget_id) {
            $budget = Budget::with('department', 'category')->find($disbursement->budget_id);
            if ($budget) {
                $budgetInfo = [
                    'budget' => $budget,
                    'remaining' => $budget->annual_budget - $budget->committed - $budget->actual,
                    'utilization' => $budget->annual_budget > 0
                        ? (($budget->committed + $budget->actual) / $budget->annual_budget) * 100
                        : 0,
                ];
            }
        }

        return view('pages.ap.disbursements.show', compact('disbursement', 'budgetInfo'));
    }

    public function export()
    {
        (new \App\Services\AuditService)->logActivity('exported', 'disbursement', 'Exported disbursements');
        $disbursements = DisbursementRequest::with('department', 'category')
            ->latest('request_date')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="disbursements-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($disbursements) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Request Number', 'Date', 'Payee', 'Department',
                'Category', 'Amount', 'Payment Method', 'Status',
            ]);

            foreach ($disbursements as $d) {
                fputcsv($file, [
                    $d->request_number,
                    $d->request_date,
                    $d->payee_name,
                    $d->department ? $d->department->name : '',
                    $d->category ? $d->category->name : '',
                    $d->amount,
                    $d->payment_method,
                    $d->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function submit(DisbursementRequest $disbursement)
    {
        if ($disbursement->status !== 'draft') {
            return back()->with('error', 'Only draft requests can be submitted.');
        }

        // Commit budget if applicable
        if ($disbursement->budget_id) {
            app(BudgetService::class)->commitBudget($disbursement->budget_id, (float) $disbursement->amount);
        }

        $disbursement->update(['status' => 'pending_approval']);

        app(AuditService::class)->log('submit', 'disbursement', $disbursement, null, 'Submitted for approval');
        \App\Services\NotificationService::disbursementSubmitted($disbursement);

        return back()->with('success', 'Disbursement request submitted for approval.');
    }
}
