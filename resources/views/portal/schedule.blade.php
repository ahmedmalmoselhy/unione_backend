@extends('portal.layouts.app')

@section('title', 'Schedule')
@section('heading', 'My Schedule')

@section('content')

@php
    $allWorkDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // Only show days that actually have scheduled slots (exclude Unscheduled bucket)
    $daysWithSlots = collect($allWorkDays)->filter(fn($d) => $byDay->has($d))->values();

    // Compute time range from actual slot data
    $scheduled = $byDay->except(['Unscheduled'])->flatten(1);
    if ($scheduled->isNotEmpty()) {
        $minHour   = $scheduled->map(fn($s) => (int) substr($s['start_time'], 0, 2))->min();
        $maxHour   = $scheduled->map(fn($s) => (int) substr($s['end_time'], 0, 2) + ((int) substr($s['end_time'], 3, 2) > 0 ? 1 : 0))->max();
        $startHour = max(7, $minHour - 1);
        $endHour   = min(23, $maxHour + 1);
    } else {
        $startHour = 8;
        $endHour   = 18;
    }

    $totalHours = $endHour - $startHour;
    $hourH      = 5; // rem per hour
@endphp

{{-- Term badge --}}
@if($currentTerm)
<div class="mb-4 flex items-center gap-2">
    <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-medium rounded-full">
        {{ $currentTerm->name }}
    </span>
    <span class="text-sm text-gray-400">{{ $currentTerm->academic_year }}</span>
</div>
@endif

{{-- Legend --}}
@if(!$byDay->isEmpty())
<div class="mb-4 flex items-center gap-4">
    <div class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-sm bg-blue-200 dark:bg-blue-900/60 border border-blue-300 dark:border-blue-700 inline-block"></span>
        <span class="text-xs text-gray-500 dark:text-gray-400">Lecture</span>
    </div>
    <div class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-sm bg-violet-200 dark:bg-violet-900/60 border border-violet-300 dark:border-violet-700 inline-block"></span>
        <span class="text-xs text-gray-500 dark:text-gray-400">Lab / Tutorial</span>
    </div>
</div>
@endif

@if($byDay->isEmpty())
    {{-- Empty state --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-gray-400 dark:text-gray-500 text-sm">No schedule found for this term.</p>
    </div>
@else

    {{-- ── CALENDAR GRID ── --}}
    @if($daysWithSlots->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">

        {{-- Day header row --}}
        <div class="flex border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
            {{-- Gutter above time labels --}}
            <div class="w-14 shrink-0 border-e border-gray-200 dark:border-gray-700 py-3"></div>

            @foreach($daysWithSlots as $day)
            <div class="flex-1 py-3 text-center text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-300
                        {{ !$loop->last ? 'border-e border-gray-200 dark:border-gray-700' : '' }}">
                {{ $day }}
            </div>
            @endforeach
        </div>

        {{-- Body --}}
        <div class="flex overflow-x-auto">

            {{-- Time labels column --}}
            <div class="w-14 shrink-0 border-e border-gray-200 dark:border-gray-700 relative select-none"
                 style="height: {{ $totalHours * $hourH }}rem;">
                @for($h = $startHour; $h < $endHour; $h++)
                <div class="absolute w-full" style="top: {{ ($h - $startHour) * $hourH }}rem;">
                    <span class="block text-right pe-2 pt-1 text-[10px] text-gray-400 dark:text-gray-500 leading-none">
                        {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00
                    </span>
                </div>
                @endfor
            </div>

            {{-- Day columns --}}
            @foreach($daysWithSlots as $day)
            <div class="flex-1 relative min-w-[130px] {{ !$loop->last ? 'border-e border-gray-200 dark:border-gray-700' : '' }}"
                 style="height: {{ $totalHours * $hourH }}rem;">

                {{-- Horizontal hour grid lines --}}
                @for($h = $startHour; $h <= $endHour; $h++)
                <div class="absolute w-full border-t {{ $h === $startHour ? 'border-gray-200 dark:border-gray-700' : 'border-gray-100 dark:border-gray-700/40' }}"
                     style="top: {{ ($h - $startHour) * $hourH }}rem;"></div>
                @endfor

                {{-- Half-hour tick lines --}}
                @for($h = $startHour; $h < $endHour; $h++)
                <div class="absolute w-full border-t border-dashed border-gray-100 dark:border-gray-700/20"
                     style="top: {{ (($h - $startHour) + 0.5) * $hourH }}rem;"></div>
                @endfor

                {{-- Course blocks --}}
                @foreach($byDay->get($day, collect()) as $slot)
                @php
                    [$sH, $sM] = array_map('intval', explode(':', $slot['start_time']));
                    [$eH, $eM] = array_map('intval', explode(':', $slot['end_time']));
                    $topRem    = (($sH - $startHour) + $sM / 60) * $hourH;
                    $heightRem = max(1.5, (($eH + $eM / 60) - ($sH + $sM / 60)) * $hourH);
                    $isLecture = ($slot['type'] === 'lecture');
                @endphp
                <div class="absolute rounded-lg overflow-hidden border shadow-sm
                            {{ $isLecture
                                ? 'bg-blue-50 dark:bg-blue-900/30 border-blue-200 dark:border-blue-700/60'
                                : 'bg-violet-50 dark:bg-violet-900/30 border-violet-200 dark:border-violet-700/60' }}"
                     style="top: {{ $topRem }}rem; height: {{ $heightRem }}rem; left: 3px; right: 3px;">
                    {{-- Colored left accent bar --}}
                    <div class="absolute start-0 inset-y-0 w-1 {{ $isLecture ? 'bg-blue-400 dark:bg-blue-500' : 'bg-violet-400 dark:bg-violet-500' }}"></div>
                    <div class="ps-3 pe-2 py-1.5 h-full flex flex-col justify-start">
                        <p class="text-[11px] font-bold leading-tight truncate
                                  {{ $isLecture ? 'text-blue-800 dark:text-blue-200' : 'text-violet-800 dark:text-violet-200' }}">
                            {{ $slot['course_code'] }}
                        </p>
                        <p class="text-[10px] leading-tight truncate
                                  {{ $isLecture ? 'text-blue-600 dark:text-blue-400' : 'text-violet-600 dark:text-violet-400' }}">
                            {{ $slot['start_time'] }}–{{ $slot['end_time'] }}
                        </p>
                        @if($heightRem >= 3)
                        <p class="text-[10px] text-gray-600 dark:text-gray-400 leading-tight mt-0.5 truncate">
                            {{ $slot['course_name'] }}
                        </p>
                        @endif
                        @if($heightRem >= 4 && ($slot['room'] !== '—' || $slot['professor']))
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 leading-tight truncate">
                            @if($slot['room'] !== '—')Room {{ $slot['room'] }}@endif
                            @if($slot['professor']){{ $slot['room'] !== '—' ? ' · ' : '' }}{{ $slot['professor'] }}@endif
                        </p>
                        @endif
                    </div>
                </div>
                @endforeach

            </div>
            @endforeach

        </div>
    </div>
    @endif

    {{-- Unscheduled bucket (below the calendar) --}}
    @if($byDay->has('Unscheduled'))
    <div class="mt-6">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3">Unscheduled</h3>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($byDay['Unscheduled'] as $slot)
            <div class="flex items-center gap-4 px-5 py-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $slot['course_name'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $slot['course_code'] }}</p>
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">No time slot assigned</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

@endif {{-- end if byDay not empty --}}

@endsection
