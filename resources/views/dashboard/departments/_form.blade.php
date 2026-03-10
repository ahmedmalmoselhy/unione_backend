{{--
    Shared form fields for Department create/edit.
    Variables expected:
      $department — Department model (edit) or null (create)
      $faculties  — Collection of all faculties
      $professors — Collection of active users with professor role for head selector
--}}

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Faculty --}}
    <div class="md:col-span-2">
        <label for="faculty_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('common.faculty') }} <span class="text-red-500">*</span></label>
        <select
            id="faculty_id"
            name="faculty_id"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('faculty_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">{{ __('departments.select_faculty') }}</option>
            @foreach($faculties as $faculty)
                <option value="{{ $faculty->id }}" {{ old('faculty_id', $department?->faculty_id ?? request('faculty_id')) == $faculty->id ? 'selected' : '' }}>
                    {{ $faculty->name }}
                </option>
            @endforeach
        </select>
        @error('faculty_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Name (English) --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('common.name') }} <span class="text-red-500">*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $department?->name) }}"
            required
            autocomplete="off"
            placeholder="e.g. Computer Science"
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
            value="{{ old('name_ar', $department?->name_ar) }}"
            required
            dir="rtl"
            autocomplete="off"
            placeholder="مثال: علوم الحاسوب"
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
            value="{{ old('code', $department?->code) }}"
            required
            autocomplete="off"
            placeholder="e.g. CS"
            maxlength="20"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm font-mono uppercase transition-colors
                   {{ $errors->has('code') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        />
        @error('code')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Type --}}
    <div>
        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('common.type') }} <span class="text-red-500">*</span></label>
        <select
            id="type"
            name="type"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('type') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">{{ __('departments.select_type') }}</option>
            <option value="academic"   {{ old('type', $department?->type) === 'academic'   ? 'selected' : '' }}>{{ __('common.academic') }}</option>
            <option value="managerial" {{ old('type', $department?->type) === 'managerial' ? 'selected' : '' }}>{{ __('common.managerial') }}</option>
        </select>
        @error('type')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Head --}}
    <div class="md:col-span-2">
        <label for="head_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('common.head') }} <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span></label>
        <select
            id="head_id"
            name="head_id"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('head_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">{{ __('departments.no_head_assigned') }}</option>
            @foreach($professors as $professor)
                <option value="{{ $professor->id }}" {{ old('head_id', $department?->head_id) == $professor->id ? 'selected' : '' }}>
                    {{ $professor->first_name }} {{ $professor->last_name }} ({{ $professor->email }})
                </option>
            @endforeach
        </select>
        @error('head_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Is Preparatory --}}
    <div class="flex items-center gap-3">
        <input
            id="is_preparatory"
            type="checkbox"
            name="is_preparatory"
            value="1"
            {{ old('is_preparatory', $department?->is_preparatory ?? false) ? 'checked' : '' }}
            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        />
        <div>
            <label for="is_preparatory" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('departments.preparatory_department') }}</label>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ __('departments.preparatory_hint') }}</p>
        </div>
    </div>

    {{-- Is Active --}}
    <div class="flex items-center gap-3">
        <input
            id="is_active"
            type="checkbox"
            name="is_active"
            value="1"
            {{ old('is_active', $department?->is_active ?? true) ? 'checked' : '' }}
            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        />
        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('departments.active_label') }}</label>
    </div>

</div>
