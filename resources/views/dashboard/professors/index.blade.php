@extends('dashboard.layouts.app')

@section('title', __('professors.title'))
@section('heading', __('professors.title'))

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
        <p class="font-semibold mb-2">{{ __('professors.import_failed') }}</p>
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
        ['name' => 'department_id', 'label' => __('common.faculty'), 'value' => request('department_id'), 'options' => $departments->toArray()],
        ['name' => 'rank', 'label' => __('professors.academic_rank'), 'value' => request('rank'), 'options' => ['lecturer' => __('professors.rank_lecturer'), 'assistant_professor' => __('professors.rank_assistant_professor'), 'associate_professor' => __('professors.rank_associate_professor'), 'professor' => __('professors.rank_professor')]],
        ['name' => 'status', 'label' => __('common.status'), 'value' => request('status'), 'options' => ['active' => __('common.active'), 'inactive' => __('common.inactive')]],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $professors->total() }} {{ Str::plural(__('common.professor'), $professors->total()) }}</p>
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.professors.export', request()->query()) }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-200 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            {{ __('common.export') }}
        </a>
        @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
            <button onclick="document.getElementById('professors-import-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-200 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-lg transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                {{ __('common.import') }}
            </button>
            <a href="{{ route('dashboard.professors.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('professors.new_professor') }}
            </a>
        @endif
    </div>
</div>

{{-- Professors Import Modal --}}
<div id="professors-import-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40" onclick="document.getElementById('professors-import-modal').classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-lg rounded-2xl shadow-xl p-6 z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('professors.import_professors') }}</h3>
                <button onclick="document.getElementById('professors-import-modal').classList.add('hidden')" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                {{ __('professors.import_description') }}
                <a href="{{ route('dashboard.professors.import-template') }}" class="text-blue-600 hover:underline font-medium">{{ __('professors.download_template') }}</a>.
            </p>
            <form action="{{ route('dashboard.professors.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center mb-4">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <label for="professors-import-file" class="cursor-pointer text-sm text-blue-600 hover:underline font-medium">{{ __('professors.choose_file') }}</label>
                    <input id="professors-import-file" name="file" type="file" accept=".csv,.xlsx,.xls" class="sr-only"
                           onchange="document.getElementById('professors-import-filename').textContent = this.files[0]?.name ?? ''">
                    <p id="professors-import-filename" class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ __('professors.file_hint') }}</p>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('professors-import-modal').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">{{ __('common.cancel') }}</button>
                    <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">{{ __('professors.upload_import') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    @if($professors->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400 dark:text-gray-500">
            {{ __('professors.no_professors_found') }}
        </div>
    @else
        <table class="w-full text-sm dark:text-gray-200">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('common.name') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('professors.staff_number') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.faculty') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('professors.specialization') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('professors.academic_rank') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.status') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($professors as $professor)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $professor->user->first_name }} {{ $professor->user->last_name }}</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $professor->user->email }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded">{{ $professor->staff_number }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-gray-700 dark:text-gray-300">{{ $professor->department->local_name }}</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $professor->department->faculty?->local_name }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">{{ $professor->specialization }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $rankStyles = [
                                    'lecturer'             => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                                    'assistant_professor'  => 'bg-blue-50 text-blue-700',
                                    'associate_professor'  => 'bg-indigo-50 text-indigo-700',
                                    'professor'            => 'bg-purple-50 text-purple-700',
                                ];
                                $rankLabels = [
                                    'lecturer'             => __('professors.rank_lecturer_short'),
                                    'assistant_professor'  => __('professors.rank_assistant_short'),
                                    'associate_professor'  => __('professors.rank_associate_short'),
                                    'professor'            => __('professors.rank_professor_short'),
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $rankStyles[$professor->academic_rank] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                {{ $rankLabels[$professor->academic_rank] ?? ucfirst($professor->academic_rank) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $professor->user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $professor->user->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.professors.show', $professor) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                    {{ __('common.view') }}
                                </a>
                                @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                                    <a href="{{ route('dashboard.professors.edit', $professor) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        {{ __('common.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.professors.destroy', $professor) }}"
                                          onsubmit="return confirm('{{ addslashes(__('professors.confirm_delete', ['name' => $professor->user->first_name . ' ' . $professor->user->last_name])) }}')">
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
        @if($professors->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $professors->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
