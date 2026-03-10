<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      x-data="{ dark: document.documentElement.classList.contains('dark'), sidebarOpen: false }">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Portal') — UniOne</title>
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

@php
    $__user      = auth()->user();
    $__student   = $__user?->student()->first();
    $__professor = $__user?->professor()->first();
    $__employee  = $__user?->employee()->first();
    $__unread    = $__user?->unreadNotifications()->count() ?? 0;
@endphp

<div class="flex h-screen overflow-hidden">

    {{-- Mobile sidebar backdrop --}}
    <div x-show="sidebarOpen" x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/40 z-20 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:static inset-y-0 start-0 z-30 w-64 shrink-0 bg-white dark:bg-gray-900
                  border-e border-gray-200 dark:border-gray-700 flex flex-col
                  transform transition-transform duration-200">

        {{-- Brand --}}
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <span class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">UniOne</span>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    @if($__student) Student Portal
                    @elseif($__professor) Professor Portal
                    @elseif($__employee) Employee Portal
                    @endif
                </p>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            {{-- Home --}}
            <a href="{{ route('portal.home') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('portal.home') ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Home
            </a>

            {{-- Profile --}}
            <a href="{{ route('portal.profile') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('portal.profile') ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                My Profile
            </a>

            {{-- Schedule --}}
            @if($__student || $__professor)
            <a href="{{ route('portal.schedule') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('portal.schedule') ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Schedule
            </a>
            @endif

            {{-- Student-specific --}}
            @if($__student)
            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Academic</p>
            </div>
            <a href="{{ route('portal.enrollments.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('portal.enrollments.*') ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                My Courses
            </a>
            <a href="{{ route('portal.grades') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('portal.grades') ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                My Grades
            </a>
            @endif

            {{-- Professor-specific --}}
            @if($__professor)
            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Teaching</p>
            </div>
            <a href="{{ route('portal.sections.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('portal.sections.*') ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                My Sections
            </a>
            @endif

            {{-- Common --}}
            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Communication</p>
            </div>
            <a href="{{ route('portal.announcements.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('portal.announcements.*') ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                Announcements
            </a>
            <a href="{{ route('portal.notifications.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('portal.notifications.*') ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                <div class="relative shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($__unread > 0)
                        <span class="absolute -top-1.5 -end-1.5 min-w-[16px] h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center px-0.5">
                            {{ $__unread > 9 ? '9+' : $__unread }}
                        </span>
                    @endif
                </div>
                Notifications
            </a>
        </nav>

    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Top bar --}}
        <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center gap-2 shrink-0">
            {{-- Mobile hamburger --}}
            <button @click="sidebarOpen = true" class="lg:hidden text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Page title --}}
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white flex-1 ms-1">@yield('heading', 'Portal')</h1>

            {{-- Dark / Light mode toggle --}}
            <button @click="dark = !dark; document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', dark ? 'dark' : 'light')"
                    class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :title="dark ? 'Switch to light mode' : 'Switch to dark mode'">
                <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>

            {{-- Locale switcher --}}
            <div x-data="{ localeOpen: false }" class="relative shrink-0">
                <button @click="localeOpen = !localeOpen"
                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                    </svg>
                    {{ strtoupper(app()->getLocale()) }}
                    <svg class="w-3 h-3 transition-transform duration-200" :class="localeOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="localeOpen" x-cloak
                     @click.outside="localeOpen = false"
                     class="absolute end-0 top-full mt-2 w-36 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden py-1">
                    @foreach(['en' => 'English', 'ar' => 'العربية'] as $code => $label)
                    <form method="POST" action="{{ route('portal.locale') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $code }}">
                        <button type="submit"
                                class="flex items-center justify-between w-full px-4 py-2 text-sm transition-colors
                                       {{ app()->getLocale() === $code
                                            ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 font-semibold'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                            @if(app()->getLocale() === $code)
                            <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            @endif
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>

            {{-- Notification bell --}}
            <a href="{{ route('portal.notifications.index') }}"
               class="relative p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($__unread > 0)
                    <span class="absolute top-1.5 end-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                @endif
            </a>

            {{-- User avatar + dropdown --}}
            <div x-data="{ profileOpen: false }" class="relative shrink-0">
                <button @click="profileOpen = !profileOpen"
                        class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-700 dark:text-blue-300 font-bold text-sm hover:ring-2 hover:ring-blue-300 dark:hover:ring-blue-600 transition-all">
                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                </button>

                <div x-show="profileOpen" x-cloak
                     @click.outside="profileOpen = false"
                     class="absolute end-0 top-full mt-2 w-52 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                            {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                    </div>
                    <div class="p-2">
                        <a href="{{ route('portal.profile') }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            My Profile
                        </a>
                        <form method="POST" action="{{ route('portal.logout') }}">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-sm text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 transition-colors">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-6">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 rounded-lg text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
