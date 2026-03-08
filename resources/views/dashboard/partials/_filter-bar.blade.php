{{--
    Reusable search/filter bar for dashboard index pages.
    Include this partial and pass the following:
      $action      — route URL for the form
      $search      — current search value (string|null)
      $filters     — array of filter definitions, each:
        [
          'name'        => string,         // input name
          'label'       => string,         // display label
          'options'     => array,           // ['value' => 'Label', ...]
          'value'       => string|null,     // current value
        ]
--}}

<form method="GET" action="{{ $action }}" class="mb-6">
    <div class="flex flex-wrap items-end gap-3">

        {{-- Search box --}}
        <div class="flex-1 min-w-[200px]">
            <label for="search" class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    id="search"
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Type to search…"
                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2 transition-colors"
                />
            </div>
        </div>

        {{-- Dynamic filter selects --}}
        @foreach(($filters ?? []) as $filter)
            <div class="min-w-[150px]">
                <label for="filter_{{ $filter['name'] }}" class="block text-xs font-medium text-gray-500 mb-1">{{ $filter['label'] }}</label>
                <select
                    id="filter_{{ $filter['name'] }}"
                    name="{{ $filter['name'] }}"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-200 focus:outline-none focus:ring-2 transition-colors"
                >
                    <option value="">All</option>
                    @foreach($filter['options'] as $optVal => $optLabel)
                        <option value="{{ $optVal }}" {{ ($filter['value'] ?? '') == $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach

        {{-- Buttons --}}
        <div class="flex items-center gap-2">
            <button type="submit"
                    class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium rounded-lg transition-colors">
                Filter
            </button>
            @if(($search ?? '') !== '' || collect($filters ?? [])->pluck('value')->filter()->isNotEmpty())
                <a href="{{ $action }}"
                   class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                    Clear
                </a>
            @endif
        </div>
    </div>
</form>
