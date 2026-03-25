<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'value',
    'color' => 'blue',
    'icon' => null,
    'subtitle' => null,
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
    'label',
    'value',
    'color' => 'blue',
    'icon' => null,
    'subtitle' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $colorMap = [
        'blue'   => 'bg-primary-50 text-primary-600',
        'green'  => 'bg-success-50 text-success-600',
        'red'    => 'bg-danger-50 text-danger-500',
        'yellow' => 'bg-warning-50 text-warning-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'gray'   => 'bg-secondary-100 text-secondary-600',
    ];
    $iconColor = $colorMap[$color] ?? $colorMap['blue'];
?>

<div class="stat-card">
    <div class="flex items-start justify-between">
        <div class="min-w-0 flex-1">
            <p class="stat-card-label"><?php echo e($label); ?></p>
            <p class="stat-card-value"><?php echo e($value); ?></p>
            <?php if($subtitle): ?>
                <p class="stat-card-trend mt-1 text-secondary-500"><?php echo e($subtitle); ?></p>
            <?php endif; ?>
        </div>
        <?php if($icon): ?>
            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center <?php echo e($iconColor); ?>">
                <?php echo $icon; ?>

            </div>
        <?php endif; ?>
    </div>
    <?php if($slot->isNotEmpty()): ?>
        <div class="mt-3 pt-3 border-t border-gray-100">
            <?php echo e($slot); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/components/stat-card.blade.php ENDPATH**/ ?>