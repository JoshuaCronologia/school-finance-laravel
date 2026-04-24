@props([
    'searchPlaceholder' => 'Search...',
    'searchable' => true,
])

<div class="card" data-searchable-table>
    {{-- Toolbar --}}
    <div class="card-header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full">
            {{-- Search --}}
            @if($searchable)
                <div class="flex items-center gap-2 bg-gray-100 rounded-lg px-3 py-2 w-full sm:w-72">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="text"
                           data-table-search
                           placeholder="{{ $searchPlaceholder }}"
                           class="bg-transparent border-0 text-sm text-gray-700 placeholder-gray-400 focus:outline-none w-full">
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

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-searchable-table]').forEach(function (card) {
                var input = card.querySelector('[data-table-search]');
                var tbody = card.querySelector('table tbody');
                if (!input || !tbody) return;
                var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
                var emptyRow = null;
                function apply() {
                    var q = input.value.trim().toLowerCase();
                    var visible = 0;
                    rows.forEach(function (r) {
                        if (r.hasAttribute('data-empty-search')) return;
                        var match = !q || r.textContent.toLowerCase().indexOf(q) !== -1;
                        r.style.display = match ? '' : 'none';
                        if (match) visible++;
                    });
                    if (q && visible === 0) {
                        if (!emptyRow) {
                            var colCount = (tbody.querySelector('tr') || {}).cells ? tbody.querySelector('tr').cells.length : 1;
                            emptyRow = document.createElement('tr');
                            emptyRow.setAttribute('data-empty-search', '');
                            emptyRow.innerHTML = '<td colspan="' + colCount + '" class="text-center py-6 text-secondary-400 text-sm">No matches for "<span></span>"</td>';
                            tbody.appendChild(emptyRow);
                        }
                        emptyRow.querySelector('span').textContent = q;
                        emptyRow.style.display = '';
                    } else if (emptyRow) {
                        emptyRow.style.display = 'none';
                    }
                }
                var timer;
                input.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(apply, 150);
                });
            });
        });
    </script>
    @endpush
@endonce
