@extends('dashboard.layouts.app')

@section('title', 'Enrollments')
@section('heading', 'Enrollments')

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

{{-- Search / Filter --}}
@include('dashboard.partials._filter-bar', [
    'action'  => route('dashboard.enrollments.index'),
    'search'  => request('search'),
    'filters' => [
        ['name' => 'term_id', 'label' => 'Term', 'value' => request('term_id'), 'options' => $terms->toArray()],
        ['name' => 'status', 'label' => 'Status', 'value' => request('status'), 'options' => ['registered' => 'Registered', 'completed' => 'Completed', 'dropped' => 'Dropped', 'failed' => 'Failed', 'incomplete' => 'Incomplete']],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $enrollments->total() }} {{ Str::plural('enrollment', $enrollments->total()) }} total</p>
    @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
        <a href="{{ route('dashboard.enrollments.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Enrollment
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($enrollments->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400">
            No enrollments found. <a href="{{ route('dashboard.enrollments.create') }}" class="text-blue-600 hover:underline">Create the first one.</a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">Student</th>
                    <th class="px-5 py-3 text-start">Course / Section</th>
                    <th class="px-5 py-3 text-start">Term</th>
                    <th class="px-5 py-3 text-start">Status</th>
                    <th class="px-5 py-3 text-start">Registered</th>
                    <th class="px-5 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($enrollments as $enrollment)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div>
                                <p class="font-medium text-gray-900">{{ $enrollment->student?->user?->first_name }} {{ $enrollment->student?->user?->last_name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $enrollment->student?->student_number }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $enrollment->section?->course?->code }}</span>
                            <span class="ml-1.5 text-gray-600">{{ $enrollment->section?->course?->name }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $enrollment->academicTerm?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $statusColors = [
                                    'registered' => 'bg-blue-100 text-blue-700',
                                    'completed'  => 'bg-green-100 text-green-700',
                                    'dropped'    => 'bg-yellow-100 text-yellow-700',
                                    'failed'     => 'bg-red-100 text-red-700',
                                    'incomplete' => 'bg-gray-100 text-gray-500',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$enrollment->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($enrollment->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500">{{ $enrollment->registered_at?->format('M d, Y') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.enrollments.show', $enrollment) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    View
                                </a>
                                @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                                    <a href="{{ route('dashboard.enrollments.edit', $enrollment) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.enrollments.destroy', $enrollment) }}"
                                          onsubmit="return confirm('Delete this enrollment? This action cannot be undone.')">
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

        @if($enrollments->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $enrollments->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
