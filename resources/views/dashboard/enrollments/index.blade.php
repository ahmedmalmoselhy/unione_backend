@extends('dashboard.layouts.app')

@section('title', __('enrollments.title'))
@section('heading', __('enrollments.title'))

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

{{-- Search / Filter --}}
@include('dashboard.partials._filter-bar', [
    'action'  => route('dashboard.enrollments.index'),
    'search'  => request('search'),
    'filters' => [
        ['name' => 'term_id', 'label' => __('enrollments.term'), 'value' => request('term_id'), 'options' => $terms->toArray()],
        ['name' => 'status', 'label' => __('common.status'), 'value' => request('status'), 'options' => [
            'registered'  => __('enrollments.status_registered'),
            'completed'   => __('enrollments.status_completed'),
            'dropped'     => __('enrollments.status_dropped'),
            'failed'      => __('enrollments.status_failed'),
            'incomplete'  => __('enrollments.status_incomplete'),
        ]],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $enrollments->total() }} {{ __('enrollments.title') }}</p>
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.enrollments.export', request()->query()) }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-200 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            {{ __('common.export') }}
        </a>
        @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
            <a href="{{ route('dashboard.enrollments.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('enrollments.new_enrollment') }}
            </a>
        @endif
    </div>
</div>

{{-- Table --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    @if($enrollments->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400 dark:text-gray-500">
            No enrollments found. <a href="{{ route('dashboard.enrollments.create') }}" class="text-blue-600 hover:underline">{{ __('enrollments.no_enrollments_found') }}</a>
        </div>
    @else
        <table class="w-full text-sm dark:text-gray-200">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('enrollments.student') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('enrollments.course_section') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('enrollments.term') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.status') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('enrollments.registered') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($enrollments as $enrollment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $enrollment->student?->user?->first_name }} {{ $enrollment->student?->user?->last_name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $enrollment->student?->student_number }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded">{{ $enrollment->section?->course?->code }}</span>
                            <span class="ml-1.5 text-gray-600 dark:text-gray-400">{{ $enrollment->section?->course?->local_name }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400 text-xs">{{ $enrollment->academicTerm?->local_name ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $statusColors = [
                                    'registered' => 'bg-blue-100 text-blue-700',
                                    'completed'  => 'bg-green-100 text-green-700',
                                    'dropped'    => 'bg-yellow-100 text-yellow-700',
                                    'failed'     => 'bg-red-100 text-red-700',
                                    'incomplete' => 'bg-gray-100 text-gray-500',
                                ];
                                $statusLabels = [
                                    'registered' => __('enrollments.status_registered'),
                                    'completed'  => __('enrollments.status_completed'),
                                    'dropped'    => __('enrollments.status_dropped'),
                                    'failed'     => __('enrollments.status_failed'),
                                    'incomplete' => __('enrollments.status_incomplete'),
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$enrollment->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ $statusLabels[$enrollment->status] ?? ucfirst($enrollment->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500 dark:text-gray-400">{{ $enrollment->registered_at?->format('M d, Y') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.enrollments.show', $enrollment) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                    {{ __('common.view') }}
                                </a>
                                @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                                    <a href="{{ route('dashboard.enrollments.edit', $enrollment) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        {{ __('common.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.enrollments.destroy', $enrollment) }}"
                                          onsubmit="return confirm('{{ addslashes(__('enrollments.confirm_delete')) }}')">
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

        @if($enrollments->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $enrollments->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
