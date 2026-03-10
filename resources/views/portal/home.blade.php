@extends('portal.layouts.app')

@section('title', 'Home')
@section('heading', 'Welcome, ' . auth()->user()->first_name)

@section('content')

{{-- ─── STUDENT HOME ──────────────────────────────────────────── --}}
@if($student)

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    {{-- Student number --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Student ID</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $student->student_number }}</p>
    </div>
    {{-- GPA --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">GPA</p>
        <p class="text-2xl font-bold {{ ($student->gpa ?? 0) >= 3 ? 'text-green-600 dark:text-green-400' : (($student->gpa ?? 0) >= 2 ? 'text-amber-500' : 'text-red-500') }}">
            {{ number_format($student->gpa ?? 0, 2) }}
        </p>
    </div>
    {{-- Academic year --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Year / Semester</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">Y{{ $student->academic_year }} / S{{ $student->semester }}</p>
    </div>
    {{-- Status --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Status</p>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
            {{ $student->enrollment_status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
            {{ ucfirst($student->enrollment_status) }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Current enrollments --}}
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                Current Courses
                @if($current_term)
                    <span class="ms-2 text-xs font-normal text-gray-400">{{ $current_term->name }}</span>
                @endif
            </h3>
            <a href="{{ route('portal.enrollments.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">View all →</a>
        </div>

        @if($enrollments->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500">No active enrollments this term.</p>
        @else
            <div class="space-y-3">
                @foreach($enrollments as $enrollment)
                @php $course = $enrollment->section?->course; @endphp
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $course?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $course?->code }} · {{ $enrollment->section?->room }}</p>
                    </div>
                    @if($enrollment->grade)
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $enrollment->grade->letter_grade ?? $enrollment->grade->total }}</span>
                    @else
                        <span class="text-xs text-gray-400">In progress</span>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Announcements sidebar --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900 dark:text-white">Announcements</h3>
            <a href="{{ route('portal.announcements.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">All →</a>
        </div>
        @if($announcements->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500">No announcements.</p>
        @else
            <div class="space-y-3">
                @foreach($announcements as $ann)
                <div class="p-3 rounded-xl {{ $ann['is_read'] ? 'bg-gray-50 dark:bg-gray-700/30' : 'bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900' }}">
                    @if(!$ann['is_read'])
                        <span class="inline-block w-1.5 h-1.5 bg-blue-500 rounded-full mb-1"></span>
                    @endif
                    <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">{{ $ann['title'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($ann['published_at'])->diffForHumans() }}</p>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ─── PROFESSOR HOME ─────────────────────────────────────────── --}}
@elseif($professor)

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Staff Number</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $professor->staff_number }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Academic Rank</p>
        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ ucwords(str_replace('_', ' ', $professor->academic_rank ?? '—')) }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Active Sections</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $sections->count() }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                My Sections
                @if($current_term)
                    <span class="ms-2 text-xs font-normal text-gray-400">{{ $current_term->name }}</span>
                @endif
            </h3>
            <a href="{{ route('portal.sections.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">View all →</a>
        </div>
        @if($sections->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500">No active sections this term.</p>
        @else
            <div class="space-y-3">
                @foreach($sections as $section)
                <a href="{{ route('portal.sections.show', $section) }}"
                   class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $section->course?->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $section->course?->code }} · Room {{ $section->room }}</p>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $section->enrollments_count }} students</span>
                </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900 dark:text-white">Announcements</h3>
            <a href="{{ route('portal.announcements.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">All →</a>
        </div>
        @if($announcements->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500">No announcements.</p>
        @else
            <div class="space-y-3">
                @foreach($announcements as $ann)
                <div class="p-3 rounded-xl {{ $ann['is_read'] ? 'bg-gray-50 dark:bg-gray-700/30' : 'bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900' }}">
                    @if(!$ann['is_read'])
                        <span class="inline-block w-1.5 h-1.5 bg-blue-500 rounded-full mb-1"></span>
                    @endif
                    <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">{{ $ann['title'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($ann['published_at'])->diffForHumans() }}</p>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ─── EMPLOYEE HOME ──────────────────────────────────────────── --}}
@elseif($employee)

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Staff Number</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $employee->staff_number }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Job Title</p>
        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee->job_title ?? '—' }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Department</p>
        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee->department?->name ?? '—' }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Colleagues --}}
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">My Colleagues</h3>
        @if($colleagues->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500">No colleagues in this department.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($colleagues as $col)
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <div class="w-9 h-9 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-700 dark:text-purple-300 font-semibold text-sm shrink-0">
                        {{ strtoupper(substr($col->user->first_name ?? '?', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $col->user?->first_name }} {{ $col->user?->last_name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $col->job_title ?? $col->employment_type }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Announcements --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900 dark:text-white">Announcements</h3>
            <a href="{{ route('portal.announcements.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">All →</a>
        </div>
        @if($announcements->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500">No announcements.</p>
        @else
            <div class="space-y-3">
                @foreach($announcements as $ann)
                <div class="p-3 rounded-xl {{ $ann['is_read'] ? 'bg-gray-50 dark:bg-gray-700/30' : 'bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900' }}">
                    @if(!$ann['is_read'])
                        <span class="inline-block w-1.5 h-1.5 bg-blue-500 rounded-full mb-1"></span>
                    @endif
                    <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">{{ $ann['title'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($ann['published_at'])->diffForHumans() }}</p>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endif

@endsection
