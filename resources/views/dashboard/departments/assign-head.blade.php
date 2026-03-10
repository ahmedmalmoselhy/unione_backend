@extends('dashboard.layouts.app')

@section('title', __('departments.assign_head_page_title') . ' — ' . $department->name)
@section('heading', $department->name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.departments.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">{{ __('departments.title') }}</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('dashboard.departments.show', $department) }}" class="text-gray-400 hover:text-gray-700 transition-colors">{{ $department->name }}</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium">{{ __('departments.assign_head_page_title') }}</span>
</nav>

{{-- Flash messages --}}
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
    {{ __('departments.faculty_context') }}: <span class="font-medium text-gray-700">{{ $department->faculty->name }}</span>
</div>

{{-- Current head card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('departments.current_head_section') }}</h3>

    @if($department->head)
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-sm shrink-0">
                    {{ strtoupper(substr($department->head->first_name, 0, 1)) }}{{ strtoupper(substr($department->head->last_name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $department->head->first_name }} {{ $department->head->last_name }}</p>
                    <p class="text-xs text-gray-400">{{ $department->head->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('dashboard.departments.assign-head.revoke', $department) }}"
                  onsubmit="return confirm('{{ __('departments.confirm_remove_head', ['name' => addslashes($department->head->first_name . ' ' . $department->head->last_name)]) }}')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                    {{ __('departments.remove_head') }}
                </button>
            </form>
        </div>
    @else
        <p class="text-sm text-gray-400">{{ __('departments.no_head_currently') }}</p>
    @endif
</div>

{{-- Assign form --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
        {{ $department->head ? __('departments.reassign_head') : __('departments.assign_head_page_title') }}
    </h3>

    @if($professors->isEmpty() && $employees->isEmpty())
        <p class="text-sm text-gray-400">{{ __('departments.no_professors_or_employees') }}</p>
    @else
        <form method="POST" action="{{ route('dashboard.departments.assign-head.store', $department) }}" class="space-y-5">
            @csrf

            {{-- Professors --}}
            @if($professors->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('departments.from_professors') }}</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto rounded-lg border border-gray-200 p-3">
                        @foreach($professors as $professor)
                            <label class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-lg px-2 py-1.5 transition-colors">
                                <input type="radio" name="user_id" value="{{ $professor->user_id }}"
                                       class="text-blue-600 focus:ring-blue-500"
                                       {{ old('user_id') == $professor->user_id ? 'checked' : '' }}>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $professor->user->first_name }} {{ $professor->user->last_name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $professor->specialization ?? 'Professor' }}
                                        · {{ $professor->staff_number }}
                                    </p>
                                </div>
                                @if($department->head_id === $professor->user_id)
                                    <span class="ms-auto text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Current</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Employees --}}
            @if($employees->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('departments.from_employees') }}</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto rounded-lg border border-gray-200 p-3">
                        @foreach($employees as $employee)
                            <label class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-lg px-2 py-1.5 transition-colors">
                                <input type="radio" name="user_id" value="{{ $employee->user_id }}"
                                       class="text-blue-600 focus:ring-blue-500"
                                       {{ old('user_id') == $employee->user_id ? 'checked' : '' }}>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $employee->user->first_name }} {{ $employee->user->last_name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $employee->job_title ?? 'Employee' }}
                                        · {{ $employee->staff_number }}
                                    </p>
                                </div>
                                @if($department->head_id === $employee->user_id)
                                    <span class="ms-auto text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Current</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            @error('user_id')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    {{ __('departments.assign_as_head') }}
            </button>
        </form>
    @endif
</div>

@endsection
