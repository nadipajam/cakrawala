@php
    $interactive = $interactive ?? false;
    $classKey = $classKey ?? $seatClass['key'];
@endphp

<div class="rounded-[24px] border border-slate-200 bg-slate-50/90 p-3 sm:rounded-[28px] sm:p-5">
    <div class="mb-3 flex flex-wrap items-center gap-3 text-[11px] font-semibold sm:text-xs">
        <span class="inline-flex items-center gap-1 text-emerald-700"><span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>Available</span>
        @if ($interactive)
            <span class="inline-flex items-center gap-1 text-emerald-800"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Selected</span>
        @endif
        <span class="inline-flex items-center gap-1 text-red-700"><span class="h-2.5 w-2.5 rounded-full bg-red-300"></span>Occupied</span>
    </div>

    <div class="w-full overflow-hidden">
        <div class="w-full space-y-2.5 sm:space-y-3">
            <div class="mx-auto h-3 w-20 rounded-b-full bg-slate-300/70 sm:h-4 sm:w-28"></div>

            <div class="grid grid-cols-[24px_1fr_30px_1fr_24px] items-center gap-1.5 text-center text-[9px] font-semibold tracking-wide text-slate-500 sm:grid-cols-[34px_1fr_48px_1fr_34px] sm:gap-2 sm:text-[11px]">
                <span>R</span>
                <div class="grid gap-1" style="grid-template-columns: repeat({{ $seatClass['left_columns_count'] }}, minmax(0, 1fr));">
                    @foreach ($seatClass['left_columns'] as $column)
                        <span>{{ $column }}</span>
                    @endforeach
                </div>
                <span class="uppercase text-slate-400">Aisle</span>
                <div class="grid gap-1" style="grid-template-columns: repeat({{ $seatClass['right_columns_count'] }}, minmax(0, 1fr));">
                    @foreach ($seatClass['right_columns'] as $column)
                        <span>{{ $column }}</span>
                    @endforeach
                </div>
                <span>R</span>
            </div>

            @foreach ($seatClass['rows'] as $row)
                @php($rowSeats = collect($row['seats']))
                <div class="grid grid-cols-[24px_1fr_30px_1fr_24px] items-center gap-1.5 sm:grid-cols-[34px_1fr_48px_1fr_34px] sm:gap-2">
                    <span class="text-center text-[9px] font-semibold text-slate-500 sm:text-[11px]">{{ $row['number'] }}</span>
                    <div class="grid gap-1" style="grid-template-columns: repeat({{ $seatClass['left_columns_count'] }}, minmax(0, 1fr));">
                        @foreach ($seatClass['left_columns'] as $column)
                            @php($seat = $rowSeats->get($column))
                            @if ($seat)
                                @if ($interactive)
                                    <button
                                        type="button"
                                        @click="toggleSeat({{ $seat['id'] }})"
                                        :disabled="!isSeatAvailable({{ $seat['id'] }}) || selectedClass !== '{{ $classKey }}'"
                                        class="relative h-7 min-w-0 rounded-md border text-[9px] font-semibold leading-none transition duration-200 sm:h-8 sm:text-[10px]"
                                        :class="seatClass({{ $seat['id'] }}, {{ $seat['available'] ? 'true' : 'false' }})"
                                        title="{{ $seat['seat_number'] }}"
                                    >
                                        {{ $seat['display_label'] }}
                                        <span
                                            x-show="selectedSeats.includes({{ $seat['id'] }})"
                                            x-cloak
                                            class="absolute -right-1 -top-1 grid h-3.5 w-3.5 place-content-center rounded-full bg-white text-[9px] font-bold text-[#0f3f78] shadow"
                                        >
                                            &#10003;
                                        </span>
                                    </button>
                                @else
                                    <span @class([
                                        'grid h-7 min-w-0 place-content-center rounded-md border text-[9px] font-semibold leading-none sm:h-8 sm:text-[10px]',
                                        'border-emerald-300 bg-emerald-100 text-emerald-700' => $seat['available'],
                                        'border-red-300 bg-red-100 text-red-700' => ! $seat['available'],
                                    ])>{{ $seat['display_label'] }}</span>
                                @endif
                            @else
                                <span class="h-7 sm:h-8"></span>
                            @endif
                        @endforeach
                    </div>

                    <span class="text-center text-[8px] font-semibold uppercase tracking-[0.12em] text-slate-400 sm:text-[9px] sm:tracking-[0.18em]">Aisle</span>

                    <div class="grid gap-1" style="grid-template-columns: repeat({{ $seatClass['right_columns_count'] }}, minmax(0, 1fr));">
                        @foreach ($seatClass['right_columns'] as $column)
                            @php($seat = $rowSeats->get($column))
                            @if ($seat)
                                @if ($interactive)
                                    <button
                                        type="button"
                                        @click="toggleSeat({{ $seat['id'] }})"
                                        :disabled="!isSeatAvailable({{ $seat['id'] }}) || selectedClass !== '{{ $classKey }}'"
                                        class="relative h-7 min-w-0 rounded-md border text-[9px] font-semibold leading-none transition duration-200 sm:h-8 sm:text-[10px]"
                                        :class="seatClass({{ $seat['id'] }}, {{ $seat['available'] ? 'true' : 'false' }})"
                                        title="{{ $seat['seat_number'] }}"
                                    >
                                        {{ $seat['display_label'] }}
                                        <span
                                            x-show="selectedSeats.includes({{ $seat['id'] }})"
                                            x-cloak
                                            class="absolute -right-1 -top-1 grid h-3.5 w-3.5 place-content-center rounded-full bg-white text-[9px] font-bold text-[#0f3f78] shadow"
                                        >
                                            &#10003;
                                        </span>
                                    </button>
                                @else
                                    <span @class([
                                        'grid h-7 min-w-0 place-content-center rounded-md border text-[9px] font-semibold leading-none sm:h-8 sm:text-[10px]',
                                        'border-emerald-300 bg-emerald-100 text-emerald-700' => $seat['available'],
                                        'border-red-300 bg-red-100 text-red-700' => ! $seat['available'],
                                    ])>{{ $seat['display_label'] }}</span>
                                @endif
                            @else
                                <span class="h-7 sm:h-8"></span>
                            @endif
                        @endforeach
                    </div>
                    <span class="text-center text-[9px] font-semibold text-slate-500 sm:text-[11px]">{{ $row['number'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
