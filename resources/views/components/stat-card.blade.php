@props([
    'label',
    'value',
    'color' => 'blue',
    'icon' => null,
    'subtitle' => null,
])

@php
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
@endphp

<div class="stat-card">
    <div class="flex items-start justify-between">
        <div class="min-w-0 flex-1">
            <p class="stat-card-label">{{ $label }}</p>
            <p class="stat-card-value">{{ $value }}</p>
            @if($subtitle)
                <p class="stat-card-trend mt-1 text-secondary-500">{{ $subtitle }}</p>
            @endif
        </div>
        @if($icon)
            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center {{ $iconColor }}">
                {!! $icon !!}
            </div>
        @endif
    </div>
    @if($slot->isNotEmpty())
        <div class="mt-3 pt-3 border-t border-gray-100">
            {{ $slot }}
        </div>
    @endif
</div>
