<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreVicePresidentRequest;
use App\Http\Requests\Dashboard\UpdateVicePresidentRequest;
use App\Models\AuditLog;
use App\Models\Professor;
use App\Models\University;
use App\Models\UniversityVicePresident;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UniversityVicePresidentController extends Controller
{
    public function create(): View
    {
        $professors = $this->availableProfessors();

        return view('dashboard.university.vice-presidents.create', compact('professors'));
    }

    public function store(StoreVicePresidentRequest $request): RedirectResponse
    {
        $vp = UniversityVicePresident::create([
            'university_id' => University::value('id'),
            'professor_id' => $request->professor_id,
            'title'        => $request->title,
            'title_ar'     => $request->title_ar,
            'order'        => $request->order,
            'is_active'    => $request->boolean('is_active'),
            'appointed_at' => $request->appointed_at,
            'ended_at'     => $request->ended_at,
        ]);

        AuditLog::record(
            action: 'created',
            auditableType: 'UniversityVicePresident',
            auditableId: $vp->id,
            description: "Added vice president: {$vp->title}",
            newValues: ['professor_id' => $vp->professor_id, 'title' => $vp->title, 'is_active' => $vp->is_active],
        );

        return redirect()->route('dashboard.university.show')
            ->with('success', 'Vice president added successfully.');
    }

    public function edit(UniversityVicePresident $vice_president): View
    {
        $professors = $this->availableProfessors($vice_president->professor_id);

        return view('dashboard.university.vice-presidents.edit', compact('vice_president', 'professors'));
    }

    public function update(UpdateVicePresidentRequest $request, UniversityVicePresident $vice_president): RedirectResponse
    {
        $oldValues = [
            'professor_id' => $vice_president->professor_id,
            'title'        => $vice_president->title,
            'is_active'    => $vice_president->is_active,
        ];

        $vice_president->update([
            'professor_id' => $request->professor_id,
            'title'        => $request->title,
            'title_ar'     => $request->title_ar,
            'order'        => $request->order,
            'is_active'    => $request->boolean('is_active'),
            'appointed_at' => $request->appointed_at,
            'ended_at'     => $request->ended_at,
        ]);

        AuditLog::record(
            action: 'updated',
            auditableType: 'UniversityVicePresident',
            auditableId: $vice_president->id,
            description: "Updated vice president: {$vice_president->title}",
            oldValues: $oldValues,
            newValues: ['professor_id' => $request->professor_id, 'title' => $request->title, 'is_active' => $request->boolean('is_active')],
        );

        return redirect()->route('dashboard.university.show')
            ->with('success', 'Vice president updated successfully.');
    }

    public function destroy(UniversityVicePresident $vice_president): RedirectResponse
    {
        $title = $vice_president->title;
        $id    = $vice_president->id;

        $vice_president->delete();

        AuditLog::record(
            action: 'deleted',
            auditableType: 'UniversityVicePresident',
            auditableId: $id,
            description: "Removed vice president: {$title}",
        );

        return redirect()->route('dashboard.university.show')
            ->with('success', 'Vice president removed successfully.');
    }

    private function availableProfessors(?int $includeProfessorId = null)
    {
        $assignedIds = UniversityVicePresident::pluck('professor_id')
            ->when($includeProfessorId, fn ($ids) => $ids->reject(fn ($id) => $id === $includeProfessorId))
            ->toArray();

        return Professor::with('user')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->whereNotIn('id', $assignedIds)
            ->get()
            ->sortBy(fn ($p) => $p->user->first_name . ' ' . $p->user->last_name);
    }
}
