<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    public function register(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = User::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('name', 'password'))) {
            Log::channel('custom')->warning('Failed login attempt', [
                'time' => date('d.m.Y H:i:s', now()->timestamp),
            ]);
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $request->user()->createToken('auth_token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => new UserResource(Auth::user())]);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function destroy(User $user)
    {
        Auth::user()->tokens()->delete();
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    public function getUser()
    {
        return new UserResource(Auth::user());
    }
}
