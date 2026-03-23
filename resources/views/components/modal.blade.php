@props([
    'name',
    'title' => '',
    'maxWidth' => '2xl',
])

@php
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
@endphp

<div x-data="{ show: false, name: '{{ $name }}' }"
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

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50" @click="show = false"></div>

    {{-- Modal panel --}}
    <div class="modal-content {{ $maxWidthClass }} relative"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         @click.away="show = false">

        {{-- Header --}}
        @if($title)
            <div class="modal-header">
                <h3 class="text-lg font-semibold text-secondary-900">{{ $title }}</h3>
                <button @click="show = false" class="btn-icon">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
        @endif

        {{-- Body --}}
        <div class="modal-body">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if(isset($footer))
            <div class="modal-footer">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
