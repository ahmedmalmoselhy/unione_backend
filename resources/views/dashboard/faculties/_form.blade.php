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

    {{-- Name (English) --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $faculty?->name) }}"
            required
            autocomplete="off"
            placeholder="e.g. Faculty of Engineering"
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
            value="{{ old('name_ar', $faculty?->name_ar) }}"
            required
            dir="rtl"
            autocomplete="off"
            placeholder="مثال: كلية الهندسة"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('name_ar') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('name_ar')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Code --}}
    <div>
        <label for="code" class="block text-sm font-medium text-gray-700 mb-1.5">Code <span class="text-red-500">*</span></label>
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
                   {{ $errors->has('code') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('code')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Enrollment Type --}}
    <div>
        <label for="enrollment_type" class="block text-sm font-medium text-gray-700 mb-1.5">Enrollment Type <span class="text-red-500">*</span></label>
        <select
            id="enrollment_type"
            name="enrollment_type"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('enrollment_type') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">Select type...</option>
            @foreach($enrollmentTypes as $value => $description)
                <option value="{{ $value }}" {{ old('enrollment_type', $faculty?->enrollment_type) === $value ? 'selected' : '' }}>
                    {{ ucfirst($value) }}
                </option>
            @endforeach
        </select>
        @php $selectedType = old('enrollment_type', $faculty?->enrollment_type); @endphp
        @if($selectedType && isset($enrollmentTypes[$selectedType]))
            <p class="mt-1.5 text-xs text-gray-500">{{ $enrollmentTypes[$selectedType] }}</p>
        @endif
        @error('enrollment_type')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Dean --}}
    <div class="md:col-span-2">
        <label for="dean_id" class="block text-sm font-medium text-gray-700 mb-1.5">Dean <span class="text-xs font-normal text-gray-400">(optional)</span></label>
        <select
            id="dean_id"
            name="dean_id"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('dean_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >
            <option value="">— No dean assigned —</option>
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
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>

</div>
