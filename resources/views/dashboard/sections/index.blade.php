@extends('dashboard.layouts.app')

@section('title', __('sections.title'))
@section('heading', __('sections.title'))

@section('content')

{{-- Flash messages --}}
@if(session('success'))
    <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
@endif

@if($errors->has('delete'))
    <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $errors->first('delete') }}
    </div>
@endif

{{-- Search / Filter --}}
@include('dashboard.partials._filter-bar', [
    'action'  => route('dashboard.sections.index'),
    'search'  => request('search'),
    'filters' => [
        ['name' => 'course_id', 'label' => __('common.course'), 'value' => request('course_id'), 'options' => $courses->toArray()],
        ['name' => 'term_id', 'label' => __('common.term'), 'value' => request('term_id'), 'options' => $terms->toArray()],
        ['name' => 'status', 'label' => __('common.status'), 'value' => request('status'), 'options' => ['active' => __('common.active'), 'inactive' => __('common.inactive')]],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $sections->total() }} {{ Str::plural(__('sections.title'), $sections->total()) }}</p>
    @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
        <a href="{{ route('dashboard.sections.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('sections.new_section') }}
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    @if($sections->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400 dark:text-gray-500">
            {{ __('sections.no_sections_found') }}
        </div>
    @else
        <table class="w-full text-sm dark:text-gray-200">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('common.course') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('sections.professor') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.term') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('sections.capacity') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('sections.room') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('sections.schedule') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.status') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($sections as $section)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded">{{ $section->course->code }}</span>
                            <span class="ml-1.5 text-gray-700 dark:text-gray-300">{{ $section->course->local_name }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">
                            {{ $section->professor?->user?->first_name }} {{ $section->professor?->user?->last_name }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($section->academicTerm)
                                <span class="text-xs text-gray-600 dark:text-gray-400">{{ $section->academicTerm->local_name }}</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">{{ $section->capacity }}</td>
                        <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">{{ $section->room ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            @if($section->schedule && count($section->schedule))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($section->schedule as $slot)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ ($slot['type'] ?? 'lecture') === 'lab' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }}">
                                            {{ ucfirst(substr($slot['day'] ?? '', 0, 3)) }}
                                            {{ $slot['start_time'] ?? '' }}–{{ $slot['end_time'] ?? '' }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $section->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $section->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.sections.show', $section) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                    {{ __('common.view') }}
                                </a>
                                @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                                    <a href="{{ route('dashboard.sections.edit', $section) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        {{ __('common.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.sections.destroy', $section) }}"
                                          onsubmit="return confirm('{{ addslashes(__('sections.confirm_delete')) }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                            {{ __('common.delete') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($sections->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $sections->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
