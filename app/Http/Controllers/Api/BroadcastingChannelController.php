<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BroadcastingChannelController extends Controller
{
    /**
     * GET /api/v1/broadcasting/auth
     * Returns broadcasting authentication data for Laravel Echo.
     */
    public function auth(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user_id' => $user->id,
            'channels' => [
                'private-App.Models.User.' . $user->id,
                ...$this->getAdditionalChannels($user),
            ],
            'echo_config' => [
                'broadcaster' => 'pusher',
                'key' => config('broadcasting.connections.pusher.key'),
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'forceTLS' => true,
                'authEndpoint' => route('broadcasting.auth'),
            ],
        ]);
    }

    /**
     * GET /api/v1/broadcasting/config
     * Returns broadcasting configuration for frontend.
     */
    public function config(Request $request): JsonResponse
    {
        return response()->json([
            'enabled' => config('broadcasting.default') !== 'null',
            'driver' => config('broadcasting.default'),
            'pusher_key' => config('broadcasting.connections.pusher.key'),
            'cluster' => config('broadcasting.connections.pusher.options.cluster'),
        ]);
    }

    /**
     * Get additional channels based on user role.
     */
    protected function getAdditionalChannels($user): array
    {
        $channels = [];

        // Admin channels
        if ($user->hasAnyRole(['admin', 'university_admin', 'faculty_admin'])) {
            $channels[] = 'presence-admin';
        }

        return $channels;
    }
}
