{{--
    Shared form fields for Enrollment create/edit.
    Variables expected:
      $enrollment    — Enrollment model (edit) or null (create)
      $students      — Collection of active students
      $sections      — Collection of active sections
      $academicTerms — Collection of academic terms
--}}

@php
    $isEdit = isset($enrollment) && $enrollment !== null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Student --}}
    <div class="md:col-span-2">
        <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1.5">Student <span class="text-red-500">*</span></label>
        <select
            id="student_id"
            name="student_id"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('student_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">Select student...</option>
            @foreach($students as $student)
                <option value="{{ $student->id }}" {{ (int) old('student_id', $enrollment?->student_id) === $student->id ? 'selected' : '' }}>
                    {{ $student->user->first_name }} {{ $student->user->last_name }} ({{ $student->student_number }})
                </option>
            @endforeach
        </select>
        @error('student_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Academic Term --}}
    <div>
        <label for="academic_term_id" class="block text-sm font-medium text-gray-700 mb-1.5">Academic Term <span class="text-red-500">*</span></label>
        <select
            id="academic_term_id"
            name="academic_term_id"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('academic_term_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">Select term...</option>
            @foreach($academicTerms as $term)
                <option
                    value="{{ $term->id }}"
                    data-term="{{ $term->id }}"
                    {{ (int) old('academic_term_id', $enrollment?->academic_term_id) === $term->id ? 'selected' : '' }}
                >
                    {{ $term->name }}{{ $term->is_active ? ' (Current)' : '' }}
                </option>
            @endforeach
        </select>
        @error('academic_term_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Section --}}
    <div>
        <label for="section_id" class="block text-sm font-medium text-gray-700 mb-1.5">Section <span class="text-red-500">*</span></label>
        <select
            id="section_id"
            name="section_id"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('section_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">Select section...</option>
            @foreach($sections as $section)
                <option
                    value="{{ $section->id }}"
                    data-term="{{ $section->academic_term_id }}"
                    {{ (int) old('section_id', $enrollment?->section_id) === $section->id ? 'selected' : '' }}
                >
                    {{ $section->course->code }} — {{ $section->course->name }}
                    ({{ $section->professor?->user?->first_name }} {{ $section->professor?->user?->last_name }})
                </option>
            @endforeach
        </select>
        @error('section_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Status --}}
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
        <select
            id="status"
            name="status"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('status') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >
            @foreach(['registered' => 'Registered', 'dropped' => 'Dropped', 'completed' => 'Completed', 'failed' => 'Failed', 'incomplete' => 'Incomplete'] as $val => $label)
                <option value="{{ $val }}" {{ old('status', $enrollment?->status ?? 'registered') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Registered At --}}
    <div>
        <label for="registered_at" class="block text-sm font-medium text-gray-700 mb-1.5">Registered At <span class="text-red-500">*</span></label>
        <input
            id="registered_at"
            type="datetime-local"
            name="registered_at"
            value="{{ old('registered_at', $enrollment?->registered_at?->format('Y-m-d\TH:i')) }}"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('registered_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('registered_at')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Dropped At --}}
    <div>
        <label for="dropped_at" class="block text-sm font-medium text-gray-700 mb-1.5">Dropped At <span class="text-xs font-normal text-gray-400">(optional)</span></label>
        <input
            id="dropped_at"
            type="datetime-local"
            name="dropped_at"
            value="{{ old('dropped_at', $enrollment?->dropped_at?->format('Y-m-d\TH:i')) }}"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('dropped_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('dropped_at')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const termSelect    = document.getElementById('academic_term_id');
        const sectionSelect = document.getElementById('section_id');
        const allOptions    = [...sectionSelect.querySelectorAll('option[data-term]')];

        function filterSections() {
            const termId     = termSelect.value;
            const currentVal = sectionSelect.value;

            allOptions.forEach(opt => {
                opt.hidden = termId && opt.dataset.term !== termId;
            });

            const selected = sectionSelect.querySelector(`option[value="${currentVal}"]`);
            if (selected && selected.hidden) {
                sectionSelect.value = '';
            }
        }

        termSelect.addEventListener('change', filterSections);
        filterSections();
    });
</script>
@endpush
