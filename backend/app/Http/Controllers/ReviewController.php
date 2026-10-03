<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Booking;
use App\Services\ReviewService;
use InvalidArgumentException;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Booking $booking, ReviewService $service)
    {
        try {
            $validated = $request->validated();
            $review = $service->guestToHost($booking, $request->user(), (int) $validated['rating'], $validated['content'] ?? null);
        } catch (InvalidArgumentException $exception) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->withErrors(['review' => $exception->getMessage()]);
        }

        return $request->expectsJson()
            ? response()->json(['data' => $review], 201)
            : back()->with('status', 'Đã gửi đánh giá.');
    }
}
