<?php $__env->startSection('title', 'Cash Disbursements Book'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Cash Disbursements Book (CDB)','subtitle' => 'All cash/bank disbursements (credits to cash accounts)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Cash Disbursements Book (CDB)','subtitle' => 'All cash/bank disbursements (credits to cash accounts)']); ?>
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

<?php if (isset($component)) { $__componentOriginale9f22847d79d6273acb27aff60f1f678 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale9f22847d79d6273acb27aff60f1f678 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-bar','data' => ['action' => ''.e(route('reports.cash-disbursements-book')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => ''.e(route('reports.cash-disbursements-book')).'']); ?>
    <div>
        <label class="form-label">From</label>
        <input type="date" name="date_from" class="form-input w-40" value="<?php echo e($dateFrom); ?>">
    </div>
    <div>
        <label class="form-label">To</label>
        <input type="date" name="date_to" class="form-input w-40" value="<?php echo e($dateTo); ?>">
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale9f22847d79d6273acb27aff60f1f678)): ?>
<?php $attributes = $__attributesOriginale9f22847d79d6273acb27aff60f1f678; ?>
<?php unset($__attributesOriginale9f22847d79d6273acb27aff60f1f678); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale9f22847d79d6273acb27aff60f1f678)): ?>
<?php $component = $__componentOriginale9f22847d79d6273acb27aff60f1f678; ?>
<?php unset($__componentOriginale9f22847d79d6273acb27aff60f1f678); ?>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-gray-50 text-center">
        <div class="w-full">
            <h2 class="text-sm font-bold text-secondary-900">CASH DISBURSEMENTS BOOK</h2>
            <p class="text-xs text-secondary-500"><?php echo e(\Carbon\Carbon::parse($dateFrom)->format('F d, Y')); ?> to <?php echo e(\Carbon\Carbon::parse($dateTo)->format('F d, Y')); ?></p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-28">Date</th>
                    <th class="w-28">Entry #</th>
                    <th class="w-28">Ref/Check No.</th>
                    <th>Description</th>
                    <th>Cash/Bank Account</th>
                    <th class="text-right w-32">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e(\Carbon\Carbon::parse($entry->posting_date)->format('M d, Y')); ?></td>
                    <td><span class="font-mono text-sm"><?php echo e($entry->entry_number); ?></span></td>
                    <td class="font-mono text-sm text-secondary-500"><?php echo e($entry->reference_number ?? '-'); ?></td>
                    <td><?php echo e($entry->je_description ?? $entry->description ?? '-'); ?></td>
                    <td class="text-sm"><?php echo e($entry->account_code); ?> - <?php echo e($entry->account_name); ?></td>
                    <td class="text-right font-mono font-medium">₱<?php echo e(number_format($entry->credit, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-secondary-400 py-8">No cash disbursements for this period.</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if($entries->count() > 0): ?>
            <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-400">
                <tr>
                    <td colspan="5" class="text-right">Total Cash Disbursements:</td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($totalAmount, 2)); ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/reports/cash-disbursements-book.blade.php ENDPATH**/ ?>