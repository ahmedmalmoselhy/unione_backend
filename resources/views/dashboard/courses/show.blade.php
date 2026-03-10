@extends('dashboard.layouts.app')

@section('title', $course->code . ' — ' . $course->name)
@section('heading', $course->code . ' — ' . $course->name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.courses.index') }}" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">{{ __('courses.title') }}</a>
    <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 dark:text-gray-300 font-medium truncate">{{ $course->code }}</span>
</nav>

{{-- Course info card --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $course->name }}</h2>
            <p class="text-sm text-gray-500 mt-0.5" dir="rtl">{{ $course->name_ar }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $course->is_active ? __('common.active') : __('common.inactive') }}
            </span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->is_elective ? 'bg-purple-50 text-purple-700' : 'bg-teal-50 text-teal-700' }}">
                {{ $course->is_elective ? __('courses.elective') : __('courses.required') }}
            </span>
            @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                <a href="{{ route('dashboard.courses.edit', $course) }}"
                   class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    {{ __('courses.edit_course') }}
                </a>
            @endif
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('courses.code') }}</dt>
            <dd><span class="font-mono text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded">{{ $course->code }}</span></dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('courses.level') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ __('courses.level_n', ['n' => $course->level]) }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('courses.credit_hours') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $course->credit_hours }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('courses.lecture_hours') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $course->lecture_hours }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('courses.lab_hours') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $course->lab_hours }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('courses.description') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $course->description ?? '—' }}</dd>
        </div>
    </dl>
</div>

{{-- Departments --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('courses.departments') }}</h3>
    @if($course->departments->isEmpty())
        <p class="text-sm text-gray-400">{{ __('courses.no_departments') }}</p>
    @else
        <div class="flex flex-wrap gap-2">
            @foreach($course->departments as $dept)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium {{ $dept->pivot->is_owner ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600' }}">
                    {{ $dept->name }} ({{ $dept->code }})
                    @if($dept->pivot->is_owner)
                        <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">{{ __('courses.owner') }}</span>
                    @endif
                    @if($dept->faculty)
                        <span class="text-xs text-gray-400">— {{ $dept->faculty->name }}</span>
                    @endif
                </span>
            @endforeach
        </div>
    @endif
</div>

{{-- Prerequisites --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('courses.prerequisites') }}</h3>
        @if($course->prerequisites->isEmpty())
            <p class="text-sm text-gray-400">{{ __('courses.no_prerequisites') }}</p>
        @else
            <ul class="space-y-2">
                @foreach($course->prerequisites as $prereq)
                    <li>
                        <a href="{{ route('dashboard.courses.show', $prereq) }}" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 hover:underline">
                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded">{{ $prereq->code }}</span>
                            {{ $prereq->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('courses.required_by') }}</h3>
        @if($course->dependents->isEmpty())
            <p class="text-sm text-gray-400">{{ __('courses.no_dependents') }}</p>
        @else
            <ul class="space-y-2">
                @foreach($course->dependents as $dep)
                    <li>
                        <a href="{{ route('dashboard.courses.show', $dep) }}" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 hover:underline">
                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded">{{ $dep->code }}</span>
                            {{ $dep->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

{{-- Sections --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ __('courses.sections') }} ({{ $course->sections->count() }})</h3>
    </div>

    @if($course->sections->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">{{ __('courses.no_sections') }}</div>
    @else
        <table class="w-full text-sm dark:text-gray-200">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('courses.section_number') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('courses.academic_term') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('courses.max_students') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($course->sections as $section)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-5 py-3">{{ $section->section_number ?? $section->id }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ $section->academicTerm?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ $section->max_students ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
