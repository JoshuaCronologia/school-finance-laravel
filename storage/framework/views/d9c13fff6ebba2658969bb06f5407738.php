<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status']));

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

foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $statusLower = strtolower($status);
    $styles = match($statusLower) {
        'draft'                         => 'badge-neutral',
        'pending', 'for_approval'       => 'badge-warning',
        'approved', 'posted', 'paid', 'active', 'completed' => 'badge-success',
        'rejected', 'voided', 'overdue' => 'badge-danger',
        'cancelled', 'inactive'         => 'badge-neutral',
        default                         => 'badge-info',
    };
?>

<span <?php echo e($attributes->merge(['class' => "badge $styles"])); ?>>
    <?php echo e(ucfirst(str_replace('_', ' ', $status))); ?>

</span>
<?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/components/badge.blade.php ENDPATH**/ ?>