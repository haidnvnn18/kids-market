<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('seller.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            if ($user->role !== 'seller') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tài khoản không có quyền truy cập.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('seller.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        return view('seller.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'brand_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:5'],
            'description' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'seller',
        ]);

        Supplier::create([
            'user_id' => $user->id,
            'brand_name' => $validated['brand_name'],
            'country' => $validated['country'],
            'description' => $validated['description'] ?? null,
        ]);

        Auth::login($user);

        return redirect()->route('seller.dashboard')->with('success', 'Đăng ký thành công!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('seller.login');
    }
}
