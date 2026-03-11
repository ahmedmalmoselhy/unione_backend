@extends('portal.layouts.app')

@section('title', 'Course Ratings')
@section('heading', 'Course Ratings & Feedback')

@section('content')

@if(session('success'))
<div class="mb-6 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl text-sm text-green-700 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-6 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl text-sm text-red-700 dark:text-red-300">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Pending ratings --}}
@if($pending->isNotEmpty())
<div class="mb-8">
    <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Courses Awaiting Your Rating</h2>
    <div class="space-y-4">
        @foreach($pending as $enrollment)
        @php
            $section    = $enrollment->section;
            $course     = $section?->course;
            $term       = $section?->academicTerm;
            $professor  = $section?->professor;
            $pUser      = $professor?->user;
        @endphp
        <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button @click="open = !open" class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $course?->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <span class="font-mono">{{ $course?->code }}</span>
                        · {{ $term?->name }}
                        @if($pUser)· Prof. {{ $pUser->first_name }} {{ $pUser->last_name }}@endif
                    </p>
                </div>
                <span class="shrink-0 text-xs px-2.5 py-1 bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300 border border-amber-200 dark:border-amber-700 rounded-full font-medium">
                    Rate now
                </span>
            </button>
            <div x-show="open" x-cloak class="border-t border-gray-100 dark:border-gray-700 p-5">
                <form action="{{ route('portal.ratings.store') }}" method="POST" x-data="{ rating: 0 }">
                    @csrf
                    <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">

                    {{-- Star rating --}}
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Your Rating <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-1">
                            @for($star = 1; $star <= 5; $star++)
                            <button type="button" @click="rating = {{ $star }}"
                                    :class="rating >= {{ $star }} ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'"
                                    class="text-2xl hover:scale-110 transition-transform focus:outline-none">★</button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" :value="rating">
                        <p class="text-xs text-gray-400 mt-1" x-text="rating > 0 ? rating + ' / 5' : 'Click to rate'"></p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Comment (optional)</label>
                        <textarea name="comment" rows="3" placeholder="Share your experience with this course…"
                                  class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
                    </div>

                    <button type="submit" :disabled="rating === 0"
                            :class="rating === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors">
                        Submit Rating
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Already rated --}}
@if($rated->isNotEmpty())
<div>
    <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Your Previous Ratings</h2>
    <div class="space-y-3">
        @foreach($rated as $courseRating)
        @php
            $enrollment = $courseRating->enrollment;
            $section    = $enrollment?->section;
            $course     = $section?->course;
            $term       = $section?->academicTerm;
            $professor  = $section?->professor;
            $pUser      = $professor?->user;
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $course?->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <span class="font-mono">{{ $course?->code }}</span>
                        · {{ $term?->name }}
                        @if($pUser)· Prof. {{ $pUser->first_name }} {{ $pUser->last_name }}@endif
                    </p>
                    @if($courseRating->comment)
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 italic">{{ $courseRating->comment }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">Rated {{ $courseRating->rated_at?->diffForHumans() }}</p>
                </div>
                <div class="shrink-0 flex flex-col items-end">
                    <div class="flex items-center gap-0.5 text-amber-400 text-lg leading-none">
                        @for($i = 1; $i <= 5; $i++)
                        <span class="{{ $i <= $courseRating->rating ? 'opacity-100' : 'opacity-25' }}">★</span>
                        @endfor
                    </div>
                    <span class="text-xs text-gray-400 mt-1">{{ $courseRating->rating }}/5</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($pending->isEmpty() && $rated->isEmpty())
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-sm">No completed courses to rate yet.</p>
</div>
@endif

@endsection
