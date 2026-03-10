<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login — UniOne Dashboard</title>
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
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Dashboard</p>
    </div>

    {{-- Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Sign in to your account</h2>

        <form method="POST" action="{{ route('dashboard.login') }}" class="space-y-5" novalidate>
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
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
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
                    class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm bg-white dark:bg-gray-700 dark:text-gray-200 transition-colors
                           focus:outline-none focus:ring-2 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800"
                />
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="flex items-center gap-2">
                <input id="remember" name="remember" type="checkbox"
                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                <label for="remember" class="text-sm text-gray-600 dark:text-gray-300">Remember me</label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium
                           py-2.5 px-4 rounded-lg text-sm transition-colors focus:outline-none focus:ring-2
                           focus:ring-blue-500 focus:ring-offset-2">
                Sign in
            </button>
        </form>

    </div>

</div>

</body>
</html>
