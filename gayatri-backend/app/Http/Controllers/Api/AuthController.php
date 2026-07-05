<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Client-portal auth, separate from the staff/admin phone+OTP web login.
 * Sanctum SPA (stateful, cookie-based) — the Vite/React frontend hits
 * GET /sanctum/csrf-cookie once, then these endpoints; no bearer tokens.
 */
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:8',
            'company_name' => 'required|string|max:255',
            'gstin' => 'nullable|string|max:15',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'client',
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'company_name' => $data['company_name'],
            'gstin' => $data['gstin'] ?? null,
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'client' => $client, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::guard('web')->attempt($data)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user = Auth::guard('web')->user();

        if (! $user->is_active) {
            Auth::guard('web')->logout();
            return response()->json(['message' => 'Account is blocked. Contact admin.'], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'client' => $user->client, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json(['user' => $user, 'client' => $user->client]);
    }
}
