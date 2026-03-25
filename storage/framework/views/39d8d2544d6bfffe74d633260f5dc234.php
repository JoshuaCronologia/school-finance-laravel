<?php $__env->startSection('title', 'Cash Flow Statement'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $dateFrom = $dateFrom ?? now()->startOfYear()->format('Y-m-d');
    $dateTo = $dateTo ?? now()->format('Y-m-d');
?>

<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Statement of Cash Flows','subtitle' => 'For the period ' . \Carbon\Carbon::parse($dateFrom)->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($dateTo)->format('M d, Y')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Statement of Cash Flows','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('For the period ' . \Carbon\Carbon::parse($dateFrom)->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($dateTo)->format('M d, Y'))]); ?>
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
            <h2 class="text-lg font-bold text-secondary-900">Statement of Cash Flows</h2>
            <p class="text-sm text-secondary-500">For the period <?php echo e(\Carbon\Carbon::parse($dateFrom)->format('F d, Y')); ?> to <?php echo e(\Carbon\Carbon::parse($dateTo)->format('F d, Y')); ?></p>
        </div>
    </div>
    <div class="card-body max-w-3xl mx-auto">

        
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Cash Flows from Operating Activities</h3>
            <table class="w-full text-sm">
                <tbody>
                    <tr>
                        <td class="py-1.5 pl-6">Net Income</td>
                        <td class="py-1.5 text-right font-mono w-40">₱<?php echo e(number_format($netIncome ?? 0, 2)); ?></td>
                    </tr>
                    <tr class="text-secondary-500 italic">
                        <td class="py-1 pl-6 text-xs" colspan="2">Adjustments for non-cash items:</td>
                    </tr>
                    <tr>
                        <td class="py-1.5 pl-10">Cash received from customers</td>
                        <td class="py-1.5 text-right font-mono w-40">₱<?php echo e(number_format($cashFromCustomers ?? 0, 2)); ?></td>
                    </tr>
                    <tr>
                        <td class="py-1.5 pl-10">Cash paid to suppliers</td>
                        <td class="py-1.5 text-right font-mono w-40 text-danger-600">(₱<?php echo e(number_format(abs($cashToSuppliers ?? 0), 2)); ?>)</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Net Cash from Operating Activities</td>
                        <td class="py-2 text-right font-mono <?php echo e(($operatingCashFlow ?? 0) >= 0 ? '' : 'text-danger-600'); ?>">₱<?php echo e(number_format($operatingCashFlow ?? 0, 2)); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Cash Flows from Investing Activities</h3>
            <table class="w-full text-sm">
                <tbody>
                    <tr class="text-secondary-400 italic">
                        <td class="py-1.5 pl-6" colspan="2">No investing activities recorded for this period.</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Net Cash from Investing Activities</td>
                        <td class="py-2 text-right font-mono w-40">₱0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Cash Flows from Financing Activities</h3>
            <table class="w-full text-sm">
                <tbody>
                    <tr class="text-secondary-400 italic">
                        <td class="py-1.5 pl-6" colspan="2">No financing activities recorded for this period.</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Net Cash from Financing Activities</td>
                        <td class="py-2 text-right font-mono w-40">₱0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        
        <div class="border-t-2 border-secondary-900 pt-3 mb-4">
            <table class="w-full text-sm">
                <tr class="font-semibold">
                    <td class="py-1.5">Net Increase (Decrease) in Cash</td>
                    <td class="py-1.5 text-right font-mono w-40 <?php echo e(($netCashChange ?? 0) >= 0 ? '' : 'text-danger-600'); ?>">₱<?php echo e(number_format($netCashChange ?? 0, 2)); ?></td>
                </tr>
                <tr>
                    <td class="py-1.5">Cash at Beginning of Period</td>
                    <td class="py-1.5 text-right font-mono w-40">₱<?php echo e(number_format($beginningCash ?? 0, 2)); ?></td>
                </tr>
            </table>
        </div>
        <div class="border-t-2 border-double border-secondary-900 pt-2">
            <table class="w-full">
                <tr class="font-bold text-base">
                    <td class="py-2">Cash at End of Period</td>
                    <td class="py-2 text-right font-mono w-40">₱<?php echo e(number_format($endingCash ?? 0, 2)); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/reports/cash-flow.blade.php ENDPATH**/ ?>