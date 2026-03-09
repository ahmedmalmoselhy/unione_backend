<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateUniversityRequest;
use App\Models\AuditLog;
use App\Models\Professor;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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

        $oldValues = [
            'name'         => $university->name,
            'name_ar'      => $university->name_ar,
            'address'      => $university->address,
            'president_id' => $university->president_id,
        ];

        $logoPath = $university->logo_path;

        if ($request->boolean('remove_logo') && $logoPath) {
            Storage::disk('public')->delete($logoPath);
            $logoPath = null;
        }

        if ($request->hasFile('logo')) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $request->file('logo')->store('university', 'public');
        }

        $university->update([
            'name'           => $request->name,
            'name_ar'        => $request->name_ar,
            'address'        => $request->address,
            'established_at' => $request->established_at,
            'president_id'   => $request->president_id,
            'logo_path'      => $logoPath,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'website'        => $request->website,
        ]);

        AuditLog::record(
            action: 'updated',
            auditableType: 'University',
            auditableId: $university->id,
            description: "Updated university information",
            oldValues: $oldValues,
            newValues: ['name' => $request->name, 'name_ar' => $request->name_ar, 'address' => $request->address, 'president_id' => $request->president_id],
        );

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
