@extends('dashboard.layouts.app')

@section('title', 'Students')
@section('heading', 'Students')

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
    <p class="text-sm text-gray-500">{{ $students->total() }} {{ Str::plural('student', $students->total()) }} total</p>
    <a href="{{ route('dashboard.students.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Student
    </a>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($students->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400">
            No students found. <a href="{{ route('dashboard.students.create') }}" class="text-blue-600 hover:underline">Create the first one.</a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">Student</th>
                    <th class="px-5 py-3 text-start">Student #</th>
                    <th class="px-5 py-3 text-start">Faculty</th>
                    <th class="px-5 py-3 text-start">Department</th>
                    <th class="px-5 py-3 text-start">Year</th>
                    <th class="px-5 py-3 text-start">GPA</th>
                    <th class="px-5 py-3 text-start">Status</th>
                    <th class="px-5 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($students as $student)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div>
                                <p class="font-medium text-gray-900">{{ $student->user->first_name }} {{ $student->user->last_name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $student->user->email }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $student->student_number }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $student->faculty?->code }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $student->department?->name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $student->academic_year }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $student->gpa ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $statusColors = [
                                    'active'    => 'bg-green-100 text-green-700',
                                    'suspended' => 'bg-yellow-100 text-yellow-700',
                                    'graduated' => 'bg-blue-100 text-blue-700',
                                    'withdrawn' => 'bg-gray-100 text-gray-500',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$student->enrollment_status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($student->enrollment_status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.students.show', $student) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    View
                                </a>
                                <a href="{{ route('dashboard.students.edit', $student) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('dashboard.students.destroy', $student) }}"
                                      onsubmit="return confirm('Delete this student? This action cannot be undone.')">
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
        @if($students->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $students->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
