@php
    $width = min(100, max(0, (int) $percentage));
@endphp
<div class="h-2.5 w-full rounded-full bg-slate-200">
    <div class="h-2.5 rounded-full bg-[#0f3f78]" style="width: {{ $width }}%"></div>
</div>
