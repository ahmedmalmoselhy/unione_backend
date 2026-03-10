{{--
    Shared form fields for Course create/edit.
    Variables expected:
      $course      — Course model (edit) or null (create)
      $departments — Collection of academic departments with faculty
      $courses     — Collection of all courses for prerequisites (excludes self on edit)
--}}

@php
    $isEdit = isset($course) && $course !== null;
    $grouped = $departments->groupBy(fn ($d) => $d->faculty?->name ?? 'University');

    // Pre-build selected department IDs and is_owner map for repopulation
    $oldDepts = old('departments', []);
    if (empty($oldDepts) && $isEdit) {
        $oldDepts = $course->departments->map(fn ($d) => [
            'id'       => (string) $d->id,
            'is_owner' => (string) $d->pivot->is_owner,
        ])->toArray();
    }
    $selectedDeptIds = collect($oldDepts)->pluck('id')->map(fn ($v) => (int) $v)->toArray();
    $ownerDeptIds = collect($oldDepts)->filter(fn ($d) => !empty($d['is_owner']))->pluck('id')->map(fn ($v) => (int) $v)->toArray();

    $oldPrereqs = old('prerequisites', $isEdit ? $course->prerequisites->pluck('id')->toArray() : []);
@endphp

{{-- Section: Course Information --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('courses.course_information') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Code --}}
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('courses.code') }} <span class="text-red-500">*</span></label>
            <input
                id="code"
                type="text"
                name="code"
                value="{{ old('code', $course?->code) }}"
                required
                autocomplete="off"
                placeholder="e.g. CS101"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm font-mono transition-colors
                       {{ $errors->has('code') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('code')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Level --}}
        <div>
            <label for="level" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('courses.level') }} <span class="text-red-500">*</span></label>
            <select
                id="level"
                name="level"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('level') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">{{ __('courses.select_level') }}</option>
                @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}" {{ (int) old('level', $course?->level) === $i ? 'selected' : '' }}>{{ __('courses.level_n', ['n' => $i]) }}</option>
                @endfor
            </select>
            @error('level')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Name (EN) --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('courses.name_english') }} <span class="text-red-500">*</span></label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $course?->name) }}"
                required
                autocomplete="off"
                placeholder="e.g. Introduction to Computer Science"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('name') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Name (AR) --}}
        <div>
            <label for="name_ar" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('courses.name_arabic') }} <span class="text-red-500">*</span></label>
            <input
                id="name_ar"
                type="text"
                name="name_ar"
                value="{{ old('name_ar', $course?->name_ar) }}"
                required
                autocomplete="off"
                dir="rtl"
                placeholder="e.g. مقدمة في علوم الحاسب"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('name_ar') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('name_ar')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Credit Hours --}}
        <div>
            <label for="credit_hours" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('courses.credit_hours') }} <span class="text-red-500">*</span></label>
            <input
                id="credit_hours"
                type="number"
                name="credit_hours"
                value="{{ old('credit_hours', $course?->credit_hours) }}"
                required
                min="1"
                max="12"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('credit_hours') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('credit_hours')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Lecture Hours --}}
        <div>
            <label for="lecture_hours" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('courses.lecture_hours') }} <span class="text-red-500">*</span></label>
            <input
                id="lecture_hours"
                type="number"
                name="lecture_hours"
                value="{{ old('lecture_hours', $course?->lecture_hours) }}"
                required
                min="0"
                max="12"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('lecture_hours') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('lecture_hours')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Lab Hours --}}
        <div>
            <label for="lab_hours" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('courses.lab_hours') }} <span class="text-red-500">*</span></label>
            <input
                id="lab_hours"
                type="number"
                name="lab_hours"
                value="{{ old('lab_hours', $course?->lab_hours ?? 0) }}"
                required
                min="0"
                max="12"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('lab_hours') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('lab_hours')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description --}}
        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('courses.description') }} <span class="text-xs font-normal text-gray-400">({{ __('common.optional') }})</span></label>
            <textarea
                id="description"
                name="description"
                rows="3"
                autocomplete="off"
                placeholder="Brief course description..."
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('description') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >{{ old('description', $course?->description) }}</textarea>
            @error('description')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Is Elective --}}
        <div class="flex items-center gap-3">
            <input
                id="is_elective"
                type="checkbox"
                name="is_elective"
                value="1"
                {{ old('is_elective', $course?->is_elective ?? false) ? 'checked' : '' }}
                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <label for="is_elective" class="text-sm font-medium text-gray-700">{{ __('courses.is_elective') }}</label>
        </div>

        {{-- Is Active (edit only) --}}
        @if($isEdit)
            <div class="flex items-center gap-3">
                <input
                    id="is_active"
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $course?->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <label for="is_active" class="text-sm font-medium text-gray-700">{{ __('courses.active') }}</label>
            </div>
        @endif

    </div>
</div>

{{-- Section: Department Assignments --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('courses.department_assignments') }} <span class="text-red-500">*</span></h3>
    @error('departments')
        <p class="mb-3 text-xs text-red-600">{{ $message }}</p>
    @enderror

    <div id="dept-assignments" class="space-y-3">
        {{-- Existing rows will be rendered here, new rows via JS --}}
    </div>

    <button type="button" onclick="addDeptRow()" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('courses.add_department') }}
    </button>
</div>

{{-- Section: Prerequisites --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('courses.prerequisites') }} <span class="text-xs font-normal text-gray-400">({{ __('common.optional') }})</span></h3>

    <div id="prereq-container" class="space-y-3">
        {{-- Rows rendered via JS --}}
    </div>

    <button type="button" onclick="addPrereqRow()" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('courses.add_prerequisite') }}
    </button>
</div>

{{-- Department / prerequisite options as JS data + row logic --}}
@push('scripts')
<script>
    // Data for JS-driven selects
    const deptGroups = @json($grouped->map(fn ($depts) => $depts->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code])));
    const courseOptions = @json($courses->map(fn ($c) => ['id' => $c->id, 'code' => $c->code, 'name' => $c->name]));

    // Localized strings
    const LNG_selectDept = @json(__('courses.select_department'));
    const LNG_owner = @json(__('courses.owner'));
    const LNG_selectCourse = @json(__('courses.select_course'));

    // Pre-selected data (for repopulation)
    let deptRows = @json(collect($oldDepts)->values());
    let prereqRows = @json(collect($oldPrereqs)->values());

    const deptContainer = document.getElementById('dept-assignments');
    const prereqContainer = document.getElementById('prereq-container');

    function buildDeptOptions(selectedId) {
        let html = `<option value="">${LNG_selectDept}</option>`;
        for (const [faculty, depts] of Object.entries(deptGroups)) {
            html += `<optgroup label="${faculty}">`;
            depts.forEach(d => {
                const sel = d.id == selectedId ? 'selected' : '';
                html += `<option value="${d.id}" ${sel}>${d.name} (${d.code})</option>`;
            });
            html += '</optgroup>';
        }
        return html;
    }

    function buildPrereqOptions(selectedId) {
        let html = `<option value="">${LNG_selectCourse}</option>`;
        courseOptions.forEach(c => {
            const sel = c.id == selectedId ? 'selected' : '';
            html += `<option value="${c.id}" ${sel}>${c.code} — ${c.name}</option>`;
        });
        return html;
    }

    function addDeptRow(deptId = '', isOwner = false) {
        const idx = deptContainer.children.length;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-3';
        row.innerHTML = `
            <select name="departments[${idx}][id]" required
                class="flex-1 px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2">
                ${buildDeptOptions(deptId)}
            </select>
            <label class="flex items-center gap-1.5 text-xs text-gray-600 whitespace-nowrap">
                <input type="checkbox" name="departments[${idx}][is_owner]" value="1" ${isOwner ? 'checked' : ''}
                    class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"/>
                ${LNG_owner}
            </label>
            <button type="button" onclick="this.parentElement.remove()" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>`;
        deptContainer.appendChild(row);
    }

    function addPrereqRow(courseId = '') {
        const idx = prereqContainer.children.length;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-3';
        row.innerHTML = `
            <select name="prerequisites[]"
                class="flex-1 px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2">
                ${buildPrereqOptions(courseId)}
            </select>
            <button type="button" onclick="this.parentElement.remove()" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>`;
        prereqContainer.appendChild(row);
    }

    // Render initial rows
    document.addEventListener('DOMContentLoaded', () => {
        if (deptRows.length === 0) {
            addDeptRow(); // at least one empty row
        } else {
            deptRows.forEach(d => addDeptRow(d.id ?? d, d.is_owner == 1 || d.is_owner === true));
        }
        prereqRows.forEach(id => addPrereqRow(id));
    });
</script>
@endpush
