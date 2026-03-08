@extends('dashboard.layouts.app')

@section('title', 'Announcements')
@section('heading', 'Announcements')

@section('content')

{{-- Flash messages --}}
@if(session('success'))
    <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
@endif

{{-- Search / Filter --}}
@include('dashboard.partials._filter-bar', [
    'action'  => route('dashboard.announcements.index'),
    'search'  => request('search'),
    'filters' => [
        ['name' => 'type', 'label' => 'Type', 'value' => request('type'), 'options' => ['general' => 'General', 'academic' => 'Academic', 'administrative' => 'Administrative', 'urgent' => 'Urgent']],
        ['name' => 'visibility', 'label' => 'Visibility', 'value' => request('visibility'), 'options' => ['university' => 'University', 'faculty' => 'Faculty', 'department' => 'Department', 'section' => 'Section']],
        ['name' => 'pub_status', 'label' => 'Status', 'value' => request('pub_status'), 'options' => ['published' => 'Published', 'draft' => 'Draft', 'expired' => 'Expired']],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $announcements->total() }} {{ Str::plural('announcement', $announcements->total()) }} total</p>
    <a href="{{ route('dashboard.announcements.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Announcement
    </a>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($announcements->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400">
            No announcements yet. <a href="{{ route('dashboard.announcements.create') }}" class="text-blue-600 hover:underline">Create the first one.</a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">Title</th>
                    <th class="px-5 py-3 text-start">Type</th>
                    <th class="px-5 py-3 text-start">Visibility</th>
                    <th class="px-5 py-3 text-start">Author</th>
                    <th class="px-5 py-3 text-center">Reads</th>
                    <th class="px-5 py-3 text-start">Status</th>
                    <th class="px-5 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($announcements as $announcement)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-gray-900 truncate max-w-xs">{{ $announcement->title }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                                $typeColors = [
                                    'general'        => 'bg-gray-100 text-gray-600',
                                    'academic'       => 'bg-blue-100 text-blue-700',
                                    'administrative' => 'bg-purple-100 text-purple-700',
                                    'urgent'         => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$announcement->type] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($announcement->type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 text-xs">{{ ucfirst($announcement->visibility) }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $announcement->author?->first_name }} {{ $announcement->author?->last_name }}</td>
                        <td class="px-5 py-3.5 text-center text-gray-600">{{ $announcement->reads_count }}</td>
                        <td class="px-5 py-3.5">
                            @if($announcement->published_at === null)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Draft</span>
                            @elseif($announcement->expires_at && $announcement->expires_at->isPast())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Expired</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Published</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.announcements.show', $announcement) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    View
                                </a>
                                <a href="{{ route('dashboard.announcements.edit', $announcement) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('dashboard.announcements.destroy', $announcement) }}"
                                      onsubmit="return confirm('Delete this announcement?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($announcements->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $announcements->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
