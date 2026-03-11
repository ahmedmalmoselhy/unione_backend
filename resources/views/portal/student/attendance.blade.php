@extends('portal.layouts.app')

@section('title', 'My Attendance')
@section('heading', 'My Attendance')

@section('content')

@if($summary->isEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-sm">No attendance records available yet.</p>
</div>
@else
<div class="space-y-6">
    @foreach($summary as $course)
    @php
        $section = $course['section'];
        $pct = $course['percentage'];
        $barColor = $pct >= 75 ? 'bg-green-500' : ($pct >= 50 ? 'bg-amber-400' : 'bg-red-500');
    @endphp
    <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        {{-- Course header / summary --}}
        <button @click="open = !open"
                class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $section?->course?->name ?? 'Course' }}
                    </p>
                    <span class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-md font-mono">
                        {{ $section?->course?->code }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $section?->academicTerm?->name }}</span>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 mb-2">
                    <span class="text-green-600 dark:text-green-400 font-medium">{{ $course['present'] }} present</span>
                    <span class="text-amber-500 font-medium">{{ $course['late'] }} late</span>
                    <span class="text-gray-400">{{ $course['excused'] }} excused</span>
                    <span class="text-red-500 font-medium">{{ $course['absent'] }} absent</span>
                    <span class="text-gray-400">/ {{ $course['total'] }} sessions</span>
                </div>
                {{-- Progress bar --}}
                <div class="flex items-center gap-3">
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-2 max-w-xs">
                        <div class="{{ $barColor }} h-2 rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-xs font-semibold {{ $pct >= 75 ? 'text-green-600 dark:text-green-400' : ($pct >= 50 ? 'text-amber-500' : 'text-red-500') }}">
                        {{ $pct }}%
                    </span>
                </div>
            </div>
            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform ml-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        {{-- Session detail rows --}}
        <div x-show="open" x-cloak class="border-t border-gray-100 dark:border-gray-700">
            @foreach($course['records'] as $record)
            @php
                $status = $record->status;
                $statusClass = match($status) {
                    'present' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300',
                    'late'    => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300',
                    'excused' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300',
                    default   => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300',
                };
            @endphp
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                <div>
                    <p class="text-sm text-gray-900 dark:text-white">
                        {{ $record->attendanceSession?->session_date?->format('D, M j Y') }}
                    </p>
                    @if($record->attendanceSession?->topic)
                    <p class="text-xs text-gray-400">{{ $record->attendanceSession->topic }}</p>
                    @endif
                    @if($record->note)
                    <p class="text-xs text-gray-400 italic">{{ $record->note }}</p>
                    @endif
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusClass }}">
                    {{ ucfirst($status) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
