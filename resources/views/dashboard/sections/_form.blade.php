{{--
    Shared form fields for Section create/edit.
    Variables expected:
      $section       — Section model (edit) or null (create)
      $courses       — Collection of active courses
      $professors    — Collection of professors with user
      $academicTerms — Collection of academic terms
--}}

@php
    $isEdit = isset($section) && $section !== null;
    $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    $oldSchedule = old('schedule', $section?->schedule ?? []);
@endphp

{{-- Section: Assignment --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Section Assignment</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Course --}}
        <div>
            <label for="course_id" class="block text-sm font-medium text-gray-700 mb-1.5">Course <span class="text-red-500">*</span></label>
            <select
                id="course_id"
                name="course_id"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('course_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">Select course...</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ (int) old('course_id', $section?->course_id) === $course->id ? 'selected' : '' }}>
                        {{ $course->code }} — {{ $course->name }}
                    </option>
                @endforeach
            </select>
            @error('course_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Professor --}}
        <div>
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
                @foreach($professors as $prof)
                    <option value="{{ $prof->id }}" {{ (int) old('professor_id', $section?->professor_id) === $prof->id ? 'selected' : '' }}>
                        {{ $prof->user->first_name }} {{ $prof->user->last_name }} ({{ $prof->staff_number }})
                    </option>
                @endforeach
            </select>
            @error('professor_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Academic Term --}}
        <div>
            <label for="academic_term_id" class="block text-sm font-medium text-gray-700 mb-1.5">Academic Term <span class="text-red-500">*</span></label>
            <select
                id="academic_term_id"
                name="academic_term_id"
                required
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('academic_term_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            >
                <option value="">Select term...</option>
                @foreach($academicTerms as $term)
                    <option value="{{ $term->id }}" {{ (int) old('academic_term_id', $section?->academic_term_id) === $term->id ? 'selected' : '' }}>
                        {{ $term->name }}{{ $term->is_active ? ' ★' : '' }}
                    </option>
                @endforeach
            </select>
            @error('academic_term_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Capacity --}}
        <div>
            <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1.5">Capacity <span class="text-red-500">*</span></label>
            <input
                id="capacity"
                type="number"
                name="capacity"
                value="{{ old('capacity', $section?->capacity) }}"
                required
                min="1"
                max="999"
                placeholder="e.g. 40"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('capacity') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('capacity')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Room --}}
        <div>
            <label for="room" class="block text-sm font-medium text-gray-700 mb-1.5">Room <span class="text-xs font-normal text-gray-400">(optional)</span></label>
            <input
                id="room"
                type="text"
                name="room"
                value="{{ old('room', $section?->room) }}"
                autocomplete="off"
                placeholder="e.g. B-204"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                       {{ $errors->has('room') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200' }}
                       focus:outline-none focus:ring-2"
            />
            @error('room')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Is Active (edit only) --}}
        @if($isEdit)
            <div class="flex items-center gap-3">
                <input
                    id="is_active"
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $section?->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
            </div>
        @endif

    </div>
</div>

{{-- Section: Schedule --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Schedule <span class="text-xs font-normal text-gray-400">(optional)</span></h3>
    @error('schedule')
        <p class="mb-3 text-xs text-red-600">{{ $message }}</p>
    @enderror

    <div id="schedule-container" class="space-y-3">
        {{-- Rendered via JS --}}
    </div>

    <button type="button" onclick="addScheduleRow()" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Time Slot
    </button>
</div>

@push('scripts')
<script>
    const daysOfWeek = @json($days);
    const dayLabels = { sunday:'Sun', monday:'Mon', tuesday:'Tue', wednesday:'Wed', thursday:'Thu', friday:'Fri', saturday:'Sat' };
    let scheduleRows = @json(collect($oldSchedule)->values());
    const container = document.getElementById('schedule-container');

    function addScheduleRow(data = {}) {
        const idx = container.children.length;
        const row = document.createElement('div');
        row.className = 'flex flex-wrap items-center gap-3 bg-gray-50 rounded-lg p-3';

        let dayOpts = '<option value="">Day</option>';
        daysOfWeek.forEach(d => {
            const sel = d === (data.day || '') ? 'selected' : '';
            dayOpts += `<option value="${d}" ${sel}>${dayLabels[d] || d}</option>`;
        });

        const typeOpts = `
            <option value="lecture" ${(data.type || 'lecture') === 'lecture' ? 'selected' : ''}>Lecture</option>
            <option value="lab" ${data.type === 'lab' ? 'selected' : ''}>Lab</option>`;

        row.innerHTML = `
            <select name="schedule[${idx}][day]" required class="px-2.5 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2">
                ${dayOpts}
            </select>
            <input type="time" name="schedule[${idx}][start_time]" value="${data.start_time || ''}" required placeholder="Start"
                class="px-2.5 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2"/>
            <span class="text-gray-400 text-sm">to</span>
            <input type="time" name="schedule[${idx}][end_time]" value="${data.end_time || ''}" required placeholder="End"
                class="px-2.5 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2"/>
            <select name="schedule[${idx}][type]" class="px-2.5 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2">
                ${typeOpts}
            </select>
            <button type="button" onclick="this.parentElement.remove()" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>`;
        container.appendChild(row);
    }

    document.addEventListener('DOMContentLoaded', () => {
        scheduleRows.forEach(s => addScheduleRow(s));
    });
</script>
@endpush
