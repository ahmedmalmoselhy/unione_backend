@extends('dashboard.layouts.app')

@section('title', $university->name)
@section('heading', $university->name)

@section('content')

{{-- Success flash --}}
@if(session('success'))
    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
        {{ session('success') }}
    </div>
@endif

{{-- University info card --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-8">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $university->name }}</h2>
            <p class="text-sm text-gray-400 mt-0.5" dir="rtl">{{ $university->name_ar }}</p>
        </div>
        <a href="{{ route('dashboard.university.edit') }}"
           class="shrink-0 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
            Edit University
        </a>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-5 text-sm">
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Address</dt>
            <dd class="text-gray-700">{{ $university->address }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Established</dt>
            <dd class="text-gray-700">
                {{ $university->established_at?->format('Y') ?? '—' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">President</dt>
            <dd class="text-gray-700">
                @if($university->president)
                    {{ $university->president->user->first_name }} {{ $university->president->user->last_name }}
                @else
                    <span class="text-gray-400">Not assigned</span>
                @endif
            </dd>
        </div>
    </dl>

</div>

{{-- Vice Presidents section header --}}
<div class="flex items-center justify-between mb-4">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">
        Vice Presidents
        <span class="ml-2 text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full normal-case tracking-normal">
            {{ $university->vicePresidents->count() }}
        </span>
    </h3>
    <a href="{{ route('dashboard.university.vice-presidents.create') }}"
       class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Vice President
    </a>
</div>

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($university->vicePresidents->isEmpty())
        <div class="px-6 py-10 text-center text-sm text-gray-400">
            No vice presidents assigned yet.
            <a href="{{ route('dashboard.university.vice-presidents.create') }}" class="text-blue-600 hover:underline">Add one</a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-start">#</th>
                    <th class="px-5 py-3 text-start">Professor</th>
                    <th class="px-5 py-3 text-start">Title</th>
                    <th class="px-5 py-3 text-start">Status</th>
                    <th class="px-5 py-3 text-start">Appointed</th>
                    <th class="px-5 py-3 text-start">Ended</th>
                    <th class="px-5 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($university->vicePresidents as $vp)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $vp->order }}</td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-gray-900">
                                {{ $vp->professor->user->first_name }} {{ $vp->professor->user->last_name }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $vp->professor->user->email }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-gray-700">{{ $vp->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5" dir="rtl">{{ $vp->title_ar }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $vp->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $vp->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $vp->appointed_at->format('Y-m-d') }}</td>
                        <td class="px-5 py-3.5 text-gray-600 text-xs">
                            {{ $vp->ended_at?->format('Y-m-d') ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('dashboard.university.vice-presidents.edit', $vp) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('dashboard.university.vice-presidents.destroy', $vp) }}"
                                      onsubmit="return confirm('Remove {{ addslashes($vp->professor->user->first_name . ' ' . $vp->professor->user->last_name) }} as vice president?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
