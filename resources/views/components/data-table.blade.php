@props([
    'searchPlaceholder' => 'Search...',
    'searchable' => true,
])

<div class="card">
    {{-- Toolbar --}}
    <div class="card-header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full">
            {{-- Search --}}
            @if($searchable)
                <div class="flex items-center gap-2 bg-gray-100 rounded-lg px-3 py-2 w-full sm:w-72">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="text"
                           placeholder="{{ $searchPlaceholder }}"
                           class="bg-transparent border-0 text-sm text-gray-700 placeholder-gray-400 focus:outline-none w-full"
                           {{ $attributes->whereStartsWith('wire:model') }}>
                </div>
            @endif

            {{-- Action buttons slot --}}
            @if(isset($actions))
                <div class="flex items-center gap-2 flex-shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="data-table">
            {{ $slot }}
        </table>
    </div>

    {{-- Footer / Pagination --}}
    @if(isset($footer))
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>
