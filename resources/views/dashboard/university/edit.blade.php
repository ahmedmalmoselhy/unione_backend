@extends('dashboard.layouts.app')

@section('title', 'Edit University')
@section('heading', 'Edit University')

@section('content')

<div class="max-w-2xl">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm mb-6">
        <a href="{{ route('dashboard.university.show') }}" class="text-gray-400 hover:text-gray-700 transition-colors">University</a>
        <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">Edit</span>
    </nav>

    <div class="bg-white rounded-2xl border border-gray-200 p-6">

        <form method="POST" action="{{ route('dashboard.university.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            @include('dashboard.university._form', ['university' => $university, 'professors' => $professors])

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('dashboard.university.show') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Save Changes
                </button>
            </div>
        </form>

    </div>

</div>

@endsection
