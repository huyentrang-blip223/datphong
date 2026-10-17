<?php
namespace App\Http\Controllers;

use App\Http\Requests\MockPaymentRequest;
use App\Models\ExperienceBooking;
use App\Services\CommercePaymentService;
use InvalidArgumentException;

class ExperienceBookingPaymentController extends Controller
{
    public function show(ExperienceBooking $experienceBooking)
    {
        abort_unless((int) $experienceBooking->guest_id === (int) request()->user()->id, 403);

        $experienceBooking->load([
            'experience.homestay',
            'payments' => fn($query) => $query->latest('id'),
        ]);

        return view('payments.experience_booking', compact('experienceBooking'));
    }

    public function store(MockPaymentRequest $request, ExperienceBooking $experienceBooking, CommercePaymentService $service)
    {
        try {
            $payment = $service->payExperienceBooking($experienceBooking, $request->user(), $request->validated('result'));
        } catch (InvalidArgumentException $exception) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return $request->expectsJson()
            ? response()->json(['data' => $payment], $payment->status === 'paid' ? 201 : 200)
            : back()->with('status', $payment->status === 'paid' ? 'Thanh toán trải nghiệm thành công.' : 'Thanh toán trải nghiệm thất bại.');
    }
}
