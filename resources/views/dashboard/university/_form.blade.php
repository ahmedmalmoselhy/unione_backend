{{--
    Shared form fields for University edit.
    Variables expected:
      $university  — University model
      $professors  — Collection of active Professor models (with 'user' eager-loaded)
--}}

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Name (English) --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $university->name) }}"
            required
            autocomplete="off"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('name') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('name')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Name (Arabic) --}}
    <div>
        <label for="name_ar" class="block text-sm font-medium text-gray-700 mb-1.5">Name (Arabic) <span class="text-red-500">*</span></label>
        <input
            id="name_ar"
            type="text"
            name="name_ar"
            value="{{ old('name_ar', $university->name_ar) }}"
            required
            dir="rtl"
            autocomplete="off"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('name_ar') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('name_ar')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Address --}}
    <div class="md:col-span-2">
        <label for="address" class="block text-sm font-medium text-gray-700 mb-1.5">Address <span class="text-red-500">*</span></label>
        <input
            id="address"
            type="text"
            name="address"
            value="{{ old('address', $university->address) }}"
            required
            autocomplete="off"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('address') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('address')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Established At --}}
    <div>
        <label for="established_at" class="block text-sm font-medium text-gray-700 mb-1.5">
            Established Date
            <span class="text-xs font-normal text-gray-400">(optional)</span>
        </label>
        <input
            id="established_at"
            type="date"
            name="established_at"
            value="{{ old('established_at', $university->established_at?->format('Y-m-d')) }}"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('established_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('established_at')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- President --}}
    <div>
        <label for="president_id" class="block text-sm font-medium text-gray-700 mb-1.5">
            President
            <span class="text-xs font-normal text-gray-400">(optional)</span>
        </label>
        <select
            id="president_id"
            name="president_id"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('president_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">— No president assigned —</option>
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
