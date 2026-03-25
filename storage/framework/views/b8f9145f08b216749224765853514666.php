<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'title' => '',
    'maxWidth' => '2xl',
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
    'name',
    'title' => '',
    'maxWidth' => '2xl',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $maxWidthClass = [
        'sm'  => 'max-w-sm',
        'md'  => 'max-w-md',
        'lg'  => 'max-w-lg',
        'xl'  => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
    ][$maxWidth] ?? 'max-w-2xl';
?>

<div x-data="{ show: false, name: '<?php echo e($name); ?>' }"
     x-show="show"
     x-on:open-modal.window="if ($event.detail === name) show = true"
     x-on:close-modal.window="if ($event.detail === name) show = false"
     x-on:keydown.escape.window="show = false"
     class="modal-overlay"
     style="display: none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    
    <div class="absolute inset-0 bg-black/50" @click="show = false"></div>

    
    <div class="modal-content <?php echo e($maxWidthClass); ?> relative"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         @click.away="show = false">

        
        <?php if($title): ?>
            <div class="modal-header">
                <h3 class="text-lg font-semibold text-secondary-900"><?php echo e($title); ?></h3>
                <button @click="show = false" class="btn-icon">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
        <?php endif; ?>

        
        <div class="modal-body">
            <?php echo e($slot); ?>

        </div>

        
        <?php if(isset($footer)): ?>
            <div class="modal-footer">
                <?php echo e($footer); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/components/modal.blade.php ENDPATH**/ ?>