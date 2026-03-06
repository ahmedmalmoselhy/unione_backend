<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreFacultyRequest;
use App\Http\Requests\Dashboard\UpdateFacultyRequest;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FacultyController extends Controller
{
    public function index(): View
    {
        $faculties = Faculty::with('dean')->orderBy('name')->paginate(15);

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
        Faculty::create([
            'name'            => $request->name,
            'name_ar'         => $request->name_ar,
            'code'            => strtoupper($request->code),
            'enrollment_type' => $request->enrollment_type,
            'dean_id'         => $request->dean_id,
            'is_active'       => $request->boolean('is_active'),
        ]);

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
        $faculty->update([
            'name'            => $request->name,
            'name_ar'         => $request->name_ar,
            'code'            => strtoupper($request->code),
            'enrollment_type' => $request->enrollment_type,
            'dean_id'         => $request->dean_id,
            'is_active'       => $request->boolean('is_active'),
        ]);

        return redirect()->route('dashboard.faculties.index')
            ->with('success', 'Faculty updated successfully.');
    }

    public function destroy(Faculty $faculty): RedirectResponse
    {
        try {
            $faculty->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['delete' => 'This faculty cannot be deleted because it has associated records (departments or students).']);
        }

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
