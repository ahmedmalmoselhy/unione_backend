@extends('portal.layouts.app')

@section('title', 'Enroll in a Course')
@section('heading', 'Available Courses — ' . $currentTerm->name)

@section('content')

<div class="mb-4">
    <a href="{{ route('portal.enrollments.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">← Back to my courses</a>
</div>

@if($sections->isEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-sm">No available sections to enroll in.</p>
</div>
@else
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                    <th class="px-5 py-3">Course</th>
                    <th class="px-5 py-3">Professor</th>
                    <th class="px-5 py-3">Room</th>
                    <th class="px-5 py-3 text-center">Seats</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($sections as $section)
                @php
                    $remaining = $section->capacity - $section->enrollments_count;
                    $full = $remaining <= 0;
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $section->course?->name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $section->course?->code }}
                            @if($section->course?->credit_hours) · {{ $section->course->credit_hours }} hrs @endif
                        </p>
                    </td>
                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300">
                        {{ $section->professor?->user?->first_name }} {{ $section->professor?->user?->last_name }}
                    </td>
                    <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $section->room ?? '—' }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="{{ $full ? 'text-red-500' : ($remaining <= 5 ? 'text-amber-500' : 'text-green-600 dark:text-green-400') }} font-medium">
                            {{ $remaining }} / {{ $section->capacity }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-end">
                        @if(!$full)
                        <form method="POST" action="{{ route('portal.enrollments.store') }}">
                            @csrf
                            <input type="hidden" name="section_id" value="{{ $section->id }}" />
                            <button type="submit"
                                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors">
                                Enroll
                            </button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400">Full</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
