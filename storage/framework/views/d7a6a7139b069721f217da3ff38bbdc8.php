<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'searchPlaceholder' => 'Search...',
    'searchable' => true,
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
    'searchPlaceholder' => 'Search...',
    'searchable' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="card">
    
    <div class="card-header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full">
            
            <?php if($searchable): ?>
                <div class="flex items-center gap-2 bg-gray-100 rounded-lg px-3 py-2 w-full sm:w-72">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="text"
                           placeholder="<?php echo e($searchPlaceholder); ?>"
                           class="bg-transparent border-0 text-sm text-gray-700 placeholder-gray-400 focus:outline-none w-full"
                           <?php echo e($attributes->whereStartsWith('wire:model')); ?>>
                </div>
            <?php endif; ?>

            
            <?php if(isset($actions)): ?>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <?php echo e($actions); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="overflow-x-auto">
        <table class="data-table">
            <?php echo e($slot); ?>

        </table>
    </div>

    
    <?php if(isset($footer)): ?>
        <div class="card-footer">
            <?php echo e($footer); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/components/data-table.blade.php ENDPATH**/ ?>