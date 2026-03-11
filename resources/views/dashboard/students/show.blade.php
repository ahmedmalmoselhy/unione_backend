@extends('dashboard.layouts.app')

@section('title', $student->user->first_name . ' ' . $student->user->last_name)
@section('heading', $student->user->first_name . ' ' . $student->user->last_name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.students.index') }}" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">{{ __('students.title') }}</a>
    <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 dark:text-gray-300 font-medium truncate">{{ $student->user->first_name }} {{ $student->user->last_name }}</span>
</nav>

{{-- Student info card --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">

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
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $student->user->first_name }} {{ $student->user->last_name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    <span class="font-mono bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded text-xs">{{ $student->student_number }}</span>
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
                $statusLabels = [
                    'active'    => __('students.status_active'),
                    'suspended' => __('students.status_suspended'),
                    'graduated' => __('students.status_graduated'),
                    'withdrawn' => __('students.status_withdrawn'),
                ];
            @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$student->enrollment_status] ?? 'bg-gray-100 text-gray-500' }}">
                {{ $statusLabels[$student->enrollment_status] ?? ucfirst($student->enrollment_status) }}
            </span>
            @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                <a href="{{ route('dashboard.students.edit', $student) }}"
                   class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    {{ __('students.edit_student') }}
                </a>
            @endif
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('students.faculty') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $student->faculty?->name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('students.department') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $student->department?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('students.academic_year') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">Year {{ $student->academic_year }} · {{ ucfirst($student->semester) }} Semester</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('students.gpa') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">
                {{ $student->gpa !== null ? number_format($student->gpa, 2) : '—' }}
                @if($student->academic_standing)
                @php
                    $standingConfig = match($student->academic_standing) {
                        'good_standing' => ['label' => 'Good Standing', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],
                        'probation'     => ['label' => 'Probation',     'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
                        'dismissal'     => ['label' => 'Dismissal',     'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
                        default         => ['label' => ucfirst(str_replace('_', ' ', $student->academic_standing)), 'class' => 'bg-gray-100 text-gray-700'],
                    };
                @endphp
                <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full {{ $standingConfig['class'] }}">
                    {{ $standingConfig['label'] }}
                </span>
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('students.enrolled_at') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $student->enrolled_at?->format('M d, Y') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('students.graduated_at') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $student->graduated_at?->format('M d, Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('students.gender') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ ucfirst($student->user->gender) }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('students.phone') }}</dt>
            <dd class="text-gray-700 dark:text-gray-300">{{ $student->user->phone ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">{{ __('students.account_status') }}</dt>
            <dd>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $student->user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $student->user->is_active ? __('common.active') : __('common.inactive') }}
                </span>
            </dd>
        </div>
    </dl>
</div>

{{-- Enrollments --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ __('students.course_enrollments', ['count' => $student->enrollments->count()]) }}</h3>
    </div>

    @if($student->enrollments->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">{{ __('students.no_enrollments') }}</div>
    @else
        <table class="w-full text-sm dark:text-gray-200">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('common.course') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.term') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.status') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('students.enrolled_at') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($student->enrollments as $enrollment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded">{{ $enrollment->section?->course?->code }}</span>
                            <span class="ml-1.5 text-gray-700 dark:text-gray-300">{{ $enrollment->section?->course?->name }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-600 dark:text-gray-400">{{ $enrollment->section?->academicTerm?->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                {{ ucfirst($enrollment->status ?? 'enrolled') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $enrollment->created_at?->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Department Transfer --}}
@if(auth()->user()->hasActiveRole('admin') && $departments->isNotEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('students.transfer_department') }}</h3>
    @if(session('success') && str_contains(session('success'), 'transfer'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('dashboard.students.transfer', $student) }}" class="flex flex-col sm:flex-row gap-3 items-end">
        @csrf
        <div class="flex-1">
            <label for="to_department_id" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">{{ __('students.new_department') }}</label>
            <select name="to_department_id" id="to_department_id" required
                    class="w-full px-3 py-2 text-sm rounded-lg border {{ $errors->has('to_department_id') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200' }} focus:outline-none focus:ring-2 focus:ring-blue-500">
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
            <label for="note" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">{{ __('students.note') }} <span class="text-gray-400 dark:text-gray-600">({{ __('common.optional') }})</span></label>
            <input type="text" name="note" id="note" value="{{ old('note') }}" maxlength="500"
                   placeholder="Reason for transfer…"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-800">
            @error('note')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shrink-0"
                onclick="return confirm('{{ addslashes(__('students.confirm_transfer', ['name' => $student->user->first_name . ' ' . $student->user->last_name])) }}')">{{ __('students.transfer_btn') }}
        </button>
    </form>
</div>
@endif

{{-- Department History --}}
@if($student->departmentHistory->isNotEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ __('students.transfer_history') }}</h3>
    </div>
    <table class="w-full text-sm dark:text-gray-200">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                <th class="px-5 py-3 text-start">{{ __('common.date') }}</th>
                <th class="px-5 py-3 text-start">{{ __('common.from') }}</th>
                <th class="px-5 py-3 text-start">{{ __('common.to') }}</th>
                <th class="px-5 py-3 text-start">{{ __('common.note') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
            @foreach($student->departmentHistory->sortByDesc('switched_at') as $history)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $history->switched_at->format('M d, Y H:i') }}</td>
                    <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ $history->fromDepartment?->name ?? '<em class="text-gray-400">Initial enrolment</em>' }}</td>
                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $history->toDepartment?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $history->note ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

