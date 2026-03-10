@extends('portal.layouts.app')

@section('title', $section->course?->name . ' — Section')
@section('heading', $section->course?->name ?? 'Section')

@section('content')

<div class="mb-4">
    <a href="{{ route('portal.sections.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">← Back to my sections</a>
</div>

{{-- Section info --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Course Code</p>
            <p class="font-mono font-medium text-gray-900 dark:text-white">{{ $section->course?->code }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Term</p>
            <p class="text-gray-900 dark:text-white">{{ $section->academicTerm?->name }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Room</p>
            <p class="text-gray-900 dark:text-white">{{ $section->room ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Enrolled</p>
            <p class="text-gray-900 dark:text-white">{{ $enrollments->count() }} / {{ $section->capacity }}</p>
        </div>
    </div>
    @if(!empty($section->schedule))
    <div class="mt-3 flex flex-wrap gap-1.5">
        @foreach($section->schedule as $slot)
        <span class="text-xs px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-md">
            {{ $slot['day'] ?? '' }} {{ $slot['start_time'] ?? '' }}–{{ $slot['end_time'] ?? '' }}
        </span>
        @endforeach
    </div>
    @endif
</div>

{{-- Students & grades --}}
@if($enrollments->isEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-sm">No students enrolled in this section.</p>
</div>
@else
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
        <h3 class="font-semibold text-gray-900 dark:text-white">Students &amp; Grades</h3>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-700">
        @foreach($enrollments as $enrollment)
        @php
            $student = $enrollment->student;
            $sUser   = $student?->user;
            $grade   = $enrollment->grade;
        @endphp
        <div x-data="{ open: false }" class="p-4">
            <div class="flex items-center justify-between gap-4 cursor-pointer" @click="open = !open">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 font-semibold text-sm shrink-0">
                        {{ strtoupper(substr($sUser?->first_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $sUser?->first_name }} {{ $sUser?->last_name }}</p>
                        <p class="text-xs text-gray-400">{{ $student?->student_number }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($grade?->letter_grade)
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full text-sm font-bold
                        {{ in_array($grade->letter_grade, ['A+','A','A-']) ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' :
                           (in_array($grade->letter_grade, ['B+','B','B-']) ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' :
                           (in_array($grade->letter_grade, ['C+','C','C-']) ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' :
                           'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300')) }}">
                        {{ $grade->letter_grade }}
                    </span>
                    @elseif($grade)
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $grade->total ?? '—' }}%</span>
                    @else
                    <span class="text-xs text-gray-400">No grade</span>
                    @endif
                    <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            {{-- Grade form (collapsible) --}}
            <div x-show="open" x-cloak class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <form method="POST" action="{{ route('portal.sections.grade', $section) }}" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @csrf
                    <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}" />

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Midterm</label>
                        <input type="number" name="midterm" min="0" max="100" step="0.01"
                               value="{{ $grade?->midterm }}"
                               class="w-full px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Final</label>
                        <input type="number" name="final" min="0" max="100" step="0.01"
                               value="{{ $grade?->final }}"
                               class="w-full px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Coursework</label>
                        <input type="number" name="coursework" min="0" max="100" step="0.01"
                               value="{{ $grade?->coursework }}"
                               class="w-full px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Total</label>
                        <input type="number" name="total" min="0" max="100" step="0.01"
                               value="{{ $grade?->total }}"
                               class="w-full px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Letter Grade</label>
                        <input type="text" name="letter_grade" maxlength="5"
                               value="{{ $grade?->letter_grade }}"
                               placeholder="A+, B, C−…"
                               class="w-full px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Grade Points</label>
                        <input type="number" name="grade_points" min="0" max="4" step="0.01"
                               value="{{ $grade?->grade_points }}"
                               class="w-full px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="sm:col-span-2 flex items-end">
                        <button type="submit"
                                class="w-full px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                            Save Grade
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
