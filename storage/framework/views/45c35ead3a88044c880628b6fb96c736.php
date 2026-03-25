<?php $__env->startSection('title', 'BIR 0619-F'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'BIR 0619-F','subtitle' => 'Monthly Remittance of Final Income Taxes Withheld']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'BIR 0619-F','subtitle' => 'Monthly Remittance of Final Income Taxes Withheld']); ?>
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
                <label class="form-label">Month</label>
                <select name="month" class="form-input w-36">
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-input w-28" value="<?php echo e($year); ?>">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header bg-gray-50 text-center">
        <div class="w-full">
            <h2 class="text-sm font-bold">BIR FORM 0619-F</h2>
            <p class="text-xs text-secondary-500">Monthly Remittance Form for Final Income Taxes Withheld</p>
            <p class="text-xs text-secondary-500">For the month of <?php echo e(date('F', mktime(0,0,0,$month,1))); ?> <?php echo e($year); ?></p>
        </div>
    </div>
    <div class="card-body max-w-2xl mx-auto">
        <table class="w-full text-sm">
            <tr class="border-b"><td class="py-3 font-medium">1. Total Taxes Withheld for the Month (Final)</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">2. Less: Tax Credits/Payments</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">3. Tax Still Due</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">4. Add: Penalties</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="bg-gray-50 font-bold"><td class="py-3">5. Total Amount Due</td><td class="py-3 text-right font-mono w-48 text-primary-700">₱0.00</td></tr>
        </table>
        <p class="text-xs text-secondary-400 text-center mt-4">Final withholding tax data will be populated from payroll and other final tax transactions when available.</p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/tax/bir-0619f.blade.php ENDPATH**/ ?>