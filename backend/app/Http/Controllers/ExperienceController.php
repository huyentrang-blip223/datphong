<?php
namespace App\Http\Controllers;

use App\Exceptions\AvailabilityConflictException;
use App\Http\Requests\StoreExperienceBookingRequest;
use App\Models\Experience;
use App\Services\ExperienceBookingService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ExperienceController extends Controller
{
    public function index(Request $request)
    {
        $experiences = Experience::with('homestay')
            ->whereIn('status', ['open', 'full'])
            ->when($request->query('location'), function ($query, $location) {
                $query->whereHas('homestay', function ($homestayQuery) use ($location) {
                    $homestayQuery->where('province', 'like', "%{$location}%")
                        ->orWhere('district', 'like', "%{$location}%");
                });
            })
            ->orderBy('start_at')
            ->paginate(12)
            ->withQueryString();

        return view('experiences.index', compact('experiences'));
    }

    public function show(Experience $experience)
    {
        $experience->load('homestay');

        return view('experiences.show', compact('experience'));
    }

    public function book(StoreExperienceBookingRequest $request, Experience $experience, ExperienceBookingService $bookingService)
    {
        try {
            $booking = $bookingService->createBooking($request->user(), $experience, (int) $request->validated('people_count'));
        } catch (AvailabilityConflictException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 409);
            }

            return back()->withErrors(['experience' => $exception->getMessage()])->withInput();
        } catch (InvalidArgumentException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->withErrors(['experience' => $exception->getMessage()])->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => $booking], 201);
        }

        return redirect()->route('experiences.show', $experience)->with('status', 'Đã đặt trải nghiệm thành công.');
    }
}
