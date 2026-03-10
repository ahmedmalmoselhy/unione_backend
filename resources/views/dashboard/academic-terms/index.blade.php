@extends('dashboard.layouts.app')

@section('title', __('academic_terms.title'))
@section('heading', __('academic_terms.title'))

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
        ['name' => 'semester', 'label' => __('academic_terms.semester'), 'value' => request('semester'), 'options' => ['first' => __('academic_terms.semester_first_short'), 'second' => __('academic_terms.semester_second_short'), 'summer' => __('academic_terms.semester_summer_short')]],
        ['name' => 'status', 'label' => __('common.status'), 'value' => request('status'), 'options' => ['active' => __('common.active'), 'inactive' => __('common.inactive')]],
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
        {{ __('academic_terms.new_term') }}
    </a>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($terms->isEmpty())
        <div class="px-6 py-16 text-center text-sm text-gray-400">
            No academic terms found. <a href="{{ route('dashboard.academic-terms.create') }}" class="text-blue-600 hover:underline">{{ __('academic_terms.create_first') }}</a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">{{ __('common.name') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('academic_terms.academic_year') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('academic_terms.semester') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('academic_terms.period') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('academic_terms.registration') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('common.status') }}</th>
                    <th class="px-5 py-3 text-end">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($terms as $term)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-gray-900">{{ $term->local_name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5" dir="auto">{{ app()->getLocale() === 'ar' ? $term->name : $term->name_ar }}</div>
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
                                {{ __('academic_terms.semester_' . $term->semester . '_short') }}
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
                                {{ $term->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.academic-terms.show', $term) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                                    {{ __('common.view') }}
                                </a>
                                <a href="{{ route('dashboard.academic-terms.edit', $term) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                    {{ __('common.edit') }}
                                </a>
                                <form method="POST" action="{{ route('dashboard.academic-terms.destroy', $term) }}"
                                      onsubmit="return confirm('{{ __('academic_terms.confirm_delete', ['name' => addslashes($term->local_name)]) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                        {{ __('common.delete') }}
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
