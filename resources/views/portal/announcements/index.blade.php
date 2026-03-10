@extends('portal.layouts.app')

@section('title', 'Announcements')
@section('heading', 'Announcements')

@section('content')

<div class="space-y-4">
    @forelse($announcements as $ann)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border {{ $readIds->has($ann->id) ? 'border-gray-200 dark:border-gray-700' : 'border-blue-200 dark:border-blue-800' }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    @if(!$readIds->has($ann->id))
                        <span class="w-2 h-2 bg-blue-500 rounded-full shrink-0"></span>
                    @endif
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                        @if($ann->type === 'urgent') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300
                        @elseif($ann->type === 'event') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300
                        @else bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                        @endif">
                        {{ ucfirst($ann->type ?? 'general') }}
                    </span>
                    <span class="text-xs text-gray-400">
                        {{ ucfirst($ann->visibility) }}
                        @if($ann->expires_at)
                            · Expires {{ \Carbon\Carbon::parse($ann->expires_at)->format('d M Y') }}
                        @endif
                    </span>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $ann->title }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 leading-relaxed">{{ $ann->body }}</p>
                <p class="text-xs text-gray-400 mt-2">
                    {{ $ann->author ? $ann->author->first_name . ' ' . $ann->author->last_name : 'Administration' }}
                    · {{ \Carbon\Carbon::parse($ann->published_at)->format('d M Y, H:i') }}
                </p>
            </div>
            @if(!$readIds->has($ann->id))
            <form method="POST" action="{{ route('portal.announcements.read', $ann->id) }}" class="shrink-0">
                @csrf
                <button type="submit" class="text-xs text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap">
                    Mark read
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
        </svg>
        <p class="text-gray-400 dark:text-gray-500 text-sm">No announcements at this time.</p>
    </div>
    @endforelse
</div>

{{ $announcements->links() }}

@endsection
