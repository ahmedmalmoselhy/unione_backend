@extends('dashboard.layouts.app')

@section('title', 'Notifications')
@section('heading', 'Notifications')

@section('content')

@if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
        {{ session('success') }}
    </div>
@endif

{{-- Actions bar --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">
        {{ $notifications->total() }} notification{{ $notifications->total() !== 1 ? 's' : '' }}
    </p>
    @if(auth()->user()->unreadNotifications()->exists())
        <form method="POST" action="{{ route('dashboard.notifications.read-all') }}">
            @csrf
            <button type="submit"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors">
                Mark all as read
            </button>
        </form>
    @endif
</div>

@if($notifications->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <p class="text-sm text-gray-400">No notifications yet.</p>
    </div>
@else
    <div class="bg-white rounded-2xl border border-gray-200 divide-y divide-gray-100 mb-4">
        @foreach($notifications as $notification)
            @php
                $data   = $notification->data;
                $isRead = ! is_null($notification->read_at);
            @endphp
            <div class="flex items-start gap-4 px-5 py-4 {{ $isRead ? '' : 'bg-blue-50/40' }}">
                {{-- Icon --}}
                <div class="shrink-0 mt-0.5">
                    @php
                        $iconClass = match($data['type'] ?? '') {
                            'enrollment_confirmed' => 'text-green-600 bg-green-100',
                            'grade_posted'         => 'text-purple-600 bg-purple-100',
                            'admin_role_assigned'  => 'text-blue-600 bg-blue-100',
                            'admin_role_revoked'   => 'text-red-600 bg-red-100',
                            default                => 'text-gray-500 bg-gray-100',
                        };
                    @endphp
                    <div class="w-8 h-8 rounded-full {{ $iconClass }} flex items-center justify-center">
                        @if(($data['type'] ?? '') === 'enrollment_confirmed')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @elseif(($data['type'] ?? '') === 'grade_posted')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        @elseif(in_array($data['type'] ?? '', ['admin_role_assigned', 'admin_role_revoked']))
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        @endif
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $data['title'] ?? 'Notification' }}</p>
                        @if(! $isRead)
                            <span class="inline-block w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 leading-snug">{{ $data['body'] ?? '' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 shrink-0">
                    @if(! $isRead)
                        <form method="POST" action="{{ route('dashboard.notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 transition-colors">
                                Mark read
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('dashboard.notifications.destroy', $notification->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    {{ $notifications->links() }}
@endif

@endsection
