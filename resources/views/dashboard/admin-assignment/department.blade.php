@extends('dashboard.layouts.app')

@section('title', 'Assign Department Administrator — ' . $department->name)
@section('heading', $department->name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.departments.index') }}" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">Departments</a>
    <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('dashboard.departments.show', $department) }}" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">{{ $department->name }}</a>
    <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 dark:text-gray-300 font-medium">Assign Administrator</span>
</nav>

{{-- Success / error --}}
@if(session('success'))
    <div class="mb-6 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-6 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
@endif

{{-- Faculty context --}}
<div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 mb-6 text-sm text-gray-500">
    Faculty: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $department->faculty->name }}</span>
</div>

{{-- Current admin card --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Current Department Administrator</h3>

    @if($currentAdmin)
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 font-bold text-sm shrink-0">
                    {{ strtoupper(substr($currentAdmin->first_name, 0, 1)) }}{{ strtoupper(substr($currentAdmin->last_name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $currentAdmin->first_name }} {{ $currentAdmin->last_name }}</p>
                    <p class="text-xs text-gray-400">{{ $currentAdmin->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('dashboard.departments.assign-admin.revoke', $department) }}"
                  onsubmit="return confirm('Revoke department administrator access for {{ addslashes($currentAdmin->first_name . ' ' . $currentAdmin->last_name) }}?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    Revoke Access
                </button>
            </form>
        </div>
    @else
        <p class="text-sm text-gray-400">No department administrator currently assigned.</p>
    @endif
</div>

{{-- Assign form --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
        {{ $currentAdmin ? 'Reassign Administrator' : 'Assign Administrator' }}
    </h3>

    @if($employees->isEmpty())
        <p class="text-sm text-gray-400">No employees found in this department. Add employees first.</p>
    @else
        <form method="POST" action="{{ route('dashboard.departments.assign-admin.store', $department) }}" class="space-y-4">
            @csrf

            <div>
                <label for="employee_user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Select Employee
                </label>
                <select name="employee_user_id" id="employee_user_id" required
                        class="w-full px-3 py-2 text-sm rounded-lg border {{ $errors->has('employee_user_id') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200' }} focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-md">
                    <option value="">Choose an employee…</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->user_id }}" {{ old('employee_user_id') == $employee->user_id ? 'selected' : '' }}>
                            {{ $employee->user->first_name }} {{ $employee->user->last_name }}
                            @if($employee->job_title) — {{ $employee->job_title }}@endif
                            ({{ $employee->staff_number }})
                        </option>
                    @endforeach
                </select>
                @error('employee_user_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-700 max-w-md">
                <strong>Note:</strong> The assigned employee will be required to set a new password on their next login.
                @if($currentAdmin) The current administrator's elevated access will be revoked. @endif
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                Assign as Department Administrator
            </button>
        </form>
    @endif
</div>

@endsection
