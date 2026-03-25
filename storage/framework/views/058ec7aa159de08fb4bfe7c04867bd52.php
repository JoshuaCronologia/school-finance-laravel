
<?php $__env->startSection('title', 'BIR 1601-E'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $taxableMonth = $taxableMonth ?? request('month', now()->format('Y-m'));
    $totalTaxWithheld = $totalTaxWithheld ?? 0;
    $taxCredits = $taxCredits ?? 0;
    $netTaxDue = $netTaxDue ?? ($totalTaxWithheld - $taxCredits);
    $penalties = $penalties ?? 0;
    $totalAmountDue = $totalAmountDue ?? ($netTaxDue + $penalties);
    $atcEntries = $atcEntries ?? collect();
    $atcCodesUsed = $atcCodesUsed ?? 0;
    $monthlyTrend = $monthlyTrend ?? collect();
?>

<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'BIR 1601-E','subtitle' => 'Expanded Withholding Tax Return']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'BIR 1601-E','subtitle' => 'Expanded Withholding Tax Return']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(request()->fullUrlWithQuery(['export' => 'excel'])); ?>" class="btn-secondary text-sm">Excel</a>
        <a href="<?php echo e(request()->fullUrlWithQuery(['export' => 'pdf'])); ?>" class="btn-secondary text-sm">PDF</a>
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
                <label class="form-label">Taxable Month</label>
                <input type="month" name="month" value="<?php echo e($taxableMonth); ?>" class="form-input w-48">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
            <a href="<?php echo e(request()->url()); ?>" class="btn-secondary">Clear</a>
        </div>
    </form>
</div>


<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Tax Computation</h3>
    </div>
    <div class="card-body">
        <div class="space-y-3 max-w-lg">
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-secondary-700">Total Taxes Withheld</span>
                <span class="font-mono font-semibold">₱<?php echo e(number_format($totalTaxWithheld, 2)); ?></span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-secondary-700">Less: Tax Credits / Payments</span>
                <span class="font-mono text-danger-600">(₱<?php echo e(number_format($taxCredits, 2)); ?>)</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-200 bg-blue-50 px-3 rounded">
                <span class="text-sm font-semibold text-blue-800">Net Tax Due</span>
                <span class="font-mono font-bold text-blue-800">₱<?php echo e(number_format($netTaxDue, 2)); ?></span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-secondary-700">Add: Penalties / Surcharge / Interest</span>
                <span class="font-mono text-danger-600">₱<?php echo e(number_format($penalties, 2)); ?></span>
            </div>
            <div class="flex items-center justify-between py-3 bg-primary-50 px-3 rounded-lg">
                <span class="text-sm font-bold text-primary-800">Total Amount Due</span>
                <span class="text-lg font-mono font-bold text-primary-800">₱<?php echo e(number_format($totalAmountDue, 2)); ?></span>
            </div>
        </div>
    </div>
</div>


<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Total Tax Withheld','value' => '₱' . number_format($totalTaxWithheld, 2),'color' => 'blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Tax Withheld','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($totalTaxWithheld, 2)),'color' => 'blue']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'ATC Entries','value' => number_format($atcEntries->count()),'color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'ATC Entries','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($atcEntries->count())),'color' => 'green']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'ATC Codes Used','value' => number_format($atcCodesUsed),'color' => 'purple']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'ATC Codes Used','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($atcCodesUsed)),'color' => 'purple']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
</div>


<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Breakdown by ATC Code</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ATC</th>
                    <th>Nature of Payment</th>
                    <th class="text-right">Tax Base</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Tax Withheld</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $atcEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-mono text-sm font-medium"><?php echo e($entry->atc ?? ''); ?></td>
                        <td><?php echo e($entry->nature ?? ''); ?></td>
                        <td class="text-right font-mono">₱<?php echo e(number_format($entry->tax_base ?? 0, 2)); ?></td>
                        <td class="text-right font-mono"><?php echo e(number_format($entry->rate ?? 0, 1)); ?>%</td>
                        <td class="text-right font-mono font-semibold">₱<?php echo e(number_format($entry->tax_withheld ?? 0, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center py-8 text-secondary-400">No ATC entries found for this period.</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if($atcEntries->isNotEmpty()): ?>
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td colspan="2" class="text-right">Total</td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($atcEntries->sum('tax_base'), 2)); ?></td>
                    <td></td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($atcEntries->sum('tax_withheld'), 2)); ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Yearly Trend</h3>
    </div>
    <div class="card-body" x-data="{}" x-init="
        if (typeof Chart !== 'undefined') {
            new Chart($refs.trendChart.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                    datasets: [{
                        label: 'Tax Withheld',
                        data: <?php echo json_encode($monthlyTrend->pluck('amount')->toArray() ?: array_fill(0, 12, 0)); ?>,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => '₱' + ctx.parsed.y.toLocaleString() } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => '₱' + (v/1000).toFixed(0) + 'K' } } }
                }
            });
        }
    ">
        <canvas x-ref="trendChart" height="100"></canvas>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/tax/bir-1601e.blade.php ENDPATH**/ ?>