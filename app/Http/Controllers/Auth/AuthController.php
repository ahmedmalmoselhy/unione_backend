<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = \App\Models\User::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => __('auth.credentials')], 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('auth.logout')]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * POST /api/auth/change-password
     * Changes the authenticated user's password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => __('auth.password')], 422);
        }

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return response()->json(['message' => 'Password updated successfully.']);
    }

    /**
     * PATCH /api/auth/profile
     * Updates the authenticated user's own profile (phone, date_of_birth, avatar_path).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone'         => ['sometimes', 'string', 'max:20'],
            'date_of_birth' => ['sometimes', 'date', 'before:today'],
            'avatar_path'   => ['sometimes', 'string', 'max:500'],
        ]);

        $request->user()->update($data);

        return response()->json(['user' => $request->user()->fresh()]);
    }
}
