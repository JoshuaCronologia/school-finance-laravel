
<?php $__env->startSection('title', 'Vendors / Payees'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $vendorCount = $vendors instanceof \Illuminate\Pagination\LengthAwarePaginator ? $vendors->total() : count($vendors);
?>

<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Vendors / Payees','subtitle' => $vendorCount . ' vendors']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Vendors / Payees','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vendorCount . ' vendors')]); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <button @click="$dispatch('open-modal', 'add-vendor')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            + Add Vendor
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


<?php if (isset($component)) { $__componentOriginale9f22847d79d6273acb27aff60f1f678 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale9f22847d79d6273acb27aff60f1f678 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-bar','data' => ['action' => ''.e(route('vendors.index')).'','method' => 'GET']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => ''.e(route('vendors.index')).'','method' => 'GET']); ?>
    <div>
        <label class="form-label">Type</label>
        <select name="vendor_type" class="form-input w-44">
            <option value="">All Types</option>
            <?php $__currentLoopData = ['supplier', 'contractor', 'utility', 'government', 'individual', 'other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($t); ?>" <?php echo e(request('vendor_type') == $t ? 'selected' : ''); ?>><?php echo e(ucfirst($t)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-input w-36">
            <option value="">All</option>
            <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Active</option>
            <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>Inactive</option>
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


<?php if (isset($component)) { $__componentOriginalc8463834ba515134d5c98b88e1a9dc03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8463834ba515134d5c98b88e1a9dc03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-table','data' => ['searchPlaceholder' => 'Search vendors...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['search-placeholder' => 'Search vendors...']); ?>
    <thead>
        <tr>
            <th>Vendor Code</th>
            <th>Name</th>
            <th>Type</th>
            <th>TIN</th>
            <th>Tax Classification</th>
            <th>Phone</th>
            <th class="text-right">Outstanding Balance</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $vatLabels = ['vatable' => 'VAT', 'non-vatable' => 'Non-VAT', 'zero-rated' => 'Zero Rated', 'tax_exempt' => 'Tax Exempt'];
        ?>
        <tr>
            <td class="font-medium text-secondary-900"><?php echo e($vendor->vendor_code ?? '-'); ?></td>
            <td class="font-medium"><?php echo e($vendor->name); ?></td>
            <td><?php echo e(ucfirst($vendor->vendor_type ?? '-')); ?></td>
            <td><?php echo e($vendor->tin ?? '-'); ?></td>
            <td>
                <span class="text-xs"><?php echo e($vatLabels[$vendor->vat_type] ?? '-'); ?></span>
                <?php if($vendor->withholding_tax_type): ?>
                    <span class="text-xs text-secondary-400">/ <?php echo e($vendor->withholding_tax_type); ?></span>
                <?php endif; ?>
            </td>
            <td><?php echo e($vendor->phone ?? '-'); ?></td>
            <td class="text-right font-medium"><?php echo e('₱' . number_format($vendor->outstanding_balance ?? 0, 2)); ?></td>
            <td><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $vendor->is_active ? 'active' : 'inactive']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vendor->is_active ? 'active' : 'inactive')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></td>
            <td>
                <button @click="$dispatch('open-modal', 'edit-vendor-<?php echo e($vendor->id); ?>')" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Edit</button>
            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="9" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                No vendors found. Click "+ Add Vendor" to create one.
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
    <?php if($vendors instanceof \Illuminate\Pagination\LengthAwarePaginator && $vendors->hasPages()): ?>
     <?php $__env->slot('footer', null, []); ?> 
        <?php echo e($vendors->withQueryString()->links()); ?>

     <?php $__env->endSlot(); ?>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc8463834ba515134d5c98b88e1a9dc03)): ?>
<?php $attributes = $__attributesOriginalc8463834ba515134d5c98b88e1a9dc03; ?>
<?php unset($__attributesOriginalc8463834ba515134d5c98b88e1a9dc03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc8463834ba515134d5c98b88e1a9dc03)): ?>
<?php $component = $__componentOriginalc8463834ba515134d5c98b88e1a9dc03; ?>
<?php unset($__componentOriginalc8463834ba515134d5c98b88e1a9dc03); ?>
<?php endif; ?>


<?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'add-vendor','title' => 'Add Vendor','maxWidth' => '4xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'add-vendor','title' => 'Add Vendor','maxWidth' => '4xl']); ?>
    <form action="<?php echo e(route('vendors.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Vendor Code <span class="text-danger-500">*</span></label>
                <input type="text" name="vendor_code" class="form-input" required placeholder="e.g., V-001">
            </div>
            <div>
                <label class="form-label">Name <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-input" required placeholder="Vendor / Company name">
            </div>
            <div>
                <label class="form-label">Type <span class="text-danger-500">*</span></label>
                <select name="vendor_type" class="form-input" required>
                    <option value="">Select Type</option>
                    <option value="supplier">Supplier</option>
                    <option value="contractor">Contractor</option>
                    <option value="utility">Utility</option>
                    <option value="government">Government</option>
                    <option value="individual">Individual</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-input" placeholder="Full name">
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" placeholder="e.g., 0917-xxx-xxxx">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" placeholder="vendor@email.com">
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-input" placeholder="Complete address">
            </div>
            <div>
                <label class="form-label">TIN</label>
                <input type="text" name="tin" class="form-input" placeholder="xxx-xxx-xxx-xxx">
            </div>
            <div>
                <label class="form-label">Tax Classification</label>
                <select name="vat_type" class="form-input">
                    <option value="">Select</option>
                    <option value="vatable">VATable (VAT Registered)</option>
                    <option value="non-vatable">Non-VAT</option>
                    <option value="zero-rated">Zero Rated</option>
                    <option value="tax_exempt">Tax Exempt</option>
                </select>
            </div>
            <div>
                <label class="form-label">ATC (Alphanumeric Tax Code)</label>
                <select name="withholding_tax_type" class="form-input">
                    <option value="">Select ATC</option>
                    <option value="WI010">WI010 - EWT Prof. fees (individual) 5%</option>
                    <option value="WI020">WI020 - EWT Prof. fees (individual) 10%</option>
                    <option value="WI100">WI100 - EWT Prof. fees (individual) 15%</option>
                    <option value="WC010">WC010 - EWT Prof. fees (corporate) 10%</option>
                    <option value="WC020">WC020 - EWT Prof. fees (corporate) 15%</option>
                    <option value="WC100">WC100 - EWT Rental (corporate) 5%</option>
                    <option value="WB010">WB010 - EWT Goods 1%</option>
                    <option value="WB020">WB020 - EWT Services 2%</option>
                    <option value="WB050">WB050 - EWT Rentals 5%</option>
                </select>
            </div>
            <div>
                <label class="form-label">Payment Terms</label>
                <select name="payment_terms_id" class="form-input">
                    <option value="">Select</option>
                    <?php $__currentLoopData = $paymentTerms ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($pt->id); ?>"><?php echo e($pt->name ?? $pt->code); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-input" placeholder="e.g., BDO, BPI">
            </div>
            <div>
                <label class="form-label">Account Name</label>
                <input type="text" name="account_name" class="form-input" placeholder="Account holder name">
            </div>
            <div>
                <label class="form-label">Account Number</label>
                <input type="text" name="account_number" class="form-input" placeholder="Bank account number">
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'add-vendor')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Vendor</button>
        </div>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>


<?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'edit-vendor-'.e($vendor->id).'','title' => 'Edit Vendor','maxWidth' => '4xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit-vendor-'.e($vendor->id).'','title' => 'Edit Vendor','maxWidth' => '4xl']); ?>
    <form action="<?php echo e(route('vendors.update', $vendor)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Vendor Code <span class="text-danger-500">*</span></label>
                <input type="text" name="vendor_code" class="form-input" value="<?php echo e($vendor->vendor_code); ?>" required>
            </div>
            <div>
                <label class="form-label">Name <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-input" value="<?php echo e($vendor->name); ?>" required>
            </div>
            <div>
                <label class="form-label">Type <span class="text-danger-500">*</span></label>
                <select name="vendor_type" class="form-input" required>
                    <option value="supplier" <?php echo e(($vendor->vendor_type ?? '') == 'supplier' ? 'selected' : ''); ?>>Supplier</option>
                    <option value="contractor" <?php echo e(($vendor->vendor_type ?? '') == 'contractor' ? 'selected' : ''); ?>>Contractor</option>
                    <option value="utility" <?php echo e(($vendor->vendor_type ?? '') == 'utility' ? 'selected' : ''); ?>>Utility</option>
                    <option value="government" <?php echo e(($vendor->vendor_type ?? '') == 'government' ? 'selected' : ''); ?>>Government</option>
                    <option value="individual" <?php echo e(($vendor->vendor_type ?? '') == 'individual' ? 'selected' : ''); ?>>Individual</option>
                    <option value="other" <?php echo e(($vendor->vendor_type ?? '') == 'other' ? 'selected' : ''); ?>>Other</option>
                </select>
            </div>
            <div>
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-input" value="<?php echo e($vendor->contact_person ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" value="<?php echo e($vendor->phone ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="<?php echo e($vendor->email ?? ''); ?>">
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-input" value="<?php echo e($vendor->address ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">TIN</label>
                <input type="text" name="tin" class="form-input" value="<?php echo e($vendor->tin ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Tax Classification</label>
                <select name="vat_type" class="form-input">
                    <option value="">Select</option>
                    <option value="vatable" <?php echo e(($vendor->vat_type ?? '') == 'vatable' ? 'selected' : ''); ?>>VATable (VAT Registered)</option>
                    <option value="non-vatable" <?php echo e(($vendor->vat_type ?? '') == 'non-vatable' ? 'selected' : ''); ?>>Non-VAT</option>
                    <option value="zero-rated" <?php echo e(($vendor->vat_type ?? '') == 'zero-rated' ? 'selected' : ''); ?>>Zero Rated</option>
                    <option value="tax_exempt" <?php echo e(($vendor->vat_type ?? '') == 'tax_exempt' ? 'selected' : ''); ?>>Tax Exempt</option>
                </select>
            </div>
            <div>
                <label class="form-label">ATC (Alphanumeric Tax Code)</label>
                <select name="withholding_tax_type" class="form-input">
                    <option value="">Select ATC</option>
                    <option value="WI010" <?php echo e(($vendor->withholding_tax_type ?? '') == 'WI010' ? 'selected' : ''); ?>>WI010 - EWT Prof. fees (indiv.) 5%</option>
                    <option value="WI020" <?php echo e(($vendor->withholding_tax_type ?? '') == 'WI020' ? 'selected' : ''); ?>>WI020 - EWT Prof. fees (indiv.) 10%</option>
                    <option value="WI100" <?php echo e(($vendor->withholding_tax_type ?? '') == 'WI100' ? 'selected' : ''); ?>>WI100 - EWT Prof. fees (indiv.) 15%</option>
                    <option value="WC010" <?php echo e(($vendor->withholding_tax_type ?? '') == 'WC010' ? 'selected' : ''); ?>>WC010 - EWT Prof. fees (corp.) 10%</option>
                    <option value="WC020" <?php echo e(($vendor->withholding_tax_type ?? '') == 'WC020' ? 'selected' : ''); ?>>WC020 - EWT Prof. fees (corp.) 15%</option>
                    <option value="WC100" <?php echo e(($vendor->withholding_tax_type ?? '') == 'WC100' ? 'selected' : ''); ?>>WC100 - EWT Rental (corp.) 5%</option>
                    <option value="WB010" <?php echo e(($vendor->withholding_tax_type ?? '') == 'WB010' ? 'selected' : ''); ?>>WB010 - EWT Goods 1%</option>
                    <option value="WB020" <?php echo e(($vendor->withholding_tax_type ?? '') == 'WB020' ? 'selected' : ''); ?>>WB020 - EWT Services 2%</option>
                    <option value="WB050" <?php echo e(($vendor->withholding_tax_type ?? '') == 'WB050' ? 'selected' : ''); ?>>WB050 - EWT Rentals 5%</option>
                </select>
            </div>
            <div>
                <label class="form-label">Payment Terms</label>
                <select name="payment_terms_id" class="form-input">
                    <option value="">Select</option>
                    <?php $__currentLoopData = $paymentTerms ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($pt->id); ?>" <?php echo e(($vendor->payment_terms_id ?? '') == $pt->id ? 'selected' : ''); ?>><?php echo e($pt->name ?? $pt->code); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-input" value="<?php echo e($vendor->bank_name ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Account Name</label>
                <input type="text" name="account_name" class="form-input" value="<?php echo e($vendor->account_name ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Account Number</label>
                <input type="text" name="account_number" class="form-input" value="<?php echo e($vendor->account_number ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="is_active" class="form-input">
                    <option value="1" <?php echo e($vendor->is_active ? 'selected' : ''); ?>>Active</option>
                    <option value="0" <?php echo e(!$vendor->is_active ? 'selected' : ''); ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'edit-vendor-<?php echo e($vendor->id); ?>')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Update Vendor</button>
        </div>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/ap/vendors/index.blade.php ENDPATH**/ ?>