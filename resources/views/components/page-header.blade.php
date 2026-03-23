@props([
    'title',
    'subtitle' => null,
])

<div class="page-header">
    <div>
        <h1 class="page-title">{{ $title }}</h1>
        @if($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex items-center gap-2 flex-shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
