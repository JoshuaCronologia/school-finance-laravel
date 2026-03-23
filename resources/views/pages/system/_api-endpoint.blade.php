@php
    $methodColors = [
        'GET' => 'bg-success-100 text-success-700',
        'POST' => 'bg-primary-100 text-primary-700',
        'PUT' => 'bg-warning-100 text-warning-700',
        'DELETE' => 'bg-danger-100 text-danger-700',
        'PATCH' => 'bg-purple-100 text-purple-700',
    ];
    $badgeColor = $methodColors[$method] ?? 'bg-gray-100 text-gray-700';
@endphp

<div class="flex items-start gap-3 py-3 border-b border-gray-100 last:border-0">
    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider {{ $badgeColor }} min-w-[56px]">{{ $method }}</span>
    <div class="flex-1 min-w-0">
        <code class="text-sm font-mono text-secondary-800">{{ $url }}</code>
        <p class="text-xs text-secondary-500 mt-0.5">{{ $desc }}</p>
    </div>
</div>
