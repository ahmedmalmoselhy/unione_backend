@extends('portal.layouts.app')

@section('title', 'Notifications')
@section('heading', 'Notifications')

@section('content')

<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $notifications->total() }} total</p>
    @if($notifications->isNotEmpty())
    <form method="POST" action="{{ route('portal.notifications.read-all') }}">
        @csrf
        <button type="submit" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
            Mark all as read
        </button>
    </form>
    @endif
</div>

<div class="space-y-3">
    @forelse($notifications as $notification)
    @php
        $data = $notification->data;
        $isRead = !is_null($notification->read_at);
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-2xl border {{ $isRead ? 'border-gray-200 dark:border-gray-700' : 'border-blue-200 dark:border-blue-800' }} p-4 flex items-start gap-4">
        <div class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center
            {{ $isRead ? 'bg-gray-100 dark:bg-gray-700 text-gray-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white">
                {{ $data['message'] ?? $data['title'] ?? 'Notification' }}
            </p>
            @if(!empty($data['body']) || !empty($data['description']))
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $data['body'] ?? $data['description'] }}</p>
            @endif
            <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if(!$isRead)
            <form method="POST" action="{{ route('portal.notifications.read', $notification->id) }}">
                @csrf
                <button type="submit" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Read</button>
            </form>
            @endif
            <form method="POST" action="{{ route('portal.notifications.destroy', $notification->id) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <p class="text-gray-400 dark:text-gray-500 text-sm">No notifications yet.</p>
    </div>
    @endforelse
</div>

<div class="mt-4">{{ $notifications->links() }}</div>

@endsection
