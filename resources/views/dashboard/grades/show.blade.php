@extends('dashboard.layouts.app')

@section('title', __('grades.grade_details'))
@section('heading', __('grades.grade_details'))

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.grades.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">{{ __('grades.title') }}</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium truncate">#{{ $grade->id }}</span>
</nav>

{{-- Student & Course info --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                {{ $grade->enrollment?->student?->user?->first_name }} {{ $grade->enrollment?->student?->user?->last_name }}
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">
                <span class="font-mono bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs">{{ $grade->enrollment?->student?->student_number }}</span>
                <span class="mx-1.5">·</span>
                <span class="font-mono bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs">{{ $grade->enrollment?->section?->course?->code }}</span>
                {{ $grade->enrollment?->section?->course?->name }}
            </p>
        </div>
        @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
            <a href="{{ route('dashboard.grades.edit', $grade) }}"
               class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors shrink-0">
                {{ __('grades.edit_grade') }}
            </a>
        @endif
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm mb-6">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('grades.academic_term') }}</dt>
            <dd class="text-gray-700">{{ $grade->enrollment?->academicTerm?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('grades.professor') }}</dt>
            <dd class="text-gray-700">{{ $grade->enrollment?->section?->professor?->user?->first_name }} {{ $grade->enrollment?->section?->professor?->user?->last_name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('grades.enrollment_status') }}</dt>
            @php
                $statusLabels = [
                    'registered' => __('enrollments.status_registered'),
                    'completed'  => __('enrollments.status_completed'),
                    'dropped'    => __('enrollments.status_dropped'),
                    'failed'     => __('enrollments.status_failed'),
                    'incomplete' => __('enrollments.status_incomplete'),
                ];
            @endphp
            <dd class="text-gray-700">{{ $statusLabels[$grade->enrollment?->status] ?? ucfirst($grade->enrollment?->status) }}</dd>
        </div>
    </dl>

    {{-- Score breakdown --}}
    <div class="border-t border-gray-100 pt-6">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('grades.score_breakdown') }}</h3>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">{{ __('grades.midterm') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $grade->midterm ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">{{ __('grades.coursework') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $grade->coursework ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">{{ __('grades.final') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $grade->final ?? '—' }}</p>
            </div>
            <div class="bg-blue-50 rounded-xl p-4 text-center">
                <p class="text-xs text-blue-500 uppercase tracking-wider mb-1">{{ __('grades.total') }}</p>
                <p class="text-2xl font-bold text-blue-700">{{ $grade->total ?? '—' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-5 mt-5">
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">{{ __('grades.letter_grade') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $grade->letter_grade ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">{{ __('grades.grade_points') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $grade->grade_points ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">{{ __('grades.graded_by') }}</p>
                <p class="text-sm font-medium text-gray-700 mt-1">{{ $grade->gradedBy?->first_name }} {{ $grade->gradedBy?->last_name ?? '—' }}</p>
                @if($grade->graded_at)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $grade->graded_at->format('M d, Y h:i A') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
