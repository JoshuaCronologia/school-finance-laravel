<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'action' => null,
    'method' => 'GET',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'action' => null,
    'method' => 'GET',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div <?php echo e($attributes->merge(['class' => 'card mb-6'])); ?>>
    <form <?php echo e($action ? "action=$action" : ''); ?> method="<?php echo e($method); ?>" class="card-body">
        <?php if($method !== 'GET'): ?>
            <?php echo csrf_field(); ?>
        <?php endif; ?>
        <div class="flex flex-wrap items-end gap-4">
            
            <div class="flex items-center gap-2">
                <div>
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="form-input w-40">
                </div>
                <span class="text-secondary-400 mt-5">&mdash;</span>
                <div>
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="form-input w-40">
                </div>
            </div>

            
            <?php echo e($slot); ?>


            
            <div class="flex items-center gap-2 ml-auto">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                    Filter
                </button>
                <a href="<?php echo e(request()->url()); ?>" class="btn-secondary">Clear</a>
            </div>
        </div>
    </form>
</div>
<?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/components/filter-bar.blade.php ENDPATH**/ ?>