@props(['status'])

@php
    $statusLower = strtolower($status);
    $styleMap = [
        'draft' => 'badge-neutral',
        'pending' => 'badge-warning',
        'for_approval' => 'badge-warning',
        'approved' => 'badge-success',
        'posted' => 'badge-success',
        'paid' => 'badge-success',
        'active' => 'badge-success',
        'completed' => 'badge-success',
        'rejected' => 'badge-danger',
        'voided' => 'badge-danger',
        'overdue' => 'badge-danger',
        'cancelled' => 'badge-neutral',
        'inactive' => 'badge-neutral',
    ];
    $styles = $styleMap[$statusLower] ?? 'badge-info';
@endphp

<span {{ $attributes->merge(['class' => "badge $styles"]) }}>
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
