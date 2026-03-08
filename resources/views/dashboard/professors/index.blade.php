@extends('dashboard.layouts.app')

@section('title', 'Professors')
@section('heading', 'Professors')

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
    'action'  => route('dashboard.professors.index'),
    'search'  => request('search'),
    'filters' => [
        ['name' => 'department_id', 'label' => 'Department', 'value' => request('department_id'), 'options' => $departments->toArray()],
        ['name' => 'rank', 'label' => 'Rank', 'value' => request('rank'), 'options' => ['lecturer' => 'Lecturer', 'assistant_professor' => 'Asst. Professor', 'associate_professor' => 'Assoc. Professor', 'professor' => 'Professor']],
        ['name' => 'status', 'label' => 'Status', 'value' => request('status'), 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $professors->total() }} {{ Str::plural('professor', $professors->total()) }} total</p>
    <a href="{{ route('dashboard.professors.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Professor
    </a>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($professors->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400">
            No professors found. <a href="{{ route('dashboard.professors.create') }}" class="text-blue-600 hover:underline">Create the first one.</a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">Name</th>
                    <th class="px-5 py-3 text-start">Staff #</th>
                    <th class="px-5 py-3 text-start">Department</th>
                    <th class="px-5 py-3 text-start">Specialization</th>
                    <th class="px-5 py-3 text-start">Rank</th>
                    <th class="px-5 py-3 text-start">Status</th>
                    <th class="px-5 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($professors as $professor)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-gray-900">{{ $professor->user->first_name }} {{ $professor->user->last_name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $professor->user->email }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $professor->staff_number }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-gray-700">{{ $professor->department->name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $professor->department->faculty?->name }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $professor->specialization }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $rankStyles = [
                                    'lecturer'             => 'bg-gray-100 text-gray-600',
                                    'assistant_professor'  => 'bg-blue-50 text-blue-700',
                                    'associate_professor'  => 'bg-indigo-50 text-indigo-700',
                                    'professor'            => 'bg-purple-50 text-purple-700',
                                ];
                                $rankLabels = [
                                    'lecturer'             => 'Lecturer',
                                    'assistant_professor'  => 'Asst. Prof.',
                                    'associate_professor'  => 'Assoc. Prof.',
                                    'professor'            => 'Professor',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $rankStyles[$professor->academic_rank] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $rankLabels[$professor->academic_rank] ?? ucfirst($professor->academic_rank) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $professor->user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $professor->user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.professors.show', $professor) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    View
                                </a>
                                <a href="{{ route('dashboard.professors.edit', $professor) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('dashboard.professors.destroy', $professor) }}"
                                      onsubmit="return confirm('Delete professor \'{{ addslashes($professor->user->first_name . ' ' . $professor->user->last_name) }}\'? This will also delete their user account. This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($professors->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $professors->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
