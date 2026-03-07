@extends('dashboard.layouts.app')

@section('title', $course->code . ' — ' . $course->name)
@section('heading', $course->code . ' — ' . $course->name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.courses.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">Courses</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium truncate">{{ $course->code }}</span>
</nav>

{{-- Course info card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $course->name }}</h2>
            <p class="text-sm text-gray-500 mt-0.5" dir="rtl">{{ $course->name_ar }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $course->is_active ? 'Active' : 'Inactive' }}
            </span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->is_elective ? 'bg-purple-50 text-purple-700' : 'bg-teal-50 text-teal-700' }}">
                {{ $course->is_elective ? 'Elective' : 'Required' }}
            </span>
            <a href="{{ route('dashboard.courses.edit', $course) }}"
               class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                Edit Course
            </a>
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Code</dt>
            <dd><span class="font-mono text-sm bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $course->code }}</span></dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Level</dt>
            <dd class="text-gray-700">Level {{ $course->level }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Credit Hours</dt>
            <dd class="text-gray-700">{{ $course->credit_hours }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Lecture Hours</dt>
            <dd class="text-gray-700">{{ $course->lecture_hours }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Lab Hours</dt>
            <dd class="text-gray-700">{{ $course->lab_hours }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Description</dt>
            <dd class="text-gray-700">{{ $course->description ?? '—' }}</dd>
        </div>
    </dl>
</div>

{{-- Departments --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Departments</h3>
    @if($course->departments->isEmpty())
        <p class="text-sm text-gray-400">No departments assigned.</p>
    @else
        <div class="flex flex-wrap gap-2">
            @foreach($course->departments as $dept)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium {{ $dept->pivot->is_owner ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-50 text-gray-700 border border-gray-200' }}">
                    {{ $dept->name }} ({{ $dept->code }})
                    @if($dept->pivot->is_owner)
                        <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">Owner</span>
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
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Prerequisites</h3>
        @if($course->prerequisites->isEmpty())
            <p class="text-sm text-gray-400">No prerequisites.</p>
        @else
            <ul class="space-y-2">
                @foreach($course->prerequisites as $prereq)
                    <li>
                        <a href="{{ route('dashboard.courses.show', $prereq) }}" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 hover:underline">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $prereq->code }}</span>
                            {{ $prereq->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Required By</h3>
        @if($course->dependents->isEmpty())
            <p class="text-sm text-gray-400">No courses depend on this one.</p>
        @else
            <ul class="space-y-2">
                @foreach($course->dependents as $dep)
                    <li>
                        <a href="{{ route('dashboard.courses.show', $dep) }}" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 hover:underline">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $dep->code }}</span>
                            {{ $dep->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

{{-- Sections --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Sections ({{ $course->sections->count() }})</h3>
    </div>

    @if($course->sections->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400">No sections assigned to this course yet.</div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">Section #</th>
                    <th class="px-5 py-3 text-start">Academic Term</th>
                    <th class="px-5 py-3 text-start">Max Students</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($course->sections as $section)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">{{ $section->section_number ?? $section->id }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $section->academicTerm?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $section->max_students ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
