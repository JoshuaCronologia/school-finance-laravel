<?php $__env->startSection('title', 'Budget Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Budget Dashboard','subtitle' => 'Budget monitoring and analysis']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Budget Dashboard','subtitle' => 'Budget monitoring and analysis']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>


<div class="card mb-6">
    <div class="card-body">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-input w-64" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        <?php $__currentLoopData = $departments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($dept->id); ?>" <?php echo e(request('department_id') == $dept->id ? 'selected' : ''); ?>><?php echo e($dept->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php if(request('department_id')): ?>
                    <a href="<?php echo e(request()->url()); ?>" class="btn-secondary">Clear Filter</a>
                <?php endif; ?>
            </form>
            <a href="<?php echo e(route('budget.budget-vs-actual.pdf', ['department_id' => request('department_id')])); ?>" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                Budget vs Actual PDF
            </a>
        </div>
    </div>
</div>

<?php
    $incomeVariance = ($incomeBudget ?? 0) > 0 ? $totalIncome - $incomeBudget : $totalIncome;
    $incomeVariancePct = ($incomeBudget ?? 0) > 0 ? round(($incomeVariance / $incomeBudget) * 100, 2) : 0;
    $netVariance = $netIncome - ($incomeBudget ?? 0);
    $netVariancePct = ($incomeBudget ?? 0) > 0 ? round(($netVariance / $incomeBudget) * 100, 2) : 0;
?>


<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    
    <div class="card">
        <div class="card-header"><h3 class="card-title">Budget and Variance Year-To-Date</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 font-medium text-secondary-600">As of <?php echo e(now()->format('F j, Y')); ?></th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Actual</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Budget</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Variance</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">%</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="px-4 py-2.5">+ Income</td>
                        <td class="text-right px-3 py-2.5 font-mono"><?php echo e(number_format($totalIncome ?? 0, 2)); ?></td>
                        <td class="text-right px-3 py-2.5 font-mono"><?php echo e(number_format($incomeBudget ?? 0, 2)); ?></td>
                        <td class="text-right px-3 py-2.5 font-mono <?php echo e($incomeVariance < 0 ? 'text-danger-600' : 'text-success-600'); ?>"><?php echo e(number_format($incomeVariance, 2)); ?></td>
                        <td class="text-right px-3 py-2.5 font-mono <?php echo e($incomeVariancePct < 0 ? 'text-danger-600' : ''); ?>"><?php echo e($incomeVariancePct); ?></td>
                    </tr>
                    <tr class="border-b">
                        <td class="px-4 py-2.5">- Expenses</td>
                        <td class="text-right px-3 py-2.5 font-mono"><?php echo e(number_format($totalExpenses ?? 0, 2)); ?></td>
                        <td class="text-right px-3 py-2.5 font-mono">0.00</td>
                        <td class="text-right px-3 py-2.5 font-mono text-success-600"><?php echo e(number_format($totalExpenses ?? 0, 2)); ?></td>
                        <td class="text-right px-3 py-2.5">-</td>
                    </tr>
                    <tr class="border-b bg-gray-50 font-semibold">
                        <td class="px-4 py-2.5">Gross Profit</td>
                        <td class="text-right px-3 py-2.5 font-mono"><?php echo e(number_format($grossProfit ?? 0, 2)); ?></td>
                        <td class="text-right px-3 py-2.5 font-mono"><?php echo e(number_format($incomeBudget ?? 0, 2)); ?></td>
                        <td class="text-right px-3 py-2.5 font-mono <?php echo e($incomeVariance < 0 ? 'text-danger-600' : 'text-success-600'); ?>"><?php echo e(number_format($incomeVariance, 2)); ?></td>
                        <td class="text-right px-3 py-2.5 font-mono <?php echo e($incomeVariancePct < 0 ? 'text-danger-600' : ''); ?>"><?php echo e($incomeVariancePct); ?></td>
                    </tr>
                    <tr class="border-b font-bold text-lg bg-white">
                        <td class="px-4 py-3">Net Income</td>
                        <td class="text-right px-3 py-3 font-mono"><?php echo e(number_format($netIncome ?? 0, 2)); ?></td>
                        <td class="text-right px-3 py-3 font-mono"><?php echo e(number_format($incomeBudget ?? 0, 2)); ?></td>
                        <td class="text-right px-3 py-3 font-mono <?php echo e($netVariance < 0 ? 'text-danger-600' : 'text-success-600'); ?>"><?php echo e(number_format($netVariance, 2)); ?></td>
                        <td class="text-right px-3 py-3 font-mono <?php echo e($netVariancePct < 0 ? 'text-danger-600' : ''); ?>"><?php echo e($netVariancePct); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header"><h3 class="card-title">Expenses Year-To-Date</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 font-medium text-secondary-600">As of <?php echo e(now()->format('F j, Y')); ?></th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Actual</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Budget</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Variance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $expenseAccounts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2"><?php echo e($exp->account_name); ?></td>
                        <td class="text-right px-3 py-2 font-mono"><?php echo e(number_format($exp->actual, 2)); ?></td>
                        <td class="text-right px-3 py-2 font-mono text-secondary-400">0.00</td>
                        <td class="text-right px-3 py-2 font-mono <?php echo e($exp->actual > 0 ? 'text-success-600' : 'text-danger-600'); ?>"><?php echo e(number_format($exp->actual, 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="text-center py-4 text-secondary-400">No expenses recorded.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50 font-semibold border-t">
                    <tr>
                        <td class="px-4 py-2.5">Total Expenses</td>
                        <td class="text-right px-3 py-2.5 font-mono"><?php echo e(number_format($totalExpenses ?? 0, 2)); ?></td>
                        <td class="text-right px-3 py-2.5 font-mono">0.00</td>
                        <td class="text-right px-3 py-2.5 font-mono text-success-600"><?php echo e(number_format($totalExpenses ?? 0, 2)); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Income Year-To-Date</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 font-medium text-secondary-600">As of <?php echo e(now()->format('F j, Y')); ?></th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Actual</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Budget</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Variance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $incomeAccounts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2"><?php echo e($inc->account_name); ?></td>
                        <td class="text-right px-3 py-2 font-mono"><?php echo e(number_format($inc->actual, 2)); ?></td>
                        <td class="text-right px-3 py-2 font-mono text-secondary-400">0.00</td>
                        <td class="text-right px-3 py-2 font-mono <?php echo e($inc->actual > 0 ? 'text-success-600' : 'text-danger-600'); ?>"><?php echo e(number_format($inc->actual, 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="text-center py-4 text-secondary-400">No income recorded.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50 font-semibold border-t">
                    <tr>
                        <td class="px-4 py-2.5">Total Income</td>
                        <td class="text-right px-3 py-2.5 font-mono"><?php echo e(number_format($totalIncome ?? 0, 2)); ?></td>
                        <td class="text-right px-3 py-2.5 font-mono"><?php echo e(number_format($incomeBudget ?? 0, 2)); ?></td>
                        <td class="text-right px-3 py-2.5 font-mono <?php echo e($incomeVariance < 0 ? 'text-danger-600' : 'text-success-600'); ?>"><?php echo e(number_format($incomeVariance, 2)); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="card p-4">
                <p class="text-xs font-medium text-secondary-500 uppercase">Total Budget</p>
                <p class="text-xl font-bold text-secondary-900 mt-1"><?php echo '&#8369;' . number_format($totalBudget, 2); ?></p>
            </div>
            <div class="card p-4">
                <p class="text-xs font-medium text-secondary-500 uppercase">Committed</p>
                <p class="text-xl font-bold text-warning-600 mt-1"><?php echo '&#8369;' . number_format($totalCommitted, 2); ?></p>
            </div>
            <div class="card p-4">
                <p class="text-xs font-medium text-secondary-500 uppercase">Actual Spent</p>
                <p class="text-xl font-bold text-danger-600 mt-1"><?php echo '&#8369;' . number_format($totalActual, 2); ?></p>
            </div>
            <div class="card p-4">
                <p class="text-xs font-medium text-secondary-500 uppercase">Remaining</p>
                <p class="text-xl font-bold text-success-600 mt-1"><?php echo '&#8369;' . number_format($totalRemaining, 2); ?></p>
                <p class="text-xs text-secondary-400 mt-0.5">Utilization: <?php echo e(number_format($utilizationRate, 1)); ?>%</p>
            </div>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-secondary-500 uppercase">Net Income</p>
            <p class="text-2xl font-bold <?php echo e(($netIncome ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600'); ?> mt-1"><?php echo '&#8369;' . number_format($netIncome ?? 0, 2); ?></p>
            <p class="text-xs text-secondary-400 mt-0.5">Income: <?php echo '&#8369;' . number_format($totalIncome ?? 0, 2); ?> — Expenses: <?php echo '&#8369;' . number_format($totalExpenses ?? 0, 2); ?></p>
        </div>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6" data-vue-root>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Budget by Department</h3></div>
        <div class="card-body">
            <div style="min-height: 320px;">
                <bar-chart :labels='<?php echo json_encode($deptLabels, 15, 512) ?>' :datasets='<?php echo json_encode($deptDatasets, 15, 512) ?>' :currency="true"></bar-chart>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Budget Utilization</h3></div>
        <div class="card-body flex flex-col items-center justify-center">
            <div style="max-width: 320px; width: 100%;">
                <doughnut-chart :labels='<?php echo json_encode($utilizationLabels, 15, 512) ?>' :data='<?php echo json_encode($utilizationValues, 15, 512) ?>' :currency="true"></doughnut-chart>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-6 text-center text-sm">
                <div><div class="font-semibold text-primary-600"><?php echo '&#8369;' . number_format($totalActual, 2); ?></div><div class="text-secondary-400">Actual</div></div>
                <div><div class="font-semibold text-warning-600"><?php echo '&#8369;' . number_format($totalCommitted, 2); ?></div><div class="text-secondary-400">Committed</div></div>
                <div><div class="font-semibold text-success-600"><?php echo '&#8369;' . number_format($totalRemaining, 2); ?></div><div class="text-secondary-400">Remaining</div></div>
            </div>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header"><h3 class="card-title">All Budgets</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Budget Name</th>
                    <th>Department</th>
                    <th>Category</th>
                    <th class="text-right">Annual Budget</th>
                    <th class="text-right">Committed</th>
                    <th class="text-right">Actual</th>
                    <th class="text-right">Remaining</th>
                    <th class="text-right">Variance</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $budgets ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $budget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $pct = $budget->annual_budget > 0 ? round(($budget->actual / $budget->annual_budget) * 100, 1) : 0;
                    $variance = $budget->annual_budget - $budget->actual;
                ?>
                <tr>
                    <td class="font-medium"><?php echo e($budget->budget_name); ?></td>
                    <td><?php echo e($budget->department->name ?? '-'); ?></td>
                    <td><?php echo e($budget->category->name ?? '-'); ?></td>
                    <td class="text-right font-mono"><?php echo '&#8369;' . number_format($budget->annual_budget, 2); ?></td>
                    <td class="text-right font-mono"><?php echo '&#8369;' . number_format($budget->committed ?? 0, 2); ?></td>
                    <td class="text-right font-mono"><?php echo '&#8369;' . number_format($budget->actual, 2); ?></td>
                    <td class="text-right font-mono <?php echo e(($budget->remaining ?? 0) < 0 ? 'text-danger-600' : ''); ?>"><?php echo '&#8369;' . number_format($budget->remaining ?? 0, 2); ?></td>
                    <td class="text-right font-mono <?php echo e($variance < 0 ? 'text-danger-600' : 'text-success-600'); ?>"><?php echo '&#8369;' . number_format($variance, 2); ?></td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="<?php echo e($pct > 90 ? 'bg-danger-500' : ($pct > 70 ? 'bg-warning-500' : 'bg-primary-500')); ?> h-2 rounded-full" style="width: <?php echo e(min($pct, 100)); ?>%"></div>
                            </div>
                            <span class="text-xs font-medium <?php echo e($pct > 100 ? 'text-danger-600' : ''); ?>"><?php echo e($pct); ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="9" class="text-center text-secondary-400 py-8">No budgets found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/budget/dashboard.blade.php ENDPATH**/ ?>