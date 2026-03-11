@extends('portal.layouts.app')

@section('title', 'Attendance — ' . $section->course?->name)
@section('heading', 'Attendance — ' . ($section->course?->code ?? 'Section'))

@section('content')

<div class="mb-4 flex items-center justify-between">
    <a href="{{ route('portal.sections.show', $section) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">← Back to section</a>
</div>

{{-- Section info strip --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Course</p>
            <p class="font-medium text-gray-900 dark:text-white">{{ $section->course?->name }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Term</p>
            <p class="text-gray-900 dark:text-white">{{ $section->academicTerm?->name }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Room</p>
            <p class="text-gray-900 dark:text-white">{{ $section->room ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Sessions</p>
            <p class="text-gray-900 dark:text-white">{{ $sessions->count() }}</p>
        </div>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl text-sm text-green-700 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

{{-- New session form --}}
<div x-data="{ open: false, students: @js($enrolledStudents->map(fn($s) => ['id' => $s->id, 'name' => ($s->user?->first_name ?? '') . ' ' . ($s->user?->last_name ?? ''), 'number' => $s->student_number])->values()) }"
     class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden">
    <button @click="open = !open"
            class="w-full px-5 py-4 flex items-center justify-between text-left">
        <span class="font-semibold text-gray-900 dark:text-white">+ Record New Session</span>
        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-cloak class="border-t border-gray-100 dark:border-gray-700 p-5">
        @if($enrolledStudents->isEmpty())
            <p class="text-sm text-gray-400">No enrolled students in this section yet.</p>
        @else
        <form action="{{ route('portal.attendance.store', $section) }}" method="POST" x-data="{ status: 'present' }">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Session Date <span class="text-red-500">*</span></label>
                    <input type="date" name="session_date" value="{{ old('session_date', now()->toDateString()) }}" required
                           class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Topic (optional)</label>
                    <input type="text" name="topic" value="{{ old('topic') }}" placeholder="e.g. Chapter 3 review"
                           class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            {{-- Bulk status selector --}}
            <div class="flex items-center gap-3 mb-4">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mark all as:</span>
                @foreach(['present','absent','late','excused'] as $s)
                <button type="button" @click="status = '{{ $s }}'"
                        :class="status === '{{ $s }}' ? 'ring-2 ring-blue-500' : ''"
                        class="px-3 py-1 text-xs font-medium rounded-full border
                               {{ $s === 'present' ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-700' :
                                  ($s === 'absent'  ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-700' :
                                  ($s === 'late'    ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-700' :
                                                     'bg-gray-50 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600')) }}">
                    {{ ucfirst($s) }}
                </button>
                @endforeach
            </div>

            {{-- Student list --}}
            <div class="space-y-2 mb-5 max-h-80 overflow-y-auto pr-1">
                <template x-for="(student, i) in students" :key="student.id">
                    <div x-data="{ studentStatus: 'present' }" x-init="$watch('status', v => studentStatus = v)"
                         class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="student.name"></p>
                            <p class="text-xs text-gray-400" x-text="student.number"></p>
                        </div>
                        <input type="hidden" :name="'records[' + i + '][student_id]'" :value="student.id">
                        <select :name="'records[' + i + '][status]'" x-model="studentStatus"
                                class="border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1 text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="excused">Excused</option>
                        </select>
                    </div>
                </template>
            </div>

            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                Save Session
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Sessions list --}}
@if($sessions->isEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-sm">No attendance sessions recorded yet.</p>
</div>
@else
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
        <h3 class="font-semibold text-gray-900 dark:text-white">Sessions</h3>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-700">
        @foreach($sessions as $session)
        <div class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
            <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ $session->session_date->format('M j, Y') }}
                    @if($session->topic)
                    <span class="text-gray-400 font-normal">— {{ $session->topic }}</span>
                    @endif
                </p>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-xs text-green-600 dark:text-green-400">{{ $session->present_count }} present</span>
                    <span class="text-xs text-amber-500">{{ $session->late_count }} late</span>
                    <span class="text-xs text-red-500">{{ $session->absent_count }} absent</span>
                    <span class="text-xs text-gray-400">/ {{ $session->total_count }} total</span>
                </div>
            </div>
            <a href="{{ route('portal.attendance.show', [$section, $session]) }}"
               class="text-xs px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                View / Edit
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
