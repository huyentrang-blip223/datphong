<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ]);

        $user = User::create([
            'full_name' => $data['full_name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'role' => 'guest',
            'password_hash' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('account_fingerprint', hash('sha256', $user->password_hash));
        $request->session()->put('account_fingerprint_user', $user->id);

        return redirect($this->redirectPathFor($user->role))->with('status', 'Đăng ký tài khoản thành công.');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:180'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        $user = User::where('email', $data['email'])->where('status', 'active')->first();
        if (! $user || ! Hash::check($data['password'], $user->password_hash)) {
            Log::warning('Login failed.', [
                'email_hash' => hash('sha256', strtolower($data['email'])),
                'ip' => $request->ip(),
            ]);

            return back()->withErrors(['email' => 'Thông tin đăng nhập không đúng.'])->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('account_fingerprint', hash('sha256', $user->password_hash));
        $request->session()->put('account_fingerprint_user', $user->id);

        return redirect($this->redirectPathFor($user->role));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Đã đăng xuất.');
    }

    private function redirectPathFor(string $role): string
    {
        return match ($role) {
            'admin' => route('admin.dashboard'),
            'host' => route('host.dashboard'),
            'seller' => route('seller.dashboard'),
            'guest' => route('guest.bookings.index'),
            default => route('home'),
        };
    }
}
