{{--
    Shared form fields for Grade create/edit.
    Variables expected:
      $grade       — Grade model (edit) or null (create)
      $enrollments — Collection of ungraded enrollments (create only)
--}}

@php
    $isEdit = isset($grade) && $grade !== null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Enrollment --}}
    <div class="md:col-span-2">
        <label for="enrollment_id" class="block text-sm font-medium text-gray-700 mb-1.5">Enrollment <span class="text-red-500">*</span></label>

        @if($isEdit)
            <input type="hidden" name="enrollment_id" value="{{ $grade->enrollment_id }}"/>
            <div class="w-full px-3.5 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-700">
                <span class="font-mono bg-gray-200 text-gray-700 px-2 py-0.5 rounded text-xs">{{ $grade->enrollment?->section?->course?->code }}</span>
                <span class="ml-1.5">{{ $grade->enrollment?->section?->course?->name }}</span>
                <span class="mx-1.5 text-gray-400">—</span>
                {{ $grade->enrollment?->student?->user?->first_name }} {{ $grade->enrollment?->student?->user?->last_name }}
                <span class="text-xs text-gray-400 ml-1">({{ $grade->enrollment?->student?->student_number }})</span>
            </div>
        @else
            <select
                id="enrollment_id"
                name="enrollment_id"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('enrollment_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">Select enrollment...</option>
                @foreach($enrollments as $enrollment)
                    <option value="{{ $enrollment->id }}" {{ (int) old('enrollment_id') === $enrollment->id ? 'selected' : '' }}>
                        {{ $enrollment->student?->user?->first_name }} {{ $enrollment->student?->user?->last_name }}
                        ({{ $enrollment->student?->student_number }})
                        — {{ $enrollment->section?->course?->code }} {{ $enrollment->section?->course?->name }}
                        · {{ $enrollment->academicTerm?->name ?? '' }}
                    </option>
                @endforeach
            </select>
            @error('enrollment_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        @endif
    </div>

    {{-- Midterm --}}
    <div>
        <label for="midterm" class="block text-sm font-medium text-gray-700 mb-1.5">Midterm <span class="text-xs font-normal text-gray-400">(0–100)</span></label>
        <input
            id="midterm"
            type="number"
            name="midterm"
            value="{{ old('midterm', $grade?->midterm) }}"
            step="0.01"
            min="0"
            max="100"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('midterm') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('midterm')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Coursework --}}
    <div>
        <label for="coursework" class="block text-sm font-medium text-gray-700 mb-1.5">Coursework <span class="text-xs font-normal text-gray-400">(0–100)</span></label>
        <input
            id="coursework"
            type="number"
            name="coursework"
            value="{{ old('coursework', $grade?->coursework) }}"
            step="0.01"
            min="0"
            max="100"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('coursework') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('coursework')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Final --}}
    <div>
        <label for="final" class="block text-sm font-medium text-gray-700 mb-1.5">Final <span class="text-xs font-normal text-gray-400">(0–100)</span></label>
        <input
            id="final"
            type="number"
            name="final"
            value="{{ old('final', $grade?->final) }}"
            step="0.01"
            min="0"
            max="100"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('final') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('final')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Total --}}
    <div>
        <label for="total" class="block text-sm font-medium text-gray-700 mb-1.5">Total <span class="text-xs font-normal text-gray-400">(0–100)</span></label>
        <input
            id="total"
            type="number"
            name="total"
            value="{{ old('total', $grade?->total) }}"
            step="0.01"
            min="0"
            max="100"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('total') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('total')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Letter Grade --}}
    <div>
        <label for="letter_grade" class="block text-sm font-medium text-gray-700 mb-1.5">Letter Grade <span class="text-xs font-normal text-gray-400">(e.g. A, B+)</span></label>
        <input
            id="letter_grade"
            type="text"
            name="letter_grade"
            value="{{ old('letter_grade', $grade?->letter_grade) }}"
            maxlength="3"
            placeholder="A+"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('letter_grade') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('letter_grade')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Grade Points --}}
    <div>
        <label for="grade_points" class="block text-sm font-medium text-gray-700 mb-1.5">Grade Points <span class="text-xs font-normal text-gray-400">(0.00–4.00)</span></label>
        <input
            id="grade_points"
            type="number"
            name="grade_points"
            value="{{ old('grade_points', $grade?->grade_points) }}"
            step="0.01"
            min="0"
            max="4"
            placeholder="3.70"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('grade_points') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('grade_points')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const midterm    = document.getElementById('midterm');
        const coursework = document.getElementById('coursework');
        const finalExam  = document.getElementById('final');
        const total      = document.getElementById('total');
        const letterGrade = document.getElementById('letter_grade');
        const gradePoints = document.getElementById('grade_points');

        function autoCalc() {
            const m = parseFloat(midterm.value) || 0;
            const c = parseFloat(coursework.value) || 0;
            const f = parseFloat(finalExam.value) || 0;

            if (midterm.value || coursework.value || finalExam.value) {
                const t = Math.min(m + c + f, 100);
                total.value = t.toFixed(2);

                // Auto letter grade & points
                let letter, points;
                if (t >= 90)      { letter = 'A+'; points = 4.00; }
                else if (t >= 85) { letter = 'A';  points = 3.75; }
                else if (t >= 80) { letter = 'B+'; points = 3.50; }
                else if (t >= 75) { letter = 'B';  points = 3.00; }
                else if (t >= 70) { letter = 'C+'; points = 2.50; }
                else if (t >= 65) { letter = 'C';  points = 2.00; }
                else if (t >= 60) { letter = 'D+'; points = 1.50; }
                else if (t >= 50) { letter = 'D';  points = 1.00; }
                else              { letter = 'F';  points = 0.00; }

                letterGrade.value = letter;
                gradePoints.value = points.toFixed(2);
            }
        }

        [midterm, coursework, finalExam].forEach(el => el.addEventListener('input', autoCalc));
    });
</script>
@endpush
