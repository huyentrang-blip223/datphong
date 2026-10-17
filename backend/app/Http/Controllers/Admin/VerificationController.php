<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewVerificationRequest;
use App\Models\HomestayVerification;
use App\Services\HomestaySubmissionService;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class VerificationController extends Controller
{
    public function index()
    {
        return view('admin.verifications.index', ['verifications' => HomestayVerification::with('homestay.owner')->latest('id')->paginate(15)]);
    }

    public function review(ReviewVerificationRequest $request, HomestayVerification $verification, HomestaySubmissionService $service)
    {
        try {
            $service->review($verification, $request->user(), $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['verification' => $e->getMessage()]);
        }

        return back()->with('status', 'Đã duyệt hồ sơ.');
    }

    public function document(HomestayVerification $verification)
    {
        $user = request()->user();
        abort_unless($user->role === 'admin' || ($user->role === 'host' && (int) $verification->homestay->owner_id === (int) $user->id), 403);
        abort_unless(str_starts_with($verification->document_ref, 'verifications/') && Storage::disk('local')->exists($verification->document_ref), 404);

        return Storage::disk('local')->download($verification->document_ref);
    }
}
