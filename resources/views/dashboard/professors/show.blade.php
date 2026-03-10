@extends('dashboard.layouts.app')

@section('title', $professor->user->first_name . ' ' . $professor->user->last_name)
@section('heading', $professor->user->first_name . ' ' . $professor->user->last_name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.professors.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">{{ __('professors.title') }}</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium truncate">{{ $professor->user->first_name }} {{ $professor->user->last_name }}</span>
</nav>

{{-- Professor info card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-8">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            @if($professor->user->avatar_path)
                <img src="{{ Storage::disk('public')->url($professor->user->avatar_path) }}"
                     alt="{{ $professor->user->first_name }}"
                     class="w-14 h-14 rounded-full object-cover border border-gray-200 shrink-0">
            @else
                <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg shrink-0">
                    {{ strtoupper(substr($professor->user->first_name, 0, 1)) }}{{ strtoupper(substr($professor->user->last_name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $professor->user->first_name }} {{ $professor->user->last_name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $professor->specialization }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $professor->user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $professor->user->is_active ? __('common.active') : __('common.inactive') }}
            </span>
            @php
                $rankStyles = [
                    'lecturer'             => 'bg-gray-100 text-gray-600',
                    'assistant_professor'  => 'bg-blue-50 text-blue-700',
                    'associate_professor'  => 'bg-indigo-50 text-indigo-700',
                    'professor'            => 'bg-purple-50 text-purple-700',
                ];
                $rankLabels = [
                    'lecturer'             => __('professors.rank_lecturer'),
                    'assistant_professor'  => __('professors.rank_assistant_professor'),
                    'associate_professor'  => __('professors.rank_associate_professor'),
                    'professor'            => __('professors.rank_professor'),
                ];
            @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $rankStyles[$professor->academic_rank] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $rankLabels[$professor->academic_rank] ?? ucfirst($professor->academic_rank) }}
            </span>
            @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                <a href="{{ route('dashboard.professors.edit', $professor) }}"
                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    {{ __('professors.edit_professor') }}
                </a>
            @endif
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('professors.staff_number_full') }}</dt>
            <dd><span class="font-mono text-sm bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $professor->staff_number }}</span></dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('common.email') }}</dt>
            <dd class="text-gray-700">{{ $professor->user->email }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('professors.phone') }}</dt>
            <dd class="text-gray-700">{{ $professor->user->phone ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('professors.national_id') }}</dt>
            <dd class="text-gray-700 font-mono text-xs">{{ $professor->user->national_id }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('professors.gender') }}</dt>
            <dd class="text-gray-700">{{ ucfirst($professor->user->gender) }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('professors.date_of_birth') }}</dt>
            <dd class="text-gray-700">{{ $professor->user->date_of_birth?->format('M d, Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('common.department') }}</dt>
            <dd class="text-gray-700">{{ $professor->department->name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('common.faculty') }}</dt>
            <dd class="text-gray-700">{{ $professor->department->faculty?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('professors.office_location') }}</dt>
            <dd class="text-gray-700">{{ $professor->office_location ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('professors.hire_date') }}</dt>
            <dd class="text-gray-700">{{ $professor->hired_at->format('M d, Y') }}</dd>
        </div>
    </dl>

</div>

{{-- Sections --}}
<div class="flex items-center justify-between mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">
        {{ __('professors.assigned_sections') }}
        <span class="ml-2 text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full normal-case tracking-normal">
            {{ $professor->sections->count() }}
        </span>
    </h3>
</div>

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($professor->sections->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400">
            {{ __('professors.no_sections_yet') }}
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('grades.course') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('students.academic_year') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('enrollments.term') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('sections.room') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('sections.capacity') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($professor->sections as $section)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-gray-900">{{ $section->course->name ?? '—' }}</div>
                            <div class="text-xs text-gray-400 mt-0.5 font-mono">{{ $section->course->code ?? '' }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $section->academic_year }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $section->semester }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $section->room ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $section->capacity }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $section->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $section->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
