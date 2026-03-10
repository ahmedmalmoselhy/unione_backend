<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Set New Password — UniOne Dashboard</title>
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

        {{-- Icon --}}
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 mx-auto mb-4">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1 text-center">Set your new password</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">Your account requires a password change before you can continue.</p>

        <form method="POST" action="{{ route('dashboard.password.update') }}" class="space-y-5" novalidate>
            @csrf
            @method('PUT')

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">New password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600
                              {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                              focus:outline-none focus:ring-2"/>
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Confirm new password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full px-3.5 py-2.5 rounded-lg border text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800 focus:outline-none focus:ring-2"/>
            </div>

            <button type="submit"
                    class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                Set Password & Continue
            </button>
        </form>

        <form method="POST" action="{{ route('dashboard.logout') }}" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-xs text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                Sign out instead
            </button>
        </form>

    </div>
</div>

</body>
</html>
