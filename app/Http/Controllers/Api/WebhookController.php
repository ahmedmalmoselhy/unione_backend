<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\DispatchWebhooks;
use App\Models\Webhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * GET /api/admin/webhooks
     * Lists all webhooks registered by the authenticated admin.
     */
    public function index(Request $request): JsonResponse
    {
        $webhooks = $request->user()
            ->webhooks()
            ->withCount('deliveries')
            ->latest()
            ->get()
            ->map(fn ($w) => [
                'id'                 => $w->id,
                'url'                => $w->url,
                'events'             => $w->events,
                'is_active'          => $w->is_active,
                'failure_count'      => $w->failure_count,
                'last_triggered_at'  => $w->last_triggered_at?->toDateTimeString(),
                'deliveries_count'   => $w->deliveries_count,
                'created_at'         => $w->created_at->toDateTimeString(),
            ]);

        return response()->json(['webhooks' => $webhooks]);
    }

    /**
     * POST /api/admin/webhooks
     * Registers a new webhook endpoint.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url'      => ['required', 'url', 'max:2048'],
            'events'   => ['required', 'array', 'min:1'],
            'events.*' => ['required', 'string', 'in:' . implode(',', DispatchWebhooks::EVENTS)],
        ]);

        $webhook = $request->user()->webhooks()->create([
            'url'    => $data['url'],
            'events' => array_values(array_unique($data['events'])),
            'secret' => bin2hex(random_bytes(32)),
        ]);

        return response()->json([
            'webhook' => [
                'id'     => $webhook->id,
                'url'    => $webhook->url,
                'events' => $webhook->events,
                'secret' => $webhook->secret, // returned once at creation only
            ],
            'message' => 'Webhook registered. Store the secret — it will not be shown again.',
        ], 201);
    }

    /**
     * PATCH /api/admin/webhooks/{webhook}
     * Updates a webhook's URL, events, or active state.
     */
    public function update(Request $request, Webhook $webhook): JsonResponse
    {
        $this->authorizeOwnership($request, $webhook);

        $data = $request->validate([
            'url'      => ['sometimes', 'url', 'max:2048'],
            'events'   => ['sometimes', 'array', 'min:1'],
            'events.*' => ['string', 'in:' . implode(',', DispatchWebhooks::EVENTS)],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['events'])) {
            $data['events'] = array_values(array_unique($data['events']));
        }

        // Updating the webhook clears the failure count so it gets a fresh start
        if (isset($data['is_active']) && $data['is_active']) {
            $data['failure_count'] = 0;
        }

        $webhook->update($data);

        return response()->json(['webhook' => $this->format($webhook->fresh())]);
    }

    /**
     * DELETE /api/admin/webhooks/{webhook}
     * Removes a webhook registration.
     */
    public function destroy(Request $request, Webhook $webhook): JsonResponse
    {
        $this->authorizeOwnership($request, $webhook);

        $webhook->delete();

        return response()->json(['message' => 'Webhook deleted.']);
    }

    /**
     * GET /api/admin/webhooks/{webhook}/deliveries
     * Returns the 50 most recent delivery attempts for the webhook.
     */
    public function deliveries(Request $request, Webhook $webhook): JsonResponse
    {
        $this->authorizeOwnership($request, $webhook);

        $deliveries = $webhook->deliveries()
            ->orderByDesc('created_at')
            ->take(50)
            ->get()
            ->map(fn ($d) => [
                'id'              => $d->id,
                'event'           => $d->event,
                'response_status' => $d->response_status,
                'attempt'         => $d->attempt,
                'delivered_at'    => $d->delivered_at?->toDateTimeString(),
                'created_at'      => $d->created_at->toDateTimeString(),
            ]);

        return response()->json(['deliveries' => $deliveries]);
    }

    private function authorizeOwnership(Request $request, Webhook $webhook): void
    {
        if ($webhook->user_id !== $request->user()->id) {
            abort(403, 'Forbidden.');
        }
    }

    private function format(Webhook $webhook): array
    {
        return [
            'id'                => $webhook->id,
            'url'               => $webhook->url,
            'events'            => $webhook->events,
            'is_active'         => $webhook->is_active,
            'failure_count'     => $webhook->failure_count,
            'last_triggered_at' => $webhook->last_triggered_at?->toDateTimeString(),
            'created_at'        => $webhook->created_at->toDateTimeString(),
        ];
    }
}
