@extends('dashboard.layouts.app')

@section('title', 'Departments')
@section('heading', 'Departments')

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

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $departments->total() }} {{ Str::plural('department', $departments->total()) }} total</p>
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.departments.create.academic') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Academic
        </a>
        <a href="{{ route('dashboard.departments.create.managerial') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Managerial
        </a>
    </div>
</div>

@if($departments->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 px-6 py-16 text-center text-sm text-gray-400">
        No departments found.
    </div>
@else
    @php
        $academic   = $departments->filter(fn($d) => $d->type === 'academic');
        $managerial = $departments->filter(fn($d) => $d->type === 'managerial');
    @endphp

    {{-- Academic Departments --}}
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">
            Academic
            <span class="ml-2 text-xs font-medium bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full normal-case tracking-normal">
                {{ $academic->count() }}
            </span>
        </h3>
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            @if($academic->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-gray-400">No academic departments on this page.</div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-5 py-3 text-start">Name</th>
                            <th class="px-5 py-3 text-start">Faculty</th>
                            <th class="px-5 py-3 text-start">Code</th>
                            <th class="px-5 py-3 text-start">Head</th>
                            <th class="px-5 py-3 text-start">Status</th>
                            <th class="px-5 py-3 text-end">Actions</th>
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
                                <td class="px-5 py-3.5 text-gray-600 text-xs">
                                    {{ $department->faculty?->name ?? '—' }}
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Managerial Departments --}}
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">
            Managerial
            <span class="ml-2 text-xs font-medium bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full normal-case tracking-normal">
                {{ $managerial->count() }}
            </span>
        </h3>
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            @if($managerial->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-gray-400">No managerial departments on this page.</div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-5 py-3 text-start">Name</th>
                            <th class="px-5 py-3 text-start">Faculty</th>
                            <th class="px-5 py-3 text-start">Code</th>
                            <th class="px-5 py-3 text-start">Manager</th>
                            <th class="px-5 py-3 text-start">Status</th>
                            <th class="px-5 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($managerial as $department)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <p class="font-medium text-gray-900">{{ $department->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5" dir="rtl">{{ $department->name_ar }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600 text-xs">
                                    {{ $department->faculty?->name ?? '—' }}
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Pagination --}}
    @if($departments->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $departments->links() }}
        </div>
    @endif
@endif

@endsection
