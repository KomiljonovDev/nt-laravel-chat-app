<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Foydalanuvchi ro'yxatdan o'tishi
    public function register(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:6|confirmed',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'location'     => 'nullable|string|max:255'
        ])->validate();

        $user = User::create([
            'name'         => $validatedData['name'],
            'email'        => $validatedData['email'],
            'password'     => Hash::make($validatedData['password']),
            'phone_number' => $validatedData['phone_number'],
            'location'     => $validatedData['location'] ?? null,
        ]);

        // Token yaratish (Sanctum misolida)
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
            'token'   => $token
        ], 201);
    }

    // Foydalanuvchi tizimga kirishi
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully',
            'user'    => $user,
            'token'   => $token
        ], 200);
    }
}
