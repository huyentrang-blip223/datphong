<?php
namespace App\Http\Controllers;

use App\Http\Requests\CashPaymentRequest;
use App\Models\ExperienceBooking;
use App\Services\CashPaymentService;
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

    public function store(CashPaymentRequest $request, ExperienceBooking $experienceBooking, CashPaymentService $service)
    {
        try {
            $payment = $service->select($experienceBooking, $request->user());
        } catch (InvalidArgumentException $exception) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 422)
                : back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return $request->expectsJson()
            ? response()->json(['data' => $payment], 201)
            : back()->with('status', 'Đã chọn thanh toán tiền mặt trực tiếp cho chủ homestay. Chưa ghi nhận đã thu tiền.');
    }
}
