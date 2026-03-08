@extends('dashboard.layouts.app')

@section('title', 'Home')
@section('heading', 'Home')

@section('content')

{{-- Welcome banner --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg shrink-0">
            {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
        </div>
        <div>
            <h2 class="text-xl font-semibold text-gray-900">
                Welcome, {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
            </h2>
            <div class="flex items-center gap-2 mt-1">
                @foreach(auth()->user()->roles()->whereNull('role_user.revoked_at')->get() as $role)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $role->label }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════
     SYSTEM ADMIN VIEW
     ════════════════════════════════════════ --}}
@if($role === 'system_admin')

    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">University Overview</h3>

    @php
    $statCards = [
        ['key' => 'students',    'label' => 'Students',    'color' => 'blue',   'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['key' => 'professors',  'label' => 'Professors',  'color' => 'indigo', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ['key' => 'employees',   'label' => 'Employees',   'color' => 'purple', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['key' => 'courses',     'label' => 'Courses',     'color' => 'emerald','icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['key' => 'sections',    'label' => 'Sections',    'color' => 'teal',   'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
        ['key' => 'faculties',   'label' => 'Faculties',   'color' => 'amber',  'icon' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
        ['key' => 'departments', 'label' => 'Departments', 'color' => 'orange', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
    ]
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 mb-10">
        @foreach($statCards as $card)
            @php
                $colorMap = [
                    'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-600'],
                    'indigo' => ['bg' => 'bg-indigo-50', 'icon' => 'text-indigo-600'],
                    'purple' => ['bg' => 'bg-purple-50', 'icon' => 'text-purple-600'],
                    'emerald'=> ['bg' => 'bg-emerald-50','icon' => 'text-emerald-600'],
                    'teal'   => ['bg' => 'bg-teal-50',   'icon' => 'text-teal-600'],
                    'amber'  => ['bg' => 'bg-amber-50',  'icon' => 'text-amber-600'],
                    'orange' => ['bg' => 'bg-orange-50', 'icon' => 'text-orange-600'],
                ];
                $c = $colorMap[$card['color']];
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ number_format($globalStats[$card['key']]) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">By Faculty</h3>

    @if($faculties->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center text-sm text-gray-400">
            No faculties found.
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach($faculties as $faculty)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 truncate">{{ $faculty->name }}</p>
                            @if($faculty->code)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $faculty->code }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $faculty->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $faculty->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="text-gray-500">Departments</span>
                            <span class="font-semibold text-gray-900">{{ number_format($faculty->departments_count) }}</span>
                        </div>
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="text-gray-500">Professors</span>
                            <span class="font-semibold text-gray-900">{{ number_format($professorsByFaculty[$faculty->id] ?? 0) }}</span>
                        </div>
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="text-gray-500">Students</span>
                            <span class="font-semibold text-gray-900">{{ number_format($faculty->students_count) }}</span>
                        </div>
                        <div class="px-5 py-3 grid grid-cols-2 gap-x-4 gap-y-1.5">
                            @foreach([
                                ['key' => 'active_students_count',    'label' => 'Active',    'color' => 'text-green-600'],
                                ['key' => 'graduated_students_count', 'label' => 'Graduated', 'color' => 'text-blue-600'],
                                ['key' => 'suspended_students_count', 'label' => 'Suspended', 'color' => 'text-amber-600'],
                                ['key' => 'withdrawn_students_count', 'label' => 'Withdrawn', 'color' => 'text-red-500'],
                            ] as $row)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-400">{{ $row['label'] }}</span>
                                    <span class="font-medium {{ $row['color'] }}">{{ number_format($faculty->{$row['key']}) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── DATA HEALTH WARNINGS ─────────────────────────────── --}}
    @php
        $warnings = array_filter([
            $dataHealth['faculties_without_admin'] > 0
                ? ['icon' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z',
                   'label' => 'Faculties without an admin',
                   'count' => $dataHealth['faculties_without_admin'],
                   'link'  => route('dashboard.faculties.index')]
                : null,
            $dataHealth['depts_without_head'] > 0
                ? ['icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                   'label' => 'Active departments without a department head',
                   'count' => $dataHealth['depts_without_head'],
                   'link'  => route('dashboard.departments.index')]
                : null,
            $dataHealth['sections_without_prof'] > 0
                ? ['icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                   'label' => 'Active sections without an assigned professor',
                   'count' => $dataHealth['sections_without_prof'],
                   'link'  => route('dashboard.sections.index')]
                : null,
            $dataHealth['students_without_dept'] > 0
                ? ['icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                   'label' => 'Active students without a department',
                   'count' => $dataHealth['students_without_dept'],
                   'link'  => route('dashboard.students.index')]
                : null,
        ]);
    @endphp
    @if(count($warnings) > 0)
        <div class="mb-8">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                Data Health Warnings
            </h3>
            <div class="space-y-2">
                @foreach($warnings as $w)
                    <a href="{{ $w['link'] }}"
                       class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 group hover:bg-amber-100 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0 group-hover:bg-amber-200 transition-colors">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $w['icon'] }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-amber-800">{{ $w['label'] }}</p>
                        </div>
                        <span class="shrink-0 text-lg font-bold text-amber-700">{{ $w['count'] }}</span>
                        <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="mb-8 flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-5 py-3">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-green-700">All data health checks passed — no issues found.</p>
        </div>
    @endif

    {{-- ── QUICK ACTIONS ─────────────────────────────────────── --}}
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
            @foreach([
                ['label' => 'New Faculty',     'route' => 'dashboard.faculties.create',    'icon' => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'blue'],
                ['label' => 'New Department',  'route' => 'dashboard.departments.create.academic', 'icon' => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'indigo'],
                ['label' => 'New Student',     'route' => 'dashboard.students.create',     'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'color' => 'emerald'],
                ['label' => 'New Professor',   'route' => 'dashboard.professors.create',   'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'color' => 'purple'],
                ['label' => 'New Course',      'route' => 'dashboard.courses.create',      'icon' => 'M12 9v3m0 0v3m0-3h3m-3 0H9M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253', 'color' => 'teal'],
                ['label' => 'Audit Log',       'route' => 'dashboard.audit-logs.index',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'color' => 'gray'],
            ] as $action)
                @php
                    $qaColors = [
                        'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-600',   'text' => 'text-blue-700',   'hover' => 'hover:bg-blue-100'],
                        'indigo' => ['bg' => 'bg-indigo-50', 'icon' => 'text-indigo-600', 'text' => 'text-indigo-700', 'hover' => 'hover:bg-indigo-100'],
                        'emerald'=> ['bg' => 'bg-emerald-50','icon' => 'text-emerald-600','text' => 'text-emerald-700','hover' => 'hover:bg-emerald-100'],
                        'purple' => ['bg' => 'bg-purple-50', 'icon' => 'text-purple-600', 'text' => 'text-purple-700', 'hover' => 'hover:bg-purple-100'],
                        'teal'   => ['bg' => 'bg-teal-50',   'icon' => 'text-teal-600',   'text' => 'text-teal-700',   'hover' => 'hover:bg-teal-100'],
                        'gray'   => ['bg' => 'bg-gray-50',   'icon' => 'text-gray-600',   'text' => 'text-gray-700',   'hover' => 'hover:bg-gray-100'],
                    ];
                    $qc = $qaColors[$action['color']];
                @endphp
                <a href="{{ route($action['route']) }}"
                   class="flex flex-col items-center gap-2 rounded-2xl border border-gray-200 py-4 px-3 {{ $qc['bg'] }} {{ $qc['hover'] }} transition-colors text-center">
                    <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 {{ $qc['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $action['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold {{ $qc['text'] }} leading-tight">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ── RECENT ACTIVITY + RECENT ASSIGNMENTS (2-col) ─────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Recent Audit Activity --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Recent Audit Activity</h3>
                <a href="{{ route('dashboard.audit-logs.index') }}"
                   class="text-xs text-blue-600 hover:text-blue-700 font-medium">View all →</a>
            </div>
            @if($recentAuditLogs->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-gray-400">No audit entries yet.</div>
            @else
                <ul class="divide-y divide-gray-50">
                    @foreach($recentAuditLogs as $log)
                        @php
                            $actionColors = [
                                'created'  => 'bg-green-100 text-green-700',
                                'updated'  => 'bg-blue-100 text-blue-700',
                                'deleted'  => 'bg-red-100 text-red-700',
                                'assigned' => 'bg-indigo-100 text-indigo-700',
                                'revoked'  => 'bg-amber-100 text-amber-700',
                            ];
                            $logBadgeClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <li class="px-5 py-3 flex items-start gap-3">
                            <span class="mt-0.5 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $logBadgeClass }} shrink-0 capitalize">
                                {{ $log->action }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 truncate">{{ $log->description }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $log->user?->first_name }} {{ $log->user?->last_name }}
                                    · {{ $log->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Recent Admin Assignments --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Recent Admin Assignments</h3>
                <a href="{{ route('dashboard.audit-logs.index', ['action' => 'assigned']) }}"
                   class="text-xs text-blue-600 hover:text-blue-700 font-medium">View all →</a>
            </div>
            @if($recentAssignments->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-gray-400">No assignment events yet.</div>
            @else
                <ul class="divide-y divide-gray-50">
                    @foreach($recentAssignments as $log)
                        @php
                            $isAssign = $log->action === 'assigned';
                        @endphp
                        <li class="px-5 py-3 flex items-start gap-3">
                            <div class="mt-1 w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ $isAssign ? 'bg-green-100' : 'bg-red-100' }}">
                                <svg class="w-3.5 h-3.5 {{ $isAssign ? 'text-green-600' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="{{ $isAssign ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 truncate">{{ $log->description }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    By {{ $log->user?->first_name }} {{ $log->user?->last_name }}
                                    · {{ $log->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>

{{-- ════════════════════════════════════════
     FACULTY ADMIN VIEW
     ════════════════════════════════════════ --}}
@elseif($role === 'faculty_admin')

    {{-- Faculty context banner --}}
    <div class="bg-blue-50 border border-blue-100 rounded-2xl px-5 py-4 mb-6 flex items-center gap-3">
        <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-blue-800">{{ $faculty->name }}</p>
            @if($faculty->code)
                <p class="text-xs text-blue-500 mt-0.5">{{ $faculty->code }}</p>
            @endif
        </div>
        <span class="ms-auto shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $faculty->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $faculty->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Faculty Overview</h3>

    @php
    $facultyCards = [
        ['key' => 'departments', 'label' => 'Departments', 'color' => 'blue',   'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['key' => 'professors',  'label' => 'Professors',  'color' => 'indigo', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ['key' => 'employees',   'label' => 'Employees',   'color' => 'purple', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['key' => 'students',    'label' => 'Students',    'color' => 'emerald','icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['key' => 'courses',     'label' => 'Courses',     'color' => 'amber',  'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    ];
    $colorMap = [
        'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-600'],
        'indigo' => ['bg' => 'bg-indigo-50', 'icon' => 'text-indigo-600'],
        'purple' => ['bg' => 'bg-purple-50', 'icon' => 'text-purple-600'],
        'emerald'=> ['bg' => 'bg-emerald-50','icon' => 'text-emerald-600'],
        'amber'  => ['bg' => 'bg-amber-50',  'icon' => 'text-amber-600'],
    ];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4 mb-10">
        @foreach($facultyCards as $card)
            @php $c = $colorMap[$card['color']]; @endphp
            <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ number_format($stats[$card['key']]) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Departments</h3>

    @if($departments->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center text-sm text-gray-400">
            No departments found.
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="px-5 py-3 font-medium text-gray-500">Department</th>
                        <th class="px-5 py-3 font-medium text-gray-500 text-center">Professors</th>
                        <th class="px-5 py-3 font-medium text-gray-500 text-center">Employees</th>
                        <th class="px-5 py-3 font-medium text-gray-500 text-center">Students</th>
                        <th class="px-5 py-3 font-medium text-gray-500 text-center">Courses</th>
                        <th class="px-5 py-3 font-medium text-gray-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($departments as $dept)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900">{{ $dept->name }}</p>
                                @if($dept->code)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $dept->code }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center font-semibold text-gray-700">{{ number_format($dept->professors_count) }}</td>
                            <td class="px-5 py-3 text-center font-semibold text-gray-700">{{ number_format($dept->employees_count) }}</td>
                            <td class="px-5 py-3 text-center font-semibold text-gray-700">{{ number_format($dept->students_count) }}</td>
                            <td class="px-5 py-3 text-center font-semibold text-gray-700">{{ number_format($dept->courses_count) }}</td>
                            <td class="px-5 py-3 text-end">
                                <a href="{{ route('dashboard.departments.show', $dept) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium text-xs">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

{{-- ════════════════════════════════════════
     DEPARTMENT ADMIN VIEW
     ════════════════════════════════════════ --}}
@elseif($role === 'department_admin')

    {{-- Department context banner --}}
    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl px-5 py-4 mb-6 flex items-center gap-3">
        <svg class="w-5 h-5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-indigo-800">{{ $department->name }}</p>
            <p class="text-xs text-indigo-500 mt-0.5">
                {{ $department->faculty->name ?? '—' }}
                @if($department->code) · {{ $department->code }} @endif
            </p>
        </div>
        <span class="ms-auto shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $department->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $department->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Department Overview</h3>

    @php
    $deptCards = [
        ['key' => 'professors', 'label' => 'Professors', 'color' => 'indigo', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ['key' => 'employees',  'label' => 'Employees',  'color' => 'purple', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['key' => 'students',   'label' => 'Students',   'color' => 'emerald','icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['key' => 'courses',    'label' => 'Courses',    'color' => 'amber',  'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['key' => 'sections',   'label' => 'Sections',   'color' => 'teal',   'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
    ];
    $colorMapDept = [
        'indigo' => ['bg' => 'bg-indigo-50', 'icon' => 'text-indigo-600'],
        'purple' => ['bg' => 'bg-purple-50', 'icon' => 'text-purple-600'],
        'emerald'=> ['bg' => 'bg-emerald-50','icon' => 'text-emerald-600'],
        'amber'  => ['bg' => 'bg-amber-50',  'icon' => 'text-amber-600'],
        'teal'   => ['bg' => 'bg-teal-50',   'icon' => 'text-teal-600'],
    ];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
        @foreach($deptCards as $card)
            @php $c = $colorMapDept[$card['color']]; @endphp
            <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ number_format($stats[$card['key']]) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Student status breakdown --}}
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Student Status</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['key' => 'active',    'label' => 'Active',    'bg' => 'bg-green-50',  'text' => 'text-green-700',  'num' => 'text-green-800'],
            ['key' => 'graduated', 'label' => 'Graduated', 'bg' => 'bg-blue-50',   'text' => 'text-blue-600',   'num' => 'text-blue-800'],
            ['key' => 'suspended', 'label' => 'Suspended', 'bg' => 'bg-amber-50',  'text' => 'text-amber-600',  'num' => 'text-amber-800'],
            ['key' => 'withdrawn', 'label' => 'Withdrawn', 'bg' => 'bg-red-50',    'text' => 'text-red-500',    'num' => 'text-red-700'],
        ] as $row)
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-sm text-gray-500 mb-1">{{ $row['label'] }}</p>
                <p class="text-2xl font-bold {{ $row['num'] }}">{{ number_format($studentBreakdown[$row['key']]) }}</p>
                @if($stats['students'] > 0)
                    <p class="text-xs {{ $row['text'] }} mt-1">
                        {{ number_format($studentBreakdown[$row['key']] / $stats['students'] * 100, 1) }}%
                    </p>
                @endif
            </div>
        @endforeach
    </div>

@endif

@endsection

