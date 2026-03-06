@extends('dashboard.layouts.app')

@section('title', 'Home')
@section('heading', 'Home')

@section('content')

{{-- Welcome banner --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-8">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg shrink-0">
            {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
        </div>
        <div>
            <h2 class="text-xl font-semibold text-gray-900">
                Welcome, {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
            </h2>
            <div class="flex items-center gap-2 mt-1">
                @foreach(auth()->user()->roles()->wherePivotNull('revoked_at')->get() as $role)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $role->label }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Global stats --}}
<h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Overview</h3>

@php
$statCards = [
    ['key' => 'students',    'label' => 'Students',    'color' => 'blue',   'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
    ['key' => 'professors',  'label' => 'Professors',  'color' => 'indigo', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    ['key' => 'employees',   'label' => 'Employees',   'color' => 'purple', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    ['key' => 'courses',     'label' => 'Courses',     'color' => 'emerald','icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    ['key' => 'sections',    'label' => 'Sections',    'color' => 'teal',   'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
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

{{-- Per-faculty breakdown --}}
<h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">By Faculty</h3>

@if($faculties->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center text-sm text-gray-400">
        No faculties found.
    </div>
@else
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @foreach($faculties as $faculty)
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

                {{-- Faculty header --}}
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

                {{-- Stat rows --}}
                <div class="divide-y divide-gray-50">

                    {{-- Departments --}}
                    <div class="px-5 py-3 flex items-center justify-between text-sm">
                        <span class="text-gray-500">Departments</span>
                        <span class="font-semibold text-gray-900">{{ number_format($faculty->departments_count) }}</span>
                    </div>

                    {{-- Professors --}}
                    <div class="px-5 py-3 flex items-center justify-between text-sm">
                        <span class="text-gray-500">Professors</span>
                        <span class="font-semibold text-gray-900">{{ number_format($professorsByFaculty[$faculty->id] ?? 0) }}</span>
                    </div>

                    {{-- Students total --}}
                    <div class="px-5 py-3 flex items-center justify-between text-sm">
                        <span class="text-gray-500">Students</span>
                        <span class="font-semibold text-gray-900">{{ number_format($faculty->students_count) }}</span>
                    </div>

                    {{-- Student status breakdown --}}
                    <div class="px-5 py-3 grid grid-cols-2 gap-x-4 gap-y-1.5">
                        @foreach([
                            ['key' => 'active_students_count',    'label' => 'Active',     'color' => 'text-green-600'],
                            ['key' => 'graduated_students_count', 'label' => 'Graduated',  'color' => 'text-blue-600'],
                            ['key' => 'suspended_students_count', 'label' => 'Suspended',  'color' => 'text-amber-600'],
                            ['key' => 'withdrawn_students_count', 'label' => 'Withdrawn',  'color' => 'text-red-500'],
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

@endsection
