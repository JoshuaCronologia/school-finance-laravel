
<?php $__env->startSection('title', 'Settings'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'System Settings','subtitle' => 'Configure application preferences and defaults']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'System Settings','subtitle' => 'Configure application preferences and defaults']); ?>
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

<div x-data="{ activeTab: '<?php echo e(request('tab', 'general')); ?>' }">
    
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex gap-6 -mb-px">
            <?php $__currentLoopData = ['general' => 'General', 'approval' => 'Approval', 'budget' => 'Budget', 'tax' => 'Tax', 'numbering' => 'Numbering']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button @click="activeTab = '<?php echo e($tab); ?>'"
                    :class="activeTab === '<?php echo e($tab); ?>' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-gray-300'"
                    class="py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                <?php echo e($label); ?>

            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
    </div>

    
    <div x-show="activeTab === 'general'" x-transition>
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">General Information</h3>
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('settings.update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="section" value="general">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">School Name <span class="text-danger-500">*</span></label>
                            <input type="text" name="school_name" class="form-input" value="<?php echo e($settings['school_name'] ?? 'OrangeApps Academy'); ?>" required>
                        </div>
                        <div>
                            <label class="form-label">TIN</label>
                            <input type="text" name="tin" class="form-input" value="<?php echo e($settings['tin'] ?? ''); ?>" placeholder="xxx-xxx-xxx-xxx">
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-input" value="<?php echo e($settings['address'] ?? ''); ?>" placeholder="Complete school address">
                        </div>
                        <div>
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-input" value="<?php echo e($settings['phone'] ?? ''); ?>" placeholder="(02) xxxx-xxxx">
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" value="<?php echo e($settings['email'] ?? ''); ?>" placeholder="finance@school.edu.ph">
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="btn-primary">Save General Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div x-show="activeTab === 'approval'" x-transition>
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">Approval Thresholds</h3>
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('settings.update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="section" value="approval">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">Level 1 Threshold (Up to)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400">₱</span>
                                    <input type="number" name="approval_level_1" class="form-input pl-8" value="<?php echo e($settings['approval_level_1'] ?? 50000); ?>" step="0.01">
                                </div>
                                <p class="text-xs text-secondary-400 mt-1">Department Head approval</p>
                            </div>
                            <div>
                                <label class="form-label">Level 2 Threshold (Up to)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400">₱</span>
                                    <input type="number" name="approval_level_2" class="form-input pl-8" value="<?php echo e($settings['approval_level_2'] ?? 200000); ?>" step="0.01">
                                </div>
                                <p class="text-xs text-secondary-400 mt-1">Finance Director approval</p>
                            </div>
                            <div>
                                <label class="form-label">Level 3 Threshold (Above Level 2)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400">₱</span>
                                    <input type="number" name="approval_level_3" class="form-input pl-8" value="<?php echo e($settings['approval_level_3'] ?? 500000); ?>" step="0.01">
                                </div>
                                <p class="text-xs text-secondary-400 mt-1">School Administrator approval</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="btn-primary">Save Approval Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div x-show="activeTab === 'budget'" x-transition>
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">Budget Policy</h3>
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('settings.update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="section" value="budget">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Budget Policy</label>
                            <select name="budget_policy" class="form-input">
                                <option value="strict" <?php echo e(($settings['budget_policy'] ?? '') == 'strict' ? 'selected' : ''); ?>>Strict - Block over-budget transactions</option>
                                <option value="warning" <?php echo e(($settings['budget_policy'] ?? 'warning') == 'warning' ? 'selected' : ''); ?>>Warning - Allow with warning</option>
                                <option value="none" <?php echo e(($settings['budget_policy'] ?? '') == 'none' ? 'selected' : ''); ?>>None - No budget checking</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Overspend Tolerance (%)</label>
                            <input type="number" name="overspend_tolerance" class="form-input" value="<?php echo e($settings['overspend_tolerance'] ?? 10); ?>" min="0" max="100" step="1">
                            <p class="text-xs text-secondary-400 mt-1">Percentage above budget allowed before blocking</p>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="btn-primary">Save Budget Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div x-show="activeTab === 'tax'" x-transition>
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">Tax Rates</h3>
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('settings.update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="section" value="tax">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Default VAT Rate (%)</label>
                            <input type="number" name="default_vat_rate" class="form-input" value="<?php echo e($settings['default_vat_rate'] ?? 12); ?>" step="0.01" min="0">
                        </div>
                        <div>
                            <label class="form-label">WHT Professional (WC010) %</label>
                            <input type="number" name="wht_professional" class="form-input" value="<?php echo e($settings['wht_professional'] ?? 10); ?>" step="0.01" min="0">
                        </div>
                        <div>
                            <label class="form-label">WHT Rental (WC020) %</label>
                            <input type="number" name="wht_rental" class="form-input" value="<?php echo e($settings['wht_rental'] ?? 5); ?>" step="0.01" min="0">
                        </div>
                        <div>
                            <label class="form-label">WHT Services (WC050) %</label>
                            <input type="number" name="wht_services" class="form-input" value="<?php echo e($settings['wht_services'] ?? 2); ?>" step="0.01" min="0">
                        </div>
                        <div>
                            <label class="form-label">WHT Supplies (WC100) %</label>
                            <input type="number" name="wht_supplies" class="form-input" value="<?php echo e($settings['wht_supplies'] ?? 1); ?>" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="btn-primary">Save Tax Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div x-show="activeTab === 'numbering'" x-transition>
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">Document Numbering Prefixes</h3>
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('settings.update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="section" value="numbering">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Disbursement Request (DR)</label>
                            <input type="text" name="prefix_dr" class="form-input" value="<?php echo e($settings['prefix_dr'] ?? 'DR-'); ?>" placeholder="e.g., DR-">
                        </div>
                        <div>
                            <label class="form-label">Payment Voucher (PV)</label>
                            <input type="text" name="prefix_pv" class="form-input" value="<?php echo e($settings['prefix_pv'] ?? 'PV-'); ?>" placeholder="e.g., PV-">
                        </div>
                        <div>
                            <label class="form-label">Official Receipt (OR)</label>
                            <input type="text" name="prefix_or" class="form-input" value="<?php echo e($settings['prefix_or'] ?? 'OR-'); ?>" placeholder="e.g., OR-">
                        </div>
                        <div>
                            <label class="form-label">Journal Entry (JE)</label>
                            <input type="text" name="prefix_je" class="form-input" value="<?php echo e($settings['prefix_je'] ?? 'JE-'); ?>" placeholder="e.g., JE-">
                        </div>
                        <div>
                            <label class="form-label">Bill (BILL)</label>
                            <input type="text" name="prefix_bill" class="form-input" value="<?php echo e($settings['prefix_bill'] ?? 'BILL-'); ?>" placeholder="e.g., BILL-">
                        </div>
                        <div>
                            <label class="form-label">Invoice (INV)</label>
                            <input type="text" name="prefix_inv" class="form-input" value="<?php echo e($settings['prefix_inv'] ?? 'INV-'); ?>" placeholder="e.g., INV-">
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="btn-primary">Save Numbering Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/system/settings.blade.php ENDPATH**/ ?>