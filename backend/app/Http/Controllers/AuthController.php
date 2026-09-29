<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email','max:180'],
            'password' => ['required','string','min:8','max:128'],
        ]);

        $user = User::where('email', $data['email'])->where('status', 'active')->first();
        if (!$user || !Hash::check($data['password'], $user->password_hash)) {
            return back()->withErrors(['email' => 'Thông tin đăng nhập không đúng.'])->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect(match ($user->role) {
            'admin' => route('admin.dashboard'),
            'host' => route('host.dashboard'),
            default => route('home'),
        });
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('status', 'Đã đăng xuất.');
    }
}
