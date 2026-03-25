<?php $__env->startSection('title', 'BIR 1601-C'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'BIR 1601-C','subtitle' => 'Monthly Remittance Return of Income Taxes Withheld on Compensation']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'BIR 1601-C','subtitle' => 'Monthly Remittance Return of Income Taxes Withheld on Compensation']); ?>
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
            <h2 class="text-sm font-bold">BIR FORM 1601-C</h2>
            <p class="text-xs text-secondary-500">Monthly Remittance Return of Income Taxes Withheld on Compensation</p>
            <p class="text-xs text-secondary-500">For the month of <?php echo e(date('F', mktime(0,0,0,$month,1))); ?> <?php echo e($year); ?></p>
        </div>
    </div>
    <div class="card-body max-w-2xl mx-auto">
        <table class="w-full text-sm">
            <tr class="border-b"><td class="py-3 font-medium">1. Taxes Withheld on Compensation</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">2. Adjustment from Previous Month(s)</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">3. Total Taxes Required to be Withheld (1+2)</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">4. Less: Tax Remitted in Return Previously Filed</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">5. Tax Still Due (3 less 4)</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">6. Add: Penalties (Surcharge + Interest + Compromise)</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="bg-gray-50 font-bold"><td class="py-3">7. Total Amount Still Due (5+6)</td><td class="py-3 text-right font-mono w-48 text-primary-700">₱0.00</td></tr>
        </table>
        <p class="text-xs text-secondary-400 text-center mt-4">Compensation withholding tax data will be populated from payroll module when available.</p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/tax/bir-1601c.blade.php ENDPATH**/ ?>