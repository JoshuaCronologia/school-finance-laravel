
<?php $__env->startSection('title', 'AR Invoices / Charges'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $invoiceCount = $invoices instanceof \Illuminate\Pagination\LengthAwarePaginator ? $invoices->total() : count($invoices);
    $totalInvoiced = collect($invoices instanceof \Illuminate\Pagination\LengthAwarePaginator ? $invoices->items() : $invoices)->sum('net_amount');
    $totalCollected = collect($invoices instanceof \Illuminate\Pagination\LengthAwarePaginator ? $invoices->items() : $invoices)->sum('amount_paid');
    $totalOutstanding = $totalInvoiced - $totalCollected;
?>

<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'AR Invoices / Charges','subtitle' => $invoiceCount . ' invoices']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'AR Invoices / Charges','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoiceCount . ' invoices')]); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <button @click="$dispatch('open-modal', 'new-invoice')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            + New Invoice
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


<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Total Invoiced','value' => '₱' . number_format($totalInvoiced, 2),'color' => 'blue','icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Invoiced','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($totalInvoiced, 2)),'color' => 'blue','icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Total Collected','value' => '₱' . number_format($totalCollected, 2),'color' => 'green','icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Collected','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($totalCollected, 2)),'color' => 'green','icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Outstanding','value' => '₱' . number_format($totalOutstanding, 2),'color' => 'red','icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Outstanding','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($totalOutstanding, 2)),'color' => 'red','icon' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>']); ?>
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


<?php if (isset($component)) { $__componentOriginale9f22847d79d6273acb27aff60f1f678 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale9f22847d79d6273acb27aff60f1f678 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-bar','data' => ['action' => ''.e(route('ar.invoices.index')).'','method' => 'GET']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => ''.e(route('ar.invoices.index')).'','method' => 'GET']); ?>
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-input w-44">
            <option value="">All Status</option>
            <?php $__currentLoopData = ['draft', 'posted', 'partially_paid', 'paid', 'overdue', 'voided']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s); ?>" <?php echo e(request('status') == $s ? 'selected' : ''); ?>><?php echo e(ucfirst(str_replace('_', ' ', $s))); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="form-label">School Year</label>
        <select name="school_year" class="form-input w-40">
            <option value="">All Years</option>
            <?php $__currentLoopData = $schoolYears ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($sy); ?>" <?php echo e(request('school_year') == $sy ? 'selected' : ''); ?>><?php echo e($sy); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-table','data' => ['searchPlaceholder' => 'Search invoices...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['search-placeholder' => 'Search invoices...']); ?>
    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Date</th>
            <th>Customer</th>
            <th>School Year</th>
            <th>Semester</th>
            <th>Description</th>
            <th class="text-right">Gross</th>
            <th class="text-right">Discount</th>
            <th class="text-right">Net</th>
            <th class="text-right">Paid</th>
            <th class="text-right">Balance</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td class="font-medium">
                <a href="#" @click.prevent="$dispatch('open-modal', 'edit-invoice-<?php echo e($invoice->id); ?>')" class="text-primary-600 hover:text-primary-700 hover:underline">
                    <?php echo e($invoice->invoice_number); ?>

                </a>
            </td>
            <td><?php echo e(\Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y')); ?></td>
            <td><?php echo e($invoice->customer->name ?? $invoice->customer_name ?? '-'); ?></td>
            <td><?php echo e($invoice->school_year ?? '-'); ?></td>
            <td><?php echo e($invoice->semester ?? '-'); ?></td>
            <td class="max-w-xs truncate"><?php echo e($invoice->description ?? '-'); ?></td>
            <td class="text-right"><?php echo e('₱' . number_format($invoice->gross_amount ?? 0, 2)); ?></td>
            <td class="text-right"><?php echo e('₱' . number_format($invoice->discount_amount ?? 0, 2)); ?></td>
            <td class="text-right font-medium"><?php echo e('₱' . number_format($invoice->net_amount ?? 0, 2)); ?></td>
            <td class="text-right"><?php echo e('₱' . number_format($invoice->amount_paid ?? 0, 2)); ?></td>
            <td class="text-right font-medium <?php echo e(($invoice->balance ?? 0) > 0 ? 'text-danger-500' : ''); ?>"><?php echo e('₱' . number_format($invoice->balance ?? 0, 2)); ?></td>
            <td><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $invoice->status ?? 'draft']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->status ?? 'draft')]); ?>
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
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="12" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                No invoices found. Click "+ New Invoice" to create one.
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
    <?php if($invoices instanceof \Illuminate\Pagination\LengthAwarePaginator && $invoices->hasPages()): ?>
     <?php $__env->slot('footer', null, []); ?> 
        <?php echo e($invoices->withQueryString()->links()); ?>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'new-invoice','title' => 'New Invoice','maxWidth' => '5xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'new-invoice','title' => 'New Invoice','maxWidth' => '5xl']); ?>
    <form action="<?php echo e(route('ar.invoices.store')); ?>" method="POST" v-pre x-data="{
        lines: [{ fee_code: '', description: '', qty: 1, unit_amount: 0, amount: 0, revenue_account: '' }],
        discount: 0,
        tax: 0,
        get gross() { return this.lines.reduce((s, l) => s + parseFloat(l.amount || 0), 0); },
        get net() { return this.gross - parseFloat(this.discount || 0) + parseFloat(this.tax || 0); },
        updateAmount(i) { this.lines[i].amount = (parseFloat(this.lines[i].qty || 0) * parseFloat(this.lines[i].unit_amount || 0)).toFixed(2); },
        addLine() { this.lines.push({ fee_code: '', description: '', qty: 1, unit_amount: 0, amount: 0, revenue_account: '' }); },
        removeLine(i) { if (this.lines.length > 1) this.lines.splice(i, 1); }
    }">
        <?php echo csrf_field(); ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="form-label">Customer <span class="text-danger-500">*</span></label>
                <select name="customer_id" class="form-input" required>
                    <option value="">Select Customer</option>
                    <?php $__currentLoopData = $customers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="form-label">Invoice Date <span class="text-danger-500">*</span></label>
                <input type="date" name="invoice_date" class="form-input" value="<?php echo e(date('Y-m-d')); ?>" required>
            </div>
            <div>
                <label class="form-label">Due Date <span class="text-danger-500">*</span></label>
                <input type="date" name="due_date" class="form-input" required>
            </div>
            <div>
                <label class="form-label">School Year</label>
                <input type="text" name="school_year" class="form-input" placeholder="e.g., 2025-2026">
            </div>
            <div>
                <label class="form-label">Semester</label>
                <select name="semester" class="form-input">
                    <option value="">Select</option>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                    <option value="Summer">Summer</option>
                    <option value="Full Year">Full Year</option>
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-input" placeholder="Invoice description">
            </div>
        </div>

        
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-secondary-700 mb-2">Line Items</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Fee Code</th>
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Description</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-20">Qty</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-32">Unit Amount</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-32">Amount</th>
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Revenue Account</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, index) in lines" :key="index">
                            <tr class="border-b border-gray-100">
                                <td class="py-1 px-2"><input type="text" x-model="line.fee_code" :name="'lines['+index+'][fee_code]'" class="form-input text-sm" placeholder="FEE-001"></td>
                                <td class="py-1 px-2"><input type="text" x-model="line.description" :name="'lines['+index+'][description]'" class="form-input text-sm" placeholder="Description"></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.qty" :name="'lines['+index+'][qty]'" @input="updateAmount(index)" class="form-input text-sm text-right" min="1"></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.unit_amount" :name="'lines['+index+'][unit_amount]'" @input="updateAmount(index)" class="form-input text-sm text-right" step="0.01" min="0"></td>
                                <td class="py-1 px-2"><input type="text" :value="parseFloat(line.amount).toFixed(2)" class="form-input text-sm text-right bg-gray-50" readonly></td>
                                <td class="py-1 px-2">
                                    <select x-model="line.revenue_account" :name="'lines['+index+'][revenue_account]'" class="form-input text-sm">
                                        <option value="">Select</option>
                                        <?php $__currentLoopData = $revenueAccounts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($acct->id); ?>"><?php echo e($acct->code); ?> - <?php echo e($acct->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td class="py-1 px-2">
                                    <button type="button" @click="removeLine(index)" x-show="lines.length > 1" class="text-danger-500 hover:text-danger-700">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button type="button" @click="addLine()" class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium">+ Add Line Item</button>
        </div>

        
        <div class="flex justify-end">
            <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-secondary-600">Gross Amount:</span><span class="font-medium" x-text="'₱' + gross.toFixed(2)"></span></div>
                <div class="flex justify-between items-center">
                    <span class="text-secondary-600">Discount:</span>
                    <input type="number" name="discount_amount" x-model="discount" class="form-input w-28 text-right text-sm" step="0.01" min="0">
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-secondary-600">Tax:</span>
                    <input type="number" name="tax_amount" x-model="tax" class="form-input w-28 text-right text-sm" step="0.01" min="0">
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-200 font-semibold"><span>Net Amount:</span><span x-text="'₱' + net.toFixed(2)"></span></div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'new-invoice')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Invoice</button>
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


<?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'edit-invoice-'.e($invoice->id).'','title' => 'Edit Invoice #'.e($invoice->invoice_number).'','maxWidth' => '5xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit-invoice-'.e($invoice->id).'','title' => 'Edit Invoice #'.e($invoice->invoice_number).'','maxWidth' => '5xl']); ?>
    <form action="<?php echo e(route('ar.invoices.update', $invoice)); ?>" method="POST" v-pre x-data="{
        lines: <?php echo \Illuminate\Support\Js::from($invoice->lines ?? [['fee_code' => '', 'description' => '', 'qty' => 1, 'unit_amount' => 0, 'amount' => 0, 'revenue_account' => '']])->toHtml() ?>,
        discount: <?php echo e($invoice->discount_amount ?? 0); ?>,
        tax: <?php echo e($invoice->tax_amount ?? 0); ?>,
        get gross() { return this.lines.reduce((s, l) => s + parseFloat(l.amount || 0), 0); },
        get net() { return this.gross - parseFloat(this.discount || 0) + parseFloat(this.tax || 0); },
        updateAmount(i) { this.lines[i].amount = (parseFloat(this.lines[i].qty || 0) * parseFloat(this.lines[i].unit_amount || 0)).toFixed(2); },
        addLine() { this.lines.push({ fee_code: '', description: '', qty: 1, unit_amount: 0, amount: 0, revenue_account: '' }); },
        removeLine(i) { if (this.lines.length > 1) this.lines.splice(i, 1); }
    }">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="form-label">Customer <span class="text-danger-500">*</span></label>
                <select name="customer_id" class="form-input" required>
                    <option value="">Select Customer</option>
                    <?php $__currentLoopData = $customers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($customer->id); ?>" <?php echo e(($invoice->customer_id ?? '') == $customer->id ? 'selected' : ''); ?>><?php echo e($customer->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="form-label">Invoice Date <span class="text-danger-500">*</span></label>
                <input type="date" name="invoice_date" class="form-input" value="<?php echo e(\Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d')); ?>" required>
            </div>
            <div>
                <label class="form-label">Due Date <span class="text-danger-500">*</span></label>
                <input type="date" name="due_date" class="form-input" value="<?php echo e(\Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d')); ?>" required>
            </div>
            <div>
                <label class="form-label">School Year</label>
                <input type="text" name="school_year" class="form-input" value="<?php echo e($invoice->school_year ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Semester</label>
                <select name="semester" class="form-input">
                    <option value="">Select</option>
                    <?php $__currentLoopData = ['1st Semester', '2nd Semester', 'Summer', 'Full Year']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($sem); ?>" <?php echo e(($invoice->semester ?? '') == $sem ? 'selected' : ''); ?>><?php echo e($sem); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-input" value="<?php echo e($invoice->description ?? ''); ?>">
            </div>
        </div>

        
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-secondary-700 mb-2">Line Items</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Fee Code</th>
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Description</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-20">Qty</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-32">Unit Amount</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-32">Amount</th>
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Revenue Account</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, index) in lines" :key="index">
                            <tr class="border-b border-gray-100">
                                <td class="py-1 px-2"><input type="text" x-model="line.fee_code" :name="'lines['+index+'][fee_code]'" class="form-input text-sm"></td>
                                <td class="py-1 px-2"><input type="text" x-model="line.description" :name="'lines['+index+'][description]'" class="form-input text-sm"></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.qty" :name="'lines['+index+'][qty]'" @input="updateAmount(index)" class="form-input text-sm text-right" min="1"></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.unit_amount" :name="'lines['+index+'][unit_amount]'" @input="updateAmount(index)" class="form-input text-sm text-right" step="0.01" min="0"></td>
                                <td class="py-1 px-2"><input type="text" :value="parseFloat(line.amount).toFixed(2)" class="form-input text-sm text-right bg-gray-50" readonly></td>
                                <td class="py-1 px-2">
                                    <select x-model="line.revenue_account" :name="'lines['+index+'][revenue_account]'" class="form-input text-sm">
                                        <option value="">Select</option>
                                        <?php $__currentLoopData = $revenueAccounts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($acct->id); ?>"><?php echo e($acct->code); ?> - <?php echo e($acct->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td class="py-1 px-2">
                                    <button type="button" @click="removeLine(index)" x-show="lines.length > 1" class="text-danger-500 hover:text-danger-700">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button type="button" @click="addLine()" class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium">+ Add Line Item</button>
        </div>

        
        <div class="flex justify-end">
            <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-secondary-600">Gross Amount:</span><span class="font-medium" x-text="'₱' + gross.toFixed(2)"></span></div>
                <div class="flex justify-between items-center">
                    <span class="text-secondary-600">Discount:</span>
                    <input type="number" name="discount_amount" x-model="discount" class="form-input w-28 text-right text-sm" step="0.01" min="0">
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-secondary-600">Tax:</span>
                    <input type="number" name="tax_amount" x-model="tax" class="form-input w-28 text-right text-sm" step="0.01" min="0">
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-200 font-semibold"><span>Net Amount:</span><span x-text="'₱' + net.toFixed(2)"></span></div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'edit-invoice-<?php echo e($invoice->id); ?>')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Update Invoice</button>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/ar/invoices/index.blade.php ENDPATH**/ ?>