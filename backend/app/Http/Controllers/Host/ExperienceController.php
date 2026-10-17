<?php
namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;
use App\Models\Experience;
use App\Models\Homestay;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    public function index(Request $request)
    {
        $experiences = Experience::with('homestay')
            ->whereHas('homestay', fn($q) => $q->where('owner_id', $request->user()->id))
            ->orderByDesc('start_at')
            ->paginate(12);

        return view('host.experiences.index', compact('experiences'));
    }

    public function create(Request $request)
    {
        return view('host.experiences.create', [
            'experience' => new Experience(['duration_minutes' => 120, 'capacity' => 10, 'status' => 'draft']),
            'homestays' => $this->ownedHomestays($request),
        ]);
    }

    public function store(StoreExperienceRequest $request)
    {
        Experience::create($request->validated() + ['booked_count' => 0]);

        return redirect()->route('host.experiences.index')->with('status', 'Đã tạo trải nghiệm.');
    }

    public function edit(Request $request, Experience $experience)
    {
        $this->ensureOwned($request, $experience);

        return view('host.experiences.edit', [
            'experience' => $experience,
            'homestays' => $this->ownedHomestays($request),
        ]);
    }

    public function update(UpdateExperienceRequest $request, Experience $experience)
    {
        $validated = $request->validated();
        if ((int) $validated['capacity'] < (int) $experience->booked_count) {
            return back()->withErrors(['capacity' => 'Sức chứa không được nhỏ hơn số chỗ đã đặt.'])->withInput();
        }

        $experience->update($validated);

        return redirect()->route('host.experiences.index')->with('status', 'Đã cập nhật trải nghiệm.');
    }

    public function destroy(Request $request, Experience $experience)
    {
        $this->ensureOwned($request, $experience);

        if ($experience->bookings()->exists()) {
            return back()->withErrors(['experience' => 'Không thể xóa trải nghiệm đã có đặt chỗ; hãy chuyển trạng thái sang cancelled.']);
        }

        $experience->delete();

        return redirect()->route('host.experiences.index')->with('status', 'Đã xóa trải nghiệm.');
    }

    private function ownedHomestays(Request $request)
    {
        return Homestay::where('owner_id', $request->user()->id)->orderBy('name')->get();
    }

    private function ensureOwned(Request $request, Experience $experience): void
    {
        abort_unless(
            Homestay::where('id', $experience->homestay_id)->where('owner_id', $request->user()->id)->exists(),
            403
        );
    }
}
