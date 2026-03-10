@extends('portal.layouts.app')

@section('title', 'My Profile')
@section('heading', 'My Profile')

@section('content')

<div class="max-w-3xl space-y-6">

    {{-- Personal information --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-700 dark:text-blue-300 font-bold text-2xl shrink-0">
                {{ strtoupper(substr($user->first_name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->first_name }} {{ $user->last_name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                <div class="flex flex-wrap gap-1 mt-1">
                    @foreach($user->roles()->whereNull('role_user.revoked_at')->get() as $role)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                            {{ $role->label }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-8 text-sm">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">National ID</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $user->national_id ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $user->phone ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Gender</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ ucfirst($user->gender ?? '—') }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Date of birth</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $user->date_of_birth?->format('d M Y') ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Student-specific --}}
    @if($student)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Academic Information</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-8 text-sm">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Student Number</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white font-mono">{{ $student->student_number }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">GPA</dt>
                <dd class="mt-0.5 font-bold {{ ($student->gpa ?? 0) >= 3 ? 'text-green-600 dark:text-green-400' : (($student->gpa ?? 0) >= 2 ? 'text-amber-500' : 'text-red-500') }}">
                    {{ number_format($student->gpa ?? 0, 2) }}
                </dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Academic Year</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">Year {{ $student->academic_year }}, Semester {{ $student->semester }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Enrollment Status</dt>
                <dd class="mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $student->enrollment_status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                        {{ ucfirst($student->enrollment_status) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Faculty</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $student->faculty?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Department</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $student->department?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Enrolled At</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $student->enrolled_at?->format('d M Y') ?? '—' }}</dd>
            </div>
            @if($student->graduated_at)
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Graduated At</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $student->graduated_at->format('d M Y') }}</dd>
            </div>
            @endif
        </dl>
    </div>
    @endif

    {{-- Professor-specific --}}
    @if($professor)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Academic Information</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-8 text-sm">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Staff Number</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white font-mono">{{ $professor->staff_number }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Academic Rank</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ ucwords(str_replace('_', ' ', $professor->academic_rank ?? '—')) }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Specialization</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $professor->specialization ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Office Location</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $professor->office_location ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Department</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $professor->department?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Faculty</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $professor->department?->faculty?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Hired At</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $professor->hired_at?->format('d M Y') ?? '—' }}</dd>
            </div>
        </dl>
    </div>
    @endif

    {{-- Employee-specific --}}
    @if($employee)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Employment Information</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-8 text-sm">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Staff Number</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white font-mono">{{ $employee->staff_number }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Job Title</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $employee->job_title ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Employment Type</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ ucfirst($employee->employment_type ?? '—') }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Department</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $employee->department?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Faculty</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $employee->department?->faculty?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Hired At</dt>
                <dd class="mt-0.5 text-gray-900 dark:text-white">{{ $employee->hired_at?->format('d M Y') ?? '—' }}</dd>
            </div>
        </dl>
    </div>
    @endif
</div>

@endsection
