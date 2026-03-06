{{--
    Shared form fields for Vice President create/edit.
    Variables expected:
      $vicePresident  — UniversityVicePresident model (edit) or null (create)
      $professors     — Collection of available Professor models (with 'user' eager-loaded)
--}}

@php $isEdit = isset($vicePresident) && $vicePresident !== null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Professor --}}
    <div class="md:col-span-2">
        <label for="professor_id" class="block text-sm font-medium text-gray-700 mb-1.5">Professor <span class="text-red-500">*</span></label>
        <select
            id="professor_id"
            name="professor_id"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('professor_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">Select professor...</option>
            @foreach($professors as $professor)
                <option value="{{ $professor->id }}" {{ old('professor_id', $vicePresident?->professor_id) == $professor->id ? 'selected' : '' }}>
                    {{ $professor->user->first_name }} {{ $professor->user->last_name }} ({{ $professor->user->email }})
                </option>
            @endforeach
        </select>
        @error('professor_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Title (English) --}}
    <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
        <input
            id="title"
            type="text"
            name="title"
            value="{{ old('title', $vicePresident?->title) }}"
            required
            autocomplete="off"
            placeholder="e.g. Vice President for Academic Affairs"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('title') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('title')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Title (Arabic) --}}
    <div>
        <label for="title_ar" class="block text-sm font-medium text-gray-700 mb-1.5">Title (Arabic) <span class="text-red-500">*</span></label>
        <input
            id="title_ar"
            type="text"
            name="title_ar"
            value="{{ old('title_ar', $vicePresident?->title_ar) }}"
            required
            dir="rtl"
            autocomplete="off"
            placeholder="مثال: نائب الرئيس للشؤون الأكاديمية"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('title_ar') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('title_ar')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Order --}}
    <div>
        <label for="order" class="block text-sm font-medium text-gray-700 mb-1.5">
            Display Order <span class="text-red-500">*</span>
        </label>
        <input
            id="order"
            type="number"
            name="order"
            value="{{ old('order', $vicePresident?->order ?? 0) }}"
            required
            min="0"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('order') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('order')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Appointed At --}}
    <div>
        <label for="appointed_at" class="block text-sm font-medium text-gray-700 mb-1.5">Appointed Date <span class="text-red-500">*</span></label>
        <input
            id="appointed_at"
            type="date"
            name="appointed_at"
            value="{{ old('appointed_at', $vicePresident?->appointed_at?->format('Y-m-d')) }}"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('appointed_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('appointed_at')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Ended At --}}
    <div>
        <label for="ended_at" class="block text-sm font-medium text-gray-700 mb-1.5">
            End Date
            <span class="text-xs font-normal text-gray-400">(optional)</span>
        </label>
        <input
            id="ended_at"
            type="date"
            name="ended_at"
            value="{{ old('ended_at', $vicePresident?->ended_at?->format('Y-m-d')) }}"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('ended_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('ended_at')
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
            {{ old('is_active', $vicePresident?->is_active ?? true) ? 'checked' : '' }}
            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        />
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>

</div>
