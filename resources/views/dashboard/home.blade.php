@extends('dashboard.layouts.app')

@section('title', __('home.title'))
@section('heading', __('home.title'))

@section('content')

{{-- Welcome banner --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-700 dark:text-blue-300 font-bold text-lg shrink-0">
            {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
        </div>
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ __('home.welcome', ['name' => auth()->user()->first_name . ' ' . auth()->user()->last_name]) }}
            </h2>
            <div class="flex items-center gap-2 mt-1">
                @foreach(auth()->user()->roles()->whereNull('role_user.revoked_at')->get() as $userRole)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                        {{ $userRole->label }}
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

    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('home.university_overview') }}</h3>

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
                    'blue'   => ['bg' => 'bg-blue-50 dark:bg-blue-900/30',     'icon' => 'text-blue-600 dark:text-blue-400'],
                    'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/30', 'icon' => 'text-indigo-600 dark:text-indigo-400'],
                    'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/30', 'icon' => 'text-purple-600 dark:text-purple-400'],
                    'emerald'=> ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30','icon' => 'text-emerald-600 dark:text-emerald-400'],
                    'teal'   => ['bg' => 'bg-teal-50 dark:bg-teal-900/30',     'icon' => 'text-teal-600 dark:text-teal-400'],
                    'amber'  => ['bg' => 'bg-amber-50 dark:bg-amber-900/30',   'icon' => 'text-amber-600 dark:text-amber-400'],
                    'orange' => ['bg' => 'bg-orange-50 dark:bg-orange-900/30', 'icon' => 'text-orange-600 dark:text-orange-400'],
                ];
                $c = $colorMap[$card['color']];
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('home.' . $card['key']) }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ number_format($globalStats[$card['key']]) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── CHARTS ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-5 mb-5">

        {{-- Donut: Student Enrollment Status --}}
        <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 flex flex-col">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('home.chart_enrollment_status') }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 mb-4">{{ __('home.chart_enrollment_subtitle') }}</p>
            <div class="flex-1 relative" style="min-height:190px">
                <canvas id="studentStatusChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2">
                @foreach([
                    ['label' => __('home.active'),    'color' => 'bg-emerald-500', 'val' => $faculties->sum('active_students_count')],
                    ['label' => __('home.graduated'), 'color' => 'bg-blue-500',    'val' => $faculties->sum('graduated_students_count')],
                    ['label' => __('home.suspended'), 'color' => 'bg-amber-500',   'val' => $faculties->sum('suspended_students_count')],
                    ['label' => __('home.withdrawn'), 'color' => 'bg-red-500',     'val' => $faculties->sum('withdrawn_students_count')],
                ] as $item)
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $item['color'] }} shrink-0"></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $item['label'] }}</span>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 ml-auto">{{ number_format($item['val']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Stacked Bar: Students by Faculty --}}
        <div class="xl:col-span-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 flex flex-col">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('home.chart_by_faculty') }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 mb-4">{{ __('home.chart_by_faculty_subtitle') }}</p>
            <div class="flex-1 relative" style="min-height:190px">
                <canvas id="studentsByFacultyChart"></canvas>
            </div>
        </div>

    </div>

    {{-- Grouped Bar: Staff by Faculty --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-10">
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('home.chart_staff_by_faculty') }}</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 mb-4">{{ __('home.chart_staff_subtitle') }}</p>
        <div class="relative" style="height:180px">
            <canvas id="staffByFacultyChart"></canvas>
        </div>
    </div>

    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('home.by_faculty') }}</h3>

    @if($faculties->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 text-center text-sm text-gray-400 dark:text-gray-500">
            {{ __('home.no_faculties') }}
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach($faculties as $faculty)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $faculty->name_ar ?? $faculty->name }}</p>
                            @if($faculty->code)
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $faculty->code }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $faculty->is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                            {{ $faculty->is_active ? __('home.active') : __('home.inactive') }}
                        </span>
                    </div>
                    <div class="divide-y divide-gray-50 dark:divide-gray-700">
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('home.departments') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($faculty->departments_count) }}</span>
                        </div>
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('home.professors') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($professorsByFaculty[$faculty->id] ?? 0) }}</span>
                        </div>
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('home.students') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($faculty->students_count) }}</span>
                        </div>
                        <div class="px-5 py-3 grid grid-cols-2 gap-x-4 gap-y-1.5">
                            @foreach([
                                ['key' => 'active_students_count',    'label' => __('home.active'),    'color' => 'text-green-600'],
                                ['key' => 'graduated_students_count', 'label' => __('home.graduated'), 'color' => 'text-blue-600'],
                                ['key' => 'suspended_students_count', 'label' => __('home.suspended'), 'color' => 'text-amber-600'],
                                ['key' => 'withdrawn_students_count', 'label' => __('home.withdrawn'), 'color' => 'text-red-500'],
                            ] as $row)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-400 dark:text-gray-600">{{ $row['label'] }}</span>
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
                   'label' => __('home.health_faculties_no_admin'),
                   'count' => $dataHealth['faculties_without_admin'],
                   'link'  => route('dashboard.faculties.index')]
                : null,
            $dataHealth['depts_without_head'] > 0
                ? ['icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                   'label' => __('home.health_depts_no_head'),
                   'count' => $dataHealth['depts_without_head'],
                   'link'  => route('dashboard.departments.index', ['no_head' => 1])]
                : null,
            $dataHealth['sections_without_prof'] > 0
                ? ['icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                   'label' => __('home.health_sections_no_prof'),
                   'count' => $dataHealth['sections_without_prof'],
                   'link'  => route('dashboard.sections.index')]
                : null,
            $dataHealth['students_without_dept'] > 0
                ? ['icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                   'label' => __('home.health_students_no_dept'),
                   'count' => $dataHealth['students_without_dept'],
                   'link'  => route('dashboard.students.index', ['no_dept' => 1])]
                : null,
        ]);
    @endphp
    @if(count($warnings) > 0)
        <div class="mt-10 mb-8">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                {{ __('home.data_health_warnings') }}
            </h3>
            <div class="space-y-2">
                @foreach($warnings as $w)
                    <a href="{{ $w['link'] }}"
                       class="flex items-center gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl px-4 py-3 group hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/40 transition-colors">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $w['icon'] }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">{{ $w['label'] }}</p>
                        </div>
                        <span class="shrink-0 text-lg font-bold text-amber-700 dark:text-amber-400">{{ $w['count'] }}</span>
                        <svg class="w-4 h-4 text-amber-400 dark:text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="mt-10 mb-8 flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-5 py-3">
            <svg class="w-5 h-5 text-green-500 dark:text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-green-700 dark:text-green-400">{{ __('home.health_all_passed') }}</p>
        </div>
    @endif

    {{-- ── QUICK ACTIONS ─────────────────────────────────────── --}}
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('home.quick_actions') }}</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
            @foreach([
                ['label' => __('home.action_new_faculty'),     'route' => 'dashboard.faculties.create',    'icon' => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'blue'],
                ['label' => __('home.action_new_department'),  'route' => 'dashboard.departments.create.academic', 'icon' => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'indigo'],
                ['label' => __('home.action_new_student'),     'route' => 'dashboard.students.create',     'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'color' => 'emerald'],
                ['label' => __('home.action_new_professor'),   'route' => 'dashboard.professors.create',   'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'color' => 'purple'],
                ['label' => __('home.action_new_course'),      'route' => 'dashboard.courses.create',      'icon' => 'M12 9v3m0 0v3m0-3h3m-3 0H9M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253', 'color' => 'teal'],
                ['label' => __('home.action_audit_log'),       'route' => 'dashboard.audit-logs.index',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'color' => 'gray'],
            ] as $action)
                @php
                    $qaColors = [
                        'blue'   => ['bg' => 'bg-blue-50 dark:bg-blue-900/20',     'icon' => 'text-blue-600 dark:text-blue-400',    'text' => 'text-blue-700 dark:text-blue-400',    'hover' => 'hover:bg-blue-100 dark:hover:bg-blue-900/30'],
                        'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'icon' => 'text-indigo-600 dark:text-indigo-400', 'text' => 'text-indigo-700 dark:text-indigo-400', 'hover' => 'hover:bg-indigo-100 dark:hover:bg-indigo-900/30'],
                        'emerald'=> ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20','icon' => 'text-emerald-600 dark:text-emerald-400','text' => 'text-emerald-700 dark:text-emerald-400','hover' => 'hover:bg-emerald-100 dark:hover:bg-emerald-900/30'],
                        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/20', 'icon' => 'text-purple-600 dark:text-purple-400', 'text' => 'text-purple-700 dark:text-purple-400', 'hover' => 'hover:bg-purple-100 dark:hover:bg-purple-900/30'],
                        'teal'   => ['bg' => 'bg-teal-50 dark:bg-teal-900/20',     'icon' => 'text-teal-600 dark:text-teal-400',    'text' => 'text-teal-700 dark:text-teal-400',    'hover' => 'hover:bg-teal-100 dark:hover:bg-teal-900/30'],
                        'gray'   => ['bg' => 'bg-gray-50 dark:bg-gray-800',         'icon' => 'text-gray-600 dark:text-gray-400',    'text' => 'text-gray-700 dark:text-gray-300',    'hover' => 'hover:bg-gray-100 dark:hover:bg-gray-700'],
                    ];
                    $qc = $qaColors[$action['color']];
                @endphp
                <a href="{{ route($action['route']) }}"
                   class="flex flex-col items-center gap-2 rounded-2xl border border-gray-200 dark:border-gray-700 py-4 px-3 {{ $qc['bg'] }} {{ $qc['hover'] }} transition-colors text-center">
                    <div class="w-9 h-9 rounded-xl bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm">
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
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('home.recent_audit') }}</h3>
                <a href="{{ route('dashboard.audit-logs.index') }}"
                   class="text-xs text-blue-600 hover:text-blue-700 font-medium">{{ __('home.view_all') }}</a>
            </div>
            @if($recentAuditLogs->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-gray-400 dark:text-gray-500">{{ __('home.no_audit_entries') }}</div>
            @else
                <ul class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($recentAuditLogs as $log)
                        @php
                            $actionColors = [
                                'created'  => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                'updated'  => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                'deleted'  => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                'assigned' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
                                'revoked'  => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                            ];
                            $logBadgeClass = $actionColors[$log->action] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
                        @endphp
                        <li class="px-5 py-3 flex items-start gap-3">
                            <span class="mt-0.5 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $logBadgeClass }} shrink-0 capitalize">
                                {{ $log->action }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 dark:text-gray-200 truncate">{{ $log->description }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
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
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('home.recent_assignments') }}</h3>
                <a href="{{ route('dashboard.audit-logs.index', ['action' => 'assigned']) }}"
                   class="text-xs text-blue-600 hover:text-blue-700 font-medium">{{ __('home.view_all') }}</a>
            </div>
            @if($recentAssignments->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-gray-400 dark:text-gray-500">{{ __('home.no_assignments') }}</div>
            @else
                <ul class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($recentAssignments as $log)
                        @php
                            $isAssign = $log->action === 'assigned';
                        @endphp
                        <li class="px-5 py-3 flex items-start gap-3">
                            <div class="mt-1 w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ $isAssign ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                                <svg class="w-3.5 h-3.5 {{ $isAssign ? 'text-green-600' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="{{ $isAssign ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 dark:text-gray-200 truncate">{{ $log->description }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ __('home.by_user') }} {{ $log->user?->first_name }} {{ $log->user?->last_name }}
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
     UNIVERSITY ADMIN VIEW
     ════════════════════════════════════════ --}}
@elseif($role === 'university_admin')

    {{-- University settings banner --}}
    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-2xl px-5 py-4 mb-8 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 10l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V10z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-indigo-800 dark:text-indigo-300">{{ $university->name }}</p>
            <p class="text-xs text-indigo-500 dark:text-indigo-400 mt-0.5">
                {{ __('home.university_admin_role') }}
                @if($university->established_at) · Est. {{ $university->established_at->format('Y') }}@endif
            </p>
        </div>
        <a href="{{ route('dashboard.university.show') }}"
           class="shrink-0 text-xs font-semibold text-indigo-700 dark:text-indigo-300 bg-white dark:bg-gray-800 border border-indigo-200 dark:border-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 px-3 py-1.5 rounded-lg transition-colors">
            {{ __('home.manage_university') }}
        </a>
    </div>

    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('home.university_overview') }}</h3>

    @php
    $uaStatCards = [
        ['key' => 'students',    'label' => 'Students',    'color' => 'blue',   'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['key' => 'professors',  'label' => 'Professors',  'color' => 'indigo', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ['key' => 'employees',   'label' => 'Employees',   'color' => 'purple', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['key' => 'courses',     'label' => 'Courses',     'color' => 'emerald','icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['key' => 'sections',    'label' => 'Sections',    'color' => 'teal',   'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
        ['key' => 'faculties',   'label' => 'Faculties',   'color' => 'amber',  'icon' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
        ['key' => 'departments', 'label' => 'Departments', 'color' => 'orange', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
    ];
    $uaColorMap = [
        'blue'   => ['bg' => 'bg-blue-50 dark:bg-blue-900/30',     'icon' => 'text-blue-600 dark:text-blue-400'],
        'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/30', 'icon' => 'text-indigo-600 dark:text-indigo-400'],
        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/30', 'icon' => 'text-purple-600 dark:text-purple-400'],
        'emerald'=> ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30','icon' => 'text-emerald-600 dark:text-emerald-400'],
        'teal'   => ['bg' => 'bg-teal-50 dark:bg-teal-900/30',     'icon' => 'text-teal-600 dark:text-teal-400'],
        'amber'  => ['bg' => 'bg-amber-50 dark:bg-amber-900/30',   'icon' => 'text-amber-600 dark:text-amber-400'],
        'orange' => ['bg' => 'bg-orange-50 dark:bg-orange-900/30', 'icon' => 'text-orange-600 dark:text-orange-400'],
    ];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 mb-10">
        @foreach($uaStatCards as $card)
            @php $c = $uaColorMap[$card['color']]; @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('home.' . $card['key']) }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ number_format($globalStats[$card['key']]) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── CHARTS ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-5 mb-5">
        <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 flex flex-col">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('home.chart_enrollment_status') }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 mb-4">{{ __('home.chart_enrollment_subtitle') }}</p>
            <div class="flex-1 relative" style="min-height:190px">
                <canvas id="studentStatusChartUA"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2">
                @foreach([
                    ['label' => __('home.active'),    'color' => 'bg-emerald-500', 'val' => $faculties->sum('active_students_count')],
                    ['label' => __('home.graduated'), 'color' => 'bg-blue-500',    'val' => $faculties->sum('graduated_students_count')],
                    ['label' => __('home.suspended'), 'color' => 'bg-amber-500',   'val' => $faculties->sum('suspended_students_count')],
                    ['label' => __('home.withdrawn'), 'color' => 'bg-red-500',     'val' => $faculties->sum('withdrawn_students_count')],
                ] as $item)
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $item['color'] }} shrink-0"></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $item['label'] }}</span>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 ml-auto">{{ number_format($item['val']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="xl:col-span-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 flex flex-col">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('home.chart_by_faculty') }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 mb-4">{{ __('home.chart_by_faculty_subtitle') }}</p>
            <div class="flex-1 relative" style="min-height:190px">
                <canvas id="studentsByFacultyChartUA"></canvas>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-10">
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('home.chart_staff_by_faculty') }}</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 mb-4">{{ __('home.chart_staff_subtitle') }}</p>
        <div class="relative" style="height:180px">
            <canvas id="staffByFacultyChartUA"></canvas>
        </div>
    </div>

    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('home.by_faculty') }}</h3>

    @if($faculties->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 text-center text-sm text-gray-400 dark:text-gray-500">
            {{ __('home.no_faculties') }}
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach($faculties as $faculty)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $faculty->name_ar ?? $faculty->name }}</p>
                            @if($faculty->code)
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $faculty->code }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $faculty->is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                            {{ $faculty->is_active ? __('home.active') : __('home.inactive') }}
                        </span>
                    </div>
                    <div class="divide-y divide-gray-50 dark:divide-gray-700">
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('home.departments') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($faculty->departments_count) }}</span>
                        </div>
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('home.professors') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($professorsByFaculty[$faculty->id] ?? 0) }}</span>
                        </div>
                        <div class="px-5 py-3 flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('home.students') }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($faculty->students_count) }}</span>
                        </div>
                        <div class="px-5 py-3 grid grid-cols-2 gap-x-4 gap-y-1.5">
                            @foreach([
                                ['key' => 'active_students_count',    'label' => __('home.active'),    'color' => 'text-green-600'],
                                ['key' => 'graduated_students_count', 'label' => __('home.graduated'), 'color' => 'text-blue-600'],
                                ['key' => 'suspended_students_count', 'label' => __('home.suspended'), 'color' => 'text-amber-600'],
                                ['key' => 'withdrawn_students_count', 'label' => __('home.withdrawn'), 'color' => 'text-red-500'],
                            ] as $row)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-400 dark:text-gray-600">{{ $row['label'] }}</span>
                                    <span class="font-medium {{ $row['color'] }}">{{ number_format($faculty->{$row['key']}) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

{{-- ════════════════════════════════════════
     FACULTY ADMIN VIEW
     ════════════════════════════════════════ --}}
@elseif($role === 'faculty_admin')

    {{-- Faculty context banner --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-2xl px-5 py-4 mb-6 flex items-center gap-3">
        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ $faculty->name_ar ?? $faculty->name }}</p>
            @if($faculty->code)
                <p class="text-xs text-blue-500 dark:text-blue-400 mt-0.5">{{ $faculty->code }}</p>
            @endif
        </div>
        <span class="ms-auto shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $faculty->is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
            {{ $faculty->is_active ? __('home.active') : __('home.inactive') }}
        </span>
    </div>

    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('home.faculty_overview') }}</h3>

    @php
    $facultyCards = [
        ['key' => 'departments', 'label' => 'Departments', 'color' => 'blue',   'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['key' => 'professors',  'label' => 'Professors',  'color' => 'indigo', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ['key' => 'employees',   'label' => 'Employees',   'color' => 'purple', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['key' => 'students',    'label' => 'Students',    'color' => 'emerald','icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['key' => 'courses',     'label' => 'Courses',     'color' => 'amber',  'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    ];
    $colorMap = [
        'blue'   => ['bg' => 'bg-blue-50 dark:bg-blue-900/30',     'icon' => 'text-blue-600 dark:text-blue-400'],
        'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/30', 'icon' => 'text-indigo-600 dark:text-indigo-400'],
        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/30', 'icon' => 'text-purple-600 dark:text-purple-400'],
        'emerald'=> ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30','icon' => 'text-emerald-600 dark:text-emerald-400'],
        'amber'  => ['bg' => 'bg-amber-50 dark:bg-amber-900/30',   'icon' => 'text-amber-600 dark:text-amber-400'],
    ];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4 mb-10">
        @foreach($facultyCards as $card)
            @php $c = $colorMap[$card['color']]; @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('home.' . $card['key']) }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ number_format($stats[$card['key']]) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('home.departments_section') }}</h3>

    @if($departments->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 text-center text-sm text-gray-400 dark:text-gray-500">
            {{ __('home.no_departments') }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm dark:text-gray-200">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 text-left">
                        <th class="px-5 py-3 font-medium text-gray-500 dark:text-gray-400">{{ __('home.table_department') }}</th>
                        <th class="px-5 py-3 font-medium text-gray-500 dark:text-gray-400 text-center">{{ __('home.professors') }}</th>
                        <th class="px-5 py-3 font-medium text-gray-500 dark:text-gray-400 text-center">{{ __('home.employees') }}</th>
                        <th class="px-5 py-3 font-medium text-gray-500 dark:text-gray-400 text-center">{{ __('home.students') }}</th>
                        <th class="px-5 py-3 font-medium text-gray-500 dark:text-gray-400 text-center">{{ __('home.courses') }}</th>
                        <th class="px-5 py-3 font-medium text-gray-500 dark:text-gray-400"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($departments as $dept)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $dept->name }}</p>
                                @if($dept->code)
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $dept->code }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">{{ number_format($dept->professors_count) }}</td>
                            <td class="px-5 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">{{ number_format($dept->employees_count) }}</td>
                            <td class="px-5 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">{{ number_format($dept->students_count) }}</td>
                            <td class="px-5 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">{{ number_format($dept->courses_count) }}</td>
                            <td class="px-5 py-3 text-end">
                                <a href="{{ route('dashboard.departments.show', $dept) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium text-xs">{{ __('home.view') }}</a>
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
    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-2xl px-5 py-4 mb-6 flex items-center gap-3">
        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-indigo-800 dark:text-indigo-300">{{ $department->name }}</p>
            <p class="text-xs text-indigo-500 dark:text-indigo-400 mt-0.5">
                {{ $department->faculty->name ?? '—' }}
                @if($department->code) · {{ $department->code }} @endif
            </p>
        </div>
        <span class="ms-auto shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $department->is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
            {{ $department->is_active ? __('home.active') : __('home.inactive') }}
        </span>
    </div>

    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('home.department_overview') }}</h3>

    @php
    $deptCards = [
        ['key' => 'professors', 'label' => 'Professors', 'color' => 'indigo', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ['key' => 'employees',  'label' => 'Employees',  'color' => 'purple', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['key' => 'students',   'label' => 'Students',   'color' => 'emerald','icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['key' => 'courses',    'label' => 'Courses',    'color' => 'amber',  'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['key' => 'sections',   'label' => 'Sections',   'color' => 'teal',   'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
    ];
    $colorMapDept = [
        'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/30', 'icon' => 'text-indigo-600 dark:text-indigo-400'],
        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/30', 'icon' => 'text-purple-600 dark:text-purple-400'],
        'emerald'=> ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30','icon' => 'text-emerald-600 dark:text-emerald-400'],
        'amber'  => ['bg' => 'bg-amber-50 dark:bg-amber-900/30',   'icon' => 'text-amber-600 dark:text-amber-400'],
        'teal'   => ['bg' => 'bg-teal-50 dark:bg-teal-900/30',     'icon' => 'text-teal-600 dark:text-teal-400'],
    ];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
        @foreach($deptCards as $card)
            @php $c = $colorMapDept[$card['color']]; @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('home.' . $card['key']) }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ number_format($stats[$card['key']]) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Student status breakdown --}}
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('home.student_status') }}</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['key' => 'active',    'label' => __('home.active'),    'bg' => 'bg-green-50',  'text' => 'text-green-700',  'num' => 'text-green-800'],
            ['key' => 'graduated', 'label' => __('home.graduated'), 'bg' => 'bg-blue-50',   'text' => 'text-blue-600',   'num' => 'text-blue-800'],
            ['key' => 'suspended', 'label' => __('home.suspended'), 'bg' => 'bg-amber-50',  'text' => 'text-amber-600',  'num' => 'text-amber-800'],
            ['key' => 'withdrawn', 'label' => __('home.withdrawn'), 'bg' => 'bg-red-50',    'text' => 'text-red-500',    'num' => 'text-red-700'],
        ] as $row)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ $row['label'] }}</p>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@if(in_array($role ?? '', ['system_admin', 'university_admin']))
<script>
    const __isDark = document.documentElement.classList.contains('dark');
    Chart.defaults.font.family = 'ui-sans-serif, system-ui, sans-serif';
    Chart.defaults.color       = __isDark ? '#9ca3af' : '#6b7280';
    const __gridColor          = __isDark ? '#374151' : '#f3f4f6';

    const __role = @json($role);
    const __sfx  = __role === 'university_admin' ? 'UA' : '';

    function chartId(base) { return base + __sfx; }

    // ── Student Status Donut ──────────────────────────────────
    (function () {
        const active    = {{ $faculties->sum('active_students_count') }};
        const graduated = {{ $faculties->sum('graduated_students_count') }};
        const suspended = {{ $faculties->sum('suspended_students_count') }};
        const withdrawn = {{ $faculties->sum('withdrawn_students_count') }};
        const total     = active + graduated + suspended + withdrawn;

        new Chart(document.getElementById(chartId('studentStatusChart')), {
            type: 'doughnut',
            data: {
                labels: [@json(__('home.active')), @json(__('home.graduated')), @json(__('home.suspended')), @json(__('home.withdrawn'))],
                datasets: [{
                    data: [active, graduated, suspended, withdrawn],
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                    borderColor: __isDark ? '#1f2937' : '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString()}  (${total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0}%)`
                        }
                    }
                }
            },
            plugins: [{
                id: 'centerLabel',
                afterDraw(chart) {
                    const { width, height, ctx } = chart;
                    ctx.save();
                    ctx.textAlign    = 'center';
                    ctx.textBaseline = 'middle';
                    const cx = width / 2, cy = height / 2;
                    ctx.font      = 'bold 24px ui-sans-serif,system-ui,sans-serif';
                    ctx.fillStyle = __isDark ? '#f9fafb' : '#111827';
                    ctx.fillText(total.toLocaleString(), cx, cy - 9);
                    ctx.font      = '11px ui-sans-serif,system-ui,sans-serif';
                    ctx.fillStyle = __isDark ? '#6b7280' : '#9ca3af';
                    ctx.fillText('{{ __('home.students') }}', cx, cy + 12);
                    ctx.restore();
                }
            }]
        });
    })();

    // ── Students by Faculty (Stacked Bar) ────────────────────
    (function () {
        const labels = @json(app()->getLocale() === 'ar' ? $faculties->pluck('name_ar') : $faculties->pluck('name'));
        new Chart(document.getElementById(chartId('studentsByFacultyChart')), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: @json(__('home.active')),    data: @json($faculties->pluck('active_students_count')->values()),    backgroundColor: '#10b981', stack: 's', borderRadius: 2 },
                    { label: @json(__('home.graduated')), data: @json($faculties->pluck('graduated_students_count')->values()), backgroundColor: '#3b82f6', stack: 's', borderRadius: 2 },
                    { label: @json(__('home.suspended')), data: @json($faculties->pluck('suspended_students_count')->values()), backgroundColor: '#f59e0b', stack: 's', borderRadius: 2 },
                    { label: @json(__('home.withdrawn')), data: @json($faculties->pluck('withdrawn_students_count')->values()), backgroundColor: '#ef4444', stack: 's', borderRadius: 2 },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 10, padding: 14, usePointStyle: true, pointStyle: 'circle' } },
                },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, grid: { color: __gridColor }, beginAtZero: true, ticks: { precision: 0 } },
                }
            }
        });
    })();

    // ── Staff by Faculty (Grouped Bar) ───────────────────────
    (function () {
        const labels   = @json(app()->getLocale() === 'ar' ? $faculties->pluck('name_ar') : $faculties->pluck('name'));
        const profData = @json($faculties->map(fn ($f) => $professorsByFaculty[$f->id] ?? 0)->values());
        const empData  = @json($faculties->map(fn ($f) => $employeesByFaculty[$f->id] ?? 0)->values());
        new Chart(document.getElementById(chartId('staffByFacultyChart')), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: @json(__('home.professors')), data: profData, backgroundColor: '#6366f1', borderRadius: 4, barPercentage: 0.65 },
                    { label: @json(__('home.employees')),  data: empData,  backgroundColor: '#a5b4fc', borderRadius: 4, barPercentage: 0.65 },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 10, padding: 14, usePointStyle: true, pointStyle: 'circle' } },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: __gridColor }, beginAtZero: true, ticks: { precision: 0 } },
                }
            }
        });
    })();
</script>
@endif
@endpush

@endsection

