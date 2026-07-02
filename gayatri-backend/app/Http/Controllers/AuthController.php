<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    // ==========================================
    // 1. SHOW CHOOSE/LOGIN PAGE
    // ==========================================
    public function showLoginForm() {
        if (Auth::check()) {
            return redirect($this->getRedirectUrl(Auth::user()));
        }
        return view('auth.portal-login');
    }

    // ==========================================
    // 2. CHECK PHONE (New First Step)
    // ==========================================
    public function checkRole(Request $request)
    {
        $request->validate(['phone' => 'required|digits:10']);

        $user = User::where('phone', $request->phone)->first();

        // Security: Masked responses to prevent user enumeration
        if (!$user) {
            return response()->json([
                'status' => 'success',
                'role' => 'client', // Dummy role hint
                'name' => 'Valued User', // Generic name
                'message' => 'Identity verified.'
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is blocked. Please contact admin.'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'role' => $user->role,
            'name' => explode(' ', $user->name)[0], // Return first name for better UI
            'message' => 'Identity verified.'
        ]);
    }

    // ==========================================
    // 3. UNIFIED PASSWORD LOGIN (For All Roles)
    // ==========================================
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10',
            'password' => 'required'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials.'], 401);
        }

        // Check if account is ALREADY hard-locked
        if (!$user->is_active) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Account permanently locked due to multiple failed attempts. Please contact admin.'
            ], 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            // Increment Failed Attempts
            $user->increment('login_attempts');

            // HARD LOCK CHECK: After 5 attempts, deactivate the account (SKIP FOR ADMINS)
            if ($user->login_attempts >= 5 && $user->role !== 'admin') {
                $user->update(['is_active' => false]);
                
                Log::warning("Account Locked: Phone {$user->phone} after 5 failed attempts.");

                return response()->json([
                    'status' => 'error',
                    'message' => 'Account locked after 5 failed attempts. Please contact admin.'
                ], 403);
            }

            $maxAttempts = 5;
            $remaining = $maxAttempts - $user->login_attempts;
            
            if ($user->role === 'admin' && $remaining <= 0) {
                 return response()->json([
                    'status' => 'error', 
                    'message' => 'Too many failed attempts. Please wait for the timer or try later.'
                ], 401);
            }

            return response()->json([
                'status' => 'error', 
                'message' => 'Incorrect password. ' . ($remaining > 0 ? $remaining : 0) . ' attempts remaining before lock.'
            ], 401);
        }

        // Success: Reset Attempts
        $user->update(['login_attempts' => 0]);

        // Check Active Again
        if(!$user->is_active) {
             return response()->json(['status' => 'error', 'message' => 'Account Blocked.'], 403);
        }

        Auth::login($user);

        // LOG ACTIVITY
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Login',
            'description' => 'Logged in with Password',
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'name' => explode(' ', $user->name)[0],
            'profile_photo' => $user->profile_photo ? $user->avatar_url : null,
            'role' => $user->role,
            'redirect' => $this->getRedirectUrl($user)
        ]);
    }

    // ==========================================
    // 4. LOGOUT
    // ==========================================
    public function logout(Request $request)
    {
        if(Auth::check()){
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Logout',
                'description' => 'User logged out',
                'ip_address' => $request->ip()
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('portal.login');
    }

    // ==========================================
    // 5. HELPER: Redirect Based on Role
    // ==========================================
    private function getRedirectUrl($user) {
        if ($user->role === 'admin') {
            return route('admin.dashboard');
        }
        if ($user->role === 'staff') {
            return route('staff.dashboard'); 
        }
        return route('client.dashboard');
    }


}