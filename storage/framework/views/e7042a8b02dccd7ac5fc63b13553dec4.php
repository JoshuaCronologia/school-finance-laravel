<?php $__env->startSection('title', 'Invoice #' . $invoice->invoice_number); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Invoice #' . $invoice->invoice_number,'subtitle' => $invoice->customer->name ?? 'AR Invoice']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Invoice #' . $invoice->invoice_number),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->customer->name ?? 'AR Invoice')]); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('ar.invoices.index')); ?>" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                Back to Invoices
            </a>

            <?php if($invoice->status === 'draft'): ?>
                <form action="<?php echo e(route('ar.invoices.update', $invoice)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <input type="hidden" name="status" value="posted">
                    <button type="submit" class="btn-primary inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        Post Invoice
                    </button>
                </form>
                <form action="<?php echo e(route('ar.invoices.update', $invoice)); ?>" method="POST" class="inline" onsubmit="return confirm('Cancel this invoice?');">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn-secondary text-danger-600 hover:text-danger-700 inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        Cancel
                    </button>
                </form>
            <?php endif; ?>

            <button onclick="window.print()" class="btn-secondary inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M9.75 21h4.5" /></svg>
                Print
            </button>
        </div>
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
<?php if(session('error')): ?>
    <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => ['type' => 'danger','message' => session('error'),'class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('error')),'class' => 'mb-4']); ?>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    
    <div class="card lg:col-span-2">
        <div class="card-header">
            <div class="flex items-center justify-between w-full">
                <h3 class="card-title">Invoice Information</h3>
                <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $invoice->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-xs text-secondary-500">Invoice Number</dt>
                    <dd class="text-sm font-semibold text-secondary-900"><?php echo e($invoice->invoice_number); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Invoice Date</dt>
                    <dd class="text-sm font-medium"><?php echo e($invoice->invoice_date->format('M d, Y')); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Due Date</dt>
                    <dd class="text-sm font-medium <?php echo e($invoice->due_date->isPast() && $invoice->balance > 0 ? 'text-danger-600' : ''); ?>">
                        <?php echo e($invoice->due_date->format('M d, Y')); ?>

                        <?php if($invoice->due_date->isPast() && $invoice->balance > 0): ?>
                            <span class="text-xs text-danger-500">(Overdue)</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Reference #</dt>
                    <dd class="text-sm font-medium"><?php echo e($invoice->reference_number ?: '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Customer</dt>
                    <dd class="text-sm font-semibold text-secondary-900"><?php echo e($invoice->customer->name ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Campus</dt>
                    <dd class="text-sm font-medium"><?php echo e($invoice->campus->name ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">School Year</dt>
                    <dd class="text-sm font-medium"><?php echo e($invoice->school_year ?: '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Semester</dt>
                    <dd class="text-sm font-medium"><?php echo e($invoice->semester ?: '-'); ?></dd>
                </div>
                <?php if($invoice->description): ?>
                <div class="col-span-2 md:col-span-4">
                    <dt class="text-xs text-secondary-500">Description</dt>
                    <dd class="text-sm font-medium"><?php echo e($invoice->description); ?></dd>
                </div>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header"><h3 class="card-title">Amount Summary</h3></div>
        <div class="card-body space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Gross Amount</span>
                <span class="font-medium"><?php echo '&#8369;' . number_format($invoice->gross_amount, 2); ?></span>
            </div>
            <?php if($invoice->discount_amount > 0): ?>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Discount</span>
                <span class="font-medium text-success-600">(<?php echo '&#8369;' . number_format($invoice->discount_amount, 2); ?>)</span>
            </div>
            <?php endif; ?>
            <?php if($invoice->tax_amount > 0): ?>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Tax</span>
                <span class="font-medium"><?php echo '&#8369;' . number_format($invoice->tax_amount, 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between text-sm pt-2 border-t border-gray-200 font-semibold">
                <span>Net Receivable</span>
                <span><?php echo '&#8369;' . number_format($invoice->net_receivable, 2); ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Amount Paid</span>
                <span class="font-medium text-success-600"><?php echo '&#8369;' . number_format($invoice->amount_paid, 2); ?></span>
            </div>
            <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                <span class="font-bold text-lg">Balance</span>
                <span class="font-bold text-lg <?php echo e($invoice->balance > 0 ? 'text-danger-600' : 'text-success-600'); ?>">
                    <?php echo '&#8369;' . number_format($invoice->balance, 2); ?>
                </span>
            </div>
            <?php if($invoice->balance <= 0 && $invoice->status !== 'draft'): ?>
                <div class="text-center pt-2">
                    <span class="inline-flex items-center gap-1 text-sm font-semibold text-success-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        Fully Paid
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="card mb-6">
    <div class="card-header"><h3 class="card-title">Line Items</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fee Code</th>
                    <th>Description</th>
                    <th>Revenue Account</th>
                    <th>Department</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Amount</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $invoice->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="font-mono text-sm"><?php echo e($line->fee_code ?: '-'); ?></td>
                    <td><?php echo e($line->description); ?></td>
                    <td class="text-sm"><?php echo e($line->revenueAccount->account_code ?? ''); ?> <?php echo e($line->revenueAccount->account_name ?? '-'); ?></td>
                    <td><?php echo e($line->department->name ?? '-'); ?></td>
                    <td class="text-right"><?php echo e(number_format($line->quantity, 2)); ?></td>
                    <td class="text-right"><?php echo '&#8369;' . number_format($line->unit_amount, 2); ?></td>
                    <td class="text-right font-medium"><?php echo '&#8369;' . number_format($line->amount, 2); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-center text-secondary-400 py-4">No line items.</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if($invoice->lines->count() > 0): ?>
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td colspan="6" class="text-right">Total:</td>
                    <td class="text-right"><?php echo '&#8369;' . number_format($invoice->lines->sum('amount'), 2); ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>


<?php if($invoice->allocations && $invoice->allocations->count() > 0): ?>
<div class="card mb-6">
    <div class="card-header"><h3 class="card-title">Payments Applied</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Receipt #</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th class="text-right">Amount Applied</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $invoice->allocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alloc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="font-medium">
                        <?php if($alloc->collection): ?>
                            <a href="<?php echo e(route('ar.collections.show', $alloc->collection_id)); ?>" class="text-primary-600 hover:underline">
                                <?php echo e($alloc->collection->receipt_number ?? '-'); ?>

                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($alloc->collection?->collection_date?->format('M d, Y') ?? '-'); ?></td>
                    <td><?php echo e(ucfirst(str_replace('_', ' ', $alloc->collection?->payment_method ?? '-'))); ?></td>
                    <td class="text-right font-medium"><?php echo '&#8369;' . number_format($alloc->amount_applied ?? $alloc->amount ?? 0, 2); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>


<?php if($invoice->journalEntry): ?>
<div class="card">
    <div class="card-header">
        <div class="flex items-center justify-between w-full">
            <h3 class="card-title">Journal Entry</h3>
            <a href="<?php echo e(route('gl.journal-entries.show', $invoice->journalEntry)); ?>" class="text-sm text-primary-600 hover:underline">
                <?php echo e($invoice->journalEntry->entry_number); ?>

            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $invoice->journalEntry->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jeLine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="font-mono text-sm"><?php echo e($jeLine->account->account_code ?? '-'); ?></td>
                    <td><?php echo e($jeLine->account->account_name ?? '-'); ?></td>
                    <td class="text-right"><?php echo e($jeLine->debit > 0 ? '₱' . number_format($jeLine->debit, 2) : ''); ?></td>
                    <td class="text-right"><?php echo e($jeLine->credit > 0 ? '₱' . number_format($jeLine->credit, 2) : ''); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td colspan="2" class="text-right">Totals:</td>
                    <td class="text-right"><?php echo '&#8369;' . number_format($invoice->journalEntry->lines->sum('debit'), 2); ?></td>
                    <td class="text-right"><?php echo '&#8369;' . number_format($invoice->journalEntry->lines->sum('credit'), 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/ar/invoices/show.blade.php ENDPATH**/ ?>