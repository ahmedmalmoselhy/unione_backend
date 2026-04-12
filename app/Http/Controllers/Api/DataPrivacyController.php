<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DataPrivacyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DataPrivacyController extends Controller
{
    protected DataPrivacyService $privacyService;

    public function __construct(DataPrivacyService $privacyService)
    {
        $this->privacyService = $privacyService;
    }

    /**
     * GET /api/v1/privacy/export
     * Export all personal data (GDPR Article 20 - Data Portability).
     */
    public function export(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->privacyService->exportUserData($userId);

        return response()->json([
            'message' => 'Personal data exported successfully.',
            'data' => $data,
            'exported_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * POST /api/v1/privacy/export/download
     * Export data as JSON file download.
     */
    public function exportDownload(Request $request)
    {
        $userId = $request->user()->id;
        $data = $this->privacyService->exportUserData($userId);

        $filename = "personal-data-export-{$userId}-" . now()->format('Y-m-d') . '.json';
        $path = 'exports/' . $filename;

        Storage::disk('local')->put($path, json_encode($data, JSON_PRETTY_PRINT));

        return response()->download(
            storage_path("app/{$path}"),
            $filename,
            ['Content-Type' => 'application/json']
        )->deleteFileAfterSend();
    }

    /**
     * POST /api/v1/privacy/anonymize
     * Anonymize user data (GDPR Article 17 - Right to be Forgotten).
     * This soft-deletes the user and removes personal information.
     */
    public function anonymize(Request $request): JsonResponse
    {
        $data = $request->validate([
            'confirmation' => 'required|in:I_UNDERSTAND_THIS_IS_IRREVERSIBLE',
        ]);

        $userId = $request->user()->id;
        $this->privacyService->anonymizeUser($userId);

        // Logout the user
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Your account has been anonymized successfully. All personal data has been removed.',
        ]);
    }

    /**
     * DELETE /api/v1/privacy/account
     * Hard delete account (IRREVERSIBLE - requires admin confirmation).
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'confirmation' => 'required|in:PERMANENTLY_DELETE_MY_ACCOUNT',
            'password' => 'required|string',
        ]);

        // Verify password
        if (!password_verify($data['password'], $request->user()->password)) {
            return response()->json([
                'message' => 'Invalid password.',
            ], 403);
        }

        $userId = $request->user()->id;
        $this->privacyService->hardDeleteUser($userId);

        // Logout
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Your account has been permanently deleted.',
        ]);
    }
}
