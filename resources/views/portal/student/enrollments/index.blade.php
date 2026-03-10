@extends('portal.layouts.app')

@section('title', 'My Courses')
@section('heading', 'My Courses')

@section('content')

<div class="flex items-center justify-between mb-4">
    <div>
        @if($currentTerm)
            <span class="text-sm text-gray-500 dark:text-gray-400">Current term: <strong class="text-gray-900 dark:text-white">{{ $currentTerm->name }}</strong></span>
            @if($inRegPeriod)
                <span class="ms-3 px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-medium rounded-full">Registration open</span>
            @endif
        @endif
    </div>
    @if($inRegPeriod)
    <a href="{{ route('portal.enrollments.create') }}"
       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
        + Enroll in a course
    </a>
    @endif
</div>

@if($byTerm->isEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-sm">No enrollments yet.</p>
    @if($inRegPeriod)
        <a href="{{ route('portal.enrollments.create') }}" class="mt-3 inline-block text-sm text-blue-600 dark:text-blue-400 hover:underline">Browse available courses →</a>
    @endif
</div>
@else
<div class="space-y-6">
    @foreach($byTerm as $termName => $enrollments)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $termName }}</h3>
            <span class="text-xs text-gray-400">{{ $enrollments->count() }} course(s)</span>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($enrollments as $enrollment)
            @php
                $course    = $enrollment->section?->course;
                $professor = $enrollment->section?->professor?->user;
                $grade     = $enrollment->grade;
            @endphp
            <div class="flex items-center justify-between p-4 gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $course?->name ?? '—' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $course?->code ?? '' }}
                        @if($course?->credit_hours) · {{ $course->credit_hours }} credit hrs @endif
                        @if($enrollment->section?->room) · Room {{ $enrollment->section->room }} @endif
                        @if($professor) · {{ $professor->first_name }} {{ $professor->last_name }} @endif
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    {{-- Grade badge --}}
                    @if($grade?->letter_grade)
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full text-sm font-bold
                            {{ in_array($grade->letter_grade, ['A+','A','A-']) ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' :
                               (in_array($grade->letter_grade, ['B+','B','B-']) ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' :
                               (in_array($grade->letter_grade, ['C+','C','C-']) ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' :
                               'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300')) }}">
                            {{ $grade->letter_grade }}
                        </span>
                    @elseif($grade?->total)
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $grade->total }}%</span>
                    @endif

                    {{-- Status badge --}}
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $enrollment->status === 'registered' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' :
                           ($enrollment->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' :
                           'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300') }}">
                        {{ ucfirst($enrollment->status) }}
                    </span>

                    {{-- Drop button (only during reg period and if registered) --}}
                    @if($inRegPeriod && $enrollment->status === 'registered')
                    <form method="POST" action="{{ route('portal.enrollments.destroy', $enrollment) }}"
                          onsubmit="return confirm('Drop this course?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 hover:underline">Drop</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
