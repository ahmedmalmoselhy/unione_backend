@extends('portal.layouts.app')

@section('title', 'My Sections')
@section('heading', 'My Sections')

@section('content')

@if($sections->isEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-sm">No sections assigned.</p>
</div>
@else
<div class="space-y-4">
    @foreach($sections as $section)
    <a href="{{ route('portal.sections.show', $section) }}"
       class="block bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $section->course?->name }}</h3>
                    <span class="text-xs font-mono text-gray-400">{{ $section->course?->code }}</span>
                    @if($section->is_active)
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 text-xs font-medium rounded-full">Active</span>
                    @else
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 text-xs font-medium rounded-full">Inactive</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $section->academicTerm?->name }}
                    @if($section->room) · Room {{ $section->room }} @endif
                    · {{ $section->course?->credit_hours }} credit hrs
                </p>
                @if(!empty($section->schedule))
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @foreach($section->schedule as $slot)
                    <span class="text-xs px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-md">
                        {{ $slot['day'] ?? '' }} {{ $slot['start_time'] ?? '' }}–{{ $slot['end_time'] ?? '' }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="text-right shrink-0">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $section->enrollments_count }}</p>
                <p class="text-xs text-gray-400">/ {{ $section->capacity }} students</p>
            </div>
        </div>
    </a>
    @endforeach
</div>
@endif

@endsection
