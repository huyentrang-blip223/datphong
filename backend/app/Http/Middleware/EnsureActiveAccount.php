<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureActiveAccount
{
    public function handle($request, Closure $next)
    {
        if ($user = $request->user()) {
            $user = $user->fresh();
            if (! $user) {
                Auth::logout();
                $request->session()->invalidate();

                return redirect()->route('login');
            }
            Auth::setUser($user);
            $fingerprint = hash('sha256', $user->password_hash);
            $known = (int) $request->session()->get('account_fingerprint_user') === (int) $user->id ? $request->session()->get('account_fingerprint') : null;
            if ($user->status !== 'active' || ($known && ! hash_equals($known, $fingerprint))) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return $request->expectsJson() ? response()->json(['message' => 'Phiên không còn hợp lệ.'], 401) : redirect()->route('login')->withErrors(['email' => 'Tài khoản bị khóa hoặc mật khẩu đã thay đổi. Vui lòng đăng nhập lại.']);
            }
            $request->session()->put('account_fingerprint', $fingerprint);
            $request->session()->put('account_fingerprint_user', $user->id);
        }

        return $next($request);
    }
}
