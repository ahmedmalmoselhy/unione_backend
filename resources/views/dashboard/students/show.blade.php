@extends('dashboard.layouts.app')

@section('title', $student->user->first_name . ' ' . $student->user->last_name)
@section('heading', $student->user->first_name . ' ' . $student->user->last_name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.students.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">Students</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium truncate">{{ $student->user->first_name }} {{ $student->user->last_name }}</span>
</nav>

{{-- Student info card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            @if($student->user->avatar_path)
                <img src="{{ Storage::disk('public')->url($student->user->avatar_path) }}"
                     alt="{{ $student->user->first_name }}"
                     class="w-12 h-12 rounded-full object-cover border border-gray-200 shrink-0">
            @else
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg shrink-0">
                    {{ strtoupper(substr($student->user->first_name, 0, 1)) }}{{ strtoupper(substr($student->user->last_name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $student->user->first_name }} {{ $student->user->last_name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    <span class="font-mono bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs">{{ $student->student_number }}</span>
                    <span class="mx-1.5">·</span>
                    {{ $student->user->email }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
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
            @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                <a href="{{ route('dashboard.students.edit', $student) }}"
                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Edit Student
                </a>
            @endif
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Faculty</dt>
            <dd class="text-gray-700">{{ $student->faculty?->name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Department</dt>
            <dd class="text-gray-700">{{ $student->department?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Academic Year</dt>
            <dd class="text-gray-700">Year {{ $student->academic_year }} · {{ ucfirst($student->semester) }} Semester</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">GPA</dt>
            <dd class="text-gray-700">{{ $student->gpa ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Enrolled</dt>
            <dd class="text-gray-700">{{ $student->enrolled_at?->format('M d, Y') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Graduated</dt>
            <dd class="text-gray-700">{{ $student->graduated_at?->format('M d, Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Gender</dt>
            <dd class="text-gray-700">{{ ucfirst($student->user->gender) }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Phone</dt>
            <dd class="text-gray-700">{{ $student->user->phone ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Account Status</dt>
            <dd>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $student->user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $student->user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </dd>
        </div>
    </dl>
</div>

{{-- Enrollments --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Course Enrollments ({{ $student->enrollments->count() }})</h3>
    </div>

    @if($student->enrollments->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400">No course enrollments found.</div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">Course</th>
                    <th class="px-5 py-3 text-start">Term</th>
                    <th class="px-5 py-3 text-start">Status</th>
                    <th class="px-5 py-3 text-start">Enrolled At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($student->enrollments as $enrollment)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $enrollment->section?->course?->code }}</span>
                            <span class="ml-1.5 text-gray-700">{{ $enrollment->section?->course?->name }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-600">{{ $enrollment->section?->academicTerm?->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                {{ ucfirst($enrollment->status ?? 'enrolled') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $enrollment->created_at?->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Department Transfer --}}
@if(auth()->user()->hasActiveRole('admin') && $departments->isNotEmpty())
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Transfer Department</h3>
    @if(session('success') && str_contains(session('success'), 'transfer'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('dashboard.students.transfer', $student) }}" class="flex flex-col sm:flex-row gap-3 items-end">
        @csrf
        <div class="flex-1">
            <label for="to_department_id" class="block text-xs font-medium text-gray-600 mb-1.5">New Department</label>
            <select name="to_department_id" id="to_department_id" required
                    class="w-full px-3 py-2 text-sm rounded-lg border {{ $errors->has('to_department_id') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-white' }} focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select department…</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('to_department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
            @error('to_department_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex-1">
            <label for="note" class="block text-xs font-medium text-gray-600 mb-1.5">Note <span class="text-gray-400">(optional)</span></label>
            <input type="text" name="note" id="note" value="{{ old('note') }}" maxlength="500"
                   placeholder="Reason for transfer…"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('note')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shrink-0"
                onclick="return confirm('Transfer {{ addslashes($student->user->first_name . ' ' . $student->user->last_name) }} to the selected department?')">
            Transfer
        </button>
    </form>
</div>
@endif

{{-- Department History --}}
@if($student->departmentHistory->isNotEmpty())
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Department Transfer History</h3>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <th class="px-5 py-3 text-start">Date</th>
                <th class="px-5 py-3 text-start">From</th>
                <th class="px-5 py-3 text-start">To</th>
                <th class="px-5 py-3 text-start">Note</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($student->departmentHistory->sortByDesc('switched_at') as $history)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $history->switched_at->format('M d, Y H:i') }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $history->fromDepartment?->name ?? '<em class="text-gray-400">Initial enrolment</em>' }}</td>
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $history->toDepartment?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $history->note ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

