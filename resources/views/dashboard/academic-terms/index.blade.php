@extends('dashboard.layouts.app')

@section('title', 'Academic Terms')
@section('heading', 'Academic Terms')

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

@if($errors->has('delete'))
    <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $errors->first('delete') }}
    </div>
@endif

{{-- Search / Filter --}}
@include('dashboard.partials._filter-bar', [
    'action'  => route('dashboard.academic-terms.index'),
    'search'  => request('search'),
    'filters' => [
        ['name' => 'semester', 'label' => 'Semester', 'value' => request('semester'), 'options' => ['first' => 'First', 'second' => 'Second', 'summer' => 'Summer']],
        ['name' => 'status', 'label' => 'Status', 'value' => request('status'), 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
    ],
])

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $terms->total() }} {{ Str::plural('term', $terms->total()) }} total</p>
    <a href="{{ route('dashboard.academic-terms.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Academic Term
    </a>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($terms->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400">
            No academic terms found. <a href="{{ route('dashboard.academic-terms.create') }}" class="text-blue-600 hover:underline">Create the first one.</a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">Name</th>
                    <th class="px-5 py-3 text-start">Academic Year</th>
                    <th class="px-5 py-3 text-start">Semester</th>
                    <th class="px-5 py-3 text-start">Period</th>
                    <th class="px-5 py-3 text-start">Registration</th>
                    <th class="px-5 py-3 text-start">Status</th>
                    <th class="px-5 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($terms as $term)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-gray-900">{{ $term->name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5" dir="rtl">{{ $term->name_ar }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $term->academic_year }}/{{ $term->academic_year + 1 }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $semesterStyles = [
                                    'first'  => 'bg-blue-50 text-blue-700',
                                    'second' => 'bg-indigo-50 text-indigo-700',
                                    'summer' => 'bg-amber-50 text-amber-700',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $semesterStyles[$term->semester] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($term->semester) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500">
                            {{ $term->starts_at->format('M d') }} — {{ $term->ends_at->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500">
                            {{ $term->registration_starts_at->format('M d') }} — {{ $term->registration_ends_at->format('M d') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $term->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $term->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.academic-terms.show', $term) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    View
                                </a>
                                <a href="{{ route('dashboard.academic-terms.edit', $term) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('dashboard.academic-terms.destroy', $term) }}"
                                      onsubmit="return confirm('Delete academic term \'{{ addslashes($term->name) }}\'? This action cannot be undone.')">
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

        {{-- Pagination --}}
        @if($terms->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $terms->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
