
<?php $__env->startSection('title', 'Payment Processing'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Payment Processing','subtitle' => 'Process approved disbursements and manage payment history']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payment Processing','subtitle' => 'Process approved disbursements and manage payment history']); ?>
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


<?php $readyCount = count($readyForPayment ?? []); ?>
<div class="card mb-6">
    <div class="card-header">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-semibold text-secondary-700">Ready for Payment</h3>
            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-primary-600 rounded-full"><?php echo e($readyCount); ?></span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Request #</th>
                    <th>Payee</th>
                    <th>Description</th>
                    <th>Department</th>
                    <th class="text-right">Amount</th>
                    <th>Method</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $readyForPayment ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="font-medium">
                        <a href="<?php echo e(route('ap.disbursements.show', $request)); ?>" class="text-primary-600 hover:text-primary-700 hover:underline">
                            <?php echo e($request->request_number); ?>

                        </a>
                    </td>
                    <td><?php echo e($request->payee_name ?? $request->payee->name ?? '-'); ?></td>
                    <td class="max-w-xs truncate"><?php echo e($request->description ?? '-'); ?></td>
                    <td><?php echo e($request->department->name ?? '-'); ?></td>
                    <td class="text-right font-medium"><?php echo e('₱' . number_format($request->amount, 2)); ?></td>
                    <td><?php echo e(ucfirst(str_replace('_', ' ', $request->payment_method ?? '-'))); ?></td>
                    <td>
                        <button @click="$dispatch('open-modal', 'process-payment-<?php echo e($request->id); ?>')" class="btn-primary text-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                            Process
                        </button>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center text-secondary-400 py-8">
                        <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        No approved requests awaiting payment.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php $__currentLoopData = $readyForPayment ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'process-payment-'.e($request->id).'','title' => 'Process Payment','maxWidth' => '3xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'process-payment-'.e($request->id).'','title' => 'Process Payment','maxWidth' => '3xl']); ?>
    <form action="<?php echo e(route('ap.payments.store', $request)); ?>" method="POST" x-data="{
        grossAmount: <?php echo e($request->amount); ?>,
        whtRate: 0.02,
        get whtAmount() { return parseFloat((this.grossAmount * this.whtRate).toFixed(2)); },
        get netAmount() { return parseFloat((this.grossAmount - this.whtAmount).toFixed(2)); },
        fmt(val) { return parseFloat(val || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    }">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="disbursement_id" value="<?php echo e($request->id); ?>">

        
        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-secondary-500">Request #:</span>
                    <span class="font-medium"><?php echo e($request->request_number); ?></span>
                </div>
                <div>
                    <span class="text-secondary-500">Payee:</span>
                    <span class="font-medium"><?php echo e($request->payee_name ?? $request->payee->name ?? '-'); ?></span>
                </div>
                <div class="col-span-2">
                    <span class="text-secondary-500">Description:</span>
                    <span class="font-medium"><?php echo e($request->description ?? '-'); ?></span>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="form-label">Payment Date <span class="text-danger-500">*</span></label>
                <input type="date" name="payment_date" class="form-input" value="<?php echo e(date('Y-m-d')); ?>" required>
            </div>
            <div>
                <label class="form-label">Bank Account</label>
                <input type="text" name="bank_account" class="form-input" placeholder="e.g., BDO - 1234-5678">
            </div>
            <div>
                <label class="form-label">Payment Method <span class="text-danger-500">*</span></label>
                <select name="payment_method" class="form-input" required>
                    <option value="check" <?php echo e(($request->payment_method ?? '') == 'check' ? 'selected' : ''); ?>>Check</option>
                    <option value="bank_transfer" <?php echo e(($request->payment_method ?? '') == 'bank_transfer' ? 'selected' : ''); ?>>Bank Transfer</option>
                    <option value="cash" <?php echo e(($request->payment_method ?? '') == 'cash' ? 'selected' : ''); ?>>Cash</option>
                    <option value="online" <?php echo e(($request->payment_method ?? '') == 'online' ? 'selected' : ''); ?>>Online</option>
                </select>
            </div>
            <div>
                <label class="form-label">Reference / Check Number <span class="text-danger-500">*</span></label>
                <input type="text" name="reference_number" class="form-input" required placeholder="e.g., CHK-00123">
            </div>
        </div>

        
        <div class="bg-gray-50 rounded-lg p-4 mb-4 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Gross Amount</span>
                <span class="font-medium" x-text="'₱' + fmt(grossAmount)"></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Withholding Tax (2%)</span>
                <span class="font-medium text-danger-600" x-text="'(₱' + fmt(whtAmount) + ')'"></span>
            </div>
            <div class="flex justify-between text-sm font-semibold border-t border-gray-200 pt-2">
                <span class="text-secondary-900">Net Amount to Pay</span>
                <span class="text-primary-700" x-text="'₱' + fmt(netAmount)"></span>
            </div>
            <input type="hidden" name="gross_amount" :value="grossAmount">
            <input type="hidden" name="wht_amount" :value="whtAmount">
            <input type="hidden" name="net_amount" :value="netAmount">
        </div>

        
        <div class="mb-4">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-input text-sm" rows="2" placeholder="Payment notes or remarks..."></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'process-payment-<?php echo e($request->id); ?>')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                Confirm Payment
            </button>
        </div>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-700">Payment History</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Voucher #</th>
                    <th>Date</th>
                    <th>Request #</th>
                    <th>Payee</th>
                    <th class="text-right">Gross</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Net Paid</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th class="w-12"></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $payments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="font-medium"><?php echo e($payment->voucher_number); ?></td>
                    <td><?php echo e(\Carbon\Carbon::parse($payment->payment_date)->format('M d, Y')); ?></td>
                    <td>
                        <a href="<?php echo e(route('ap.disbursements.show', $payment->disbursement_id)); ?>" class="text-primary-600 hover:text-primary-700 hover:underline">
                            <?php echo e($payment->disbursement->request_number ?? '-'); ?>

                        </a>
                    </td>
                    <td><?php echo e($payment->payee_name ?? $payment->disbursement->payee_name ?? '-'); ?></td>
                    <td class="text-right"><?php echo e('₱' . number_format($payment->gross_amount, 2)); ?></td>
                    <td class="text-right"><?php echo e('₱' . number_format($payment->wht_amount, 2)); ?></td>
                    <td class="text-right font-medium"><?php echo e('₱' . number_format($payment->net_amount, 2)); ?></td>
                    <td><?php echo e(ucfirst(str_replace('_', ' ', $payment->payment_method ?? '-'))); ?></td>
                    <td><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $payment->status ?? 'completed']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($payment->status ?? 'completed')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></td>
                    <td>
                        <a href="<?php echo e(route('ap.payments.print', $payment)); ?>" class="btn-icon text-secondary-500 hover:text-secondary-700" title="Print Voucher" target="_blank">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M9.75 21h4.5" /></svg>
                        </a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="10" class="text-center text-secondary-400 py-8">
                        <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                        No payment history yet.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if(isset($payments) && $payments instanceof \Illuminate\Pagination\LengthAwarePaginator && $payments->hasPages()): ?>
    <div class="card-footer">
        <?php echo e($payments->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/ap/payment-processing.blade.php ENDPATH**/ ?>