<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    /**
     * GET /api/auth/tokens
     * Lists all active tokens for the authenticated user.
     * The raw token value is never returned; only metadata.
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()
            ->tokens()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'last_used_at' => $t->last_used_at?->toDateTimeString(),
                'created_at'   => $t->created_at->toDateTimeString(),
                'is_current'   => $request->user()->currentAccessToken()?->id === $t->id,
            ]);

        return response()->json(['tokens' => $tokens]);
    }

    /**
     * DELETE /api/auth/tokens/{tokenId}
     * Revokes a specific token belonging to the authenticated user.
     * A user cannot revoke another user's token.
     */
    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $token = $request->user()
            ->tokens()
            ->where('id', $tokenId)
            ->first();

        if (! $token) {
            return response()->json(['message' => 'Token not found.'], 404);
        }

        $token->delete();

        return response()->json(['message' => 'Token revoked.']);
    }

    /**
     * DELETE /api/auth/tokens
     * Revokes ALL tokens for the authenticated user (logout everywhere).
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'All tokens revoked.']);
    }
}
