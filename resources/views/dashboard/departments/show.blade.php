@extends('dashboard.layouts.app')

@section('title', $department->name)
@section('heading', $department->name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.departments.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">Departments</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium truncate">{{ $department->name }}</span>
</nav>

{{-- Department info card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-8">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $department->name }}</h2>
            <p class="text-sm text-gray-400 mt-0.5" dir="rtl">{{ $department->name_ar }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if($department->is_mandatory)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                    Mandatory
                </span>
            @endif
            @if($department->is_preparatory)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                    Preparatory
                </span>
            @endif
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $department->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $department->is_active ? 'Active' : 'Inactive' }}
            </span>
            @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin())
                <a href="{{ route('dashboard.departments.assign-admin', $department) }}"
                   class="px-3 py-1.5 text-xs font-medium text-violet-600 hover:text-violet-700 bg-violet-50 hover:bg-violet-100 rounded-lg transition-colors">
                    Assign Admin
                </a>
                <a href="{{ route('dashboard.departments.edit', $department) }}"
                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Edit
                </a>
            @endif
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Code</dt>
            <dd><span class="font-mono text-sm bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $department->code }}</span></dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Type</dt>
            <dd>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $department->type === 'academic' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                    {{ ucfirst($department->type) }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Faculty</dt>
            <dd class="text-gray-700">{{ $department->faculty?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Head / Manager</dt>
            <dd class="text-gray-700">
                @if($department->head)
                    {{ $department->head->first_name }} {{ $department->head->last_name }}
                @else
                    <span class="text-gray-400">Not assigned</span>
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Professors</dt>
            <dd class="text-gray-700">{{ $department->professors->count() }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Students</dt>
            <dd class="text-gray-700">{{ $department->students->count() }}</dd>
        </div>
    </dl>
</div>

{{-- Professors --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">
        Professors
        <span class="ml-2 text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full normal-case tracking-normal">{{ $department->professors->count() }}</span>
    </h3>
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        @if($department->professors->isEmpty())
            <div class="px-6 py-8 text-center text-sm text-gray-400">No professors assigned to this department.</div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-start">Name</th>
                        <th class="px-5 py-3 text-start">Staff No.</th>
                        <th class="px-5 py-3 text-start">Specialization</th>
                        <th class="px-5 py-3 text-start">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($department->professors as $professor)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $professor->user->first_name }} {{ $professor->user->last_name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $professor->user->email }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $professor->staff_number }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $professor->specialization ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $professor->user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $professor->user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Employees --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">
        Employees
        <span class="ml-2 text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full normal-case tracking-normal">{{ $department->employees->count() }}</span>
    </h3>
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        @if($department->employees->isEmpty())
            <div class="px-6 py-8 text-center text-sm text-gray-400">No employees assigned to this department.</div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-start">Name</th>
                        <th class="px-5 py-3 text-start">Staff No.</th>
                        <th class="px-5 py-3 text-start">Job Title</th>
                        <th class="px-5 py-3 text-start">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($department->employees as $employee)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $employee->user->first_name }} {{ $employee->user->last_name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $employee->user->email }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $employee->staff_number }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $employee->job_title ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $employee->user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $employee->user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Students --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">
        Students
        <span class="ml-2 text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full normal-case tracking-normal">{{ $department->students->count() }}</span>
    </h3>
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        @if($department->students->isEmpty())
            <div class="px-6 py-8 text-center text-sm text-gray-400">No students enrolled in this department.</div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-start">Name</th>
                        <th class="px-5 py-3 text-start">Student No.</th>
                        <th class="px-5 py-3 text-start">Year / Semester</th>
                        <th class="px-5 py-3 text-start">GPA</th>
                        <th class="px-5 py-3 text-start">Status</th>
                        <th class="px-5 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($department->students as $student)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $student->user->first_name }} {{ $student->user->last_name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $student->user->email }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $student->student_number }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 text-xs">Year {{ $student->academic_year }} · {{ ucfirst($student->semester) }}</td>
                            <td class="px-5 py-3.5 text-gray-700">{{ $student->gpa ?? '—' }}</td>
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
                            <td class="px-5 py-3.5 text-end">
                                <a href="{{ route('dashboard.students.show', $student) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Courses --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">
        Courses
        <span class="ml-2 text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full normal-case tracking-normal">{{ $department->courses->count() }}</span>
    </h3>
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        @if($department->courses->isEmpty())
            <div class="px-6 py-8 text-center text-sm text-gray-400">No courses assigned to this department.</div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-start">Code</th>
                        <th class="px-5 py-3 text-start">Name</th>
                        <th class="px-5 py-3 text-start">Credit Hours</th>
                        <th class="px-5 py-3 text-start">Level</th>
                        <th class="px-5 py-3 text-start">Ownership</th>
                        <th class="px-5 py-3 text-start">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($department->courses as $course)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $course->code }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $course->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5" dir="rtl">{{ $course->name_ar }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $course->credit_hours }} hrs</td>
                            <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $course->level ? 'Level ' . $course->level : '—' }}</td>
                            <td class="px-5 py-3.5">
                                @if($course->pivot->is_owner)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Owner</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Shared</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $course->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

@endsection
