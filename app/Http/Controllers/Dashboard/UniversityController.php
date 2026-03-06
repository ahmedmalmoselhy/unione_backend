<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateUniversityRequest;
use App\Models\Professor;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UniversityController extends Controller
{
    public function show(): View
    {
        $university = University::with([
            'president.user',
            'vicePresidents.professor.user',
        ])->firstOrFail();

        return view('dashboard.university.show', compact('university'));
    }

    public function edit(): View
    {
        $university = University::firstOrFail();
        $professors = $this->activeProfessors();

        return view('dashboard.university.edit', compact('university', 'professors'));
    }

    public function update(UpdateUniversityRequest $request): RedirectResponse
    {
        $university = University::firstOrFail();

        $university->update([
            'name'           => $request->name,
            'name_ar'        => $request->name_ar,
            'address'        => $request->address,
            'established_at' => $request->established_at,
            'president_id'   => $request->president_id,
        ]);

        return redirect()->route('dashboard.university.show')
            ->with('success', 'University information updated successfully.');
    }

    private function activeProfessors()
    {
        return Professor::with('user')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->orderBy('id')
            ->get()
            ->sortBy(fn ($p) => $p->user->first_name . ' ' . $p->user->last_name);
    }
}
