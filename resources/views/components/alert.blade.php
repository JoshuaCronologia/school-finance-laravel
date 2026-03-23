@props([
    'type' => 'success',
    'message' => '',
    'dismissible' => true,
])

@php
    $alertClass = match($type) {
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'danger', 'error' => 'alert-danger',
        'info'    => 'alert-info',
        default   => 'alert-info',
    };

    $iconPath = match($type) {
        'success' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'warning' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        'danger', 'error' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
        default   => 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
    };
@endphp

<div {{ $attributes->merge(['class' => "alert $alertClass"]) }}
     @if($dismissible) x-data="{ visible: true }" x-show="visible" x-transition @endif>
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
    </svg>
    <div class="flex-1">
        {{ $message ?: $slot }}
    </div>
    @if($dismissible)
        <button @click="visible = false" class="flex-shrink-0 ml-auto -mr-1 -mt-1 p-1 rounded hover:bg-black/5 transition-colors">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
    @endif
</div>
