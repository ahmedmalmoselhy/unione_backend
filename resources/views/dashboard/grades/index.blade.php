@extends('dashboard.layouts.app')

@section('title', 'Grades')
@section('heading', 'Grades')

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
    'action'  => route('dashboard.grades.index'),
    'search'  => request('search'),
    'filters' => [
        ['name' => 'term_id', 'label' => 'Term', 'value' => request('term_id'), 'options' => $terms->toArray()],
        ['name' => 'letter_grade', 'label' => 'Grade', 'value' => request('letter_grade'), 'options' => ['A+' => 'A+', 'A' => 'A', 'B+' => 'B+', 'B' => 'B', 'C+' => 'C+', 'C' => 'C', 'D+' => 'D+', 'D' => 'D', 'F' => 'F']],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $grades->total() }} {{ Str::plural('grade', $grades->total()) }} total</p>
    @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
        <a href="{{ route('dashboard.grades.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Record Grade
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($grades->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400">
            No grades recorded yet. <a href="{{ route('dashboard.grades.create') }}" class="text-blue-600 hover:underline">Record the first one.</a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">Student</th>
                    <th class="px-5 py-3 text-start">Course</th>
                    <th class="px-5 py-3 text-start">Term</th>
                    <th class="px-5 py-3 text-center">Midterm</th>
                    <th class="px-5 py-3 text-center">CW</th>
                    <th class="px-5 py-3 text-center">Final</th>
                    <th class="px-5 py-3 text-center">Total</th>
                    <th class="px-5 py-3 text-center">Grade</th>
                    <th class="px-5 py-3 text-center">GPA</th>
                    <th class="px-5 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($grades as $grade)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-900">{{ $grade->enrollment?->student?->user?->first_name }} {{ $grade->enrollment?->student?->user?->last_name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $grade->enrollment?->student?->student_number }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $grade->enrollment?->section?->course?->code }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-600">{{ $grade->enrollment?->academicTerm?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-center text-gray-600">{{ $grade->midterm ?? '—' }}</td>
                        <td class="px-5 py-3 text-center text-gray-600">{{ $grade->coursework ?? '—' }}</td>
                        <td class="px-5 py-3 text-center text-gray-600">{{ $grade->final ?? '—' }}</td>
                        <td class="px-5 py-3 text-center font-semibold text-gray-900">{{ $grade->total ?? '—' }}</td>
                        <td class="px-5 py-3 text-center">
                            @if($grade->letter_grade)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">{{ $grade->letter_grade }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center text-gray-600">{{ $grade->grade_points ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.grades.show', $grade) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    View
                                </a>
                                @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                                    <a href="{{ route('dashboard.grades.edit', $grade) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.grades.destroy', $grade) }}"
                                          onsubmit="return confirm('Delete this grade? This action cannot be undone.')">
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

        @if($grades->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $grades->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
