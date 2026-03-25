<?php $__env->startSection('title', 'Alphalist - Quarterly'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Alphalist of Payees (Quarterly)','subtitle' => 'Quarterly Alphalist of Payees Subject to Withholding Tax']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Alphalist of Payees (Quarterly)','subtitle' => 'Quarterly Alphalist of Payees Subject to Withholding Tax']); ?>
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
                <label class="form-label">Quarter</label>
                <select name="quarter" class="form-input w-36">
                    <?php for($q = 1; $q <= 4; $q++): ?>
                        <option value="<?php echo e($q); ?>" <?php echo e($quarter == $q ? 'selected' : ''); ?>>Q<?php echo e($q); ?> (<?php echo e(['Jan-Mar','Apr-Jun','Jul-Sep','Oct-Dec'][$q-1]); ?>)</option>
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
            <h2 class="text-sm font-bold">QUARTERLY ALPHALIST OF PAYEES (QAP)</h2>
            <p class="text-xs text-secondary-500">Q<?php echo e($quarter); ?> <?php echo e($year); ?> - <?php echo e(['January to March','April to June','July to September','October to December'][$quarter-1]); ?></p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>TIN</th>
                    <th>Payee Name</th>
                    <th>ATC</th>
                    <th class="text-right">Income Payment</th>
                    <th class="text-right">Tax Withheld</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $alphalist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td class="font-mono text-sm"><?php echo e($row->tin ?: '-'); ?></td>
                    <td class="font-medium"><?php echo e($row->payee_name); ?></td>
                    <td class="font-mono text-sm"><?php echo e($row->atc ?: '-'); ?></td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($row->income_payment, 2)); ?></td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($row->tax_withheld, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-secondary-400 py-6">No withholding tax transactions for this quarter.</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if($alphalist->count() > 0): ?>
            <tfoot class="bg-gray-50 font-bold">
                <tr>
                    <td colspan="4" class="text-right">Grand Total:</td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($alphalist->sum('income_payment'), 2)); ?></td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($alphalist->sum('tax_withheld'), 2)); ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/tax/alphalist-quarterly.blade.php ENDPATH**/ ?>