@extends('dashboard.layouts.app')

@section('title', __('announcements.title'))
@section('heading', __('announcements.title'))

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
        ['name' => 'type', 'label' => __('announcements.type'), 'value' => request('type'), 'options' => [
            'general'        => __('announcements.type_general'),
            'academic'       => __('announcements.type_academic'),
            'administrative' => __('announcements.type_administrative'),
            'urgent'         => __('announcements.type_urgent'),
        ]],
        ['name' => 'visibility', 'label' => __('announcements.visibility'), 'value' => request('visibility'), 'options' => [
            'university' => __('announcements.vis_university'),
            'faculty'    => __('announcements.vis_faculty'),
            'department' => __('announcements.vis_department'),
            'section'    => __('announcements.vis_section'),
        ]],
        ['name' => 'pub_status', 'label' => __('common.status'), 'value' => request('pub_status'), 'options' => [
            'published' => __('announcements.published'),
            'draft'     => __('announcements.draft'),
            'expired'   => __('announcements.expired'),
        ]],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $announcements->total() }} {{ __('announcements.title') }}</p>
    @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
        <a href="{{ route('dashboard.announcements.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('announcements.new_announcement') }}
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    @if($announcements->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400 dark:text-gray-500">
            No announcements yet. <a href="{{ route('dashboard.announcements.create') }}" class="text-blue-600 hover:underline">{{ __('announcements.no_announcements_found') }}</a>
        </div>
    @else
        <table class="w-full text-sm dark:text-gray-200">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('announcements.title_label') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('announcements.type') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('announcements.visibility') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('announcements.author') }}</th>
                    <th class="px-5 py-3 text-center">{{ __('announcements.reads') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.status') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($announcements as $announcement)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-gray-900 dark:text-white truncate max-w-xs">{{ $announcement->title }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                                $typeColors = [
                                    'general'        => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
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
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$announcement->type] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                {{ $typeLabels[$announcement->type] ?? ucfirst($announcement->type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs">
                            @php
                                $visColors = [
                                    'university' => 'bg-blue-50 text-blue-700',
                                    'faculty'    => 'bg-indigo-50 text-indigo-700',
                                    'department' => 'bg-purple-50 text-purple-700',
                                    'section'    => 'bg-teal-50 text-teal-700',
                                ];
                                $visLabels = [
                                    'university' => __('announcements.vis_university'),
                                    'faculty'    => __('announcements.vis_faculty'),
                                    'department' => __('announcements.vis_department'),
                                    'section'    => __('announcements.vis_section'),
                                ];
                                $targetName = $targetLabels[$announcement->visibility][$announcement->target_id] ?? null;
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $visColors[$announcement->visibility] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                {{ $visLabels[$announcement->visibility] ?? ucfirst($announcement->visibility) }}
                            </span>
                            @if($targetName)
                                <p class="mt-1 text-gray-500 truncate max-w-[160px]">{{ $targetName }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">{{ $announcement->author?->first_name }} {{ $announcement->author?->last_name }}</td>
                        <td class="px-5 py-3.5 text-center text-gray-600 dark:text-gray-400">{{ $announcement->reads_count }}</td>
                        <td class="px-5 py-3.5">
                            @if($announcement->published_at === null)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">{{ __('announcements.draft') }}</span>
                            @elseif($announcement->expires_at && $announcement->expires_at->isPast())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">{{ __('announcements.expired') }}</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ __('announcements.published') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.announcements.show', $announcement) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                    {{ __('common.view') }}
                                </a>
                                @if(auth()->user()->isSystemAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
                                    <a href="{{ route('dashboard.announcements.edit', $announcement) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        {{ __('common.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.announcements.destroy', $announcement) }}"
                                          onsubmit="return confirm('{{ addslashes(__('announcements.confirm_delete')) }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                            {{ __('common.delete') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($announcements->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $announcements->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
