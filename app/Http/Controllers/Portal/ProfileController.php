<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user      = auth()->user();
        $student   = $user->student()->with(['faculty', 'department'])->first();
        $professor = $user->professor()->with(['department.faculty'])->first();
        $employee  = $user->employee()->with(['department.faculty'])->first();

        return view('portal.profile', compact('user', 'student', 'professor', 'employee'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user       = $request->user();
        $avatarPath = $user->avatar_path;

        if ($request->boolean('remove_avatar') && $avatarPath) {
            Storage::disk('public')->delete($avatarPath);
            $avatarPath = null;
        }

        if ($request->hasFile('avatar')) {
            if ($avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }
            $avatarPath = $request->file('avatar')->store('avatars/users', 'public');
        }

        $user->update([
            'phone'         => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'avatar_path'   => $avatarPath,
        ]);

        return redirect()->route('portal.profile')->with('success', 'Profile updated successfully.');
    }
}
