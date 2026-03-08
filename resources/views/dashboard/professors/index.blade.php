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

@if(session('import_errors'))
    <div class="mb-6 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700 p-4">
        <p class="font-semibold mb-2">Import failed — fix these errors and try again:</p>
        <ul class="list-disc list-inside space-y-0.5 max-h-48 overflow-y-auto">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
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
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.professors.export', request()->query()) }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-200 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Export
        </a>
        @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
            <button onclick="document.getElementById('professors-import-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-200 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-lg transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Import
            </button>
            <a href="{{ route('dashboard.professors.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Professor
            </a>
        @endif
    </div>
</div>

{{-- Professors Import Modal --}}
<div id="professors-import-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40" onclick="document.getElementById('professors-import-modal').classList.add('hidden')"></div>
        <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl p-6 z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900">Import Professors</h3>
                <button onclick="document.getElementById('professors-import-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-gray-500 mb-4">
                Upload a <strong class="text-gray-700">.csv</strong> or <strong class="text-gray-700">.xlsx</strong> file to bulk-create professors.
                Initial password is set to each professor's national ID.
                <a href="{{ route('dashboard.professors.import-template') }}" class="text-blue-600 hover:underline font-medium">Download template</a>.
            </p>
            <form action="{{ route('dashboard.professors.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center mb-4">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <label for="professors-import-file" class="cursor-pointer text-sm text-blue-600 hover:underline font-medium">Choose file</label>
                    <input id="professors-import-file" name="file" type="file" accept=".csv,.xlsx,.xls" class="sr-only"
                           onchange="document.getElementById('professors-import-filename').textContent = this.files[0]?.name ?? ''">
                    <p id="professors-import-filename" class="text-xs text-gray-400 mt-1">CSV or Excel, max 5 MB</p>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('professors-import-modal').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">Upload &amp; Import</button>
                </div>
            </form>
        </div>
    </div>
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
                                @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
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
                                @endif
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
