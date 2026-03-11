@extends('portal.layouts.app')

@section('title', 'Announcements — ' . $section->course?->name)
@section('heading', 'Section Announcements — ' . ($section->course?->code ?? 'Section'))

@section('content')

<div class="mb-4">
    <a href="{{ route('portal.sections.show', $section) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">← Back to section</a>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl text-sm text-green-700 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

{{-- Post new announcement --}}
<div x-data="{ open: false }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden">
    <button @click="open = !open" class="w-full px-5 py-4 flex items-center justify-between text-left">
        <span class="font-semibold text-gray-900 dark:text-white">+ Post New Announcement</span>
        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-cloak class="border-t border-gray-100 dark:border-gray-700 p-5">
        <form action="{{ route('portal.section-announcements.store', $section) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Message <span class="text-red-500">*</span></label>
                <textarea name="body" rows="4" required
                          class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none resize-none">{{ old('body') }}</textarea>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                Post Announcement
            </button>
        </form>
    </div>
</div>

{{-- Announcements list --}}
@if($announcements->isEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-sm">No announcements posted yet.</p>
</div>
@else
<div class="space-y-4">
    @foreach($announcements as $announcement)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 dark:text-white mb-1">{{ $announcement->title }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ $announcement->body }}</p>
                <p class="text-xs text-gray-400 mt-2">
                    Posted {{ $announcement->published_at?->diffForHumans() ?? $announcement->created_at->diffForHumans() }}
                    by {{ $announcement->author?->first_name }} {{ $announcement->author?->last_name }}
                </p>
            </div>
            <form action="{{ route('portal.section-announcements.destroy', [$section, $announcement]) }}" method="POST"
                  onsubmit="return confirm('Delete this announcement?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="shrink-0 text-xs px-2.5 py-1 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    Delete
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
