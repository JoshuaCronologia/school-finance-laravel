<?php $__env->startSection('title', 'Income Statement'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $totalRevenue = $totalRevenue ?? 0;
    $totalExpenses = $totalExpenses ?? 0;
    $netIncome = $netIncome ?? ($totalRevenue - $totalExpenses);
    $revenueAccounts = $revenueAccounts ?? collect();
    $expenseAccounts = $expenseAccounts ?? collect();
    $netIncomeMargin = $netIncomeMargin ?? ($totalRevenue > 0 ? ($netIncome / $totalRevenue * 100) : 0);
    $dateFrom = $dateFrom ?? now()->startOfYear()->format('Y-m-d');
    $dateTo = $dateTo ?? now()->format('Y-m-d');
?>

<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Income Statement','subtitle' => 'For the period ' . \Carbon\Carbon::parse($dateFrom)->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($dateTo)->format('M d, Y')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Income Statement','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('For the period ' . \Carbon\Carbon::parse($dateFrom)->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($dateTo)->format('M d, Y'))]); ?>
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
                <label class="form-label">From</label>
                <input type="date" name="date_from" value="<?php echo e($dateFrom); ?>" class="form-input w-44">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="date_to" value="<?php echo e($dateTo); ?>" class="form-input w-44">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </div>
    </form>
</div>


<div class="card mb-6">
    <div class="card-header bg-gray-50">
        <div class="text-center w-full">
            <h2 class="text-lg font-bold text-secondary-900">Statement of Income</h2>
            <p class="text-sm text-secondary-500">For the period <?php echo e(\Carbon\Carbon::parse($dateFrom)->format('F d, Y')); ?> to <?php echo e(\Carbon\Carbon::parse($dateTo)->format('F d, Y')); ?></p>
        </div>
    </div>
    <div class="card-body max-w-3xl mx-auto">
        
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Revenue</h3>
            <table class="w-full text-sm">
                <tbody>
                    <?php $__currentLoopData = $revenueAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='<?php echo e(route('reports.general-ledger')); ?>?account_id=<?php echo e($account->id); ?>&date_from=<?php echo e($dateFrom); ?>&date_to=<?php echo e($dateTo); ?>'">
                        <td class="py-1.5 pl-6">
                            <span class="text-primary-600 hover:underline"><?php echo e($account->account_name); ?></span>
                        </td>
                        <td class="py-1.5 text-right font-mono w-40">₱<?php echo e(number_format(abs($account->balance), 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Total Revenue</td>
                        <td class="py-2 text-right font-mono">₱<?php echo e(number_format($totalRevenue, 2)); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Expenses</h3>
            <table class="w-full text-sm">
                <tbody>
                    <?php $__currentLoopData = $expenseAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='<?php echo e(route('reports.general-ledger')); ?>?account_id=<?php echo e($account->id); ?>&date_from=<?php echo e($dateFrom); ?>&date_to=<?php echo e($dateTo); ?>'">
                        <td class="py-1.5 pl-6">
                            <span class="text-primary-600 hover:underline"><?php echo e($account->account_name); ?></span>
                        </td>
                        <td class="py-1.5 text-right font-mono w-40">₱<?php echo e(number_format(abs($account->balance), 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Total Expenses</td>
                        <td class="py-2 text-right font-mono">₱<?php echo e(number_format($totalExpenses, 2)); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        
        <div class="border-t-2 border-double border-secondary-900 pt-2 mb-4">
            <table class="w-full">
                <tr class="font-bold text-base">
                    <td class="py-2 <?php echo e($netIncome >= 0 ? 'text-green-800' : 'text-red-800'); ?>">Net Income</td>
                    <td class="py-2 text-right font-mono w-40 <?php echo e($netIncome >= 0 ? 'text-green-800' : 'text-red-800'); ?>">₱<?php echo e(number_format($netIncome, 2)); ?></td>
                </tr>
            </table>
        </div>

        <p class="text-xs text-secondary-400 text-center">Click on any account name to view its General Ledger detail.</p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/reports/income-statement.blade.php ENDPATH**/ ?>