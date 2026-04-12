<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Integrations\MoodleIntegration;
use App\Services\Integrations\SSOIntegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationMarketplaceController extends Controller
{
    protected array $integrations;

    public function __construct()
    {
        $this->integrations = [
            'moodle' => new MoodleIntegration(),
            'sso_saml' => new SSOIntegration(),
        ];
    }

    /**
     * GET /api/v1/admin/integrations
     * List all available integrations.
     */
    public function index(): JsonResponse
    {
        $integrations = [];

        foreach ($this->integrations as $key => $integration) {
            $config = config("integrations.{$key}", []);
            $integration->initialize($config);

            $integrations[$key] = $integration->getStatus();
        }

        return response()->json([
            'integrations' => $integrations,
            'available' => array_keys($this->integrations),
        ]);
    }

    /**
     * GET /api/v1/admin/integrations/{integration}/test
     * Test an integration connection.
     */
    public function testConnection(string $integration): JsonResponse
    {
        if (!isset($this->integrations[$integration])) {
            return response()->json([
                'message' => "Integration '{$integration}' not found.",
            ], 404);
        }

        $config = config("integrations.{$integration}", []);
        $this->integrations[$integration]->initialize($config);
        $connected = $this->integrations[$integration]->testConnection();

        return response()->json([
            'integration' => $integration,
            'connected' => $connected,
            'message' => $connected ? 'Connection successful' : 'Connection failed',
        ]);
    }

    /**
     * POST /api/v1/admin/integrations/{integration}/sync
     * Sync data to an integration.
     */
    public function sync(Request $request, string $integration): JsonResponse
    {
        if (!isset($this->integrations[$integration])) {
            return response()->json([
                'message' => "Integration '{$integration}' not found.",
            ], 404);
        }

        $config = config("integrations.{$integration}", []);
        $this->integrations[$integration]->initialize($config);

        $data = $request->all();
        $success = $this->integrations[$integration]->sync($data);

        return response()->json([
            'integration' => $integration,
            'success' => $success,
            'message' => $success ? 'Sync completed' : 'Sync failed',
        ]);
    }
}
