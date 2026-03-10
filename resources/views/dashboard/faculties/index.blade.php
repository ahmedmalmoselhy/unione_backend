@extends('dashboard.layouts.app')

@section('title', __('faculties.title'))
@section('heading', __('faculties.title'))

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
    'action'  => route('dashboard.faculties.index'),
    'search'  => request('search'),
    'filters' => [
        ['name' => 'enrollment_type', 'label' => __('common.enrollment_type'), 'value' => request('enrollment_type'), 'options' => ['immediate' => __('faculties.enrollment_type_immediate'), 'deferred' => __('faculties.enrollment_type_deferred'), 'none' => __('faculties.enrollment_type_none')]],
        ['name' => 'status', 'label' => __('common.status'), 'value' => request('status'), 'options' => ['active' => __('common.active'), 'inactive' => __('common.inactive')]],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $faculties->total() }} {{ Str::plural('faculty', $faculties->total()) }} total</p>
    @if(auth()->user()->isSystemAdmin())
        <a href="{{ route('dashboard.faculties.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('faculties.new_faculty') }}
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    @if($faculties->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400 dark:text-gray-500">
            No faculties found. <a href="{{ route('dashboard.faculties.create') }}" class="text-blue-600 hover:underline">{{ __('faculties.create_first') }}</a>
        </div>
    @else
        <table class="w-full text-sm dark:text-gray-200">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('common.name') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.code') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.enrollment_type') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.dean') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.status') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($faculties as $faculty)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $faculty->local_name }}</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5" dir="auto">{{ app()->getLocale() === 'ar' ? $faculty->name : $faculty->name_ar }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded">{{ $faculty->code }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                                $typeStyles = [
                                    'immediate' => 'bg-blue-50 text-blue-700',
                                    'deferred'  => 'bg-indigo-50 text-indigo-700',
                                    'none'      => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeStyles[$faculty->enrollment_type] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                {{ __('faculties.enrollment_type_' . $faculty->enrollment_type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">
                            @if($faculty->dean)
                                {{ $faculty->dean->first_name }} {{ $faculty->dean->last_name }}
                            @else
                                <span class="text-gray-400 dark:text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $faculty->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $faculty->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.faculties.show', $faculty) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                    {{ __('common.view') }}
                                </a>
                                @if(auth()->user()->isSystemAdmin())
                                    <a href="{{ route('dashboard.faculties.edit', $faculty) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        {{ __('common.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.faculties.destroy', $faculty) }}"
                                          onsubmit="return confirm('{{ __('faculties.confirm_delete', ['name' => addslashes($faculty->local_name)]) }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                            {{ __('common.delete') }}
                                        </button>
                                    </form>
                                    <a href="{{ route('dashboard.faculties.assign-admin', $faculty) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-violet-600 hover:text-violet-700 hover:bg-violet-50 dark:hover:bg-violet-900/20 rounded-lg transition-colors">
                                        {{ __('common.assign_admin') }}
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($faculties->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $faculties->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
