{{--
    Form fields for Academic Department create/edit.
    Variables expected:
      $department — Department model (edit) or null (create)
      $faculties  — Collection of all faculties
      $professors — Collection of active users with professor role
--}}

<input type="hidden" name="type" value="academic">

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Faculty --}}
    <div class="md:col-span-2">
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
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $department?->name) }}"
            required
            autocomplete="off"
            placeholder="e.g. Computer Science"
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
            value="{{ old('name_ar', $department?->name_ar) }}"
            required
            dir="rtl"
            autocomplete="off"
            placeholder="مثال: علوم الحاسوب"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('name_ar') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('name_ar')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Code --}}
    <div class="md:col-span-2">
        <label for="code" class="block text-sm font-medium text-gray-700 mb-1.5">Code <span class="text-red-500">*</span></label>
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
                   {{ $errors->has('code') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('code')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Head (professor) --}}
    <div class="md:col-span-2">
        <label for="head_id" class="block text-sm font-medium text-gray-700 mb-1.5">Head <span class="text-xs font-normal text-gray-400">(optional — must be a professor)</span></label>
        <select
            id="head_id"
            name="head_id"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('head_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">— No head assigned —</option>
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
    <div class="md:col-span-2 flex items-start gap-3">
        <input
            id="is_preparatory"
            type="checkbox"
            name="is_preparatory"
            value="1"
            {{ old('is_preparatory', $department?->is_preparatory ?? false) ? 'checked' : '' }}
            class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        />
        <div>
            <label for="is_preparatory" class="text-sm font-medium text-gray-700">Preparatory department</label>
            <p class="text-xs text-gray-400 mt-0.5">For deferred-enrollment faculties — students start here before choosing a department</p>
        </div>
    </div>

    {{-- Is Active --}}
    <div class="md:col-span-2 flex items-center gap-3">
        <input
            id="is_active"
            type="checkbox"
            name="is_active"
            value="1"
            {{ old('is_active', $department?->is_active ?? true) ? 'checked' : '' }}
            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        />
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>

</div>
