{{--
    Shared form fields for Announcement create/edit.
    Variables expected:
      $announcement — Announcement model (edit) or null (create)
      $faculties    — Collection of active faculties
      $departments  — Collection of active departments
      $sections     — Collection of active sections
--}}

@php
    $isEdit = isset($announcement) && $announcement !== null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Title --}}
    <div class="md:col-span-2">
        <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
        <input
            id="title"
            type="text"
            name="title"
            value="{{ old('title', $announcement?->title) }}"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('title') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('title')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Body --}}
    <div class="md:col-span-2">
        <label for="body" class="block text-sm font-medium text-gray-700 mb-1.5">Body <span class="text-red-500">*</span></label>
        <textarea
            id="body"
            name="body"
            rows="6"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('body') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >{{ old('body', $announcement?->body) }}</textarea>
        @error('body')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Type --}}
    <div>
        <label for="type" class="block text-sm font-medium text-gray-700 mb-1.5">Type <span class="text-red-500">*</span></label>
        <select
            id="type"
            name="type"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('type') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >
            @foreach(['general' => 'General', 'academic' => 'Academic', 'administrative' => 'Administrative', 'urgent' => 'Urgent'] as $val => $label)
                <option value="{{ $val }}" {{ old('type', $announcement?->type ?? 'general') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Visibility --}}
    <div>
        <label for="visibility" class="block text-sm font-medium text-gray-700 mb-1.5">Visibility <span class="text-red-500">*</span></label>
        <select
            id="visibility"
            name="visibility"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('visibility') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        >
            @foreach(['university' => 'University-wide', 'faculty' => 'Faculty', 'department' => 'Department', 'section' => 'Section'] as $val => $label)
                <option value="{{ $val }}" {{ old('visibility', $announcement?->visibility ?? 'university') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('visibility')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Target: Faculty --}}
    <div id="target-faculty" class="md:col-span-2" style="display:none;">
        <label for="target_faculty" class="block text-sm font-medium text-gray-700 mb-1.5">Target Faculty</label>
        <select
            id="target_faculty"
            name="target_id_faculty"
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2"
        >
            <option value="">Select faculty...</option>
            @foreach($faculties as $faculty)
                <option value="{{ $faculty->id }}" {{ (int) old('target_id', $announcement?->target_id) === $faculty->id && old('visibility', $announcement?->visibility) === 'faculty' ? 'selected' : '' }}>
                    {{ $faculty->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Target: Department --}}
    <div id="target-department" class="md:col-span-2" style="display:none;">
        <label for="target_department" class="block text-sm font-medium text-gray-700 mb-1.5">Target Department</label>
        <select
            id="target_department"
            name="target_id_department"
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2"
        >
            <option value="">Select department...</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ (int) old('target_id', $announcement?->target_id) === $dept->id && old('visibility', $announcement?->visibility) === 'department' ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Target: Section --}}
    <div id="target-section" class="md:col-span-2" style="display:none;">
        <label for="target_section" class="block text-sm font-medium text-gray-700 mb-1.5">Target Section</label>
        <select
            id="target_section"
            name="target_id_section"
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2"
        >
            <option value="">Select section...</option>
            @foreach($sections as $section)
                <option value="{{ $section->id }}" {{ (int) old('target_id', $announcement?->target_id) === $section->id && old('visibility', $announcement?->visibility) === 'section' ? 'selected' : '' }}>
                    {{ $section->course->code }} — {{ $section->course->name }} ({{ $section->academicTerm?->name }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- Hidden target_id that gets set by JS --}}
    <input type="hidden" name="target_id" id="target_id" value="{{ old('target_id', $announcement?->target_id) }}"/>

    @error('target_id')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror

    {{-- Published At --}}
    <div>
        <label for="published_at" class="block text-sm font-medium text-gray-700 mb-1.5">Published At <span class="text-xs font-normal text-gray-400">(leave blank for draft)</span></label>
        <input
            id="published_at"
            type="datetime-local"
            name="published_at"
            value="{{ old('published_at', $announcement?->published_at?->format('Y-m-d\TH:i')) }}"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('published_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('published_at')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Expires At --}}
    <div>
        <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1.5">Expires At <span class="text-xs font-normal text-gray-400">(optional)</span></label>
        <input
            id="expires_at"
            type="datetime-local"
            name="expires_at"
            value="{{ old('expires_at', $announcement?->expires_at?->format('Y-m-d\TH:i')) }}"
            class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                   {{ $errors->has('expires_at') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                   focus:outline-none focus:ring-2"
        />
        @error('expires_at')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const visibility   = document.getElementById('visibility');
        const targetId     = document.getElementById('target_id');
        const panels       = {
            faculty:    document.getElementById('target-faculty'),
            department: document.getElementById('target-department'),
            section:    document.getElementById('target-section'),
        };
        const selects = {
            faculty:    document.getElementById('target_faculty'),
            department: document.getElementById('target_department'),
            section:    document.getElementById('target_section'),
        };

        function toggle() {
            const val = visibility.value;

            Object.keys(panels).forEach(key => {
                panels[key].style.display = val === key ? '' : 'none';
            });

            // Sync hidden target_id
            if (val === 'university') {
                targetId.value = '';
            } else if (selects[val]) {
                targetId.value = selects[val].value;
            }
        }

        // When a target select changes, update the hidden field
        Object.keys(selects).forEach(key => {
            selects[key].addEventListener('change', () => {
                if (visibility.value === key) {
                    targetId.value = selects[key].value;
                }
            });
        });

        visibility.addEventListener('change', toggle);
        toggle(); // Init
    });
</script>
@endpush
