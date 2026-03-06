@extends('dashboard.layouts.app')

@section('title', 'Edit Faculty')
@section('heading', 'Edit Faculty')

@section('content')

<div class="max-w-2xl">

    <div class="bg-white rounded-2xl border border-gray-200 p-6">

        <form method="POST" action="{{ route('dashboard.faculties.update', $faculty) }}" class="space-y-5">
            @csrf
            @method('PUT')

            @include('dashboard.faculties._form')

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('dashboard.faculties.index') }}"
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
