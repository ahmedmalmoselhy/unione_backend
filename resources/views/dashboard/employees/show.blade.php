@extends('dashboard.layouts.app')

@section('title', $employee->user->first_name . ' ' . $employee->user->last_name)
@section('heading', $employee->user->first_name . ' ' . $employee->user->last_name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.employees.index') }}" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">{{ __('employees.title') }}</a>
    <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 dark:text-gray-300 font-medium truncate">{{ $employee->user->first_name }} {{ $employee->user->last_name }}</span>
</nav>

{{-- Employee info card --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            @if($employee->user->avatar_path)
                <img src="{{ Storage::disk('public')->url($employee->user->avatar_path) }}"
                     alt="{{ $employee->user->first_name }}"
                     class="w-14 h-14 rounded-full object-cover border border-gray-200 shrink-0">
            @else
                <div class="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-lg shrink-0">
                    {{ strtoupper(substr($employee->user->first_name, 0, 1)) }}{{ strtoupper(substr($employee->user->last_name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $employee->user->first_name }} {{ $employee->user->last_name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $employee->job_title }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $employee->user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $employee->user->is_active ? __('common.active') : __('common.inactive') }}
            </span>
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
            @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                <a href="{{ route('dashboard.employees.edit', $employee) }}"
                   class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    {{ __('employees.edit_employee') }}
                </a>
            @endif
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('employees.staff_number_full') }}</dt>
            <dd><span class="font-mono text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded">{{ $employee->staff_number }}</span></dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('common.email') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $employee->user->email }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('employees.phone') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $employee->user->phone ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('employees.national_id') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300 font-mono text-xs">{{ $employee->user->national_id }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('employees.gender') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ ucfirst($employee->user->gender) }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('employees.date_of_birth') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $employee->user->date_of_birth?->format('M d, Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('common.department') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $employee->department->name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('common.faculty') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $employee->department->faculty?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('employees.salary') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $employee->salary ? number_format($employee->salary, 2) . ' EGP' : '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('employees.hire_date') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $employee->hired_at->format('M d, Y') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('employees.termination_date') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $employee->terminated_at?->format('M d, Y') ?? '—' }}</dd>
        </div>
    </dl>

</div>

@endsection
