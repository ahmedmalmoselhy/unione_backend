<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user      = $request->user();
        $student   = $user->student()->with(['faculty', 'department'])->first();
        $professor = $user->professor()->with(['department.faculty'])->first();
        $employee  = $user->employee()->with(['department.faculty'])->first();

        return view('portal.profile', compact('user', 'student', 'professor', 'employee'));
    }
}
