
<?php $__env->startSection('title', 'Audit Trail'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Audit Trail','subtitle' => 'Track all system changes and user actions']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Audit Trail','subtitle' => 'Track all system changes and user actions']); ?>
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
        <form action="<?php echo e(route('audit-trail')); ?>" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="form-input" placeholder="Search by user, record, description...">
            </div>
            <div>
                <label class="form-label">Module</label>
                <select name="module" class="form-input w-44">
                    <option value="">All Modules</option>
                    <?php $__currentLoopData = ['budget', 'bills', 'disbursements', 'invoices', 'collections', 'journal_entries', 'vendors', 'customers', 'accounts', 'settings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m); ?>" <?php echo e(request('module') == $m ? 'selected' : ''); ?>><?php echo e(ucfirst(str_replace('_', ' ', $m))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="form-label">Action</label>
                <select name="action" class="form-input w-40">
                    <option value="">All Actions</option>
                    <?php $__currentLoopData = ['created', 'updated', 'deleted', 'approved', 'rejected', 'posted', 'voided', 'reversed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($a); ?>" <?php echo e(request('action') == $a ? 'selected' : ''); ?>><?php echo e(ucfirst($a)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="form-input w-40">
            </div>
            <div>
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="form-input w-40">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="<?php echo e(route('audit-trail')); ?>" class="btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>


<div class="space-y-4">
    <?php $__empty_1 = true; $__currentLoopData = $auditLogs ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
        $actionColors = [
            'created' => ['bg' => 'bg-primary-100', 'text' => 'text-primary-600', 'icon' => 'M12 4.5v15m7.5-7.5h-15'],
            'updated' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10'],
            'approved' => ['bg' => 'bg-success-100', 'text' => 'text-success-600', 'icon' => 'm4.5 12.75 6 6 9-13.5'],
            'rejected' => ['bg' => 'bg-danger-100', 'text' => 'text-danger-500', 'icon' => 'M6 18 18 6M6 6l12 12'],
            'posted' => ['bg' => 'bg-success-100', 'text' => 'text-success-600', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            'voided' => ['bg' => 'bg-danger-100', 'text' => 'text-danger-500', 'icon' => 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636'],
            'deleted' => ['bg' => 'bg-danger-100', 'text' => 'text-danger-500', 'icon' => 'm14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0'],
            'reversed' => ['bg' => 'bg-warning-100', 'text' => 'text-warning-600', 'icon' => 'M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3'],
        ];
        $actionKey = strtolower($log->action ?? 'updated');
        $colors = $actionColors[$actionKey] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'icon' => 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z'];
    ?>
    <div class="card">
        <div class="card-body">
            <div class="flex items-start gap-4">
                
                <div class="flex-shrink-0 w-10 h-10 rounded-full <?php echo e($colors['bg']); ?> flex items-center justify-center">
                    <svg class="w-5 h-5 <?php echo e($colors['text']); ?>" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($colors['icon']); ?>" /></svg>
                </div>

                
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold text-secondary-900">
                            <?php echo e($log->user->name ?? $log->user_name ?? 'System'); ?>

                            <span class="font-normal text-secondary-600"><?php echo e($log->action ?? 'updated'); ?></span>
                            <?php echo e(ucfirst(str_replace('_', ' ', $log->module ?? ''))); ?>

                            <?php if($log->record_id): ?>
                                <span class="text-primary-600">#<?php echo e($log->record_id); ?></span>
                            <?php endif; ?>
                        </p>
                        <span class="badge badge-neutral text-xs"><?php echo e(ucfirst(str_replace('_', ' ', $log->module ?? ''))); ?></span>
                    </div>
                    <p class="text-xs text-secondary-400 mt-1"><?php echo e(\Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A')); ?> &bull; <?php echo e(\Carbon\Carbon::parse($log->created_at)->diffForHumans()); ?></p>

                    
                    <?php if($log->old_values || $log->new_values): ?>
                    <div class="mt-3 bg-gray-50 rounded-lg p-3 text-xs">
                        <?php if($log->old_values && $log->new_values): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <?php
                                    $oldVals = is_string($log->old_values) ? json_decode($log->old_values, true) : (array)$log->old_values;
                                    $newVals = is_string($log->new_values) ? json_decode($log->new_values, true) : (array)$log->new_values;
                                ?>
                                <?php $__currentLoopData = $newVals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $newVal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-secondary-600"><?php echo e(ucfirst(str_replace('_', ' ', $key))); ?>:</span>
                                    <span class="text-danger-500 line-through"><?php echo e($oldVals[$key] ?? '-'); ?></span>
                                    <svg class="w-3 h-3 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                    <span class="text-success-600"><?php echo e($newVal ?? '-'); ?></span>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="card">
        <div class="card-body text-center py-16">
            <svg class="w-12 h-12 mx-auto mb-4 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            <h3 class="text-lg font-medium text-secondary-700 mb-1">No Audit Logs</h3>
            <p class="text-secondary-400">No audit trail entries found matching your filters.</p>
        </div>
    </div>
    <?php endif; ?>
</div>


<?php if(($auditLogs ?? collect()) instanceof \Illuminate\Pagination\LengthAwarePaginator && $auditLogs->hasPages()): ?>
<div class="mt-6">
    <?php echo e($auditLogs->withQueryString()->links()); ?>

</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/system/audit-trail.blade.php ENDPATH**/ ?>