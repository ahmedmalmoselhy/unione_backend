<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      x-data="{ dark: document.documentElement.classList.contains('dark') }">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Dashboard') — UniOne</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function(){
            var t=localStorage.getItem('theme');
            if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-950 min-h-screen">

<div class="flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <aside class="w-64 shrink-0 bg-white dark:bg-gray-900 border-e border-gray-200 dark:border-gray-700 flex flex-col">

        @php
            $__locale     = app()->getLocale();
            $__university = \App\Models\University::first();
            $__univName   = $__university
                ? ($__locale === 'ar' ? $__university->name_ar : $__university->name)
                : null;

            $__facultyName = null;
            if (auth()->user()->isFacultyAdmin()) {
                $__facultyId   = auth()->user()->scopedFacultyId();
                $__faculty     = $__facultyId ? \App\Models\Faculty::find($__facultyId) : null;
                $__facultyName = $__faculty
                    ? ($__locale === 'ar' ? $__faculty->name_ar : $__faculty->name)
                    : null;
            }
        @endphp

        {{-- Brand --}}
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
            <span class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">UniOne</span>
            @if($__univName && (auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin()))
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">
                    {{ $__univName }}{{ $__facultyName ? ' — ' . $__facultyName : '' }}
                </p>
            @endif
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            {{-- Home --}}
            <a href="{{ route('dashboard.home') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('dashboard.home')
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('dashboard.nav_home') }}
            </a>

            {{-- Administration — system admin only --}}
            @if(auth()->user()->isSystemAdmin())
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('dashboard.nav_administration') }}</p>
                </div>

                <a href="{{ route('dashboard.university.show') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.university.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    {{ __('dashboard.nav_university') }}
                </a>

                <a href="{{ route('dashboard.faculties.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.faculties.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                    </svg>
                    {{ __('dashboard.nav_faculties') }}
                </a>

                <a href="{{ route('dashboard.academic-terms.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.academic-terms.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ __('dashboard.nav_academic_terms') }}
                </a>

                <a href="{{ route('dashboard.audit-logs.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.audit-logs.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    {{ __('dashboard.nav_audit_log') }}
                </a>
            @endif

            {{-- Settings — university admin only --}}
            @if(auth()->user()->isUniversityAdmin())
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('dashboard.nav_settings') }}</p>
                </div>

                <a href="{{ route('dashboard.university.show') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.university.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ __('dashboard.nav_university_settings') }}
                </a>
            @endif

            {{-- Faculty Management — system admin + faculty admin --}}
            @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin())
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('dashboard.nav_faculty_management') }}</p>
                </div>

                <a href="{{ route('dashboard.departments.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.departments.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    {{ __('dashboard.nav_departments') }}
                </a>
            @endif

            {{-- Academic Resources — all scoped admins --}}
            @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                <div class="pt-4 pb-1 px-3">
                    <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('dashboard.nav_academic_resources') }}</p>
                </div>

                <a href="{{ route('dashboard.professors.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.professors.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ __('dashboard.nav_professors') }}
                </a>

                <a href="{{ route('dashboard.employees.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.employees.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('dashboard.nav_employees') }}
                </a>

                <a href="{{ route('dashboard.courses.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.courses.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    {{ __('dashboard.nav_courses') }}
                </a>

                <a href="{{ route('dashboard.sections.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.sections.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    {{ __('dashboard.nav_sections') }}
                </a>

                <a href="{{ route('dashboard.students.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.students.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    {{ __('dashboard.nav_students') }}
                </a>

                <a href="{{ route('dashboard.enrollments.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.enrollments.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    {{ __('dashboard.nav_enrollments') }}
                </a>

                <a href="{{ route('dashboard.grades.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.grades.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    {{ __('dashboard.nav_grades') }}
                </a>

                <a href="{{ route('dashboard.announcements.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard.announcements.*')
                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    {{ __('dashboard.nav_announcements') }}
                </a>
            @endif

        </nav>

    </aside>

    {{-- Main area --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top bar --}}
        <header class="shrink-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-6 py-3 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">@yield('heading')</h1>

            <div class="flex items-center gap-2">

                {{-- Dark mode toggle --}}
                <button @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light')"
                        title="{{ __('dashboard.dark_mode') }}"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg x-show="!dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                {{-- Locale dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        {{ strtoupper(app()->getLocale()) }}
                        <svg class="w-3 h-3 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="absolute end-0 mt-1 w-32 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 py-1">
                        @foreach(['en' => 'English', 'ar' => 'العربية'] as $__code => $__label)
                            <form method="POST" action="{{ route('dashboard.locale.store') }}">
                                @csrf
                                <input type="hidden" name="locale" value="{{ $__code }}">
                                <button type="submit" @click="open = false"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-sm transition-colors
                                               {{ app()->getLocale() === $__code
                                                    ? 'text-blue-600 dark:text-blue-400 font-semibold bg-blue-50 dark:bg-blue-900/20'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                    @if(app()->getLocale() === $__code)
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                                        </svg>
                                    @else
                                        <span class="w-3.5 shrink-0"></span>
                                    @endif
                                    {{ $__label }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

                {{-- Divider --}}
                <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

                {{-- Notification bell --}}
                @php
                    $__unreadCount = auth()->user()->unreadNotifications()->count();
                    $__recentNotifs = auth()->user()->unreadNotifications()->latest()->limit(5)->get();
                @endphp
                <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false"
                        class="relative p-2 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($__unreadCount > 0)
                        <span class="absolute top-1 end-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center leading-none">
                            {{ $__unreadCount > 99 ? '99+' : $__unreadCount }}
                        </span>
                    @endif
                </button>

                <div x-show="open" x-cloak x-transition
                     class="absolute end-0 mt-1 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50">

                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('dashboard.notifications') }}</p>
                        @if($__unreadCount > 0)
                            <form method="POST" action="{{ route('dashboard.notifications.read-all') }}">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 transition-colors">
                                    {{ __('dashboard.mark_all_read') }}
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($__recentNotifs->isEmpty())
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-6">{{ __('dashboard.no_new_notifications') }}</p>
                    @else
                        <ul class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach($__recentNotifs as $__notif)
                                @php $__nd = $__notif->data; @endphp
                                <li class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ $__nd['title'] ?? 'Notification' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-snug mt-0.5 line-clamp-2">{{ $__nd['body'] ?? '' }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">{{ $__notif->created_at->diffForHumans() }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="px-4 py-2 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('dashboard.notifications.index') }}"
                           class="block text-xs text-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium transition-colors">
                            {{ __('dashboard.view_all_notifications') }}
                        </a>
                    </div>
                </div>
                </div>{{-- end notification x-data --}}

                {{-- Divider --}}
                <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

                {{-- User dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-700 dark:text-blue-400 font-semibold text-sm shrink-0">
                            {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white hidden lg:block">
                            {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                        </span>
                        <svg class="w-3 h-3 shrink-0 text-gray-400 hidden lg:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak x-transition
                         class="absolute end-0 mt-1 w-52 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 py-1">

                        {{-- User info header --}}
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 truncate mt-0.5">{{ auth()->user()->email }}</p>
                        </div>

                        {{-- Logout --}}
                        <div class="py-1">
                            <form method="POST" action="{{ route('dashboard.logout') }}">
                                @csrf
                                <button type="submit" @click="open = false"
                                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ __('dashboard.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto px-8 py-8">
            @yield('content')
        </main>

    </div>

</div>

@stack('scripts')

</body>
</html>
