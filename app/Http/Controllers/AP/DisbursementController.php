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
                    'request_number' => NumberingService::generate('DR'),
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
