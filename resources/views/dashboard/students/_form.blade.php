{{--
    Shared form fields for Student create/edit.
    Variables expected:
      $student      — Student model (edit) or null (create)
      $faculties    — Collection of active faculties
      $departments  — Collection of academic departments with faculty
--}}

@php
    $isEdit = isset($student) && $student !== null;
@endphp

{{-- Section: Personal Information --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Personal Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- National ID --}}
        <div>
            <label for="national_id" class="block text-sm font-medium text-gray-700 mb-1.5">National ID <span class="text-red-500">*</span></label>
            <input
                id="national_id"
                type="text"
                name="national_id"
                value="{{ old('national_id', $student?->user?->national_id) }}"
                required
                autocomplete="off"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('national_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('national_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $student?->user?->email) }}"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('email')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- First Name --}}
        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
            <input
                id="first_name"
                type="text"
                name="first_name"
                value="{{ old('first_name', $student?->user?->first_name) }}"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('first_name') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('first_name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Last Name --}}
        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
            <input
                id="last_name"
                type="text"
                name="last_name"
                value="{{ old('last_name', $student?->user?->last_name) }}"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('last_name') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('last_name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone <span class="text-xs font-normal text-gray-400">(optional)</span></label>
            <input
                id="phone"
                type="text"
                name="phone"
                value="{{ old('phone', $student?->user?->phone) }}"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('phone') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('phone')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Gender --}}
        <div>
            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1.5">Gender <span class="text-red-500">*</span></label>
            <select
                id="gender"
                name="gender"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('gender') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">Select...</option>
                <option value="male" {{ old('gender', $student?->user?->gender) === 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ old('gender', $student?->user?->gender) === 'female' ? 'selected' : '' }}>Female</option>
            </select>
            @error('gender')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Date of Birth --}}
        <div>
            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-1.5">Date of Birth <span class="text-xs font-normal text-gray-400">(optional)</span></label>
            <input
                id="date_of_birth"
                type="date"
                name="date_of_birth"
                value="{{ old('date_of_birth', $student?->user?->date_of_birth?->format('Y-m-d')) }}"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('date_of_birth') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('date_of_birth')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Is Active (edit only) --}}
        @if($isEdit)
            <div class="flex items-center gap-3">
                <input
                    id="is_active"
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $student?->user?->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <label for="is_active" class="text-sm font-medium text-gray-700">Active Account</label>
            </div>
        @endif
    </div>
</div>

{{-- Section: Password --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
        Password
        @if($isEdit)
            <span class="text-xs font-normal text-gray-400">(leave blank to keep current)</span>
        @endif
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password @if(!$isEdit)<span class="text-red-500">*</span>@endif</label>
            <input
                id="password"
                type="password"
                name="password"
                {{ !$isEdit ? 'required' : '' }}
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('password')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2"
            />
        </div>
    </div>
</div>

{{-- Section: Academic Information --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Academic Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Student Number --}}
        <div>
            <label for="student_number" class="block text-sm font-medium text-gray-700 mb-1.5">Student Number <span class="text-red-500">*</span></label>
            <input
                id="student_number"
                type="text"
                name="student_number"
                value="{{ old('student_number', $student?->student_number) }}"
                required
                placeholder="e.g. STU-2025-0001"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('student_number') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('student_number')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Faculty --}}
        <div>
            <label for="faculty_id" class="block text-sm font-medium text-gray-700 mb-1.5">Faculty <span class="text-red-500">*</span></label>
            <select
                id="faculty_id"
                name="faculty_id"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('faculty_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">Select faculty...</option>
                @foreach($faculties as $faculty)
                    <option value="{{ $faculty->id }}" {{ (int) old('faculty_id', $student?->faculty_id) === $faculty->id ? 'selected' : '' }}>
                        {{ $faculty->name }}
                    </option>
                @endforeach
            </select>
            @error('faculty_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Department --}}
        <div>
            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1.5">Department <span class="text-xs font-normal text-gray-400">(optional for some faculties)</span></label>
            <select
                id="department_id"
                name="department_id"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('department_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">None</option>
                @foreach($departments as $dept)
                    <option
                        value="{{ $dept->id }}"
                        data-faculty="{{ $dept->faculty_id }}"
                        {{ (int) old('department_id', $student?->department_id) === $dept->id ? 'selected' : '' }}
                    >
                        {{ $dept->name }} ({{ $dept->faculty?->code }})
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Academic Year --}}
        <div>
            <label for="academic_year" class="block text-sm font-medium text-gray-700 mb-1.5">Academic Year <span class="text-red-500">*</span></label>
            <input
                id="academic_year"
                type="number"
                name="academic_year"
                value="{{ old('academic_year', $student?->academic_year ?? 1) }}"
                required
                min="1"
                max="7"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('academic_year') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('academic_year')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Semester --}}
        <div>
            <label for="semester" class="block text-sm font-medium text-gray-700 mb-1.5">Semester <span class="text-red-500">*</span></label>
            <select
                id="semester"
                name="semester"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('semester') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                @foreach(['first' => 'First', 'second' => 'Second', 'summer' => 'Summer'] as $val => $label)
                    <option value="{{ $val }}" {{ old('semester', $student?->semester ?? 'first') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('semester')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Enrollment Status --}}
        <div>
            <label for="enrollment_status" class="block text-sm font-medium text-gray-700 mb-1.5">Enrollment Status <span class="text-red-500">*</span></label>
            <select
                id="enrollment_status"
                name="enrollment_status"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('enrollment_status') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                @foreach(['active' => 'Active', 'suspended' => 'Suspended', 'graduated' => 'Graduated', 'withdrawn' => 'Withdrawn'] as $val => $label)
                    <option value="{{ $val }}" {{ old('enrollment_status', $student?->enrollment_status ?? 'active') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('enrollment_status')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- GPA --}}
        <div>
            <label for="gpa" class="block text-sm font-medium text-gray-700 mb-1.5">GPA <span class="text-xs font-normal text-gray-400">(optional, 0.00–4.00)</span></label>
            <input
                id="gpa"
                type="number"
                name="gpa"
                value="{{ old('gpa', $student?->gpa) }}"
                step="0.01"
                min="0"
                max="4"
                placeholder="e.g. 3.50"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('gpa') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('gpa')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Enrolled At --}}
        <div>
            <label for="enrolled_at" class="block text-sm font-medium text-gray-700 mb-1.5">Enrolled At <span class="text-red-500">*</span></label>
            <input
                id="enrolled_at"
                type="date"
                name="enrolled_at"
                value="{{ old('enrolled_at', $student?->enrolled_at?->format('Y-m-d')) }}"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('enrolled_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('enrolled_at')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Graduated At --}}
        <div>
            <label for="graduated_at" class="block text-sm font-medium text-gray-700 mb-1.5">Graduated At <span class="text-xs font-normal text-gray-400">(optional)</span></label>
            <input
                id="graduated_at"
                type="date"
                name="graduated_at"
                value="{{ old('graduated_at', $student?->graduated_at?->format('Y-m-d')) }}"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('graduated_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('graduated_at')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const facultySelect = document.getElementById('faculty_id');
        const deptSelect    = document.getElementById('department_id');
        const allOptions    = [...deptSelect.querySelectorAll('option[data-faculty]')];

        function filterDepartments() {
            const facultyId = facultySelect.value;
            const currentVal = deptSelect.value;

            // Hide all, then show matching
            allOptions.forEach(opt => {
                opt.hidden = facultyId && opt.dataset.faculty !== facultyId;
            });

            // Reset if current selection is now hidden
            const selected = deptSelect.querySelector(`option[value="${currentVal}"]`);
            if (selected && selected.hidden) {
                deptSelect.value = '';
            }
        }

        facultySelect.addEventListener('change', filterDepartments);
        filterDepartments(); // Run on load
    });
</script>
@endpush
