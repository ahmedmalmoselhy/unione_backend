{{--
    Shared form fields for University edit.
    Variables expected:
      $university  — University model
      $professors  — Collection of active Professor models (with 'user' eager-loaded)
--}}

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Logo Upload --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('university.university_logo') }}</label>

        {{-- Current logo preview --}}
        @if($university->logo_path)
            <div id="current-logo-wrapper" class="mb-3 flex items-center gap-4">
                <img src="{{ Storage::disk('public')->url($university->logo_path) }}"
                     alt="University logo"
                     class="h-16 w-16 object-contain rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-1">
                <label class="flex items-center gap-1.5 text-sm text-red-600 cursor-pointer">
                    <input type="checkbox" name="remove_logo" value="1"
                           id="remove_logo"
                           class="rounded border-gray-300 text-red-600 focus:ring-red-400"
                           onchange="document.getElementById('current-logo-wrapper').style.opacity = this.checked ? '0.4' : '1'">
                    {{ __('common.remove_current_logo') }}
                </label>
            </div>
        @endif

        {{-- New logo input with live preview --}}
        <div class="flex items-center gap-3">
            <label class="cursor-pointer flex items-center gap-2 px-3.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ __('common.choose_image') }}
                <input type="file" name="logo" id="logo" accept="image/*" class="hidden"
                       onchange="
                           const file = this.files[0];
                           const preview = document.getElementById('logo-preview');
                           if (file) {
                               preview.src = URL.createObjectURL(file);
                               preview.classList.remove('hidden');
                               document.getElementById('logo-filename').textContent = file.name;
                           } else {
                               preview.classList.add('hidden');
                               document.getElementById('logo-filename').textContent = '';
                           }
                       ">
            </label>
            <span id="logo-filename" class="text-xs text-gray-400"></span>
            <img id="logo-preview" src="" alt="" class="hidden h-12 w-12 object-contain rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-1">
        </div>
        @error('logo')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ __('common.logo_hint') }}</p>
    </div>

    {{-- Name (English) --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('university.name_label') }} <span class="text-red-500">*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $university->name) }}"
            required
            autocomplete="off"
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
        <label for="name_ar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('university.name_ar_label') }} <span class="text-red-500">*</span></label>
        <input
            id="name_ar"
            type="text"
            name="name_ar"
            value="{{ old('name_ar', $university->name_ar) }}"
            required
            dir="rtl"
            autocomplete="off"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('name_ar') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        />
        @error('name_ar')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Address --}}
    <div class="md:col-span-2">
        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('university.address_label') }} <span class="text-red-500">*</span></label>
        <input
            id="address"
            type="text"
            name="address"
            value="{{ old('address', $university->address) }}"
            required
            autocomplete="off"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('address') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        />
        @error('address')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Phone --}}
    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            {{ __('university.phone_label') }}
            <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span>
        </label>
        <input
            id="phone"
            type="text"
            name="phone"
            value="{{ old('phone', $university->phone) }}"
            autocomplete="off"
            placeholder="e.g. +1 (555) 000-0000"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('phone') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        />
        @error('phone')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Email --}}
    <div>
        <label for="contact_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            {{ __('university.contact_email_label') }}
            <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span>
        </label>
        <input
            id="contact_email"
            type="email"
            name="email"
            value="{{ old('email', $university->email) }}"
            autocomplete="off"
            placeholder="e.g. info@university.edu"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        />
        @error('email')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Website --}}
    <div class="md:col-span-2">
        <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            {{ __('university.website_label') }}
            <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span>
        </label>
        <input
            id="website"
            type="url"
            name="website"
            value="{{ old('website', $university->website) }}"
            autocomplete="off"
            placeholder="https://www.university.edu"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('website') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        />
        @error('website')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Established At --}}
    <div>
        <label for="established_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            {{ __('university.established_date') }}
            <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span>
        </label>
        <input
            id="established_at"
            type="date"
            name="established_at"
            value="{{ old('established_at', $university->established_at?->format('Y-m-d')) }}"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('established_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        />
        @error('established_at')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- President --}}
    <div>
        <label for="president_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            {{ __('university.president_label') }}
            <span class="text-xs font-normal text-gray-400">{{ __('common.optional') }}</span>
        </label>
        <select
            id="president_id"
            name="president_id"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors bg-white dark:bg-gray-700 dark:text-gray-200
                   {{ $errors->has('president_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-200 dark:focus:ring-blue-800' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">{{ __('university.no_president_assigned') }}</option>
            @foreach($professors as $professor)
                <option value="{{ $professor->id }}" {{ old('president_id', $university->president_id) == $professor->id ? 'selected' : '' }}>
                    {{ $professor->user->first_name }} {{ $professor->user->last_name }} ({{ $professor->user->email }})
                </option>
            @endforeach
        </select>
        @error('president_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>
