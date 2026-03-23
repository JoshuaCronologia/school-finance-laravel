@props(['status'])

@php
    $statusLower = strtolower($status);
    $styles = match($statusLower) {
        'draft'                         => 'badge-neutral',
        'pending', 'for_approval'       => 'badge-warning',
        'approved', 'posted', 'paid', 'active', 'completed' => 'badge-success',
        'rejected', 'voided', 'overdue' => 'badge-danger',
        'cancelled', 'inactive'         => 'badge-neutral',
        default                         => 'badge-info',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge $styles"]) }}>
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
