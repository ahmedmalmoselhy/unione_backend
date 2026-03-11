@extends('dashboard.layouts.app')

@section('title', 'Course Ratings')
@section('heading', 'Course Ratings & Feedback')

@section('content')

<div class="mb-6">
    <p class="text-sm text-gray-500 dark:text-gray-400">Aggregated course ratings submitted by students after term completion.</p>
</div>

@if($professors->isEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-sm">No ratings submitted yet.</p>
</div>
@else
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                    <th class="px-5 py-3">Professor</th>
                    <th class="px-5 py-3">Department</th>
                    <th class="px-5 py-3 text-center">Avg Rating</th>
                    <th class="px-5 py-3 text-center">Total Ratings</th>
                    <th class="px-5 py-3 text-center">5★</th>
                    <th class="px-5 py-3 text-center">4★</th>
                    <th class="px-5 py-3 text-center">3★</th>
                    <th class="px-5 py-3 text-center">2★</th>
                    <th class="px-5 py-3 text-center">1★</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($professors as $professor)
                @php
                    $stats   = $statsMap->get($professor->id);
                    $avg     = $stats ? round((float) $stats->avg_rating, 2) : null;
                    $total   = $stats?->total_ratings ?? 0;
                    $avgColor = match(true) {
                        $avg === null      => '',
                        $avg >= 4          => 'text-green-600 dark:text-green-400',
                        $avg >= 3          => 'text-amber-500',
                        default            => 'text-red-500',
                    };
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $professor->user?->first_name }} {{ $professor->user?->last_name }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $professor->employee_number }}</p>
                    </td>
                    <td class="px-5 py-4 text-gray-600 dark:text-gray-300">
                        {{ $professor->department?->name ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        @if($avg !== null)
                        <div class="inline-flex items-center gap-1">
                            <span class="font-bold {{ $avgColor }}">{{ number_format($avg, 2) }}</span>
                            <span class="text-amber-400 text-base leading-none">★</span>
                        </div>
                        @else
                        <span class="text-gray-300 dark:text-gray-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center text-gray-700 dark:text-gray-300">
                        {{ $total ?: '—' }}
                    </td>
                    <td class="px-5 py-4 text-center text-green-600 dark:text-green-400 font-medium">
                        {{ $stats?->five_star ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-center text-blue-600 dark:text-blue-400 font-medium">
                        {{ $stats?->four_star ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-center text-gray-600 dark:text-gray-300 font-medium">
                        {{ $stats?->three_star ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-center text-amber-500 font-medium">
                        {{ $stats?->two_star ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-center text-red-500 font-medium">
                        {{ $stats?->one_star ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($professors->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
        {{ $professors->links() }}
    </div>
    @endif
</div>
@endif

@endsection
