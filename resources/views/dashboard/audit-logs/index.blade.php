@extends('dashboard.layouts.app')

@section('title', 'Audit Log')
@section('heading', 'Audit Log')

@section('content')

{{-- Filters --}}
<form method="GET" action="{{ route('dashboard.audit-logs.index') }}"
      class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 flex flex-wrap gap-3 items-end">

    <div class="flex-1 min-w-[180px]">
        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Description, user name or email…"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
    </div>

    <div class="min-w-[140px]">
        <label class="block text-xs font-medium text-gray-500 mb-1">Action</label>
        <select name="action" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All actions</option>
            @foreach(['created','updated','deleted','assigned','revoked'] as $a)
                <option value="{{ $a }}" @selected(request('action') === $a)>{{ ucfirst($a) }}</option>
            @endforeach
        </select>
    </div>

    <div class="min-w-[160px]">
        <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
        <select name="type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All types</option>
            @foreach(['AcademicTerm','Announcement','Course','Department','Employee','Enrollment','Faculty','Grade','FacultyAdmin','DepartmentAdmin','DepartmentHead','Login','Professor','Section','Student','University','UniversityVicePresident'] as $t)
                <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>

    <div class="min-w-[140px]">
        <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
    </div>

    <div class="min-w-[140px]">
        <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
    </div>

    <div class="flex gap-2">
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['search','action','type','date_from','date_to']))
            <a href="{{ route('dashboard.audit-logs.index') }}"
               class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition-colors">
                Clear
            </a>
        @endif
    </div>
</form>

{{-- Results count --}}
<p class="text-xs text-gray-400 mb-3">
    Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ number_format($logs->total()) }} entries
</p>

{{-- Log table --}}
@if($logs->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center text-sm text-gray-400">
        No audit log entries found.
    </div>
@else
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-left">
                    <th class="px-5 py-3 font-medium text-gray-500 w-40">When</th>
                    <th class="px-5 py-3 font-medium text-gray-500 w-24">Action</th>
                    <th class="px-5 py-3 font-medium text-gray-500 w-36">Type</th>
                    <th class="px-5 py-3 font-medium text-gray-500">Description</th>
                    <th class="px-5 py-3 font-medium text-gray-500 w-44">Performed by</th>
                    <th class="px-5 py-3 font-medium text-gray-500 w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($logs as $log)
                    @php
                        $actionStyles = [
                            'created'  => 'bg-green-100 text-green-700',
                            'updated'  => 'bg-blue-100 text-blue-700',
                            'deleted'  => 'bg-red-100 text-red-700',
                            'assigned' => 'bg-amber-100 text-amber-700',
                            'revoked'  => 'bg-gray-100 text-gray-600',
                        ];
                        $badgeClass = $actionStyles[$log->action] ?? 'bg-gray-100 text-gray-600';
                    @endphp
                    <tr x-data="{ open: false }" class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 text-gray-500 text-xs whitespace-nowrap">
                            {{ $log->created_at->format('d M Y') }}<br>
                            <span class="text-gray-400">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-600 text-xs font-mono">{{ $log->auditable_type }}</td>
                        <td class="px-5 py-3 text-gray-800">{{ $log->description }}</td>
                        <td class="px-5 py-3">
                            @if($log->user)
                                <p class="font-medium text-gray-900 text-xs">{{ $log->user->first_name }} {{ $log->user->last_name }}</p>
                                <p class="text-gray-400 text-xs truncate">{{ $log->user->email }}</p>
                            @else
                                <span class="text-gray-400 text-xs">System</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-end">
                            @if($log->old_values || $log->new_values)
                                <button @click="open = !open"
                                        class="text-gray-400 hover:text-gray-700 transition-colors"
                                        title="Show details">
                                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            @endif
                        </td>
                    </tr>

                    {{-- Expandable detail row --}}
                    @if($log->old_values || $log->new_values)
                        <tr x-show="open" x-cloak
                            class="bg-gray-50 border-b border-gray-100">
                            <td colspan="6" class="px-5 py-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @if($log->old_values)
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Before</p>
                                            <div class="rounded-lg bg-red-50 border border-red-100 p-3 space-y-1">
                                                @foreach($log->old_values as $key => $val)
                                                    <div class="flex items-center gap-2 text-xs">
                                                        <span class="text-gray-500 w-28 shrink-0">{{ str_replace('_', ' ', $key) }}</span>
                                                        <span class="font-mono text-red-700">{{ $val ?? '—' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if($log->new_values)
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">After</p>
                                            <div class="rounded-lg bg-green-50 border border-green-100 p-3 space-y-1">
                                                @foreach($log->new_values as $key => $val)
                                                    <div class="flex items-center gap-2 text-xs">
                                                        <span class="text-gray-500 w-28 shrink-0">{{ str_replace('_', ' ', $key) }}</span>
                                                        <span class="font-mono text-green-700">{{ $val ?? '—' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @if($log->ip_address)
                                    <p class="text-xs text-gray-400 mt-3">IP: {{ $log->ip_address }}</p>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    {{ $logs->links() }}
@endif

@endsection
