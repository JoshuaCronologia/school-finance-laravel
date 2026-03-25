<?php $__env->startSection('title', 'Bill #' . $bill->bill_number); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Bill #' . $bill->bill_number,'subtitle' => $bill->vendor->name ?? 'Supplier Bill']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Bill #' . $bill->bill_number),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bill->vendor->name ?? 'Supplier Bill')]); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('ap.bills.index')); ?>" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                Back to Bills
            </a>

            <?php if($bill->status === 'draft'): ?>
                <a href="<?php echo e(route('ap.bills.edit', $bill)); ?>" class="btn-secondary inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                    Edit
                </a>
                <form action="<?php echo e(route('ap.bills.approve', $bill)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-primary inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Approve
                    </button>
                </form>
            <?php endif; ?>

            <?php if($bill->status === 'approved'): ?>
                <form action="<?php echo e(route('ap.bills.post', $bill)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-primary inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        Post to GL
                    </button>
                </form>
            <?php endif; ?>
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
                <h3 class="card-title">Bill Information</h3>
                <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $bill->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bill->status)]); ?>
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
                    <dt class="text-xs text-secondary-500">Bill Number</dt>
                    <dd class="text-sm font-semibold text-secondary-900"><?php echo e($bill->bill_number); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Bill Date</dt>
                    <dd class="text-sm font-medium"><?php echo e($bill->bill_date->format('M d, Y')); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Due Date</dt>
                    <dd class="text-sm font-medium <?php echo e($bill->due_date->isPast() && $bill->balance > 0 ? 'text-danger-600' : ''); ?>"><?php echo e($bill->due_date->format('M d, Y')); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Reference #</dt>
                    <dd class="text-sm font-medium"><?php echo e($bill->reference_number ?: '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Vendor</dt>
                    <dd class="text-sm font-semibold text-secondary-900"><?php echo e($bill->vendor->name ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Department</dt>
                    <dd class="text-sm font-medium"><?php echo e($bill->department->name ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Campus</dt>
                    <dd class="text-sm font-medium"><?php echo e($bill->campus->name ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Category</dt>
                    <dd class="text-sm font-medium"><?php echo e($bill->category->name ?? '-'); ?></dd>
                </div>
            </dl>
            <?php if($bill->description): ?>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <dt class="text-xs text-secondary-500">Description</dt>
                <dd class="text-sm text-secondary-900 mt-1"><?php echo e($bill->description); ?></dd>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header"><h3 class="card-title">Amount Summary</h3></div>
        <div class="card-body space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Gross Amount</span>
                <span class="font-medium"><?php echo e('₱' . number_format($bill->gross_amount, 2)); ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">VAT Amount</span>
                <span class="font-medium"><?php echo e('₱' . number_format($bill->vat_amount, 2)); ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Withholding Tax</span>
                <span class="font-medium text-danger-600"><?php echo e($bill->withholding_tax > 0 ? '(₱' . number_format($bill->withholding_tax, 2) . ')' : '-'); ?></span>
            </div>
            <div class="flex justify-between text-sm font-semibold border-t border-gray-200 pt-2">
                <span class="text-secondary-700">Net Payable</span>
                <span class="text-primary-700"><?php echo e('₱' . number_format($bill->net_payable, 2)); ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Amount Paid</span>
                <span class="font-medium text-success-600"><?php echo e('₱' . number_format($bill->amount_paid ?? 0, 2)); ?></span>
            </div>
            <div class="flex justify-between text-sm font-semibold border-t border-gray-200 pt-2">
                <span class="text-secondary-700">Balance</span>
                <span class="<?php echo e($bill->balance > 0 ? 'text-danger-600' : 'text-success-600'); ?>"><?php echo e('₱' . number_format($bill->balance, 2)); ?></span>
            </div>
        </div>
    </div>
</div>


<div class="card mb-6">
    <div class="card-header"><h3 class="card-title">Line Items</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>Account</th>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Cost</th>
                    <th class="text-right">Amount</th>
                    <th>Tax</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $bill->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="text-secondary-400"><?php echo e($i + 1); ?></td>
                    <td class="font-medium"><?php echo e($line->account->account_code ?? '-'); ?> - <?php echo e($line->account->account_name ?? ''); ?></td>
                    <td><?php echo e($line->description ?? '-'); ?></td>
                    <td class="text-right"><?php echo e(number_format($line->quantity, 0)); ?></td>
                    <td class="text-right"><?php echo e('₱' . number_format($line->unit_cost, 2)); ?></td>
                    <td class="text-right font-medium"><?php echo e('₱' . number_format($line->amount, 2)); ?></td>
                    <td><?php echo e($line->taxCode->code ?? $line->taxCode->name ?? '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-semibold">
                    <td colspan="5" class="text-right">Total</td>
                    <td class="text-right"><?php echo e('₱' . number_format($bill->gross_amount, 2)); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>


<?php if($bill->journalEntry): ?>
<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">GL Journal Entry</h3>
        <a href="<?php echo e(route('gl.journal-entries.show', $bill->journalEntry)); ?>" class="text-sm text-primary-600 hover:underline"><?php echo e($bill->journalEntry->entry_number); ?></a>
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
                <?php $__currentLoopData = $bill->journalEntry->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jeLine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="font-medium"><?php echo e($jeLine->account->account_code ?? '-'); ?></td>
                    <td><?php echo e($jeLine->account->account_name ?? '-'); ?></td>
                    <td class="text-right"><?php echo e($jeLine->debit > 0 ? '₱' . number_format($jeLine->debit, 2) : '-'); ?></td>
                    <td class="text-right"><?php echo e($jeLine->credit > 0 ? '₱' . number_format($jeLine->credit, 2) : '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>


<?php if($bill->status === 'posted'): ?>
<div class="card">
    <div class="card-body text-sm text-secondary-500">
        <p>This bill is posted and cannot be edited.</p>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/ap/bills/show.blade.php ENDPATH**/ ?>