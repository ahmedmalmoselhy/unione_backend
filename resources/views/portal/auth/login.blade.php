<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In — UniOne</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function(){
            var t=localStorage.getItem('theme');
            if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-950 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-sm">

    {{-- Brand --}}
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">UniOne</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Student &amp; Staff Portal</p>
    </div>

    {{-- Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Sign in to your account</h2>

        <form method="POST" action="{{ route('portal.login.post') }}" class="space-y-5" novalidate>
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Email address
                </label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    autofocus
                    class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200 dark:placeholder-gray-400
                           {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                           focus:outline-none focus:ring-2"
                />
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Password
                </label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                           {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                           focus:outline-none focus:ring-2"
                />
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                    <span class="text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                </label>
                <a href="{{ route('dashboard.login') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                    Dashboard login →
                </a>
            </div>

            <button type="submit"
                    class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Sign in
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-400 dark:text-gray-500 mt-6">
        UniOne University Portal &copy; {{ date('Y') }}
    </p>
</div>

</body>
</html>
