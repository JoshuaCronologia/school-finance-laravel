<?php $__env->startSection('title', 'BIR 1604-E'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'BIR 1604-E','subtitle' => 'Annual Information Return of Creditable Income Taxes Withheld (Expanded)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'BIR 1604-E','subtitle' => 'Annual Information Return of Creditable Income Taxes Withheld (Expanded)']); ?>
     <?php $__env->slot('actions', null, []); ?> <button onclick="window.print()" class="btn-secondary text-sm">Print</button> <?php $__env->endSlot(); ?>
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
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-input w-28" value="<?php echo e($year); ?>">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </div>
    </form>
</div>

<div class="card mb-6">
    <div class="card-header bg-gray-50 text-center">
        <div class="w-full">
            <h2 class="text-sm font-bold">BIR FORM 1604-E</h2>
            <p class="text-xs text-secondary-500">Annual Information Return of Creditable Income Taxes Withheld (Expanded)</p>
            <p class="text-xs text-secondary-500">Calendar Year <?php echo e($year); ?></p>
        </div>
    </div>
    <div class="card-body max-w-2xl mx-auto">
        <table class="w-full text-sm">
            <tr class="border-b"><td class="py-3 font-medium">Total Number of Payees</td><td class="py-3 text-right font-mono font-bold w-48"><?php echo e($payeeCount); ?></td></tr>
            <tr class="border-b"><td class="py-3 font-medium">Total Amount of Income Payments</td><td class="py-3 text-right font-mono font-bold w-48">₱<?php echo e(number_format($totalTaxBase, 2)); ?></td></tr>
            <tr class="border-b"><td class="py-3 font-medium">Total Taxes Withheld</td><td class="py-3 text-right font-mono font-bold w-48">₱<?php echo e(number_format($totalTaxWithheld, 2)); ?></td></tr>
            <tr class="bg-gray-50 font-bold"><td class="py-3">Total Remittances for the Year</td><td class="py-3 text-right font-mono w-48 text-primary-700">₱<?php echo e(number_format($totalTaxWithheld, 2)); ?></td></tr>
        </table>
    </div>
</div>


<div class="card">
    <div class="card-header"><h3 class="card-title">Annual Alphalist of Payees</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr><th>Payee</th><th>TIN</th><th>ATC</th><th class="text-right">Income Payment</th><th class="text-right">Tax Withheld</th></tr>
            </thead>
            <tbody>
                <?php $grouped = $payments->groupBy(fn($p) => $p->disbursement->payee_name ?? 'Unknown'); ?>
                <?php $__empty_1 = true; $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payee => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="font-medium"><?php echo e($payee); ?></td>
                    <td class="font-mono text-sm"><?php echo e($group->first()->disbursement->vendor->tin ?? '-'); ?></td>
                    <td class="font-mono text-sm"><?php echo e($group->first()->disbursement->vendor->withholding_tax_type ?? '-'); ?></td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($group->sum('gross_amount'), 2)); ?></td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($group->sum('withholding_tax'), 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="text-center text-secondary-400 py-4">No data for this year.</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if($payments->count() > 0): ?>
            <tfoot class="bg-gray-50 font-bold">
                <tr><td colspan="3" class="text-right">Totals:</td><td class="text-right font-mono">₱<?php echo e(number_format($totalTaxBase, 2)); ?></td><td class="text-right font-mono">₱<?php echo e(number_format($totalTaxWithheld, 2)); ?></td></tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/tax/bir-1604e.blade.php ENDPATH**/ ?>