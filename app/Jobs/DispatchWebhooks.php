<?php

namespace App\Jobs;

use App\Models\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchWebhooks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     * Each webhook delivery is independent — failures are logged, not re-thrown.
     */
    public int $tries = 1;

    /** Supported webhook event names. */
    public const EVENTS = [
        'enrollment.confirmed',
        'grade.posted',
    ];

    public function __construct(
        public readonly string $event,
        public readonly array  $payload,
    ) {}

    public function handle(): void
    {
        $webhooks = Webhook::where('is_active', true)
            ->whereJsonContains('events', $this->event)
            ->get();

        foreach ($webhooks as $webhook) {
            $this->deliverTo($webhook);
        }
    }

    private function deliverTo(Webhook $webhook): void
    {
        $body      = json_encode($this->payload);
        $signature = 'sha256=' . hash_hmac('sha256', $body, $webhook->secret);

        try {
            $response = Http::withHeaders([
                'Content-Type'       => 'application/json',
                'X-UniOne-Event'     => $this->event,
                'X-UniOne-Signature' => $signature,
            ])->timeout(10)->post($webhook->url, $this->payload);

            $webhook->deliveries()->create([
                'event'           => $this->event,
                'payload'         => $this->payload,
                'response_status' => $response->status(),
                'response_body'   => substr($response->body(), 0, 2000),
                'attempt'         => 1,
                'delivered_at'    => $response->successful() ? now() : null,
            ]);

            if ($response->successful()) {
                $webhook->update([
                    'failure_count'      => 0,
                    'last_triggered_at'  => now(),
                ]);
            } else {
                $this->recordFailure($webhook);
            }
        } catch (\Throwable $e) {
            Log::warning("Webhook delivery failed for webhook #{$webhook->id}: {$e->getMessage()}");

            $webhook->deliveries()->create([
                'event'        => $this->event,
                'payload'      => $this->payload,
                'response_body' => $e->getMessage(),
                'attempt'       => 1,
            ]);

            $this->recordFailure($webhook);
        }
    }

    private function recordFailure(Webhook $webhook): void
    {
        $webhook->increment('failure_count');

        // Auto-disable after 10 consecutive failures
        if ($webhook->fresh()->failure_count >= 10) {
            $webhook->update(['is_active' => false]);
        }
    }
}
