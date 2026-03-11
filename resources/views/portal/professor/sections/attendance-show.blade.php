@extends('portal.layouts.app')

@section('title', 'Attendance Session — ' . $session->session_date->format('M j, Y'))
@section('heading', 'Session: ' . $session->session_date->format('M j, Y'))

@section('content')

<div class="mb-4 flex items-center gap-4">
    <a href="{{ route('portal.attendance.index', $section) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">← Back to attendance list</a>
</div>

{{-- Session info --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Course</p>
            <p class="font-medium text-gray-900 dark:text-white">{{ $section->course?->code }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Date</p>
            <p class="text-gray-900 dark:text-white">{{ $session->session_date->format('D, M j Y') }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Topic</p>
            <p class="text-gray-900 dark:text-white">{{ $session->topic ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Students</p>
            <p class="text-gray-900 dark:text-white">{{ $records->count() }}</p>
        </div>
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl text-sm text-green-700 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

{{-- Edit attendance form --}}
@if($records->isEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-sm">No records for this session.</p>
</div>
@else
<form action="{{ route('portal.attendance.update', [$section, $session]) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-4">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
            <h3 class="font-semibold text-gray-900 dark:text-white">Attendance Records</h3>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($records as $i => $record)
            @php
                $student = $record->student;
                $su = $student?->user;
            @endphp
            <div class="flex items-center gap-4 px-5 py-3">
                <input type="hidden" name="records[{{ $i }}][record_id]" value="{{ $record->id }}">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $su?->first_name }} {{ $su?->last_name }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $student?->student_number }}</p>
                </div>
                <select name="records[{{ $i }}][status]"
                        class="border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                    @foreach(['present','absent','late','excused'] as $s)
                    <option value="{{ $s }}" @selected($record->status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <input type="text" name="records[{{ $i }}][note]" value="{{ $record->note }}" placeholder="Note (optional)"
                       class="w-40 border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1 text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            @endforeach
        </div>
    </div>

    <button type="submit"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        Save Changes
    </button>
</form>
@endif

@endsection
