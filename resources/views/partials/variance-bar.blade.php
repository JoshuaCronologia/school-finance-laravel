@php
    $val = $pct ?? 0;
    $absVal = min(abs($val), 100);
    $isNegative = $val < 0;
@endphp
<div class="flex items-center h-4 w-20">
    @if($isNegative)
        {{-- Red bar growing from right to left --}}
        <div class="w-full h-3 bg-gray-100 rounded-sm relative overflow-hidden">
            <div class="absolute right-0 top-0 h-full bg-danger-400 rounded-sm" style="width: {{ $absVal }}%"></div>
        </div>
    @elseif($val > 0)
        {{-- Green bar growing from left to right --}}
        <div class="w-full h-3 bg-gray-100 rounded-sm relative overflow-hidden">
            <div class="absolute left-0 top-0 h-full bg-success-400 rounded-sm" style="width: {{ $absVal }}%"></div>
        </div>
    @endif
</div>
