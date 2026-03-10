{{--
    Shared form fields for AcademicTerm create/edit.
    Variables expected:
      $academicTerm — AcademicTerm model (edit) or null (create)
--}}

@php
    $isEdit = isset($academicTerm) && $academicTerm !== null;
    $semesters = [
        'first'  => __('academic_terms.semester_first'),
        'second' => __('academic_terms.semester_second'),
        'summer' => __('academic_terms.semester_summer'),
    ];
@endphp

{{-- Section: Term Identity --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('academic_terms.term_identity') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Name (EN) --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.name_english') }} <span class="text-red-500">*</span></label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $academicTerm?->name) }}"
                required
                autocomplete="off"
                placeholder="e.g. First Semester 2025/2026"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('name') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Name (AR) --}}
        <div>
            <label for="name_ar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.name_arabic') }} <span class="text-red-500">*</span></label>
            <input
                id="name_ar"
                type="text"
                name="name_ar"
                value="{{ old('name_ar', $academicTerm?->name_ar) }}"
                required
                autocomplete="off"
                dir="rtl"
                placeholder="e.g. الفصل الأول 2025/2026"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('name_ar') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('name_ar')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Academic Year --}}
        <div>
            <label for="academic_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.academic_year_start') }} <span class="text-red-500">*</span></label>
            <input
                id="academic_year"
                type="number"
                name="academic_year"
                value="{{ old('academic_year', $academicTerm?->academic_year) }}"
                required
                min="2000"
                max="2099"
                placeholder="e.g. 2025"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('academic_year') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('academic_year')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Semester --}}
        <div>
            <label for="semester" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.semester') }} <span class="text-red-500">*</span></label>
            <select
                id="semester"
                name="semester"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('semester') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">{{ __('academic_terms.select_semester') }}</option>
                @foreach($semesters as $value => $label)
                    <option value="{{ $value }}" {{ old('semester', $academicTerm?->semester) === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('semester')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>

{{-- Section: Dates --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('academic_terms.semester_dates') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Starts At --}}
        <div>
            <label for="starts_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.semester_start') }} <span class="text-red-500">*</span></label>
            <input
                id="starts_at"
                type="date"
                name="starts_at"
                value="{{ old('starts_at', $academicTerm?->starts_at?->format('Y-m-d')) }}"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('starts_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('starts_at')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Ends At --}}
        <div>
            <label for="ends_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.semester_end') }} <span class="text-red-500">*</span></label>
            <input
                id="ends_at"
                type="date"
                name="ends_at"
                value="{{ old('ends_at', $academicTerm?->ends_at?->format('Y-m-d')) }}"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('ends_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('ends_at')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>

{{-- Section: Registration --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('academic_terms.registration_period') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Registration Starts At --}}
        <div>
            <label for="registration_starts_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.registration_opens') }} <span class="text-red-500">*</span></label>
            <input
                id="registration_starts_at"
                type="date"
                name="registration_starts_at"
                value="{{ old('registration_starts_at', $academicTerm?->registration_starts_at?->format('Y-m-d')) }}"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('registration_starts_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('registration_starts_at')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Registration Ends At --}}
        <div>
            <label for="registration_ends_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.registration_closes') }} <span class="text-red-500">*</span></label>
            <input
                id="registration_ends_at"
                type="date"
                name="registration_ends_at"
                value="{{ old('registration_ends_at', $academicTerm?->registration_ends_at?->format('Y-m-d')) }}"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('registration_ends_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('registration_ends_at')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Withdrawal Deadline --}}
        <div>
            <label for="withdrawal_deadline" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.withdrawal_deadline') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
            <input
                id="withdrawal_deadline"
                type="date"
                name="withdrawal_deadline"
                value="{{ old('withdrawal_deadline', $academicTerm?->withdrawal_deadline?->format('Y-m-d')) }}"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('withdrawal_deadline') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('withdrawal_deadline')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>

{{-- Section: Exams & Grading --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('academic_terms.exams_grading') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Exam Starts At --}}
        <div>
            <label for="exam_starts_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.exam_period_start') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
            <input
                id="exam_starts_at"
                type="date"
                name="exam_starts_at"
                value="{{ old('exam_starts_at', $academicTerm?->exam_starts_at?->format('Y-m-d')) }}"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('exam_starts_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('exam_starts_at')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Exam Ends At --}}
        <div>
            <label for="exam_ends_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.exam_period_end') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
            <input
                id="exam_ends_at"
                type="date"
                name="exam_ends_at"
                value="{{ old('exam_ends_at', $academicTerm?->exam_ends_at?->format('Y-m-d')) }}"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('exam_ends_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('exam_ends_at')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Grade Submission Deadline --}}
        <div>
            <label for="grade_submission_deadline" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('academic_terms.grade_submission_deadline') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
            <input
                id="grade_submission_deadline"
                type="date"
                name="grade_submission_deadline"
                value="{{ old('grade_submission_deadline', $academicTerm?->grade_submission_deadline?->format('Y-m-d')) }}"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('grade_submission_deadline') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('grade_submission_deadline')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>

{{-- Is Active (edit only) --}}
@if($isEdit)
    <div class="flex items-center gap-3">
        <input
            id="is_active"
            type="checkbox"
            name="is_active"
            value="1"
            {{ old('is_active', $academicTerm?->is_active ?? false) ? 'checked' : '' }}
            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        />
        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('academic_terms.active_term') }} <span class="text-xs font-normal text-gray-400">{{ __('academic_terms.active_term_hint') }}</span></label>
    </div>
@endif
