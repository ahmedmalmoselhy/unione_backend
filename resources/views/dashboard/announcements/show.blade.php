@extends('dashboard.layouts.app')

@section('title', $announcement->title)
@section('heading', __('announcements.announcement_heading'))

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.announcements.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">{{ __('announcements.title') }}</a>
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
                $typeLabels = [
                    'general'        => __('announcements.type_general'),
                    'academic'       => __('announcements.type_academic'),
                    'administrative' => __('announcements.type_administrative'),
                    'urgent'         => __('announcements.type_urgent'),
                ];
                $visLabels = [
                    'university' => __('announcements.vis_university'),
                    'faculty'    => __('announcements.vis_faculty'),
                    'department' => __('announcements.vis_department'),
                    'section'    => __('announcements.vis_section'),
                ];
            @endphp
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$announcement->type] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $typeLabels[$announcement->type] ?? ucfirst($announcement->type) }}
                </span>
                @if($announcement->published_at === null)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">{{ __('announcements.draft') }}</span>
                @elseif($announcement->expires_at && $announcement->expires_at->isPast())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">{{ __('announcements.expired') }}</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ __('announcements.published') }}</span>
                @endif
            </div>
            <h2 class="text-xl font-bold text-gray-900">{{ $announcement->title }}</h2>
        </div>
        @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
            <a href="{{ route('dashboard.announcements.edit', $announcement) }}"
               class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors shrink-0">
                {{ __('common.edit') }}
            </a>
        @endif
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-4 gap-x-8 gap-y-4 text-sm mb-6">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('announcements.author') }}</dt>
            <dd class="text-gray-700">{{ $announcement->author?->first_name }} {{ $announcement->author?->last_name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('announcements.visibility') }}</dt>
            <dd class="text-gray-700">{{ $visLabels[$announcement->visibility] ?? ucfirst($announcement->visibility) }}{{ $announcement->target_id ? ' #' . $announcement->target_id : '' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('announcements.published') }}</dt>
            <dd class="text-gray-700">{{ $announcement->published_at?->format('M d, Y h:i A') ?? __('announcements.draft') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">{{ __('announcements.expires_at') }}</dt>
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
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ __('announcements.read_by', ['count' => $announcement->reads->count()]) }}</h3>
    </div>

    @if($announcement->reads->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400">{{ __('announcements.no_reads') }}</div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('announcements.user') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('announcements.read_at') }}</th>
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
