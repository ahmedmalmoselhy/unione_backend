{{--
    Shared form fields for Faculty create/edit.
    Variables expected:
      $faculty    — Faculty model (edit) or null (create)
      $professors — Collection of active users with professor role for dean selector
--}}

@php
    $isEdit = isset($faculty) && $faculty !== null;
    $enrollmentTypes = [
        'immediate' => 'Immediate — students are assigned to a department upon enrollment',
        'deferred'  => 'Deferred — students choose a department after enrollment',
        'none'      => 'None — this faculty has no departments',
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Logo --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('faculties.faculty_logo') }}</label>
        @if($isEdit && $faculty?->logo_path)
            <div id="current-logo-wrapper" class="mb-3 flex items-center gap-4">
                <img src="{{ Storage::disk('public')->url($faculty->logo_path) }}"
                     alt="Faculty logo"
                     class="h-14 w-14 object-contain rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-1">
                <label class="flex items-center gap-1.5 text-sm text-red-600 cursor-pointer">
                    <input type="checkbox" name="remove_logo" value="1"
                           class="rounded border-gray-300 text-red-600 focus:ring-red-400"
                           onchange="document.getElementById('current-logo-wrapper').style.opacity = this.checked ? '0.4' : '1'">
                    {{ __('common.remove_current_logo') }}
                </label>
            </div>
        @endif
        <div class="flex items-center gap-3">
            <label class="cursor-pointer flex items-center gap-2 px-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ __('common.choose_logo') }}
                <input type="file" name="logo" id="logo" accept="image/*" class="hidden"
                       onchange="const f=this.files[0];const p=document.getElementById('logo-preview');if(f){p.src=URL.createObjectURL(f);p.classList.remove('hidden');document.getElementById('logo-filename').textContent=f.name;}else{p.classList.add('hidden');document.getElementById('logo-filename').textContent='';}">
            </label>
            <span id="logo-filename" class="text-xs text-gray-400"></span>
            <img id="logo-preview" src="" alt="" class="hidden h-12 w-12 object-contain rounded-lg border border-gray-200">
        </div>
        @error('logo')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ __('common.logo_hint') }}</p>
    </div>

    {{-- Name (English) --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('common.name') }} <span class="text-red-500">*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $faculty?->name) }}"
            required
            autocomplete="off"
            placeholder="e.g. Faculty of Engineering"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('name') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        />
        @error('name')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Name (Arabic) --}}
    <div>
        <label for="name_ar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('common.name_ar') }} <span class="text-red-500">*</span></label>
        <input
            id="name_ar"
            type="text"
            name="name_ar"
            value="{{ old('name_ar', $faculty?->name_ar) }}"
            required
            dir="rtl"
            autocomplete="off"
            placeholder="مثال: كلية الهندسة"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('name_ar') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        />
        @error('name_ar')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Code --}}
    <div>
        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('common.code') }} <span class="text-red-500">*</span></label>
        <input
            id="code"
            type="text"
            name="code"
            value="{{ old('code', $faculty?->code) }}"
            required
            autocomplete="off"
            placeholder="e.g. ENG"
            maxlength="20"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm font-mono uppercase transition-colors
                   {{ $errors->has('code') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        />
        @error('code')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Enrollment Type --}}
    <div>
        <label for="enrollment_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('common.enrollment_type') }} <span class="text-red-500">*</span></label>
        <select
            id="enrollment_type"
            name="enrollment_type"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('enrollment_type') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">{{ __('faculties.select_type') }}</option>
            @foreach($enrollmentTypes as $value => $description)
                <option value="{{ $value }}" {{ old('enrollment_type', $faculty?->enrollment_type) === $value ? 'selected' : '' }}>
                    {{ __('faculties.enrollment_type_' . $value) }}
                </option>
            @endforeach
        </select>
        @php $selectedType = old('enrollment_type', $faculty?->enrollment_type); @endphp
        @if($selectedType && isset($enrollmentTypes[$selectedType]))
            <p class="mt-1.5 text-xs text-gray-500">{{ __('faculties.enrollment_type_hint_' . $selectedType) }}</p>
        @endif
        @error('enrollment_type')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Dean --}}
    <div class="md:col-span-2">
        <label for="dean_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('common.dean') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
        <select
            id="dean_id"
            name="dean_id"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('dean_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">{{ __('faculties.no_dean_assigned') }}</option>
            @foreach($professors as $professor)
                <option value="{{ $professor->id }}" {{ old('dean_id', $faculty?->dean_id) == $professor->id ? 'selected' : '' }}>
                    {{ $professor->first_name }} {{ $professor->last_name }} ({{ $professor->email }})
                </option>
            @endforeach
        </select>
        @error('dean_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Is Active --}}
    <div class="md:col-span-2 flex items-center gap-3">
        <input
            id="is_active"
            type="checkbox"
            name="is_active"
            value="1"
            {{ old('is_active', $faculty?->is_active ?? true) ? 'checked' : '' }}
            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        />
        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('faculties.active_label') }}</label>
    </div>

</div>
