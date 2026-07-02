@extends('layouts.admin')

@section('content')

<div class="max-w-2xl mx-auto">
    
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('manage.clients') }}" class="p-2 rounded-lg bg-slate-200 text-slate-600 hover:bg-slate-300 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-slate-800 brand-font">
                Edit {{ ucfirst($user->role) }}
            </h1>
            <p class="text-slate-500 text-sm">Update login details & profile.</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-lg border border-slate-200">
        
        <form action="{{ route('user.update', $user->id) }}" method="POST" class="space-y-6" autocomplete="off">
            @csrf
            <!-- Dummy inputs to prevent browser autofill -->
            <input type="text" style="display:none" autocomplete="off" />
            <input type="password" style="display:none" autocomplete="new-password" />
            
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                    <input type="text" name="name" value="{{ $user->name }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Phone Number</label>
                        <input type="tel" name="phone" value="{{ $user->phone }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ $user->email }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required autocomplete="off">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Date of Birth (Optional)
                        </label>
                        <input type="date" name="date_of_birth" value="{{ $user->date_of_birth }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            Anniversary Date (Optional)
                        </label>
                        <input type="date" name="anniversary_date" value="{{ $user->anniversary_date }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-200">
                <h3 class="text-sm font-bold text-blue-600 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Security Settings
                </h3>
                <div x-data="{ showPass: false }">
                    <label class="block text-sm font-bold text-slate-700 mb-2">New Password</label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password" autocomplete="new-password" placeholder="Leave empty to keep current password" class="w-full bg-slate-50 border border-slate-300 rounded-lg pl-4 pr-11 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <button type="button" @click="showPass = !showPass" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                            <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            <svg x-show="showPass" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.12-5.4M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a10.024 10.024 0 01-4.12 5.4m-1.28-1.28A3.001 3.001 0 1111.306 11.3" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" /></svg>
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Only enter if you want to change it.</p>
                </div>
            </div>

            @if($user->role === 'client')
            <div class="pt-6 border-t border-slate-200">
                <h3 class="text-sm font-bold text-blue-600 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Banking & UPI Details (Optional)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ $user->bank_name }}" placeholder="e.g. HDFC Bank" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" {{ Auth::user()->role !== 'admin' ? 'readonly title="Only admins can edit this"' : '' }}>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Account Number</label>
                        <input type="text" name="bank_account_number" value="{{ $user->bank_account_number }}" placeholder="e.g. 50100XXXXXXX" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" {{ Auth::user()->role !== 'admin' ? 'readonly title="Only admins can edit this"' : '' }}>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">IFSC Code</label>
                        <input type="text" name="bank_ifsc" value="{{ $user->bank_ifsc }}" placeholder="e.g. HDFC0001234" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition uppercase" {{ Auth::user()->role !== 'admin' ? 'readonly title="Only admins can edit this"' : '' }}>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">UPI ID</label>
                        <input type="text" name="upi_id" value="{{ $user->upi_id }}" placeholder="e.g. name@okhdfc" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" {{ Auth::user()->role !== 'admin' ? 'readonly title="Only admins can edit this"' : '' }}>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-200">
                <h3 class="text-sm font-bold text-blue-600 mb-4">Business Info (Optional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">GST Number</label>
                        <input type="text" name="gst_number" value="{{ $user->gst_number }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 uppercase focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">PAN Number</label>
                        <input type="text" name="pan_number" value="{{ $user->pan_number }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-3 text-slate-900 uppercase focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </div>
            </div>


            @endif

            @if($user->role === 'staff')
            <div class="pt-6 border-t border-slate-200">
                <h3 class="text-sm font-bold text-blue-600 mb-4">Staff Permissions</h3>
                @php $perms = json_decode($user->permissions) ?? []; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center gap-2 p-3 bg-slate-50 rounded border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="checkbox" name="permissions[]" value="upload_files" {{ in_array('upload_files', $perms) ? 'checked' : '' }} class="accent-blue-600 w-4 h-4">
                        <span class="text-sm font-medium text-slate-700">Upload Files</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 bg-slate-50 rounded border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="checkbox" name="permissions[]" value="view_files" {{ in_array('view_files', $perms) ? 'checked' : '' }} class="accent-blue-600 w-4 h-4">
                        <span class="text-sm font-medium text-slate-700">View Documents</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 bg-slate-50 rounded border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="checkbox" name="permissions[]" value="manage_clients" {{ in_array('manage_clients', $perms) ? 'checked' : '' }} class="accent-blue-600 w-4 h-4">
                        <span class="text-sm font-medium text-slate-700">Manage Clients</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 bg-slate-50 rounded border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="checkbox" name="permissions[]" value="manage_gallery" {{ in_array('manage_gallery', $perms) ? 'checked' : '' }} class="accent-blue-600 w-4 h-4">
                        <span class="text-sm font-medium text-slate-700">Manage Gallery</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 bg-slate-50 rounded border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="checkbox" name="permissions[]" value="manage_team" {{ in_array('manage_team', $perms) ? 'checked' : '' }} class="accent-blue-600 w-4 h-4">
                        <span class="text-sm font-medium text-slate-700">Manage Team Page</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 bg-slate-50 rounded border border-slate-200 opacity-60 cursor-not-allowed hidden">
                        <input type="checkbox" disabled class="accent-blue-600 w-4 h-4">
                        <span class="text-sm font-medium text-slate-700">Access CA Assistant Chat (Default for Staff)</span>
                    </label>
                </div>
            </div>
            @endif

            <div class="pt-4 flex gap-4">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-lg transition shadow-lg flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Update Profile
                </button>
                <a href="{{ route('manage.clients') }}" class="px-6 py-3.5 rounded-lg border border-slate-300 text-slate-600 font-bold hover:bg-slate-50 transition">
                    Cancel
                </a>
            </div>
        </form>

    </div>
</div>



@endsection