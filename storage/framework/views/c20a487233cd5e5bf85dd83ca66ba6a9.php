
<?php $__env->startSection('title', 'Disbursement Request #' . $disbursement->request_number); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Request #' . $disbursement->request_number,'subtitle' => 'Created ' . $disbursement->created_at->format('M d, Y')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Request #' . $disbursement->request_number),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Created ' . $disbursement->created_at->format('M d, Y'))]); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(route('ap.disbursements.index')); ?>" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to Requests
        </a>
        <?php if($disbursement->status === 'paid' && $disbursement->payment): ?>
            <a href="<?php echo e(route('ap.payments.print', $disbursement->payment)); ?>" class="btn-secondary inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" /></svg>
                Print Voucher
            </a>
        <?php endif; ?>
        <?php if($disbursement->status === 'draft'): ?>
            <a href="<?php echo e(route('ap.disbursements.edit', $disbursement)); ?>" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                Edit
            </a>
            <form action="<?php echo e(route('ap.disbursements.submit', $disbursement)); ?>" method="POST" class="inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                    Submit for Approval
                </button>
            </form>
        <?php endif; ?>
        <?php if($disbursement->status === 'pending_approval'): ?>
            <form action="<?php echo e(route('ap.approval.approve', $disbursement)); ?>" method="POST" class="inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-primary bg-success-600 hover:bg-success-700">Approve</button>
            </form>
            <form action="<?php echo e(route('ap.approval.reject', $disbursement)); ?>" method="POST" class="inline">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="comments" value="Rejected from disbursement detail page">
                <button type="submit" class="btn-secondary text-danger-600 border-danger-300 hover:bg-danger-50">Reject</button>
            </form>
        <?php endif; ?>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="lg:col-span-2 space-y-6">
        
        <div class="card">
            <div class="card-header">
                <div class="flex items-center justify-between w-full">
                    <h3 class="text-sm font-semibold text-secondary-700">Request Details</h3>
                    <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $disbursement->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($disbursement->status)]); ?>
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
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs text-secondary-500">Request Date</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e(\Carbon\Carbon::parse($disbursement->request_date)->format('M d, Y')); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Due Date</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e($disbursement->due_date ? \Carbon\Carbon::parse($disbursement->due_date)->format('M d, Y') : '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Payee</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e($disbursement->payee_name ?? $disbursement->payee->name ?? '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Payee Type</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e(ucfirst($disbursement->payee_type ?? '-')); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Department</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e($disbursement->department->name ?? '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Expense Category</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e($disbursement->category->name ?? $disbursement->expense_category ?? '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Cost Center</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e($disbursement->costCenter->name ?? '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Project</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e($disbursement->project ?? '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Payment Method</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e(ucfirst(str_replace('_', ' ', $disbursement->payment_method ?? '-'))); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Total Amount</dt>
                        <dd class="text-lg font-bold text-primary-700"><?php echo e('₱' . number_format($disbursement->amount, 2)); ?></dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-xs text-secondary-500">Description</dt>
                        <dd class="text-sm text-secondary-900"><?php echo e($disbursement->description ?? '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Requested By</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e($disbursement->requestedBy->name ?? $disbursement->requested_by_name ?? '-'); ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-700">Line Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Unit Cost</th>
                            <th class="text-right">Amount</th>
                            <th>Account</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $disbursement->items ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($i + 1); ?></td>
                            <td><?php echo e($line->description ?? '-'); ?></td>
                            <td class="text-right"><?php echo e($line->quantity); ?></td>
                            <td class="text-right"><?php echo e('₱' . number_format($line->unit_cost, 2)); ?></td>
                            <td class="text-right font-medium"><?php echo e('₱' . number_format($line->amount, 2)); ?></td>
                            <td><?php echo e($line->account_code ?? '-'); ?></td>
                            <td><?php echo e($line->remarks ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary-400 py-6">No line items.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold">
                            <td colspan="4" class="text-right">Total</td>
                            <td class="text-right"><?php echo e('₱' . number_format($disbursement->amount, 2)); ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        
        <?php if($disbursement->status === 'paid' && $disbursement->payment): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-700">Payment Information</h3>
            </div>
            <div class="card-body">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs text-secondary-500">Voucher Number</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e($disbursement->payment->voucher_number ?? '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Payment Date</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e($disbursement->payment->payment_date ? \Carbon\Carbon::parse($disbursement->payment->payment_date)->format('M d, Y') : '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Bank Account</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e($disbursement->payment->bank_account ?? '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Reference Number</dt>
                        <dd class="text-sm font-medium text-secondary-900"><?php echo e($disbursement->payment->reference_number ?? '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-secondary-500">Net Amount Paid</dt>
                        <dd class="text-lg font-bold text-success-600"><?php echo e('₱' . number_format($disbursement->payment->net_amount, 2)); ?></dd>
                    </div>
                </dl>
            </div>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="space-y-6">
        
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-700">Budget Utilization</h3>
            </div>
            <div class="card-body space-y-3">
                <?php
                    $budget = $budgetInfo ?? null;
                ?>
                <?php if($budget): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-500">Budget</span>
                        <span class="font-medium"><?php echo e('₱' . number_format($budget->budget ?? 0, 2)); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-500">Committed</span>
                        <span class="font-medium text-warning-600"><?php echo e('₱' . number_format($budget->committed ?? 0, 2)); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-500">Actual Spent</span>
                        <span class="font-medium"><?php echo e('₱' . number_format($budget->actual ?? 0, 2)); ?></span>
                    </div>
                    <div class="flex justify-between text-sm border-t border-gray-100 pt-2">
                        <span class="text-secondary-500">Remaining</span>
                        <span class="font-semibold <?php echo e(($budget->remaining ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600'); ?>"><?php echo e('₱' . number_format($budget->remaining ?? 0, 2)); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-500">This Request</span>
                        <span class="font-semibold text-primary-600"><?php echo e('₱' . number_format($disbursement->amount, 2)); ?></span>
                    </div>
                    <?php if($disbursement->amount > ($budget->remaining ?? 0)): ?>
                        <div class="bg-warning-50 text-warning-800 text-xs p-2 rounded-lg">
                            Exceeds remaining budget by <?php echo e('₱' . number_format($disbursement->amount - ($budget->remaining ?? 0), 2)); ?>

                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-sm text-secondary-400">No budget data available.</p>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-700">Approval Timeline</h3>
            </div>
            <div class="card-body">
                <?php $__empty_1 = true; $__currentLoopData = $disbursement->approvals ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex gap-3 <?php echo e(!$loop->last ? 'pb-4 mb-4 border-b border-gray-100' : ''); ?>">
                    <div class="flex-shrink-0 mt-0.5">
                        <?php if($approval->action === 'approved'): ?>
                            <div class="w-6 h-6 rounded-full bg-success-100 text-success-600 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </div>
                        <?php elseif($approval->action === 'rejected'): ?>
                            <div class="w-6 h-6 rounded-full bg-danger-100 text-danger-600 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </div>
                        <?php elseif($approval->action === 'returned'): ?>
                            <div class="w-6 h-6 rounded-full bg-warning-100 text-warning-600 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                            </div>
                        <?php else: ?>
                            <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-secondary-900"><?php echo e($approval->approver->name ?? $approval->approver_name ?? 'System'); ?></p>
                        <p class="text-xs text-secondary-500"><?php echo e(ucfirst($approval->action)); ?> &middot; <?php echo e(\Carbon\Carbon::parse($approval->created_at)->format('M d, Y h:i A')); ?></p>
                        <?php if($approval->comments): ?>
                            <p class="text-sm text-secondary-600 mt-1"><?php echo e($approval->comments); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-secondary-400 text-center py-4">No approval actions yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/ap/disbursements/show.blade.php ENDPATH**/ ?>