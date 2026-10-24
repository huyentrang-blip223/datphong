<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomChatbotRequest;
use App\Services\GeminiRoomRecommendationService;
use Illuminate\Http\JsonResponse;

class RoomChatbotController extends Controller
{
    public function __invoke(RoomChatbotRequest $request, GeminiRoomRecommendationService $service): JsonResponse
    {
        return response()->json(['data' => $service->recommend($request->validated())]);
    }
}
