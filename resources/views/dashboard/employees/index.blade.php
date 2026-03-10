@extends('dashboard.layouts.app')

@section('title', __('employees.title'))
@section('heading', __('employees.title'))

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
    'action'  => route('dashboard.employees.index'),
    'search'  => request('search'),
    'filters' => [
        ['name' => 'department_id', 'label' => __('common.department'), 'value' => request('department_id'), 'options' => $departments->toArray()],
        ['name' => 'employment_type', 'label' => __('employees.employment_type'), 'value' => request('employment_type'), 'options' => ['full_time' => __('employees.type_full_time'), 'part_time' => __('employees.type_part_time'), 'contract' => __('employees.type_contract')]],
        ['name' => 'status', 'label' => __('common.status'), 'value' => request('status'), 'options' => ['active' => __('common.active'), 'inactive' => __('common.inactive')]],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $employees->total() }} {{ Str::plural(__('employees.title'), $employees->total()) }}</p>
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.employees.export', request()->query()) }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-200 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            {{ __('common.export') }}
        </a>
        @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
            <a href="{{ route('dashboard.employees.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('employees.new_employee') }}
            </a>
        @endif
    </div>
</div>

{{-- Table --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    @if($employees->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400 dark:text-gray-500">
            {{ __('employees.no_employees_found') }}
        </div>
    @else
        <table class="w-full text-sm dark:text-gray-200">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('common.name') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('employees.staff_number') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.department') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('employees.job_title') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('employees.employment_type') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.status') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($employees as $employee)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $employee->user->first_name }} {{ $employee->user->last_name }}</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $employee->user->email }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded">{{ $employee->staff_number }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-gray-700 dark:text-gray-300">{{ $employee->department->local_name }}</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $employee->department->faculty?->local_name }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">{{ $employee->job_title }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $typeStyles = [
                                    'full_time' => 'bg-green-50 text-green-700',
                                    'part_time' => 'bg-amber-50 text-amber-700',
                                    'contract'  => 'bg-blue-50 text-blue-700',
                                ];
                                $typeLabels = [
                                    'full_time' => __('employees.type_full_time'),
                                    'part_time' => __('employees.type_part_time'),
                                    'contract'  => __('employees.type_contract'),
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeStyles[$employee->employment_type] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                {{ $typeLabels[$employee->employment_type] ?? ucfirst($employee->employment_type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $employee->user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $employee->user->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.employees.show', $employee) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                    {{ __('common.view') }}
                                </a>
                                @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                                    <a href="{{ route('dashboard.employees.edit', $employee) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        {{ __('common.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.employees.destroy', $employee) }}"
                                          onsubmit="return confirm('{{ addslashes(__('employees.confirm_delete', ['name' => $employee->user->first_name . ' ' . $employee->user->last_name])) }}')">
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
        @if($employees->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $employees->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
