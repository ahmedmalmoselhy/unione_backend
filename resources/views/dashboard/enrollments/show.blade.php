@extends('dashboard.layouts.app')

@section('title', 'Enrollment Details')
@section('heading', 'Enrollment Details')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.enrollments.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">Enrollments</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium truncate">#{{ $enrollment->id }}</span>
</nav>

{{-- Enrollment info card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                {{ $enrollment->student?->user?->first_name }} {{ $enrollment->student?->user?->last_name }}
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">
                <span class="font-mono bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs">{{ $enrollment->student?->student_number }}</span>
                <span class="mx-1.5">enrolled in</span>
                <span class="font-mono bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs">{{ $enrollment->section?->course?->code }}</span>
                {{ $enrollment->section?->course?->name }}
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
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
            @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                <a href="{{ route('dashboard.enrollments.edit', $enrollment) }}"
                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Edit
                </a>
            @endif
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Academic Term</dt>
            <dd class="text-gray-700">{{ $enrollment->academicTerm?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Professor</dt>
            <dd class="text-gray-700">{{ $enrollment->section?->professor?->user?->first_name }} {{ $enrollment->section?->professor?->user?->last_name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Room</dt>
            <dd class="text-gray-700">{{ $enrollment->section?->room ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Registered At</dt>
            <dd class="text-gray-700">{{ $enrollment->registered_at?->format('M d, Y h:i A') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Dropped At</dt>
            <dd class="text-gray-700">{{ $enrollment->dropped_at?->format('M d, Y h:i A') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Created</dt>
            <dd class="text-gray-700">{{ $enrollment->created_at?->format('M d, Y') }}</dd>
        </div>
    </dl>
</div>

{{-- Grade card --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Grade</h3>
    </div>

    @if($enrollment->grade)
        <div class="p-6">
            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-8 gap-y-5 text-sm">
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Midterm</dt>
                    <dd class="text-gray-700 text-lg font-semibold">{{ $enrollment->grade->midterm ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Coursework</dt>
                    <dd class="text-gray-700 text-lg font-semibold">{{ $enrollment->grade->coursework ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Final</dt>
                    <dd class="text-gray-700 text-lg font-semibold">{{ $enrollment->grade->final ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Total</dt>
                    <dd class="text-gray-700 text-lg font-semibold">{{ $enrollment->grade->total ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Letter Grade</dt>
                    <dd>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-700">
                            {{ $enrollment->grade->letter_grade ?? '—' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Grade Points</dt>
                    <dd class="text-gray-700 text-lg font-semibold">{{ $enrollment->grade->grade_points ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Graded By</dt>
                    <dd class="text-gray-700">{{ $enrollment->grade->gradedBy?->first_name }} {{ $enrollment->grade->gradedBy?->last_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Graded At</dt>
                    <dd class="text-gray-700">{{ $enrollment->grade->graded_at?->format('M d, Y') ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    @else
        <div class="px-6 py-10 text-center text-sm text-gray-400">No grade recorded yet.</div>
    @endif
</div>

@endsection
