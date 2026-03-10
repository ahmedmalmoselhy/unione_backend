{{--
    Shared form fields for Professor create/edit.
    Variables expected:
      $professor    — Professor model (edit) or null (create)
      $departments  — Collection of academic departments with faculty
--}}

@php
    $isEdit = isset($professor) && $professor !== null;
    $user = $professor?->user;
    $ranks = [
        'lecturer'             => __('professors.rank_lecturer'),
        'assistant_professor'  => __('professors.rank_assistant_professor'),
        'associate_professor'  => __('professors.rank_associate_professor'),
        'professor'            => __('professors.rank_professor'),
    ];
    $grouped = $departments->groupBy(fn ($d) => $d->faculty?->name ?? 'Unknown');
@endphp

{{-- Section: Profile Picture --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('professors.profile_picture') }}</h3>
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
                    {{ __('professors.remove_photo') }}
                </label>
            </div>
        @endif
        <div class="flex items-center gap-3">
            <label class="cursor-pointer flex items-center gap-2 px-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ __('professors.choose_photo') }}
                <input type="file" name="avatar" id="avatar" accept="image/*" class="hidden"
                       onchange="const f=this.files[0];const p=document.getElementById('avatar-preview');if(f){p.src=URL.createObjectURL(f);p.classList.remove('hidden');document.getElementById('avatar-filename').textContent=f.name;}else{p.classList.add('hidden');document.getElementById('avatar-filename').textContent='';}">
            </label>
            <span id="avatar-filename" class="text-xs text-gray-400"></span>
            <img id="avatar-preview" src="" alt="" class="hidden h-12 w-12 object-cover rounded-full border border-gray-200">
        </div>
        @error('avatar')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ __('professors.photo_hint') }}</p>
    </div>
</div>

{{-- Section: Personal Information --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('professors.personal_information') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- National ID --}}
        <div>
            <label for="national_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('professors.national_id') }} <span class="text-red-500">*</span></label>
            <input
                id="national_id"
                type="text"
                name="national_id"
                value="{{ old('national_id', $user?->national_id) }}"
                required
                autocomplete="off"
                placeholder="e.g. 20000000000001"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('national_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('national_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('common.email') }} <span class="text-red-500">*</span></label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $user?->email) }}"
                required
                autocomplete="off"
                placeholder="e.g. professor@unione.com"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('email')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- First Name --}}
        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('professors.first_name') }} <span class="text-red-500">*</span></label>
            <input
                id="first_name"
                type="text"
                name="first_name"
                value="{{ old('first_name', $user?->first_name) }}"
                required
                autocomplete="off"
                placeholder="e.g. Ahmed"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('first_name') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('first_name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Last Name --}}
        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('professors.last_name') }} <span class="text-red-500">*</span></label>
            <input
                id="last_name"
                type="text"
                name="last_name"
                value="{{ old('last_name', $user?->last_name) }}"
                required
                autocomplete="off"
                placeholder="e.g. Farouk"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('last_name') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('last_name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                {{ __('common.password') }}
                @if($isEdit)
                    <span class="text-xs font-normal text-gray-400">({{ __('professors.password_hint') }})</span>
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
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('password')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password Confirmation --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
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
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       border-gray-300 focus:border-blue-500 focus:ring-blue-200
                       focus:outline-none focus:ring-2"
            />
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('professors.phone') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
            <input
                id="phone"
                type="text"
                name="phone"
                value="{{ old('phone', $user?->phone) }}"
                autocomplete="off"
                placeholder="e.g. +20 123 456 7890"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('phone') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('phone')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Gender --}}
        <div>
            <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('professors.gender') }} <span class="text-red-500">*</span></label>
            <select
                id="gender"
                name="gender"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('gender') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">{{ __('professors.select_gender') }}</option>
                <option value="male" {{ old('gender', $user?->gender) === 'male' ? 'selected' : '' }}>{{ __('professors.gender_male') }}</option>
                <option value="female" {{ old('gender', $user?->gender) === 'female' ? 'selected' : '' }}>{{ __('professors.gender_female') }}</option>
            </select>
            @error('gender')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Date of Birth --}}
        <div>
            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('professors.date_of_birth') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
            <input
                id="date_of_birth"
                type="date"
                name="date_of_birth"
                value="{{ old('date_of_birth', $user?->date_of_birth?->format('Y-m-d')) }}"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('date_of_birth') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('date_of_birth')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>

{{-- Section: Professor Information --}}
<div>
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('professors.professor_information') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Staff Number --}}
        <div>
            <label for="staff_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('professors.staff_number_full') }} <span class="text-red-500">*</span></label>
            <input
                id="staff_number"
                type="text"
                name="staff_number"
                value="{{ old('staff_number', $professor?->staff_number) }}"
                required
                autocomplete="off"
                placeholder="e.g. PROF-0001"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm font-mono transition-colors
                       {{ $errors->has('staff_number') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('staff_number')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Department --}}
        <div>
            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('common.department') }} <span class="text-red-500">*</span></label>
            <select
                id="department_id"
                name="department_id"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('department_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">{{ __('professors.select_department') }}</option>
                @foreach($grouped as $facultyName => $depts)
                    <optgroup label="{{ $facultyName }}">
                        @foreach($depts as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $professor?->department_id) == $dept->id ? 'selected' : '' }}>
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

        {{-- Specialization --}}
        <div>
            <label for="specialization" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('professors.specialization') }} <span class="text-red-500">*</span></label>
            <input
                id="specialization"
                type="text"
                name="specialization"
                value="{{ old('specialization', $professor?->specialization) }}"
                required
                autocomplete="off"
                placeholder="e.g. Machine Learning & Deep Learning"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('specialization') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('specialization')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Academic Rank --}}
        <div>
            <label for="academic_rank" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('professors.academic_rank') }} <span class="text-red-500">*</span></label>
            <select
                id="academic_rank"
                name="academic_rank"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('academic_rank') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">{{ __('professors.select_rank') }}</option>
                @foreach($ranks as $value => $label)
                    <option value="{{ $value }}" {{ old('academic_rank', $professor?->academic_rank) === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('academic_rank')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Office Location --}}
        <div>
            <label for="office_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('professors.office_location') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
            <input
                id="office_location"
                type="text"
                name="office_location"
                value="{{ old('office_location', $professor?->office_location) }}"
                autocomplete="off"
                placeholder="e.g. CSIT Building, Office 101"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('office_location') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('office_location')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Hired At --}}
        <div>
            <label for="hired_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('professors.hire_date') }} <span class="text-red-500">*</span></label>
            <input
                id="hired_at"
                type="date"
                name="hired_at"
                value="{{ old('hired_at', $professor?->hired_at?->format('Y-m-d')) }}"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                       {{ $errors->has('hired_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                       focus:outline-none focus:ring-2"
            />
            @error('hired_at')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

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
                <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('professors.active') }}</label>
            </div>
        @endif

    </div>
</div>
