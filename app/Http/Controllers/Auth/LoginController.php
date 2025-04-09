<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        // API uchun token yaratish (foydalanuvchi avval tekshiriladi)
        $token = $this->guard()->user()->createToken('authToken')->plainTextToken;

        // JSON javob qaytarish
        return response()->json([
            'message' => 'User logged in successfully',
            'user'    => $this->guard()->user(),
            'token'   => $token,
        ]);
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
