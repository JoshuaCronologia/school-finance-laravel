<?php $__env->startSection('title', 'BIR 0619-E'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'BIR 0619-E','subtitle' => 'Monthly Remittance of Creditable Income Taxes Withheld (Expanded)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'BIR 0619-E','subtitle' => 'Monthly Remittance of Creditable Income Taxes Withheld (Expanded)']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
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
    <div class="card-header bg-gray-50">
        <div class="text-center w-full">
            <h2 class="text-sm font-bold text-secondary-900">BIR FORM 0619-E</h2>
            <p class="text-xs text-secondary-500">Monthly Remittance Form for Creditable Income Taxes Withheld (Expanded)</p>
            <p class="text-xs text-secondary-500">For the month of <?php echo e(date('F', mktime(0,0,0,$month,1))); ?> <?php echo e($year); ?></p>
        </div>
    </div>
    <div class="card-body max-w-2xl mx-auto">
        <table class="w-full text-sm">
            <tbody>
                <tr class="border-b border-gray-200">
                    <td class="py-3 font-medium">1. Total Amount of Taxes Withheld for the Month</td>
                    <td class="py-3 text-right font-mono font-bold w-48">₱<?php echo e(number_format($totalTaxWithheld, 2)); ?></td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="py-3 font-medium">2. Less: Tax Credits/Payments</td>
                    <td class="py-3 text-right font-mono w-48">₱0.00</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="py-3 font-medium">3. Tax Still Due (1 less 2)</td>
                    <td class="py-3 text-right font-mono font-bold w-48">₱<?php echo e(number_format($totalTaxWithheld, 2)); ?></td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="py-3 font-medium">4. Add: Penalties</td>
                    <td class="py-3 text-right font-mono w-48">₱0.00</td>
                </tr>
                <tr class="bg-gray-50 font-bold">
                    <td class="py-3">5. Total Amount Due (3 plus 4)</td>
                    <td class="py-3 text-right font-mono w-48 text-primary-700">₱<?php echo e(number_format($totalTaxWithheld, 2)); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="mt-6">
            <h4 class="text-sm font-semibold text-secondary-700 mb-3">Supporting Schedule</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payee</th>
                        <th>Voucher #</th>
                        <th class="text-right">Income Payment</th>
                        <th class="text-right">Tax Withheld</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(\Carbon\Carbon::parse($p->payment_date)->format('M d, Y')); ?></td>
                        <td><?php echo e($p->disbursement->payee_name ?? '-'); ?></td>
                        <td class="font-mono"><?php echo e($p->voucher_number); ?></td>
                        <td class="text-right font-mono">₱<?php echo e(number_format($p->gross_amount, 2)); ?></td>
                        <td class="text-right font-mono">₱<?php echo e(number_format($p->withholding_tax, 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-secondary-400 py-4">No withholding tax transactions for this period.</td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if($payments->count() > 0): ?>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="3" class="text-right">Totals:</td>
                        <td class="text-right font-mono">₱<?php echo e(number_format($totalTaxBase, 2)); ?></td>
                        <td class="text-right font-mono">₱<?php echo e(number_format($totalTaxWithheld, 2)); ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/tax/bir-0619e.blade.php ENDPATH**/ ?>