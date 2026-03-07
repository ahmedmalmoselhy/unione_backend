{{--
    Shared form fields for Employee create/edit.
    Variables expected:
      $employee    — Employee model (edit) or null (create)
      $departments — Collection of managerial departments with faculty
--}}

@php
    $isEdit = isset($employee) && $employee !== null;
    $user = $employee?->user;
    $employmentTypes = [
        'full_time' => 'Full Time',
        'part_time' => 'Part Time',
        'contract'  => 'Contract',
    ];
    $grouped = $departments->groupBy(fn ($d) => $d->faculty?->name ?? 'University');
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
                value="{{ old('national_id', $user?->national_id) }}"
                required
                autocomplete="off"
                placeholder="e.g. 30000000000001"
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
                value="{{ old('email', $user?->email) }}"
                required
                autocomplete="off"
                placeholder="e.g. employee@unione.com"
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
                value="{{ old('first_name', $user?->first_name) }}"
                required
                autocomplete="off"
                placeholder="e.g. Magda"
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
                value="{{ old('last_name', $user?->last_name) }}"
                required
                autocomplete="off"
                placeholder="e.g. Osman"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('last_name') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('last_name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                Password
                @if($isEdit)
                    <span class="text-xs font-normal text-gray-400">(leave blank to keep current)</span>
                @else
                    <span class="text-red-500">*</span>
                @endif
            </label>
            <input
                id="password"
                type="password"
                name="password"
                {{ $isEdit ? '' : 'required' }}
                autocomplete="new-password"
                placeholder="{{ $isEdit ? '••••••••' : 'Min. 8 characters' }}"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('password')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password Confirmation --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                Confirm Password
                @if(!$isEdit)
                    <span class="text-red-500">*</span>
                @endif
            </label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                {{ $isEdit ? '' : 'required' }}
                autocomplete="new-password"
                placeholder="{{ $isEdit ? '••••••••' : 'Repeat password' }}"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       border-gray-300 focus:border-blue-500 focus:ring-blue-200
                       focus:outline-none focus:ring-2"
            />
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone <span class="text-xs font-normal text-gray-400">(optional)</span></label>
            <input
                id="phone"
                type="text"
                name="phone"
                value="{{ old('phone', $user?->phone) }}"
                autocomplete="off"
                placeholder="e.g. +20 123 456 7890"
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
                <option value="">Select gender...</option>
                <option value="male" {{ old('gender', $user?->gender) === 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ old('gender', $user?->gender) === 'female' ? 'selected' : '' }}>Female</option>
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
                value="{{ old('date_of_birth', $user?->date_of_birth?->format('Y-m-d')) }}"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('date_of_birth') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('date_of_birth')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>

{{-- Section: Employment Information --}}
<div>
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Employment Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Staff Number --}}
        <div>
            <label for="staff_number" class="block text-sm font-medium text-gray-700 mb-1.5">Staff Number <span class="text-red-500">*</span></label>
            <input
                id="staff_number"
                type="text"
                name="staff_number"
                value="{{ old('staff_number', $employee?->staff_number) }}"
                required
                autocomplete="off"
                placeholder="e.g. EMP-0001"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm font-mono transition-colors
                       {{ $errors->has('staff_number') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('staff_number')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Department --}}
        <div>
            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1.5">Department <span class="text-red-500">*</span></label>
            <select
                id="department_id"
                name="department_id"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('department_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">Select department...</option>
                @foreach($grouped as $facultyName => $depts)
                    <optgroup label="{{ $facultyName }}">
                        @foreach($depts as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $employee?->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('department_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Job Title --}}
        <div>
            <label for="job_title" class="block text-sm font-medium text-gray-700 mb-1.5">Job Title <span class="text-red-500">*</span></label>
            <input
                id="job_title"
                type="text"
                name="job_title"
                value="{{ old('job_title', $employee?->job_title) }}"
                required
                autocomplete="off"
                placeholder="e.g. HR Manager"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('job_title') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('job_title')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Employment Type --}}
        <div>
            <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-1.5">Employment Type <span class="text-red-500">*</span></label>
            <select
                id="employment_type"
                name="employment_type"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('employment_type') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">Select type...</option>
                @foreach($employmentTypes as $value => $label)
                    <option value="{{ $value }}" {{ old('employment_type', $employee?->employment_type) === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('employment_type')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Salary --}}
        <div>
            <label for="salary" class="block text-sm font-medium text-gray-700 mb-1.5">Salary <span class="text-xs font-normal text-gray-400">(optional)</span></label>
            <input
                id="salary"
                type="number"
                name="salary"
                value="{{ old('salary', $employee?->salary) }}"
                min="0"
                step="0.01"
                autocomplete="off"
                placeholder="e.g. 12000.00"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('salary') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('salary')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Hired At --}}
        <div>
            <label for="hired_at" class="block text-sm font-medium text-gray-700 mb-1.5">Hire Date <span class="text-red-500">*</span></label>
            <input
                id="hired_at"
                type="date"
                name="hired_at"
                value="{{ old('hired_at', $employee?->hired_at?->format('Y-m-d')) }}"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('hired_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('hired_at')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Terminated At (edit only) --}}
        @if($isEdit)
            <div>
                <label for="terminated_at" class="block text-sm font-medium text-gray-700 mb-1.5">Termination Date <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                <input
                    id="terminated_at"
                    type="date"
                    name="terminated_at"
                    value="{{ old('terminated_at', $employee?->terminated_at?->format('Y-m-d')) }}"
                    class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                           {{ $errors->has('terminated_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                           focus:outline-none focus:ring-2"
                />
                @error('terminated_at')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        {{-- Is Active (edit only) --}}
        @if($isEdit)
            <div class="md:col-span-2 flex items-center gap-3">
                <input
                    id="is_active"
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $user?->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
            </div>
        @endif

    </div>
</div>
