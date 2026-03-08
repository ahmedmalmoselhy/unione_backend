@extends('dashboard.layouts.app')

@section('title', $employee->user->first_name . ' ' . $employee->user->last_name)
@section('heading', $employee->user->first_name . ' ' . $employee->user->last_name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.employees.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">Employees</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium truncate">{{ $employee->user->first_name }} {{ $employee->user->last_name }}</span>
</nav>

{{-- Employee info card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6">

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
                <h2 class="text-xl font-bold text-gray-900">{{ $employee->user->first_name }} {{ $employee->user->last_name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $employee->job_title }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $employee->user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $employee->user->is_active ? 'Active' : 'Inactive' }}
            </span>
            @php
                $typeStyles = [
                    'full_time' => 'bg-green-50 text-green-700',
                    'part_time' => 'bg-amber-50 text-amber-700',
                    'contract'  => 'bg-blue-50 text-blue-700',
                ];
                $typeLabels = [
                    'full_time' => 'Full Time',
                    'part_time' => 'Part Time',
                    'contract'  => 'Contract',
                ];
            @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeStyles[$employee->employment_type] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $typeLabels[$employee->employment_type] ?? ucfirst($employee->employment_type) }}
            </span>
            @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                <a href="{{ route('dashboard.employees.edit', $employee) }}"
                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Edit Employee
                </a>
            @endif
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Staff Number</dt>
            <dd><span class="font-mono text-sm bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $employee->staff_number }}</span></dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Email</dt>
            <dd class="text-gray-700">{{ $employee->user->email }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Phone</dt>
            <dd class="text-gray-700">{{ $employee->user->phone ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">National ID</dt>
            <dd class="text-gray-700 font-mono text-xs">{{ $employee->user->national_id }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Gender</dt>
            <dd class="text-gray-700">{{ ucfirst($employee->user->gender) }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Date of Birth</dt>
            <dd class="text-gray-700">{{ $employee->user->date_of_birth?->format('M d, Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Department</dt>
            <dd class="text-gray-700">{{ $employee->department->name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Faculty</dt>
            <dd class="text-gray-700">{{ $employee->department->faculty?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Salary</dt>
            <dd class="text-gray-700">{{ $employee->salary ? number_format($employee->salary, 2) . ' EGP' : '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Hire Date</dt>
            <dd class="text-gray-700">{{ $employee->hired_at->format('M d, Y') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Termination Date</dt>
            <dd class="text-gray-700">{{ $employee->terminated_at?->format('M d, Y') ?? '—' }}</dd>
        </div>
    </dl>

</div>

@endsection
