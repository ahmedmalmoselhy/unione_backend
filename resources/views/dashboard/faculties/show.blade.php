@extends('dashboard.layouts.app')

@section('title', $faculty->name)
@section('heading', $faculty->name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.faculties.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">Faculties</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium truncate">{{ $faculty->name }}</span>
</nav>

{{-- Faculty info card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-8">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $faculty->name }}</h2>
            <p class="text-sm text-gray-400 mt-0.5" dir="rtl">{{ $faculty->name_ar }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $faculty->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $faculty->is_active ? 'Active' : 'Inactive' }}
            </span>
            @if(auth()->user()->hasActiveRole('admin'))
                <a href="{{ route('dashboard.faculties.edit', $faculty) }}"
                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Edit Faculty
                </a>
            @endif
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Code</dt>
            <dd><span class="font-mono text-sm bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $faculty->code }}</span></dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Enrollment Type</dt>
            <dd>
                @php
                    $typeStyles = [
                        'immediate' => 'bg-blue-50 text-blue-700',
                        'deferred'  => 'bg-indigo-50 text-indigo-700',
                        'none'      => 'bg-gray-100 text-gray-600',
                    ];
                    $typeLabels = [
                        'immediate' => 'Immediate',
                        'deferred'  => 'Deferred',
                        'none'      => 'None',
                    ];
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeStyles[$faculty->enrollment_type] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $typeLabels[$faculty->enrollment_type] ?? ucfirst($faculty->enrollment_type) }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Dean</dt>
            <dd class="text-gray-700">
                @if($faculty->dean)
                    {{ $faculty->dean->first_name }} {{ $faculty->dean->last_name }}
                @else
                    <span class="text-gray-400">Not assigned</span>
                @endif
            </dd>
        </div>
    </dl>

</div>

{{-- Departments section header --}}
<div class="flex items-center justify-between mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">
        Departments
        <span class="ml-2 text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full normal-case tracking-normal">
            {{ $faculty->departments->count() }}
        </span>
    </h3>
    @if(auth()->user()->hasActiveRole('admin'))
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard.departments.create.academic') }}?faculty_id={{ $faculty->id }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Academic
            </a>
            <a href="{{ route('dashboard.departments.create.managerial') }}?faculty_id={{ $faculty->id }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Managerial
            </a>
        </div>
    @endif
</div>

@php
    $academic   = $faculty->departments->filter(fn($d) => $d->type === 'academic');
    $managerial = $faculty->departments->filter(fn($d) => $d->type === 'managerial');
@endphp

{{-- Academic Departments --}}
<div class="mb-6">
    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">
        Academic
        <span class="ml-1.5 font-medium bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded-full normal-case tracking-normal">{{ $academic->count() }}</span>
    </h4>
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        @if($academic->isEmpty())
            <div class="px-6 py-10 text-center text-sm text-gray-400">
                No academic departments yet.
                @if(auth()->user()->hasActiveRole('admin'))
                    <a href="{{ route('dashboard.departments.create.academic') }}?faculty_id={{ $faculty->id }}" class="text-blue-600 hover:underline">Add one</a>
                @endif
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-start">Name</th>
                        <th class="px-5 py-3 text-start">Code</th>
                        <th class="px-5 py-3 text-start">Head</th>
                        <th class="px-5 py-3 text-start">Status</th>
                        @if(auth()->user()->hasActiveRole('admin'))
                            <th class="px-5 py-3 text-end">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($academic as $department)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $department->name }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5" dir="rtl">{{ $department->name_ar }}</p>
                                    </div>
                                    @if($department->is_preparatory)
                                        <span class="shrink-0 text-[10px] font-semibold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">PREP</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $department->code }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">
                                @if($department->head)
                                    {{ $department->head->first_name }} {{ $department->head->last_name }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $department->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            @if(auth()->user()->hasActiveRole('admin'))
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('dashboard.departments.edit', $department) }}"
                                           class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                            Edit
                                        </a>
                                        @if(! $department->is_mandatory)
                                            <form method="POST" action="{{ route('dashboard.departments.destroy', $department) }}"
                                                  onsubmit="return confirm('Delete department \'{{ addslashes($department->name) }}\'? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Managerial Departments --}}
<div>
    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">
        Managerial
        <span class="ml-1.5 font-medium bg-purple-50 text-purple-600 px-1.5 py-0.5 rounded-full normal-case tracking-normal">{{ $managerial->count() }}</span>
    </h4>
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        @if($managerial->isEmpty())
            <div class="px-6 py-10 text-center text-sm text-gray-400">
                No managerial departments yet.
                @if(auth()->user()->hasActiveRole('admin'))
                    <a href="{{ route('dashboard.departments.create.managerial') }}?faculty_id={{ $faculty->id }}" class="text-purple-600 hover:underline">Add one</a>
                @endif
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-start">Name</th>
                        <th class="px-5 py-3 text-start">Code</th>
                        <th class="px-5 py-3 text-start">Manager</th>
                        <th class="px-5 py-3 text-start">Status</th>
                        @if(auth()->user()->hasActiveRole('admin'))
                            <th class="px-5 py-3 text-end">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($managerial as $department)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $department->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5" dir="rtl">{{ $department->name_ar }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $department->code }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">
                                @if($department->head)
                                    {{ $department->head->first_name }} {{ $department->head->last_name }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $department->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            @if(auth()->user()->hasActiveRole('admin'))
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('dashboard.departments.edit', $department) }}"
                                           class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                            Edit
                                        </a>
                                        @if(! $department->is_mandatory)
                                            <form method="POST" action="{{ route('dashboard.departments.destroy', $department) }}"
                                                  onsubmit="return confirm('Delete department \'{{ addslashes($department->name) }}\'? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

@endsection
