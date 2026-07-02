@extends('layouts.admin')

@section('content')

<div class="max-w-2xl mx-auto">
    
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('manage.clients') }}" class="p-2 rounded-lg bg-slate-200 text-slate-600 hover:bg-slate-300 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-slate-800 brand-font">
                Add New {{ ucfirst(request('role', 'Client')) }}
            </h1>
            <p class="text-slate-500 text-sm">Create a new account with access permissions.</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-lg border border-slate-200">
        
        <div class="mb-8 p-3 rounded-lg border flex items-center justify-center gap-2
            {{ request('role') == 'staff' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-blue-50 border-blue-200 text-blue-700' }}">
            <span class="font-bold text-sm uppercase tracking-wide">Creating {{ request('role', 'Client') }} Account</span>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 border border-green-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 border border-red-200 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('user.store') }}" method="POST" class="space-y-6" autocomplete="off">
            @csrf
            <!-- Dummy inputs to prevent browser autofill -->
            <input type="text" style="display:none" autocomplete="off" />
            <input type="password" style="display:none" autocomplete="new-password" />
            
            <input type="hidden" name="role" value="{{ request('role', 'client') }}">

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                <input type="text" name="name" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="e.g. Rahul Sharma" required>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Phone Number</label>
                <div class="flex items-center bg-slate-50 border border-slate-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 transition">
                    <span class="px-4 text-slate-500 border-r border-slate-300 bg-slate-100 font-bold">+91</span>
                    <input type="tel" name="phone" class="w-full bg-transparent p-3 text-slate-900 focus:outline-none" placeholder="9876543210" maxlength="10" required autocomplete="off">
                </div>
                <p class="text-xs text-slate-400 mt-1">This will be their login ID.</p>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                <input type="email" name="email" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="user@example.com" required autocomplete="off">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Date of Birth (Optional)
                    </label>
                    <input type="date" name="date_of_birth" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        Anniversary Date (Optional)
                    </label>
                    <input type="date" name="anniversary_date" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
            </div>

            <div class="mt-4" x-data="{ showPass: false }">
                <label class="block text-sm font-bold text-slate-700 mb-2">Set Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </span>
                    <input :type="showPass ? 'text' : 'password'" name="password" class="w-full bg-slate-50 border border-slate-300 rounded-lg pl-10 pr-11 p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="{{ request('role') == 'staff' ? 'Required' : 'Optional (Leave blank to auto-generate)' }}" {{ request('role') == 'staff' ? 'required' : '' }} autocomplete="new-password">
                    <button type="button" @click="showPass = !showPass" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                        <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <svg x-show="showPass" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.12-5.4M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a10.024 10.024 0 01-4.12 5.4m-1.28-1.28A3.001 3.001 0 1111.306 11.3" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" /></svg>
                    </button>
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ request('role') == 'staff' ? 'Required for Staff access.' : 'Optional. If left blank, a random password will be generated.' }}</p>
            </div>
            
            @if(request('role') == 'staff')
            <div class="pt-6 border-t border-slate-200">
                <label class="block text-sm font-bold text-blue-600 mb-4">Staff Access Control</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-4 bg-slate-50 rounded-lg border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="checkbox" name="permissions[]" value="manage_clients" class="w-5 h-5 accent-blue-600">
                        <span class="text-sm font-medium text-slate-700">Manage Clients</span>
                    </label>
                    <label class="flex items-center gap-3 p-4 bg-slate-50 rounded-lg border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="checkbox" name="permissions[]" value="manage_gallery" class="w-5 h-5 accent-blue-600">
                        <span class="text-sm font-medium text-slate-700">Manage Gallery</span>
                    </label>
                    <label class="flex items-center gap-3 p-4 bg-slate-50 rounded-lg border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="checkbox" name="permissions[]" value="manage_team" class="w-5 h-5 accent-blue-600">
                        <span class="text-sm font-medium text-slate-700">Manage Team Page</span>
                    </label>
                    <label class="flex items-center gap-3 p-100 rounded-lg border border-slate-200 opacity-60 cursor-not-allowed hidden">
                        <input type="checkbox" disabled class="w-5 h-5">
                        <span class="text-sm font-medium text-slate-500">Access CA Assistant Chat (Default for Staff)</span>
                    </label>
                    <label class="flex items-center gap-3 p-4 bg-slate-100 rounded-lg border border-slate-200 opacity-60 cursor-not-allowed">
                        <input type="checkbox" disabled class="w-5 h-5">
                        <span class="text-sm font-medium text-slate-500">Delete Files (Admin Only)</span>
                    </label>
                </div>
            </div>
            @endif

            @if(request('role', 'client') == 'client')
            <div class="pt-6 border-t border-slate-200">
                <label class="block text-sm font-bold text-blue-600 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Banking & UPI Details (Optional)
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Bank Name</label>
                        <input type="text" name="bank_name" placeholder="e.g. HDFC Bank" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Account Number</label>
                        <input type="text" name="bank_account_number" placeholder="e.g. 50100XXXXXXX" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">IFSC Code</label>
                        <input type="text" name="bank_ifsc" placeholder="e.g. HDFC0001234" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">UPI ID</label>
                        <input type="text" name="upi_id" placeholder="e.g. name@okhdfc" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </div>
            </div>

            @endif

            <div class="pt-4">
                <button type="submit" class="w-full {{ request('role') == 'staff' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white font-bold py-3.5 rounded-lg transition shadow-lg flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Create Account
                </button>
            </div>
        </form>

    </div>
</div>

@endsection