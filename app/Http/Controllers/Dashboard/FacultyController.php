<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreFacultyRequest;
use App\Http\Requests\Dashboard\UpdateFacultyRequest;
use App\Models\AuditLog;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FacultyController extends Controller
{
    public function index(Request $request): View
    {
        $faculties = Faculty::with('dean')
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->whereIlike('name', '%' . $request->search . '%')
                  ->orWhereIlike('name_ar', '%' . $request->search . '%')
                  ->orWhereIlike('code', '%' . $request->search . '%');
            }))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->when($request->filled('enrollment_type'), fn ($q) => $q->where('enrollment_type', $request->enrollment_type))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.faculties.index', compact('faculties'));
    }

    public function show(Faculty $faculty): View
    {
        $faculty->load(['dean', 'departments' => fn ($q) => $q->with('head')->orderBy('name')]);

        return view('dashboard.faculties.show', compact('faculty'));
    }

    public function create(): View
    {
        $professors = $this->activeProfessors();

        return view('dashboard.faculties.create', compact('professors'));
    }

    public function store(StoreFacultyRequest $request): RedirectResponse
    {
        $faculty = Faculty::create([
            'name'            => $request->name,
            'name_ar'         => $request->name_ar,
            'code'            => strtoupper($request->code),
            'logo_path'       => $request->hasFile('logo')
                ? $request->file('logo')->store('logos/faculties', 'public')
                : null,
            'enrollment_type' => $request->enrollment_type,
            'dean_id'         => $request->dean_id,
            'is_active'       => $request->boolean('is_active'),
        ]);

        AuditLog::record(
            action: 'created',
            auditableType: 'Faculty',
            auditableId: $faculty->id,
            description: "Created faculty {$faculty->name}",
            newValues: ['name' => $faculty->name, 'code' => $faculty->code, 'enrollment_type' => $faculty->enrollment_type],
        );

        return redirect()->route('dashboard.faculties.index')
            ->with('success', 'Faculty created successfully.');
    }

    public function edit(Faculty $faculty): View
    {
        $professors = $this->activeProfessors();

        return view('dashboard.faculties.edit', compact('faculty', 'professors'));
    }

    public function update(UpdateFacultyRequest $request, Faculty $faculty): RedirectResponse
    {
        $oldValues = [
            'name'            => $faculty->name,
            'name_ar'         => $faculty->name_ar,
            'code'            => $faculty->code,
            'enrollment_type' => $faculty->enrollment_type,
            'is_active'       => $faculty->is_active,
        ];

        $logoPath = $faculty->logo_path;

        if ($request->boolean('remove_logo') && $logoPath) {
            Storage::disk('public')->delete($logoPath);
            $logoPath = null;
        }

        if ($request->hasFile('logo')) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $request->file('logo')->store('logos/faculties', 'public');
        }

        $faculty->update([
            'name'            => $request->name,
            'name_ar'         => $request->name_ar,
            'code'            => strtoupper($request->code),
            'logo_path'       => $logoPath,
            'enrollment_type' => $request->enrollment_type,
            'dean_id'         => $request->dean_id,
            'is_active'       => $request->boolean('is_active'),
        ]);

        AuditLog::record(
            action: 'updated',
            auditableType: 'Faculty',
            auditableId: $faculty->id,
            description: "Updated faculty {$faculty->name}",
            oldValues: $oldValues,
            newValues: ['name' => $request->name, 'code' => strtoupper($request->code), 'enrollment_type' => $request->enrollment_type, 'is_active' => $request->boolean('is_active')],
        );

        return redirect()->route('dashboard.faculties.index')
            ->with('success', 'Faculty updated successfully.');
    }

    public function destroy(Faculty $faculty): RedirectResponse
    {
        $name = $faculty->name;
        $id   = $faculty->id;

        try {
            $faculty->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['delete' => 'This faculty cannot be deleted because it has associated records (departments or students).']);
        }

        AuditLog::record(
            action: 'deleted',
            auditableType: 'Faculty',
            auditableId: $id,
            description: "Deleted faculty {$name}",
        );

        return redirect()->route('dashboard.faculties.index')
            ->with('success', 'Faculty deleted successfully.');
    }

    private function activeProfessors()
    {
        return User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'professor')->whereNull('role_user.revoked_at'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }
}
