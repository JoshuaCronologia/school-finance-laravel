<?php $__env->startSection('title', 'Bank Reconciliation'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Bank Reconciliation','subtitle' => 'Reconcile book balance with bank statement']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Bank Reconciliation','subtitle' => 'Reconcile book balance with bank statement']); ?>
    <?php if($reconData): ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(route('gl.bank-reconciliation.pdf', ['account_id' => $accountId, 'as_of_date' => $asOfDate, 'statement_balance' => $statementBalance])); ?>" class="btn-secondary inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            Download PDF
        </a>
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
     <?php $__env->endSlot(); ?>
    <?php endif; ?>
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
    <div class="card-body">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Bank Account <span class="text-danger-500">*</span></label>
                <select name="account_id" class="form-input w-64" required>
                    <option value="">Select Account</option>
                    <?php $__currentLoopData = $bankAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ba): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($ba->id); ?>" <?php echo e($accountId == $ba->id ? 'selected' : ''); ?>>
                            <?php echo e($ba->account_code); ?> - <?php echo e($ba->account_name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" class="form-input w-44" value="<?php echo e($asOfDate); ?>">
            </div>
            <div>
                <label class="form-label">Bank Statement Balance</label>
                <input type="number" name="statement_balance" class="form-input w-48" step="0.01" placeholder="Enter bank balance" value="<?php echo e($statementBalance); ?>">
            </div>
            <button type="submit" class="btn-primary">Reconcile</button>
        </form>
    </div>
</div>

<?php if($reconData): ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    
    <div class="card">
        <div class="card-header bg-blue-50">
            <h3 class="card-title text-blue-800">Per Bank Statement</h3>
        </div>
        <div class="card-body space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Bank Statement Balance</span>
                <span class="font-bold text-lg"><?php echo e($reconData->bank_statement_balance !== null ? '₱' . number_format($reconData->bank_statement_balance, 2) : 'Not entered'); ?></span>
            </div>

            <?php if($reconData->bank_statement_balance !== null): ?>
            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Add: Deposits in Transit</p>
                <?php $__empty_1 = true; $__currentLoopData = $reconData->deposits_in_transit; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex justify-between text-sm pl-4 py-0.5">
                    <span class="text-secondary-600"><?php echo e(\Carbon\Carbon::parse($dep->posting_date)->format('M d')); ?> - <?php echo e($dep->entry_number); ?> <?php echo e(Str::limit($dep->je_description, 30)); ?></span>
                    <span class="font-mono">₱<?php echo e(number_format($dep->debit, 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-secondary-400 pl-4">None</p>
                <?php endif; ?>
                <div class="flex justify-between text-sm font-semibold pl-4 pt-1 border-t border-dashed border-gray-200 mt-1">
                    <span>Total Deposits in Transit</span>
                    <span class="font-mono">₱<?php echo e(number_format($reconData->total_deposits_transit, 2)); ?></span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Less: Outstanding Checks / Payments</p>
                <?php $__empty_1 = true; $__currentLoopData = $reconData->outstanding_checks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex justify-between text-sm pl-4 py-0.5">
                    <span class="text-secondary-600"><?php echo e(\Carbon\Carbon::parse($chk->posting_date)->format('M d')); ?> - <?php echo e($chk->entry_number); ?> <?php echo e($chk->reference_number ? '('. $chk->reference_number .')' : ''); ?></span>
                    <span class="font-mono text-danger-600">(₱<?php echo e(number_format($chk->credit, 2)); ?>)</span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-secondary-400 pl-4">None</p>
                <?php endif; ?>
                <div class="flex justify-between text-sm font-semibold pl-4 pt-1 border-t border-dashed border-gray-200 mt-1">
                    <span>Total Outstanding Checks</span>
                    <span class="font-mono text-danger-600">(₱<?php echo e(number_format($reconData->total_outstanding_checks, 2)); ?>)</span>
                </div>
            </div>

            <div class="border-t-2 border-gray-300 pt-3">
                <div class="flex justify-between font-bold text-base">
                    <span>Adjusted Bank Balance</span>
                    <span class="font-mono text-blue-800">₱<?php echo e(number_format($reconData->adjusted_bank_balance, 2)); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header bg-green-50">
            <h3 class="card-title text-green-800">Per Books (<?php echo e($reconData->account->account_code); ?> - <?php echo e($reconData->account->account_name); ?>)</h3>
        </div>
        <div class="card-body space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Book Balance per GL</span>
                <span class="font-bold text-lg">₱<?php echo e(number_format($reconData->book_balance, 2)); ?></span>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Add: Bank Credits not yet recorded</p>
                <p class="text-sm text-secondary-400 pl-4 italic">Interest earned, direct deposits, etc.</p>
                <div class="flex justify-between text-sm font-semibold pl-4 pt-1">
                    <span>Subtotal</span>
                    <span class="font-mono">₱0.00</span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Less: Bank Debits not yet recorded</p>
                <p class="text-sm text-secondary-400 pl-4 italic">Bank charges, NSF checks, etc.</p>
                <div class="flex justify-between text-sm font-semibold pl-4 pt-1">
                    <span>Subtotal</span>
                    <span class="font-mono text-danger-600">(₱0.00)</span>
                </div>
            </div>

            <div class="border-t-2 border-gray-300 pt-3">
                <div class="flex justify-between font-bold text-base">
                    <span>Adjusted Book Balance</span>
                    <span class="font-mono text-green-800">₱<?php echo e(number_format($reconData->book_balance, 2)); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>


<?php if($reconData->difference !== null): ?>
<div class="card mb-6 <?php echo e(abs($reconData->difference) < 0.01 ? 'border-green-300' : 'border-red-300'); ?> border-2">
    <div class="card-body text-center py-6">
        <?php if(abs($reconData->difference) < 0.01): ?>
            <div class="inline-flex items-center gap-2 text-green-700 text-lg font-bold">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                RECONCILED - Balances Match
            </div>
        <?php else: ?>
            <div class="inline-flex items-center gap-2 text-red-700 text-lg font-bold">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126Z" /></svg>
                UNRECONCILED - Difference: ₱<?php echo e(number_format(abs($reconData->difference), 2)); ?>

            </div>
            <p class="text-sm text-secondary-500 mt-2">Record bank charges, interest, or other adjustments as journal entries to reconcile.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Transactions (Last 60 Days)</h3>
        <span class="text-sm text-secondary-500"><?php echo e($reconData->transactions->count()); ?> entries</span>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entry #</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="text-right">Debit (In)</th>
                    <th class="text-right">Credit (Out)</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $reconData->transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="text-sm"><?php echo e(\Carbon\Carbon::parse($txn->posting_date)->format('M d, Y')); ?></td>
                    <td class="font-mono text-sm text-primary-600"><?php echo e($txn->entry_number); ?></td>
                    <td class="font-mono text-sm"><?php echo e($txn->reference_number ?? '-'); ?></td>
                    <td class="text-sm"><?php echo e($txn->je_description ?? $txn->description ?? '-'); ?></td>
                    <td class="text-right font-mono"><?php echo e($txn->debit > 0 ? '₱' . number_format($txn->debit, 2) : ''); ?></td>
                    <td class="text-right font-mono"><?php echo e($txn->credit > 0 ? '₱' . number_format($txn->credit, 2) : ''); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-secondary-400 py-6">No transactions found for this period.</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if($reconData->transactions->count() > 0): ?>
            <tfoot class="bg-gray-50 font-bold">
                <tr>
                    <td colspan="4" class="text-right">Totals:</td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($reconData->transactions->sum('debit'), 2)); ?></td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($reconData->transactions->sum('credit'), 2)); ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/gl/bank-reconciliation.blade.php ENDPATH**/ ?>