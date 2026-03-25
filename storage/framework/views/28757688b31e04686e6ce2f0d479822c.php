<?php $__env->startSection('title', 'Balance Sheet'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $asOfDate = $asOfDate ?? now()->format('Y-m-d');
    $assets = $assets ?? collect();
    $liabilities = $liabilities ?? collect();
    $equity = $equity ?? collect();
    $totalAssets = $totalAssets ?? 0;
    $totalLiabilities = $totalLiabilities ?? 0;
    $totalEquity = $totalEquity ?? 0;
    $netIncome = $netIncome ?? 0;
    $totalEquityWithNI = $totalEquityWithNI ?? ($totalEquity + $netIncome);
    $isBalanced = abs($totalAssets - ($totalLiabilities + $totalEquityWithNI)) < 0.01;
?>

<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Balance Sheet','subtitle' => 'As of ' . \Carbon\Carbon::parse($asOfDate)->format('F d, Y')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Balance Sheet','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('As of ' . \Carbon\Carbon::parse($asOfDate)->format('F d, Y'))]); ?>
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
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" value="<?php echo e($asOfDate); ?>" class="form-input w-48">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </div>
    </form>
</div>


<div class="card mb-6">
    <div class="card-header bg-gray-50">
        <div class="text-center w-full">
            <h2 class="text-lg font-bold text-secondary-900">Statement of Financial Position</h2>
            <p class="text-sm text-secondary-500">As of <?php echo e(\Carbon\Carbon::parse($asOfDate)->format('F d, Y')); ?></p>
        </div>
    </div>
    <div class="card-body max-w-3xl mx-auto">
        
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Assets</h3>
            <table class="w-full text-sm">
                <tbody>
                    <?php $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="py-1 pl-6"><?php echo e($account->account_name); ?></td>
                        <td class="py-1 text-right font-mono w-40">₱<?php echo e(number_format(abs($account->balance), 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-secondary-900 font-bold">
                        <td class="py-2 pl-2">Total Assets</td>
                        <td class="py-2 text-right font-mono">₱<?php echo e(number_format($totalAssets, 2)); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Liabilities</h3>
            <table class="w-full text-sm">
                <tbody>
                    <?php $__currentLoopData = $liabilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="py-1 pl-6"><?php echo e($account->account_name); ?></td>
                        <td class="py-1 text-right font-mono w-40">₱<?php echo e(number_format(abs($account->balance), 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Total Liabilities</td>
                        <td class="py-2 text-right font-mono">₱<?php echo e(number_format($totalLiabilities, 2)); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Equity</h3>
            <table class="w-full text-sm">
                <tbody>
                    <?php $__currentLoopData = $equity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="py-1 pl-6"><?php echo e($account->account_name); ?></td>
                        <td class="py-1 text-right font-mono w-40">₱<?php echo e(number_format(abs($account->balance), 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <tr class="italic text-secondary-600">
                        <td class="py-1 pl-6">Net Income (Current Period)</td>
                        <td class="py-1 text-right font-mono w-40">₱<?php echo e(number_format($netIncome, 2)); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Total Equity</td>
                        <td class="py-2 text-right font-mono">₱<?php echo e(number_format($totalEquityWithNI, 2)); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        
        <div class="border-t-2 border-double border-secondary-900 pt-2 mb-4">
            <table class="w-full">
                <tr class="font-bold text-base">
                    <td class="py-2">Total Liabilities & Equity</td>
                    <td class="py-2 text-right font-mono w-40">₱<?php echo e(number_format($totalLiabilities + $totalEquityWithNI, 2)); ?></td>
                </tr>
            </table>
        </div>

        <div class="p-3 rounded-lg text-center text-sm font-semibold <?php echo e($isBalanced ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'); ?>">
            <?php echo e($isBalanced ? 'BALANCED' : 'UNBALANCED - Difference: ₱' . number_format(abs($totalAssets - ($totalLiabilities + $totalEquityWithNI)), 2)); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/reports/balance-sheet.blade.php ENDPATH**/ ?>