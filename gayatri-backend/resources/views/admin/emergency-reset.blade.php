<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Admin Reset</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

<div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
    <div class="p-8 text-center bg-blue-600 text-white">
        <h2 class="text-2xl font-bold">Emergency Reset</h2>
        <p class="text-blue-100 text-sm mt-2">Secure Admin Password Recovery</p>
    </div>
    
    <div class="p-8">
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if (!session('otp_sent'))
            <!-- Step 1: Send OTP -->
            <p class="text-slate-600 text-sm mb-6 text-center">
                Click below to send a secure OTP to the registered administrator email (info@gayatrient.com).
            </p>
            <form action="{{ route('emergency-reset.send') }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Send OTP to Admin Email
                </button>
            </form>
        @else
            <!-- Step 2: Verify OTP & Reset Password -->
            <p class="text-slate-600 text-sm mb-6 text-center">
                An OTP has been sent to <strong>info@gayatrient.com</strong>. Please enter it below to set a new password.
            </p>
            <form action="{{ route('emergency-reset.verify') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">6-Digit OTP</label>
                    <input type="text" name="otp" required maxlength="6" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-center tracking-widest font-mono text-xl" placeholder="XXXXXX">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">New Password</label>
                    <input type="password" name="password" required minlength="8" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter new admin password">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required minlength="8" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Confirm new password">
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition shadow-lg flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Verify OTP & Reset Password
                    </button>
                </div>
            </form>
            
            <div class="mt-4 text-center">
                <form action="{{ route('emergency-reset.send') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-blue-600 hover:underline">Didn't receive it? Resend OTP</button>
                </form>
            </div>
        @endif
    </div>
    
    <div class="bg-slate-50 p-4 text-center border-t border-slate-200">
        <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-slate-800">Return to Login</a>
    </div>
</div>

</body>
</html>
