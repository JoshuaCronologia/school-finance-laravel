<?php $__env->startSection('title', 'General Ledger'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $accounts = $accounts ?? collect();
    $allAccounts = $allAccounts ?? collect();
?>

<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'General Ledger Report','subtitle' => 'Detailed transaction history by account (T-Account format)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'General Ledger Report','subtitle' => 'Detailed transaction history by account (T-Account format)']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <button onclick="window.print()" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" /></svg>
            Print
        </button>
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


<?php if (isset($component)) { $__componentOriginale9f22847d79d6273acb27aff60f1f678 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale9f22847d79d6273acb27aff60f1f678 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-bar','data' => ['action' => ''.e(route('reports.general-ledger')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => ''.e(route('reports.general-ledger')).'']); ?>
    <div>
        <label class="form-label">Account</label>
        <select name="account_id" class="form-input w-64">
            <option value="">All Accounts</option>
            <?php $__currentLoopData = $allAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($acct->id); ?>" <?php echo e(($accountId ?? '') == $acct->id ? 'selected' : ''); ?>>
                    <?php echo e($acct->account_code); ?> - <?php echo e($acct->account_name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
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


<?php
    $grandTotalDebit = 0;
    $grandTotalCredit = 0;
?>

<?php $__empty_1 = true; $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
        $grandTotalDebit += $account->total_debit;
        $grandTotalCredit += $account->total_credit;
    ?>
    <div class="card mb-6">
        
        <div class="card-header bg-gray-50">
            <div class="flex items-center justify-between w-full">
                <div>
                    <h3 class="text-sm font-bold text-secondary-900"><?php echo e($account->account_code); ?> - <?php echo e($account->account_name); ?></h3>
                    <p class="text-xs text-secondary-500 mt-0.5"><?php echo e(ucfirst($account->account_type)); ?> | Normal Balance: <?php echo e(ucfirst($account->normal_balance)); ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-secondary-500">Ending Balance</p>
                    <p class="text-lg font-mono font-bold <?php echo e($account->ending_balance < 0 ? 'text-danger-600' : 'text-secondary-900'); ?>">
                        <?php echo e($account->ending_balance < 0 ? '(' . '₱' . number_format(abs($account->ending_balance), 2) . ')' : '₱' . number_format($account->ending_balance, 2)); ?>

                    </p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-28">Date</th>
                        <th class="w-32">Entry #</th>
                        <th>Description</th>
                        <th class="w-28">Reference</th>
                        <th class="text-right w-32">Debit</th>
                        <th class="text-right w-32">Credit</th>
                        <th class="text-right w-36">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <tr class="bg-blue-50">
                        <td colspan="4" class="text-sm font-semibold text-blue-800">Opening Balance (as of <?php echo e(\Carbon\Carbon::parse($dateFrom)->format('M d, Y')); ?>)</td>
                        <td class="text-right font-mono"></td>
                        <td class="text-right font-mono"></td>
                        <td class="text-right font-mono font-bold text-blue-800">₱<?php echo e(number_format($account->opening_balance, 2)); ?></td>
                    </tr>

                    
                    <?php $runningBalance = $account->opening_balance; ?>
                    <?php $__currentLoopData = $account->transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            if ($account->normal_balance === 'debit') {
                                $runningBalance += ($txn->debit ?? 0) - ($txn->credit ?? 0);
                            } else {
                                $runningBalance += ($txn->credit ?? 0) - ($txn->debit ?? 0);
                            }
                        ?>
                        <tr>
                            <td class="text-sm"><?php echo e(\Carbon\Carbon::parse($txn->posting_date ?? $txn->entry_date)->format('M d, Y')); ?></td>
                            <td class="font-mono text-sm text-primary-600"><?php echo e($txn->entry_number ?? ''); ?></td>
                            <td class="text-sm"><?php echo e($txn->je_description ?? $txn->description ?? ''); ?></td>
                            <td class="font-mono text-sm text-secondary-500"><?php echo e($txn->reference_number ?? ''); ?></td>
                            <td class="text-right font-mono"><?php echo e(($txn->debit ?? 0) > 0 ? '₱' . number_format($txn->debit, 2) : ''); ?></td>
                            <td class="text-right font-mono"><?php echo e(($txn->credit ?? 0) > 0 ? '₱' . number_format($txn->credit, 2) : ''); ?></td>
                            <td class="text-right font-mono font-semibold">₱<?php echo e(number_format($runningBalance, 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>

                
                <tfoot>
                    <tr class="bg-gray-100 font-bold border-t-2 border-gray-400">
                        <td colspan="4" class="text-right text-sm text-secondary-700">Account Totals:</td>
                        <td class="text-right font-mono text-secondary-900">₱<?php echo e(number_format($account->total_debit, 2)); ?></td>
                        <td class="text-right font-mono text-secondary-900">₱<?php echo e(number_format($account->total_credit, 2)); ?></td>
                        <td class="text-right font-mono text-secondary-900">₱<?php echo e(number_format($account->ending_balance, 2)); ?></td>
                    </tr>
                    <tr class="bg-gray-50 text-xs text-secondary-500">
                        <td colspan="4" class="text-right">Net Movement:</td>
                        <td colspan="2" class="text-center font-mono font-semibold <?php echo e(($account->total_debit - $account->total_credit) >= 0 ? 'text-secondary-700' : 'text-danger-600'); ?>">
                            <?php $netMovement = $account->total_debit - $account->total_credit; ?>
                            <?php echo e($netMovement >= 0 ? 'Dr' : 'Cr'); ?> ₱<?php echo e(number_format(abs($netMovement), 2)); ?>

                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="card">
        <div class="card-body text-center py-12">
            <svg class="w-16 h-16 mx-auto mb-4 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
            <h3 class="text-lg font-semibold text-secondary-600 mb-1">No Ledger Data</h3>
            <p class="text-secondary-400">Select an account and date range, then click Filter to view the general ledger.</p>
        </div>
    </div>
<?php endif; ?>


<?php if($accounts->count() > 1): ?>
<div class="card">
    <div class="card-header bg-primary-50">
        <h3 class="card-title text-primary-800">Grand Total - All Accounts</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="bg-primary-100">
                    <th class="text-right" colspan="4"><?php echo e($accounts->count()); ?> Accounts</th>
                    <th class="text-right w-32">Total Debit</th>
                    <th class="text-right w-32">Total Credit</th>
                    <th class="text-right w-36">Difference</th>
                </tr>
            </thead>
            <tbody>
                <tr class="font-bold text-lg">
                    <td colspan="4" class="text-right text-secondary-700">Grand Total:</td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($grandTotalDebit, 2)); ?></td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($grandTotalCredit, 2)); ?></td>
                    <td class="text-right font-mono <?php echo e(round($grandTotalDebit - $grandTotalCredit, 2) == 0 ? 'text-success-600' : 'text-danger-600'); ?>">
                        <?php if(round($grandTotalDebit - $grandTotalCredit, 2) == 0): ?>
                            ₱0.00 (Balanced)
                        <?php else: ?>
                            ₱<?php echo e(number_format(abs($grandTotalDebit - $grandTotalCredit), 2)); ?>

                            (<?php echo e($grandTotalDebit > $grandTotalCredit ? 'Dr' : 'Cr'); ?>)
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/reports/general-ledger.blade.php ENDPATH**/ ?>