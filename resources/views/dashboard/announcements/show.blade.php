@extends('dashboard.layouts.app')

@section('title', $announcement->title)
@section('heading', 'Announcement')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.announcements.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">Announcements</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium truncate">{{ Str::limit($announcement->title, 50) }}</span>
</nav>

{{-- Announcement card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">

    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            @php
                $typeColors = [
                    'general'        => 'bg-gray-100 text-gray-600',
                    'academic'       => 'bg-blue-100 text-blue-700',
                    'administrative' => 'bg-purple-100 text-purple-700',
                    'urgent'         => 'bg-red-100 text-red-700',
                ];
            @endphp
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$announcement->type] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($announcement->type) }}
                </span>
                @if($announcement->published_at === null)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Draft</span>
                @elseif($announcement->expires_at && $announcement->expires_at->isPast())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Expired</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Published</span>
                @endif
            </div>
            <h2 class="text-xl font-bold text-gray-900">{{ $announcement->title }}</h2>
        </div>
        <a href="{{ route('dashboard.announcements.edit', $announcement) }}"
           class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors shrink-0">
            Edit
        </a>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-4 gap-x-8 gap-y-4 text-sm mb-6">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Author</dt>
            <dd class="text-gray-700">{{ $announcement->author?->first_name }} {{ $announcement->author?->last_name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Visibility</dt>
            <dd class="text-gray-700">{{ ucfirst($announcement->visibility) }}{{ $announcement->target_id ? ' #' . $announcement->target_id : '' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Published</dt>
            <dd class="text-gray-700">{{ $announcement->published_at?->format('M d, Y h:i A') ?? 'Draft' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Expires</dt>
            <dd class="text-gray-700">{{ $announcement->expires_at?->format('M d, Y h:i A') ?? '—' }}</dd>
        </div>
    </dl>

    <div class="border-t border-gray-100 pt-5">
        <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">{{ $announcement->body }}</div>
    </div>
</div>

{{-- Read receipts --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Read By ({{ $announcement->reads->count() }})</h3>
    </div>

    @if($announcement->reads->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400">No one has read this announcement yet.</div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">User</th>
                    <th class="px-5 py-3 text-start">Read At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($announcement->reads as $read)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">{{ $read->user?->first_name }} {{ $read->user?->last_name }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $read->read_at?->format('M d, Y h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
