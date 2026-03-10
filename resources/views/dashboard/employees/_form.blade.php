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
        'full_time' => __('employees.type_full_time'),
        'part_time' => __('employees.type_part_time'),
        'contract'  => __('employees.type_contract'),
    ];
    $grouped = $departments->groupBy(fn ($d) => $d->faculty?->name ?? 'University');
@endphp

{{-- Section: Profile Picture --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('employees.profile_picture') }}</h3>
    <div>
        @if($isEdit && $user?->avatar_path)
            <div id="current-avatar-wrapper" class="mb-3 flex items-center gap-4">
                <img src="{{ Storage::disk('public')->url($user->avatar_path) }}"
                     alt="Profile photo"
                     class="h-16 w-16 object-cover rounded-full border border-gray-200">
                <label class="flex items-center gap-1.5 text-sm text-red-600 cursor-pointer">
                    <input type="checkbox" name="remove_avatar" value="1"
                           class="rounded border-gray-300 text-red-600 focus:ring-red-400"
                           onchange="document.getElementById('current-avatar-wrapper').style.opacity = this.checked ? '0.4' : '1'">
                    {{ __('employees.remove_photo') }}
                </label>
            </div>
        @endif
        <div class="flex items-center gap-3">
            <label class="cursor-pointer flex items-center gap-2 px-3.5 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ __('employees.choose_photo') }}
                <input type="file" name="avatar" id="avatar" accept="image/*" class="hidden"
                       onchange="const f=this.files[0];const p=document.getElementById('avatar-preview');if(f){p.src=URL.createObjectURL(f);p.classList.remove('hidden');document.getElementById('avatar-filename').textContent=f.name;}else{p.classList.add('hidden');document.getElementById('avatar-filename').textContent='';}">
            </label>
            <span id="avatar-filename" class="text-xs text-gray-400"></span>
            <img id="avatar-preview" src="" alt="" class="hidden h-12 w-12 object-cover rounded-full border border-gray-200">
        </div>
        @error('avatar')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-1.5 text-xs text-gray-400">{{ __('employees.photo_hint') }}</p>
    </div>
</div>

{{-- Section: Personal Information --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('employees.personal_information') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- National ID --}}
        <div>
            <label for="national_id" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.national_id') }} <span class="text-red-500">*</span></label>
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
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('common.email') }} <span class="text-red-500">*</span></label>
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
            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.first_name') }} <span class="text-red-500">*</span></label>
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
            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.last_name') }} <span class="text-red-500">*</span></label>
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
                {{ __('common.password') }}
                @if($isEdit)
                    <span class="text-xs font-normal text-gray-400">({{ __('employees.password_hint') }})</span>
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
                {{ __('common.confirm_password') }}
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
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.phone') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
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
            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.gender') }} <span class="text-red-500">*</span></label>
            <select
                id="gender"
                name="gender"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('gender') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">{{ __('employees.select_gender') }}</option>
                <option value="male" {{ old('gender', $user?->gender) === 'male' ? 'selected' : '' }}>{{ __('employees.gender_male') }}</option>
                <option value="female" {{ old('gender', $user?->gender) === 'female' ? 'selected' : '' }}>{{ __('employees.gender_female') }}</option>
            </select>
            @error('gender')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Date of Birth --}}
        <div>
            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.date_of_birth') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
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
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('employees.employment_information') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Staff Number --}}
        <div>
            <label for="staff_number" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.staff_number_full') }} <span class="text-red-500">*</span></label>
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
            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('common.department') }} <span class="text-red-500">*</span></label>
            <select
                id="department_id"
                name="department_id"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('department_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">{{ __('employees.select_department') }}</option>
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
            <label for="job_title" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.job_title') }} <span class="text-red-500">*</span></label>
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
            <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.employment_type') }} <span class="text-red-500">*</span></label>
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
            <label for="salary" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.salary') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
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
            <label for="hired_at" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.hire_date') }} <span class="text-red-500">*</span></label>
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
                <label for="terminated_at" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('employees.termination_date') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
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
                <label for="is_active" class="text-sm font-medium text-gray-700">{{ __('employees.active') }}</label>
            </div>
        @endif

    </div>
</div>
