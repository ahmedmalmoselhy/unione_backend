@extends('dashboard.layouts.app')

@section('title', __('professors.edit_professor'))
@section('heading', __('professors.edit_professor'))

@section('content')

<div class="max-w-2xl">

    <div class="bg-white rounded-2xl border border-gray-200 p-6">

        <form method="POST" action="{{ route('dashboard.professors.update', $professor) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            @include('dashboard.professors._form')

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('dashboard.professors.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
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
