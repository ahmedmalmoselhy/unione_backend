@extends('dashboard.layouts.app')

@section('title', $academicTerm->name)
@section('heading', $academicTerm->name)

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm mb-6">
    <a href="{{ route('dashboard.academic-terms.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">Academic Terms</a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium truncate">{{ $academicTerm->name }}</span>
</nav>

{{-- Term info card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $academicTerm->name }}</h2>
            <p class="text-sm text-gray-500 mt-0.5" dir="rtl">{{ $academicTerm->name_ar }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $academicTerm->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $academicTerm->is_active ? 'Active' : 'Inactive' }}
            </span>
            @php
                $semesterStyles = [
                    'first'  => 'bg-blue-50 text-blue-700',
                    'second' => 'bg-indigo-50 text-indigo-700',
                    'summer' => 'bg-amber-50 text-amber-700',
                ];
            @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $semesterStyles[$academicTerm->semester] ?? 'bg-gray-100 text-gray-600' }}">
                {{ ucfirst($academicTerm->semester) }}
            </span>
            <a href="{{ route('dashboard.academic-terms.edit', $academicTerm) }}"
               class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                Edit Term
            </a>
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Academic Year</dt>
            <dd class="text-gray-700">{{ $academicTerm->academic_year }}/{{ $academicTerm->academic_year + 1 }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Semester Start</dt>
            <dd class="text-gray-700">{{ $academicTerm->starts_at->format('M d, Y') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Semester End</dt>
            <dd class="text-gray-700">{{ $academicTerm->ends_at->format('M d, Y') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Registration Opens</dt>
            <dd class="text-gray-700">{{ $academicTerm->registration_starts_at->format('M d, Y') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Registration Closes</dt>
            <dd class="text-gray-700">{{ $academicTerm->registration_ends_at->format('M d, Y') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Withdrawal Deadline</dt>
            <dd class="text-gray-700">{{ $academicTerm->withdrawal_deadline?->format('M d, Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Exam Period Start</dt>
            <dd class="text-gray-700">{{ $academicTerm->exam_starts_at?->format('M d, Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Exam Period End</dt>
            <dd class="text-gray-700">{{ $academicTerm->exam_ends_at?->format('M d, Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Grade Submission Deadline</dt>
            <dd class="text-gray-700">{{ $academicTerm->grade_submission_deadline?->format('M d, Y') ?? '—' }}</dd>
        </div>
    </dl>
</div>

{{-- Sections in this term --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Sections ({{ $academicTerm->sections->count() }})</h3>
    </div>

    @if($academicTerm->sections->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400">No sections in this term yet.</div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">Course</th>
                    <th class="px-5 py-3 text-start">Professor</th>
                    <th class="px-5 py-3 text-start">Capacity</th>
                    <th class="px-5 py-3 text-start">Room</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($academicTerm->sections as $section)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $section->course->code }}</span>
                            <span class="ml-2 text-gray-700">{{ $section->course->name }}</span>
                        </td>
                        <td class="px-5 py-3 text-gray-600">
                            {{ $section->professor?->user?->first_name }} {{ $section->professor?->user?->last_name }}
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $section->capacity }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $section->room ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
