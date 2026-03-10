@extends('dashboard.layouts.app')

@section('title', __('university.edit_university'))
@section('heading', __('university.edit_university'))

@section('content')

<div class="max-w-2xl">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm mb-6">
        <a href="{{ route('dashboard.university.show') }}" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">{{ __('university.title') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ __('common.edit') }}</span>
    </nav>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">

        <form method="POST" action="{{ route('dashboard.university.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            @include('dashboard.university._form', ['university' => $university, 'professors' => $professors])

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('dashboard.university.show') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    {{ __('common.save_changes') }}
                </button>
            </div>
        </form>

    </div>

</div>

@endsection
