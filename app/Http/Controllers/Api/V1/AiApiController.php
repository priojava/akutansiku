<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AiJournalingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiApiController extends Controller
{
    public function __construct(
        protected AiJournalingService $aiService
    ) {}

    public function parseNaturalLanguage(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);

        $request->validate([
            'prompt' => 'required|string|min:3',
        ]);

        $result = $this->aiService->parseNaturalLanguage($companyId, $request->prompt);

        return response()->json($result);
    }
}
