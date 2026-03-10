@extends('dashboard.layouts.app')

@section('title', __('courses.title'))
@section('heading', __('courses.title'))

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
    'action'  => route('dashboard.courses.index'),
    'search'  => request('search'),
    'filters' => [
        ['name' => 'level', 'label' => __('courses.level'), 'value' => request('level'), 'options' => [1 => __('courses.level_n', ['n' => 1]), 2 => __('courses.level_n', ['n' => 2]), 3 => __('courses.level_n', ['n' => 3]), 4 => __('courses.level_n', ['n' => 4]), 5 => __('courses.level_n', ['n' => 5]), 6 => __('courses.level_n', ['n' => 6])]],
        ['name' => 'is_elective', 'label' => __('common.type'), 'value' => request('is_elective'), 'options' => ['0' => __('courses.required'), '1' => __('courses.elective')]],
        ['name' => 'status', 'label' => __('common.status'), 'value' => request('status'), 'options' => ['active' => __('common.active'), 'inactive' => __('common.inactive')]],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $courses->total() }} {{ Str::plural(__('courses.title'), $courses->total()) }}</p>
    @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
        <a href="{{ route('dashboard.courses.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('courses.new_course') }}
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($courses->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400">
            {{ __('courses.no_courses_found') }}
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('courses.code') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.name') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('courses.level') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('courses.credit_hrs_short') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('courses.departments') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.type') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.status') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($courses as $course)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $course->code }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-gray-900">{{ $course->local_name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5" dir="auto">{{ app()->getLocale() === 'ar' ? $course->name : $course->name_ar }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $course->level }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $course->credit_hours }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap gap-1">
                                @foreach($course->departments as $dept)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $dept->pivot->is_owner ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $dept->code }}
                                        @if($dept->pivot->is_owner)
                                            <svg class="w-3 h-3 ml-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->is_elective ? 'bg-purple-50 text-purple-700' : 'bg-teal-50 text-teal-700' }}">
                                {{ $course->is_elective ? __('courses.elective') : __('courses.required') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $course->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.courses.show', $course) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    {{ __('common.view') }}
                                </a>
                                @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                                    <a href="{{ route('dashboard.courses.edit', $course) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                        {{ __('common.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.courses.destroy', $course) }}"
                                          onsubmit="return confirm('{{ addslashes(__('courses.confirm_delete', ['name' => $course->code . ' — ' . $course->local_name])) }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
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
        @if($courses->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $courses->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
