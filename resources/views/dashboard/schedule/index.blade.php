@extends('dashboard.layouts.app')

@section('title', __('schedule.title'))
@section('heading', __('schedule.title'))

@section('content')

{{-- ─── Filter card ─────────────────────────────────────────────────────── --}}
<div x-data="scheduleFilters()" class="mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">

        <form method="GET" action="{{ route('dashboard.schedule.index') }}">
            <div class="flex flex-wrap items-end gap-3">

                {{-- Faculty --}}
                @if($faculties->count() > 1)
                    <div class="min-w-[180px]">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                            {{ __('common.faculty') }}
                        </label>
                        <select name="faculty_id" x-model="selectedFaculty" @change="onFacultyChange"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800 focus:outline-none focus:ring-2 transition-colors">
                            <option value="">— {{ __('schedule.select_faculty') }}</option>
                            @foreach($faculties as $f)
                                <option value="{{ $f['id'] }}" {{ request('faculty_id') == $f['id'] ? 'selected' : '' }}>
                                    {{ $f['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    {{-- Single faculty admin: hidden input --}}
                    <input type="hidden" name="faculty_id" :value="selectedFaculty">
                @endif

                {{-- Department (cascades from faculty) --}}
                <div class="min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ __('common.department') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="department_id" x-model="selectedDepartment" required
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800 focus:outline-none focus:ring-2 transition-colors">
                        <option value="">— {{ __('schedule.select_department') }}</option>
                        <template x-for="dept in filteredDepartments" :key="dept.id">
                            <option :value="dept.id"
                                    :selected="String(dept.id) === String(selectedDepartment)"
                                    x-text="dept.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Level --}}
                <div class="min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ __('schedule.level') }}
                    </label>
                    <select name="level"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800 focus:outline-none focus:ring-2 transition-colors">
                        <option value="">{{ __('schedule.all_levels') }}</option>
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>
                                {{ __('schedule.level_label', ['n' => $lvl]) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Academic term --}}
                <div class="min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ __('sections.academic_term') }}
                    </label>
                    <select name="term_id"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800 focus:outline-none focus:ring-2 transition-colors">
                        <option value="">{{ __('schedule.select_term') }}</option>
                        @foreach($terms as $t)
                            <option value="{{ $t['id'] }}"
                                    {{ request('term_id', $defaultTermId) == $t['id'] ? 'selected' : '' }}>
                                {{ $t['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-2 pb-0.5">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ __('schedule.apply') }}
                    </button>
                    @if($hasQueried)
                        <a href="{{ route('dashboard.schedule.index') }}"
                           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                            {{ __('common.clear') }}
                        </a>
                    @endif
                </div>

            </div>
        </form>

    </div>
</div>

{{-- ─── Results ─────────────────────────────────────────────────────────── --}}
@if(! $hasQueried)

    {{-- Initial prompt --}}
    <div class="flex flex-col items-center justify-center py-20 text-center text-gray-400 dark:text-gray-500">
        <svg class="w-14 h-14 mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-base font-medium text-gray-500 dark:text-gray-400">{{ __('schedule.select_dept_prompt') }}</p>
    </div>

@elseif(empty($orderedDays))

    {{-- Queried but nothing found --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 px-6 py-16 text-center">
        <svg class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('schedule.no_schedule_data') }}</p>
        @if($sectionCount > 0)
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                ({{ $sectionCount }} {{ __('sections.title') }} — {{ __('sections.schedule') }} {{ strtolower(__('common.none')) }})
            </p>
        @endif
    </div>

@else

    {{-- Section count badge --}}
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        {{ __('schedule.sections_found', ['count' => $sectionCount]) }}
    </p>

    {{-- ─── Timetable ──────────────────────────────────────────────────── --}}
    <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <table class="border-collapse text-sm" style="min-width: {{ count($orderedDays) * 200 + 110 }}px; width: 100%">

            {{-- Header row: Time | Day... --}}
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/60">
                    <th class="sticky start-0 z-10 bg-gray-50 dark:bg-gray-900/60 border-b border-e border-gray-200 dark:border-gray-700
                               px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide text-start w-28">
                        {{ __('schedule.time') }}
                    </th>
                    @foreach($orderedDays as $day)
                        <th class="border-b border-e border-gray-200 dark:border-gray-700 px-4 py-3
                                   text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide text-center last:border-e-0">
                            {{ __('schedule.day_' . $day) }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            {{-- Body rows: one per time slot --}}
            <tbody>
                @foreach($timeSlots as $i => $timeKey)
                    <tr class="{{ $i % 2 === 0 ? '' : 'bg-gray-50/50 dark:bg-gray-900/20' }}">

                        {{-- Time column --}}
                        <td class="sticky start-0 z-10 bg-white dark:bg-gray-800 border-b border-e border-gray-100 dark:border-gray-700
                                   px-4 py-3 align-middle {{ $i % 2 !== 0 ? 'bg-gray-50/50 dark:bg-gray-900/20' : '' }}">
                            @php [$slotStart, $slotEnd] = explode('–', $timeKey, 2) + ['', '']; @endphp
                            <span class="font-mono text-xs font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                {{ $slotStart }}
                            </span>
                            <span class="block font-mono text-[10px] text-gray-400 dark:text-gray-500">{{ $slotEnd }}</span>
                        </td>

                        {{-- Day columns --}}
                        @foreach($orderedDays as $day)
                            <td class="border-b border-e border-gray-100 dark:border-gray-700 px-2 py-2 align-top last:border-e-0"
                                style="min-width: 180px">
                                @if(isset($grid[$day][$timeKey]))
                                    <div class="space-y-1.5">
                                        @foreach($grid[$day][$timeKey] as $entry)
                                            @php
                                                $section = $entry['section'];
                                                $type    = $entry['type'];
                                                $isLab   = $type === 'lab';
                                            @endphp

                                            <div class="rounded-lg border px-2.5 py-2 text-xs leading-snug
                                                        {{ $isLab
                                                            ? 'border-amber-200 bg-amber-50 dark:border-amber-700/50 dark:bg-amber-900/20'
                                                            : 'border-blue-200 bg-blue-50 dark:border-blue-700/50 dark:bg-blue-900/20' }}">

                                                {{-- Course --}}
                                                <p class="font-semibold text-gray-900 dark:text-white truncate leading-tight">
                                                    <span class="font-mono text-[10px] {{ $isLab ? 'text-amber-700 dark:text-amber-400' : 'text-blue-700 dark:text-blue-400' }}">
                                                        {{ $section->course->code }}
                                                    </span>
                                                </p>
                                                <p class="text-gray-700 dark:text-gray-300 truncate mt-0.5" title="{{ $section->course->local_name }}">
                                                    {{ $section->course->local_name }}
                                                </p>

                                                {{-- Professor --}}
                                                @if($section->professor?->user)
                                                    <p class="text-gray-500 dark:text-gray-400 truncate mt-1">
                                                        {{ __('schedule.professor') }}
                                                        {{ $section->professor->user->first_name }} {{ $section->professor->user->last_name }}
                                                    </p>
                                                @endif

                                                {{-- Room + type badge --}}
                                                <div class="flex items-center justify-between mt-1.5 gap-1">
                                                    @if($section->room)
                                                        <span class="text-gray-400 dark:text-gray-500 truncate text-[10px]">
                                                            {{ __('schedule.room') }}: {{ $section->room }}
                                                        </span>
                                                    @else
                                                        <span></span>
                                                    @endif

                                                    <span class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold
                                                                 {{ $isLab
                                                                    ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400'
                                                                    : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' }}">
                                                        {{ $isLab ? __('schedule.type_lab') : __('schedule.type_lecture') }}
                                                    </span>
                                                </div>
                                            </div>

                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        @endforeach

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endif

@endsection

@push('scripts')
<script>
function scheduleFilters() {
    return {
        allDepartments: @js($allDepartments),
        selectedFaculty:    '{{ request('faculty_id', $faculties->count() === 1 ? $faculties->first()['id'] : '') }}',
        selectedDepartment: '{{ request('department_id') }}',

        get filteredDepartments() {
            if (! this.selectedFaculty) return this.allDepartments;
            return this.allDepartments.filter(
                d => String(d.faculty_id) === String(this.selectedFaculty)
            );
        },

        onFacultyChange() {
            // Reset department if it no longer belongs to the chosen faculty
            if (! this.selectedFaculty) return;
            const currentDept = this.allDepartments.find(
                d => String(d.id) === String(this.selectedDepartment)
            );
            if (currentDept && String(currentDept.faculty_id) !== String(this.selectedFaculty)) {
                this.selectedDepartment = '';
            }
        },
    };
}
</script>
@endpush
