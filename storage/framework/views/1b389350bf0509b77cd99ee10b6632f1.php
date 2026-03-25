
<?php $__env->startSection('title', 'Journal Entries'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $jeCount = $journalEntries instanceof \Illuminate\Pagination\LengthAwarePaginator ? $journalEntries->total() : count($journalEntries);
?>

<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Journal Entries','subtitle' => $jeCount . ' entries']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Journal Entries','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jeCount . ' entries')]); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('gl.journal-entries.approval')); ?>" class="btn-secondary inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                Approval Queue
                <?php if(($pendingApprovalCount ?? 0) > 0): ?>
                    <span class="ml-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full"><?php echo e($pendingApprovalCount); ?></span>
                <?php endif; ?>
            </a>
            <button @click="$dispatch('open-modal', 'new-journal-entry')" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                + New Journal Entry
            </button>
        </div>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-bar','data' => ['action' => ''.e(route('gl.journal-entries.index')).'','method' => 'GET']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => ''.e(route('gl.journal-entries.index')).'','method' => 'GET']); ?>
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-input w-44">
            <option value="">All Status</option>
            <?php $__currentLoopData = ['draft', 'posted', 'reversed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s); ?>" <?php echo e(request('status') == $s ? 'selected' : ''); ?>><?php echo e(ucfirst($s)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="form-label">Type</label>
        <select name="journal_type" class="form-input w-44">
            <option value="">All Types</option>
            <?php $__currentLoopData = ['general', 'adjusting', 'closing', 'reversing', 'revenue', 'expense', 'payroll']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($t); ?>" <?php echo e(request('journal_type') == $t ? 'selected' : ''); ?>><?php echo e(ucfirst($t)); ?></option>
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


<div class="card">
    <div class="card-header">
        <div class="flex items-center gap-2 bg-gray-100 rounded-lg px-3 py-2 w-full sm:w-72">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input type="text" placeholder="Search journal entries..." class="bg-transparent border-0 text-sm text-gray-700 placeholder-gray-400 focus:outline-none w-full">
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-8"></th>
                    <th>Journal #</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $journalEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $je): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="cursor-pointer hover:bg-gray-50 je-toggle-row">
                    <td>
                        <button class="text-secondary-400 hover:text-secondary-600 transition-transform je-chevron">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        </button>
                    </td>
                    <td>
                        <a href="<?php echo e(route('gl.journal-entries.show', $je)); ?>" class="text-primary-600 hover:underline font-medium" onclick="event.stopPropagation()"><?php echo e($je->entry_number); ?></a>
                    </td>
                    <td><?php echo e($je->entry_date->format('M d, Y')); ?></td>
                    <td class="max-w-xs truncate"><?php echo e($je->description ?? '-'); ?></td>
                    <td>
                        <span class="badge badge-info"><?php echo e(ucfirst($je->journal_type)); ?></span>
                    </td>
                    <td class="text-right font-medium"><?php echo e('₱' . number_format($je->total_debit, 2)); ?></td>
                    <td class="text-right font-medium"><?php echo e('₱' . number_format($je->total_credit, 2)); ?></td>
                    <td><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $je->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($je->status)]); ?>
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
                
                <tr class="je-detail-row hidden">
                    <td colspan="8" class="bg-gray-50 p-0">
                        <div class="px-8 py-3">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-secondary-500">
                                        <th class="text-left py-1 font-medium">Account Code</th>
                                        <th class="text-left py-1 font-medium">Account Name</th>
                                        <th class="text-left py-1 font-medium">Description</th>
                                        <th class="text-right py-1 font-medium">Debit</th>
                                        <th class="text-right py-1 font-medium">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $je->lines ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="border-t border-gray-100">
                                        <td class="py-1"><?php echo e($line->account->account_code ?? '-'); ?></td>
                                        <td class="py-1"><?php echo e($line->account->account_name ?? '-'); ?></td>
                                        <td class="py-1"><?php echo e($line->description ?? '-'); ?></td>
                                        <td class="py-1 text-right"><?php echo e(($line->debit ?? 0) > 0 ? '₱' . number_format($line->debit, 2) : '-'); ?></td>
                                        <td class="py-1 text-right"><?php echo e(($line->credit ?? 0) > 0 ? '₱' . number_format($line->credit, 2) : '-'); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center text-secondary-400 py-8">
                        <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        No journal entries found. Click "+ New Journal Entry" to create one.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($journalEntries instanceof \Illuminate\Pagination\LengthAwarePaginator && $journalEntries->hasPages()): ?>
    <div class="card-footer">
        <?php echo e($journalEntries->withQueryString()->links()); ?>

    </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    document.querySelectorAll('.je-toggle-row').forEach(row => {
        row.addEventListener('click', () => {
            const detail = row.nextElementSibling;
            if (detail && detail.classList.contains('je-detail-row')) {
                detail.classList.toggle('hidden');
                const chevron = row.querySelector('.je-chevron');
                if (chevron) chevron.classList.toggle('rotate-90');
            }
        });
    });
</script>
<?php $__env->stopPush(); ?>


<?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'new-journal-entry','title' => 'New Journal Entry','maxWidth' => '5xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'new-journal-entry','title' => 'New Journal Entry','maxWidth' => '5xl']); ?>
    <form action="<?php echo e(route('gl.journal-entries.store')); ?>" method="POST" x-data="{
        lines: [
            { account_id: '', description: '', debit: 0, credit: 0 },
            { account_id: '', description: '', debit: 0, credit: 0 }
        ],
        get totalDebit() { return this.lines.reduce((s, l) => s + parseFloat(l.debit || 0), 0); },
        get totalCredit() { return this.lines.reduce((s, l) => s + parseFloat(l.credit || 0), 0); },
        get difference() { return this.totalDebit - this.totalCredit; },
        addLine() { this.lines.push({ account_id: '', description: '', debit: 0, credit: 0 }); },
        removeLine(i) { if (this.lines.length > 2) this.lines.splice(i, 1); }
    }">
        <?php echo csrf_field(); ?>

        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
            <strong>JE Number:</strong> Auto-generated upon saving (series-based for audit trail).
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="form-label">Date <span class="text-danger-500">*</span></label>
                <input type="date" name="entry_date" class="form-input" value="<?php echo e(date('Y-m-d')); ?>" required>
            </div>
            <div>
                <label class="form-label">Reference #</label>
                <input type="text" name="reference_number" class="form-input" placeholder="Check #, OR #, etc.">
            </div>
            <div>
                <label class="form-label">Type <span class="text-danger-500">*</span></label>
                <select name="journal_type" class="form-input" required>
                    <option value="general">General</option>
                    <option value="adjusting">Adjusting</option>
                    <option value="closing">Closing</option>
                    <option value="reversing">Reversing</option>
                    <option value="revenue">Revenue</option>
                    <option value="expense">Expense</option>
                    <option value="payroll">Payroll</option>
                </select>
            </div>
            <div>
                <label class="form-label">Description <span class="text-danger-500">*</span></label>
                <input type="text" name="description" class="form-input" placeholder="e.g. Bank charges for March" required>
            </div>
        </div>

        
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-secondary-700 mb-2">Journal Lines</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Account</th>
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Description</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-36">Debit</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-36">Credit</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, index) in lines" :key="index">
                            <tr class="border-b border-gray-100">
                                <td class="py-1 px-2">
                                    <select x-model="line.account_id" :name="'lines['+index+'][account_id]'" class="form-input text-sm" required>
                                        <option value="">Select Account</option>
                                        <?php $__currentLoopData = $accounts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($acct->id); ?>"><?php echo e($acct->account_code); ?> - <?php echo e($acct->account_name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td class="py-1 px-2"><input type="text" x-model="line.description" :name="'lines['+index+'][description]'" class="form-input text-sm" placeholder="Line description" required></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.debit" :name="'lines['+index+'][debit]'" class="form-input text-sm text-right" step="0.01" min="0" @input="if(parseFloat(line.debit) > 0) line.credit = 0"></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.credit" :name="'lines['+index+'][credit]'" class="form-input text-sm text-right" step="0.01" min="0" @input="if(parseFloat(line.credit) > 0) line.debit = 0"></td>
                                <td class="py-1 px-2">
                                    <button type="button" @click="removeLine(index)" x-show="lines.length > 2" class="text-danger-500 hover:text-danger-700">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-300 font-semibold">
                            <td colspan="2" class="py-2 px-2 text-right">Totals:</td>
                            <td class="py-2 px-2 text-right" x-text="'₱' + totalDebit.toFixed(2)"></td>
                            <td class="py-2 px-2 text-right" x-text="'₱' + totalCredit.toFixed(2)"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="py-1 px-2 text-right text-sm">Difference:</td>
                            <td colspan="2" class="py-1 px-2 text-right text-sm font-semibold" :class="difference !== 0 ? 'text-danger-500' : 'text-success-600'" x-text="'₱' + Math.abs(difference).toFixed(2) + (difference !== 0 ? ' (unbalanced)' : ' (balanced)')"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="button" @click="addLine()" class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium">+ Add Line</button>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'new-journal-entry')" class="btn-secondary">Cancel</button>
            <button type="submit" name="action" value="draft" class="btn-secondary" :disabled="difference !== 0">Save as Draft</button>
            <button type="submit" name="action" value="submit_approval" class="btn-primary" :disabled="difference !== 0">Submit for Approval</button>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/gl/journal-entries/index.blade.php ENDPATH**/ ?>