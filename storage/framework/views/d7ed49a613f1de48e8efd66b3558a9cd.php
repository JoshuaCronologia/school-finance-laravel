
<?php $__env->startSection('title', 'Budget Allocation'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Budget Allocation Table','subtitle' => 'Monthly budget distribution']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Budget Allocation Table','subtitle' => 'Monthly budget distribution']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(route('budget.allocation.export') ?? '#'); ?>" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Export to Excel
        </a>
     <?php $__env->endSlot(); ?>
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

<?php if(session('success')): ?>
    <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => ['type' => 'success','message' => session('success'),'class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('success')),'class' => 'mb-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $attributes = $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $component = $__componentOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php endif; ?>


<div class="flex items-center gap-2 text-sm text-secondary-500 mb-4">
    <svg class="w-4 h-4 flex-shrink-0 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
    <span>Click on any monthly cell to edit the allocation amount. Changes are saved automatically.</span>
</div>

<?php
    $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $grandTotalAnnual = 0;
    $grandTotalMonthly = array_fill(1, 12, 0);
?>

<div class="card">
    <div class="card-header"><h3 class="card-title">Monthly Allocation Spreadsheet</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table text-sm" id="allocation-table">
            <thead>
                <tr>
                    <th class="sticky left-0 bg-white z-10 min-w-[160px]">Department</th>
                    <th class="min-w-[140px]">Category</th>
                    <th class="text-right min-w-[120px]">Annual Budget</th>
                    <?php $__currentLoopData = $monthNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th class="text-right min-w-[100px]"><?php echo e($m); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $budgets ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $budget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $allocations = $budget->allocations ? $budget->allocations->keyBy('month') : collect();
                    $totalAllocated = $allocations->sum('amount');
                    $mismatch = abs($totalAllocated - $budget->annual_budget) > 0.01;
                    $grandTotalAnnual += $budget->annual_budget;
                    for ($m = 1; $m <= 12; $m++) {
                        $grandTotalMonthly[$m] += $allocations[$m]->amount ?? 0;
                    }
                ?>
                <tr>
                    <td class="sticky left-0 bg-white font-medium"><?php echo e($budget->department->name ?? '-'); ?></td>
                    <td><?php echo e($budget->category->name ?? $budget->budget_name); ?></td>
                    <td class="text-right font-medium"><?php echo e('₱' . number_format($budget->annual_budget, 2)); ?></td>
                    <?php for($m = 1; $m <= 12; $m++): ?>
                    <td class="text-right p-0">
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="allocation-cell w-full text-right text-sm border-0 bg-transparent px-3 py-2 focus:bg-primary-50 focus:ring-2 focus:ring-primary-300 focus:outline-none rounded transition-colors"
                            value="<?php echo e(isset($allocations[$m]) ? number_format($allocations[$m]->amount, 2, '.', '') : '0.00'); ?>"
                            data-budget-id="<?php echo e($budget->id); ?>"
                            data-month="<?php echo e($m); ?>"
                            data-original="<?php echo e(isset($allocations[$m]) ? number_format($allocations[$m]->amount, 2, '.', '') : '0.00'); ?>"
                        >
                    </td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="<?php echo e(3 + 12); ?>" class="text-center text-secondary-400 py-8">No budgets to allocate. Create budgets first in Budget Planning.</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if(count($budgets ?? []) > 0): ?>
            <tfoot>
                <tr class="bg-gray-50 font-bold">
                    <td class="sticky left-0 bg-gray-50">TOTALS</td>
                    <td></td>
                    <td class="text-right"><?php echo e('₱' . number_format($grandTotalAnnual, 2)); ?></td>
                    <?php for($m = 1; $m <= 12; $m++): ?>
                    <td class="text-right" id="month-total-<?php echo e($m); ?>"><?php echo e('₱' . number_format($grandTotalMonthly[$m], 2)); ?></td>
                    <?php endfor; ?>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>


<?php if(count($budgets ?? []) > 0): ?>
<div id="mismatch-warnings" class="mt-4 space-y-1">
    <?php $__currentLoopData = $budgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $budget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $allocations = $budget->allocations ? $budget->allocations->keyBy('month') : collect();
        $totalAllocated = $allocations->sum('amount');
        $mismatch = abs($totalAllocated - $budget->annual_budget) > 0.01;
    ?>
    <?php if($mismatch): ?>
    <p class="text-sm text-danger-600">
        <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
        <?php echo e($budget->budget_name); ?>: Allocated ₱<?php echo e(number_format($totalAllocated, 2)); ?> does not match annual budget ₱<?php echo e(number_format($budget->annual_budget, 2)); ?>

    </p>
    <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('.allocation-cell').forEach(function(input) {
        // Save on blur
        input.addEventListener('blur', function() {
            saveAllocation(this);
        });

        // Save on Enter key
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.blur();
            }
            // Tab navigates naturally
        });

        // Select all text on focus for easy editing
        input.addEventListener('focus', function() {
            this.select();
        });
    });

    function saveAllocation(input) {
        const budgetId = input.dataset.budgetId;
        const month = input.dataset.month;
        const originalValue = input.dataset.original;
        const newValue = parseFloat(input.value) || 0;

        // Skip if value has not changed
        if (parseFloat(originalValue) === newValue) {
            return;
        }

        // Visual loading state
        input.classList.add('opacity-50');

        fetch('<?php echo e(route("budget.allocation.update")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                budget_id: budgetId,
                month: month,
                amount: newValue
            })
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Save failed');
            return response.json();
        })
        .then(function(data) {
            // Update the stored original value
            input.dataset.original = newValue.toFixed(2);
            input.value = newValue.toFixed(2);

            // Flash green to confirm save
            input.classList.remove('opacity-50');
            input.classList.add('bg-success-50');
            setTimeout(function() {
                input.classList.remove('bg-success-50');
            }, 1000);

            // Recalculate column totals
            recalculateTotals();
        })
        .catch(function(error) {
            // Revert to original value on error
            input.value = originalValue;
            input.classList.remove('opacity-50');
            input.classList.add('bg-danger-50');
            setTimeout(function() {
                input.classList.remove('bg-danger-50');
            }, 2000);
            console.error('Failed to save allocation:', error);
        });
    }

    function recalculateTotals() {
        for (var m = 1; m <= 12; m++) {
            var total = 0;
            document.querySelectorAll('.allocation-cell[data-month="' + m + '"]').forEach(function(input) {
                total += parseFloat(input.value) || 0;
            });
            var totalCell = document.getElementById('month-total-' + m);
            if (totalCell) {
                totalCell.textContent = '\u20B1' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .allocation-cell::-webkit-inner-spin-button,
    .allocation-cell::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .allocation-cell[type=number] {
        -moz-appearance: textfield;
    }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/budget/allocation.blade.php ENDPATH**/ ?>