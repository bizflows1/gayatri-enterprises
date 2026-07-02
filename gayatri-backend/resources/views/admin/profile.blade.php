@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-slate-200">
    <h2 class="text-2xl font-bold mb-6 brand-font">{{ ucfirst(auth()->user()->role) }} Profile Settings</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm font-bold animate-fade-in">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-4 text-xs font-bold space-y-1 animate-fade-in">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <!-- Profile Photo Row -->
        <div class="flex items-center gap-6 p-4 bg-slate-50 rounded-2xl border border-slate-100 mb-6" x-data="{ hasPhoto: {{ $user->profile_photo ? 'true' : 'false' }}, removePhotoFlag: false }">
            <div class="relative group shrink-0">
                <img :src="removePhotoFlag ? 'https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff&bold=true' : '{{ $user->avatar_url }}'" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md transition group-hover:opacity-75" id="avatar-preview">
                <label for="profile_photo" class="absolute inset-0 flex items-center justify-center cursor-pointer opacity-0 group-hover:opacity-100 transition">
                    <span class="bg-black/50 text-white p-2 rounded-full text-xs">Edit</span>
                </label>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">Profile Photo</h3>
                <p class="text-xs text-slate-500 mb-3" x-text="removePhotoFlag ? 'Photo scheduled for removal' : 'Upload a professional photo (JPG, PNG, Max 2MB)'"></p>
                
                <input type="hidden" name="remove_photo" :value="removePhotoFlag ? '1' : '0'">
                <input type="file" name="profile_photo" id="profile_photo" class="hidden" @change="removePhotoFlag = false; hasPhoto = true; document.getElementById('avatar-preview').src = window.URL.createObjectURL($event.target.files[0])">
                
                <div class="flex items-center gap-2">
                    <label for="profile_photo" class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded-lg text-xs font-bold cursor-pointer hover:bg-slate-50 transition shadow-sm">
                        Choose New Photo
                    </label>
                    
                    <button type="button" x-show="hasPhoto && !removePhotoFlag" @click="removePhotoFlag = true; hasPhoto = false; document.getElementById('profile_photo').value = ''" class="bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-lg text-xs font-bold hover:bg-red-100 transition shadow-sm">
                        Remove Photo
                    </button>
                    
                    <button type="button" x-show="removePhotoFlag" @click="removePhotoFlag = false; hasPhoto = {{ $user->profile_photo ? 'true' : 'false' }}" style="display: none;" class="bg-slate-100 text-slate-700 border border-slate-200 px-4 py-2 rounded-lg text-xs font-bold hover:bg-slate-200 transition shadow-sm">
                        Undo Removal
                    </button>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">My Name</label>
            <input type="text" name="name" value="{{ $user->name }}" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Phone</label>
                <input type="tel" name="phone" value="{{ $user->phone }}" class="w-full border border-slate-300 rounded-lg p-2.5">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ $user->email }}" class="w-full border border-slate-300 rounded-lg p-2.5">
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100" x-data="{ showPass: false }">
            <h3 class="text-sm font-bold text-purple-600 mb-3">Change Password</h3>
            <div class="relative">
                <input :type="showPass ? 'text' : 'password'" name="password" autocomplete="new-password" placeholder="Enter new password (optional)" class="w-full border border-slate-300 rounded-lg pl-4 pr-11 p-2.5 outline-none focus:ring-2 focus:ring-blue-500 transition shadow-sm">
                <button type="button" @click="showPass = !showPass" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                    <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="showPass" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.12-5.4M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a10.024 10.024 0 01-4.12 5.4m-1.28-1.28A3.001 3.001 0 1111.306 11.3" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" /></svg>
                </button>
            </div>
            <p class="text-xs text-slate-400 mt-1">Leave empty if you don't want to change it.</p>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg mt-2 shadow-md transition">
            Save Changes
        </button>
    </form>
</div>
@endsection