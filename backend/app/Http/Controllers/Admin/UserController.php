<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveUserRequest;
use App\Models\User;
use App\Services\UserAdministrationService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:180'], 'role' => ['nullable', 'in:admin,guest,host,seller'], 'status' => ['nullable', 'in:active,locked']]);
        $users = User::when($filters['q'] ?? null, fn ($q, $v) => $q->where(fn ($q) => $q->where('email', 'like', '%'.$v.'%')->orWhere('full_name', 'like', '%'.$v.'%')))
            ->when($filters['role'] ?? null, fn ($q, $v) => $q->where('role', $v))->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->latest('id')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['managedUser' => new User(['role' => 'host', 'status' => 'active'])]);
    }

    public function edit(User $user)
    {
        return view('admin.users.form', ['managedUser' => $user]);
    }

    public function store(SaveUserRequest $request, UserAdministrationService $service)
    {
        return $this->save($request, $service);
    }

    public function update(SaveUserRequest $request, UserAdministrationService $service, User $user)
    {
        return $this->save($request, $service, $user);
    }

    private function save($request, $service, $user = null)
    {
        try {
            $user = $service->save($request->user(), $request->validated(), $user);
        } catch (InvalidArgumentException $e) {
            return $request->expectsJson() ? response()->json(['message' => $e->getMessage()], 422) : back()->withErrors(['user' => $e->getMessage()])->withInput($request->except(['password', 'password_confirmation']));
        }

        return $request->expectsJson() ? response()->json(['data' => $user], $request->isMethod('post') ? 201 : 200) : redirect()->route('admin.users.index')->with('status', 'Đã lưu tài khoản.');
    }
}
