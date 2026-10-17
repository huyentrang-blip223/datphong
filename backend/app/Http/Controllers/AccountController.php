<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\PasswordRecoveryRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use InvalidArgumentException;

class AccountController extends Controller
{
    public function edit()
    {
        return view('account.profile', ['user' => request()->user()]);
    }

    public function profile(UpdateProfileRequest $request, AccountService $service)
    {
        try {
            $service->profile($request->user(), $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['current_password' => $e->getMessage()]);
        }

        return back()->with('status', 'Đã cập nhật hồ sơ cá nhân.');
    }

    public function password(ChangePasswordRequest $request, AccountService $service)
    {
        try {
            $user = $service->changePassword($request->user(), $request->current_password, $request->password);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['current_password' => $e->getMessage()]);
        }
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        $request->session()->put('account_fingerprint', hash('sha256', $user->password_hash));

        return back()->with('status', 'Đã đổi mật khẩu. Các phiên đăng nhập khác đã hết hiệu lực.');
    }

    public function forgot()
    {
        return view('auth.forgot-password');
    }

    public function email(PasswordRecoveryRequest $request)
    {
        try {
            Password::sendResetLink(['email' => $request->email, 'status' => 'active']);
        } catch (\Throwable $e) {
            Log::error('Password recovery delivery failed', ['exception_type' => get_class($e)]);
        }
        // Same response for unknown, locked, throttled and active email addresses.
        return back()->with('status', 'Nếu tài khoản phù hợp, hướng dẫn đặt lại mật khẩu sẽ được gửi qua email.');
    }

    public function resetForm(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email', '')]);
    }

    public function reset(PasswordRecoveryRequest $request, AccountService $service)
    {
        try {
            $status = $service->reset($request->validated());
        } catch (InvalidArgumentException $e) {
            $status = Password::INVALID_TOKEN;
        }

        return $status === Password::PASSWORD_RESET ? redirect()->route('login')->with('status', 'Đã đặt lại mật khẩu. Vui lòng đăng nhập.')
            : back()->withErrors(['email' => 'Liên kết không hợp lệ hoặc đã hết hạn.'])->onlyInput('email');
    }
}
