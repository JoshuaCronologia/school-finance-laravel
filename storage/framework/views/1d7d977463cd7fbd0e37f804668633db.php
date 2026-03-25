
<?php $__env->startSection('title', 'Monthly Variance'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $monthlyData = $monthlyData ?? collect();
    $months = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
?>

<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Monthly Variance Analysis','subtitle' => 'Compare budget versus actual spending by month']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Monthly Variance Analysis','subtitle' => 'Compare budget versus actual spending by month']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(request()->fullUrlWithQuery(['export' => 'excel'])); ?>" class="btn-secondary text-sm">Excel</a>
        <a href="<?php echo e(request()->fullUrlWithQuery(['export' => 'pdf'])); ?>" class="btn-secondary text-sm">PDF</a>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-bar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div>
        <label class="form-label">School Year</label>
        <select name="school_year" class="form-input w-40">
            <option value="2025-2026" <?php echo e(request('school_year', '2025-2026') == '2025-2026' ? 'selected' : ''); ?>>2025-2026</option>
            <option value="2024-2025" <?php echo e(request('school_year') == '2024-2025' ? 'selected' : ''); ?>>2024-2025</option>
        </select>
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


<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Monthly Trend</h3>
    </div>
    <div class="card-body">
        <canvas id="trendChart" height="100"></canvas>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Monthly Details</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="text-right">Budget</th>
                    <th class="text-right">Actual</th>
                    <th class="text-right">Variance</th>
                    <th class="text-right">Variance %</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $monthlyData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $variance = ($month->budget ?? 0) - ($month->actual ?? 0);
                        $variancePct = ($month->budget ?? 0) > 0 ? ($variance / $month->budget * 100) : 0;
                        $isOver = $variance < 0;
                    ?>
                    <tr>
                        <td class="font-medium"><?php echo e($months[$index] ?? $month->month_name ?? ''); ?></td>
                        <td class="text-right font-mono">₱<?php echo e(number_format($month->budget ?? 0, 2)); ?></td>
                        <td class="text-right font-mono">₱<?php echo e(number_format($month->actual ?? 0, 2)); ?></td>
                        <td class="text-right font-mono font-semibold <?php echo e($isOver ? 'text-danger-600' : 'text-success-600'); ?>">
                            <?php echo e($isOver ? '-' : ''); ?>₱<?php echo e(number_format(abs($variance), 2)); ?>

                        </td>
                        <td class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($isOver ? 'bg-danger-50 text-danger-700' : 'bg-success-50 text-success-700'); ?>">
                                <?php echo e($isOver ? '' : '+'); ?><?php echo e(number_format($variancePct, 1)); ?>%
                            </span>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="font-medium"><?php echo e($monthName); ?></td>
                            <td class="text-right font-mono text-secondary-300">₱0.00</td>
                            <td class="text-right font-mono text-secondary-300">₱0.00</td>
                            <td class="text-right font-mono text-secondary-300">₱0.00</td>
                            <td class="text-right"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-50 text-secondary-400">0.0%</span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </tbody>
            <?php if($monthlyData->isNotEmpty()): ?>
            <tfoot class="bg-gray-50 font-semibold">
                <?php
                    $totBudget = $monthlyData->sum('budget');
                    $totActual = $monthlyData->sum('actual');
                    $totVariance = $totBudget - $totActual;
                ?>
                <tr>
                    <td>Total</td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($totBudget, 2)); ?></td>
                    <td class="text-right font-mono">₱<?php echo e(number_format($totActual, 2)); ?></td>
                    <td class="text-right font-mono <?php echo e($totVariance < 0 ? 'text-danger-600' : 'text-success-600'); ?>">₱<?php echo e(number_format(abs($totVariance), 2)); ?></td>
                    <td class="text-right font-mono"><?php echo e($totBudget > 0 ? number_format($totVariance / $totBudget * 100, 1) : '0.0'); ?>%</td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var canvas = document.getElementById('trendChart');
    if (!canvas || typeof Chart === 'undefined') return;

    new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [
                {
                    label: 'Budget',
                    data: <?php echo json_encode($monthlyData->pluck('budget')->values()->toArray() ?: array_fill(0, 12, 0)); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Actual',
                    data: <?php echo json_encode($monthlyData->pluck('actual')->values()->toArray() ?: array_fill(0, 12, 0)); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': \u20B1' + ctx.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) {
                            return '\u20B1' + (v / 1000).toFixed(0) + 'K';
                        }
                    }
                }
            }
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/reports/monthly-variance.blade.php ENDPATH**/ ?>