@extends('dashboard.layouts.app')

@section('title', 'Sections')
@section('heading', 'Sections')

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
    <p class="text-sm text-gray-500">{{ $sections->total() }} {{ Str::plural('section', $sections->total()) }} total</p>
    <a href="{{ route('dashboard.sections.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Section
    </a>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($sections->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400">
            No sections found. <a href="{{ route('dashboard.sections.create') }}" class="text-blue-600 hover:underline">Create the first one.</a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">Course</th>
                    <th class="px-5 py-3 text-start">Professor</th>
                    <th class="px-5 py-3 text-start">Term</th>
                    <th class="px-5 py-3 text-start">Capacity</th>
                    <th class="px-5 py-3 text-start">Room</th>
                    <th class="px-5 py-3 text-start">Schedule</th>
                    <th class="px-5 py-3 text-start">Status</th>
                    <th class="px-5 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($sections as $section)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $section->course->code }}</span>
                            <span class="ml-1.5 text-gray-700">{{ $section->course->name }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">
                            {{ $section->professor?->user?->first_name }} {{ $section->professor?->user?->last_name }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($section->academicTerm)
                                <span class="text-xs text-gray-600">{{ $section->academicTerm->name }}</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $section->capacity }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $section->room ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            @if($section->schedule && count($section->schedule))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($section->schedule as $slot)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ ($slot['type'] ?? 'lecture') === 'lab' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }}">
                                            {{ ucfirst(substr($slot['day'] ?? '', 0, 3)) }}
                                            {{ $slot['start_time'] ?? '' }}–{{ $slot['end_time'] ?? '' }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $section->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $section->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.sections.show', $section) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    View
                                </a>
                                <a href="{{ route('dashboard.sections.edit', $section) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('dashboard.sections.destroy', $section) }}"
                                      onsubmit="return confirm('Delete this section? This action cannot be undone.')">
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
        @if($sections->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $sections->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
