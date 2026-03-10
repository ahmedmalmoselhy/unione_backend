@extends('dashboard.layouts.app')

@section('title', $section->course->code . ' — Section #' . $section->id)
@section('heading', $section->course->code . ' — Section #' . $section->id)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.sections.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">{{ __('sections.title') }}</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium truncate">{{ $section->course->code }} #{{ $section->id }}</span>
</nav>

{{-- Section info card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $section->course->name }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                <span class="font-mono bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs">{{ $section->course->code }}</span>
                <span class="mx-1.5">·</span>
                Section #{{ $section->id }}
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $section->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $section->is_active ? __('common.active') : __('common.inactive') }}
            </span>
            @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                <a href="{{ route('dashboard.sections.edit', $section) }}"
                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    {{ __('sections.edit_section') }}
                </a>
            @endif
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('sections.professor') }}</dt>
            <dd class="text-gray-700">{{ $section->professor?->user?->first_name }} {{ $section->professor?->user?->last_name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('sections.academic_term') }}</dt>
            <dd class="text-gray-700">{{ $section->academicTerm?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('sections.capacity') }}</dt>
            <dd class="text-gray-700">{{ $section->capacity }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('sections.room') }}</dt>
            <dd class="text-gray-700">{{ $section->room ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('sections.enrolled') }}</dt>
            <dd class="text-gray-700">{{ $section->enrollments->count() }} / {{ $section->capacity }}</dd>
        </div>
    </dl>
</div>

{{-- Schedule --}}
@if($section->schedule && count($section->schedule))
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('sections.schedule') }}</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($section->schedule as $slot)
                <div class="flex items-center gap-2 px-3 py-2 rounded-lg border {{ ($slot['type'] ?? 'lecture') === 'lab' ? 'border-purple-200 bg-purple-50' : 'border-blue-200 bg-blue-50' }}">
                    <span class="text-xs font-semibold {{ ($slot['type'] ?? 'lecture') === 'lab' ? 'text-purple-700' : 'text-blue-700' }}">
                        {{ ucfirst($slot['day'] ?? '') }}
                    </span>
                    <span class="text-xs text-gray-600">{{ $slot['start_time'] ?? '' }} – {{ $slot['end_time'] ?? '' }}</span>
                    <span class="text-xs px-1.5 py-0.5 rounded {{ ($slot['type'] ?? 'lecture') === 'lab' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }}">
                        {{ ucfirst($slot['type'] ?? 'lecture') }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Enrollments --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ __('sections.enrolled_students', ['count' => $section->enrollments->count()]) }}</h3>
    </div>

    @if($section->enrollments->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400">{{ __('sections.no_students_enrolled') }}</div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('sections.student_name') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.email') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.status') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('sections.enrolled_at') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($section->enrollments as $enrollment)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-900">
                            {{ $enrollment->student?->user?->first_name }} {{ $enrollment->student?->user?->last_name }}
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $enrollment->student?->user?->email }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                {{ ucfirst($enrollment->status ?? 'enrolled') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $enrollment->created_at?->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
