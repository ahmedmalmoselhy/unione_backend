@extends('portal.layouts.app')

@section('title', 'My Grades')
@section('heading', 'My Grades')

@section('content')

@if($gpa !== null)
<div class="mb-6 inline-flex items-center gap-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-5 py-3">
    <span class="text-sm text-gray-500 dark:text-gray-400">Cumulative GPA</span>
    <span class="text-2xl font-bold {{ $gpa >= 3 ? 'text-green-600 dark:text-green-400' : ($gpa >= 2 ? 'text-amber-500' : 'text-red-500') }}">
        {{ number_format($gpa, 2) }} / 4.00
    </span>
    @if($academicStanding)
    @php
        $standingConfig = match($academicStanding) {
            'good_standing' => ['label' => 'Good Standing', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],
            'probation'     => ['label' => 'Academic Probation', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
            'dismissal'     => ['label' => 'Academic Dismissal', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
            default         => ['label' => ucfirst(str_replace('_', ' ', $academicStanding)), 'class' => 'bg-gray-100 text-gray-700'],
        };
    @endphp
    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $standingConfig['class'] }}">
        {{ $standingConfig['label'] }}
    </span>
    @endif
</div>
@endif

@if($byTerm->isEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-sm">No graded courses yet.</p>
</div>
@else
<div class="space-y-6">
    @foreach($byTerm as $termName => $enrollments)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $termName }}</h3>
            @php $tGpa = $termGpas->get($termName); @endphp
            @if($tGpa && $tGpa->gpa !== null)
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                Semester GPA:
                <span class="font-bold {{ (float)$tGpa->gpa >= 3 ? 'text-green-600 dark:text-green-400' : ((float)$tGpa->gpa >= 2 ? 'text-amber-500' : 'text-red-500') }}">
                    {{ number_format($tGpa->gpa, 2) }}
                </span>
                <span class="text-gray-400">({{ $tGpa->credit_hours }} cr)</span>
            </span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Course</th>
                        <th class="px-5 py-3">Code</th>
                        <th class="px-5 py-3 text-center">Midterm</th>
                        <th class="px-5 py-3 text-center">Final</th>
                        <th class="px-5 py-3 text-center">Coursework</th>
                        <th class="px-5 py-3 text-center">Total</th>
                        <th class="px-5 py-3 text-center">Grade</th>
                        <th class="px-5 py-3 text-center">Points</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($enrollments as $enrollment)
                    @php $grade = $enrollment->grade; $course = $enrollment->section?->course; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $course?->name ?? '—' }}</td>
                        <td class="px-5 py-3 font-mono text-gray-500 dark:text-gray-400 text-xs">{{ $course?->code ?? '—' }}</td>
                        <td class="px-5 py-3 text-center text-gray-600 dark:text-gray-300">{{ $grade?->midterm ?? '—' }}</td>
                        <td class="px-5 py-3 text-center text-gray-600 dark:text-gray-300">{{ $grade?->final ?? '—' }}</td>
                        <td class="px-5 py-3 text-center text-gray-600 dark:text-gray-300">{{ $grade?->coursework ?? '—' }}</td>
                        <td class="px-5 py-3 text-center font-semibold text-gray-900 dark:text-white">{{ $grade?->total ?? '—' }}</td>
                        <td class="px-5 py-3 text-center">
                            @if($grade?->letter_grade)
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full text-sm font-bold
                                {{ in_array($grade->letter_grade, ['A+','A','A-']) ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' :
                                   (in_array($grade->letter_grade, ['B+','B','B-']) ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' :
                                   (in_array($grade->letter_grade, ['C+','C','C-']) ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' :
                                   'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300')) }}">
                                {{ $grade->letter_grade }}
                            </span>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center text-gray-600 dark:text-gray-300">{{ $grade?->grade_points ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
