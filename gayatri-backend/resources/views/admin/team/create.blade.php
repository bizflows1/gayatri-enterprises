@extends('layouts.admin')

@section('content')
<!-- Cropper.js CDN dependencies -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<div class="max-w-4xl mx-auto space-y-6 animate-fade-in" x-data="teamMemberForm()">
    <!-- Header -->
    <div class="flex items-center gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <a href="{{ route('admin.team.index') }}" class="p-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-xl transition border border-slate-200" title="Go Back">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                Add Team Member Profile
            </h1>
            <p class="text-slate-500 text-sm mt-1">Configure and publish a professional staff profile on the public website.</p>
        </div>
    </div>

    <!-- Error Alerts -->
    @if($errors->any() || session('error'))
    <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl space-y-1">
        <div class="flex items-center gap-2 font-bold text-sm">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span>Please correct the errors:</span>
        </div>
        <ul class="list-disc pl-5 text-xs space-y-1 opacity-90">
            @if(session('error'))
                <li>{{ session('error') }}</li>
            @endif
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Form Panel -->
    <form action="{{ route('admin.team.store') }}" method="POST" @submit="if(!croppedImageBase64) { alert('Please upload and crop a profile photo first!'); $event.preventDefault(); return false; }" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8 space-y-6">
        @csrf
        <!-- Hidden base64 cropped image -->
        <input type="hidden" name="image" x-model="croppedImageBase64">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left: Avatar Image Selection and Preview -->
            <div class="flex flex-col items-center space-y-4">
                <label class="block text-sm font-bold text-slate-700 self-start md:self-center">Profile Photo <span class="text-red-500">*</span></label>
                
                <!-- Avatar Frame -->
                <div class="relative w-44 h-44 rounded-2xl overflow-hidden bg-slate-50 border-2 border-dashed border-slate-300 hover:border-blue-500 transition-all flex items-center justify-center cursor-pointer group shadow-sm"
                     @click="$refs.fileInput.click()">
                    
                    <!-- Fallback SVG Avatar -->
                    <div x-show="!croppedImageBase64" class="text-center p-4">
                        <svg class="w-12 h-12 text-slate-400 mx-auto group-hover:scale-110 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span class="text-[11px] text-slate-500 font-bold block mt-2 uppercase tracking-wide">Upload Photo <span class="text-red-500">*</span></span>
                        <span class="text-[9px] text-slate-400 block mt-0.5">(Square Recommended)</span>
                    </div>

                    <!-- Cropped Image Preview -->
                    <img x-show="croppedImageBase64" :src="croppedImageBase64" class="w-full h-full object-cover" style="display: none;">

                    <!-- Hover overlay for existing preview -->
                    <div x-show="croppedImageBase64" class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition duration-200 flex items-center justify-center" style="display: none;">
                        <span class="text-xs font-bold text-white uppercase tracking-wider">Change Photo</span>
                    </div>
                </div>

                <input type="file" x-ref="fileInput" class="hidden" accept="image/*" @change="handleFileSelect($event)">

                <button type="button" @click="$refs.fileInput.click()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Select Photo File
                </button>
            </div>

            <!-- Right: Field Details -->
            <div class="md:col-span-2 space-y-5">
                <!-- Name & Role -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name') }}" placeholder="e.g. Rakesh Sharma" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">
                    </div>
                    <div>
                        <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Designation / Role <span class="text-red-500">*</span></label>
                        <input type="text" id="role" name="role" required value="{{ old('role') }}" placeholder="e.g. Managing Partner" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">
                    </div>
                </div>

                <!-- Qualification Row -->
                <div>
                    <label for="qualification" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Qualifications <span class="text-red-500">*</span></label>
                    <input type="text" id="qualification" name="qualification" required value="{{ old('qualification') }}" placeholder="e.g. B.Com, FCA, DISA" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">
                </div>

                <!-- Custom Tags -->
                <div>
                    <label for="tags" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Expertise Areas / Tags</label>
                    <input type="text" id="tags" name="tags" value="{{ old('tags') }}" placeholder="e.g. Taxation, Audit, Corporate Law (comma separated)" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">
                    <p class="text-[10px] text-slate-500 mt-1.5">Separate multiple areas of practice using commas. These display as elegant highlight pills on their profile card.</p>
                </div>

                <!-- Biography -->
                <div>
                    <label for="bio" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Short Biography / Pitch</label>
                    <textarea id="bio" name="bio" rows="4" placeholder="Briefly introduce their background, years of practice, or domain achievements..." class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">{{ old('bio') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Form Footer Actions -->
        <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-4">
            <a href="{{ route('admin.team.index') }}" class="px-5 py-3 border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="btn-emerald text-white font-bold px-6 py-3 rounded-xl transition shadow shadow-emerald-500/25 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Add Team Profile
            </button>
        </div>
    </form>

    <!-- Crop Dialog Modal (Aspect Ratio Locked 1:1) -->
    <div x-show="cropModalOpen" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] animate-scale-up" @click.away="closeCropModal()">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-50 text-slate-800 flex items-center justify-between border-b border-slate-200">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 16h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Crop Profile Picture
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Adjust and align the frame into a perfect square profile photo.</p>
                </div>
                <button type="button" @click="closeCropModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Canvas Work Area -->
            <div class="flex-1 overflow-hidden p-6 bg-slate-100 flex items-center justify-center min-h-[300px]">
                <div class="max-w-full max-h-[50vh] overflow-hidden rounded-lg">
                    <img x-ref="cropImageElement" class="max-w-full block">
                </div>
            </div>

            <!-- Controls Footer -->
            <div class="px-6 py-4 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between border-t border-slate-200 gap-3">
                <div class="flex items-center gap-2">
                    <button type="button" @click="rotateImage(-90)" class="p-2.5 bg-white hover:bg-slate-100 text-slate-700 rounded-lg transition border border-slate-200" title="Rotate Left">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"></path></svg>
                    </button>
                    <button type="button" @click="rotateImage(90)" class="p-2.5 bg-white hover:bg-slate-100 text-slate-700 rounded-lg transition border border-slate-200" title="Rotate Right">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.934 12.8a1 1 0 000-1.6l-5.334-4A1 1 0 005 8v8a1 1 0 001.6.8l5.334-4zM19.934 12.8a1 1 0 000-1.6l-5.334-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.334-4z"></path></svg>
                    </button>
                </div>
                <div class="flex items-center gap-2.5">
                    <button type="button" @click="closeCropModal()" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg transition">
                        Cancel
                    </button>
                    <button type="button" @click="applyCrop()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5 shadow">
                        Apply & Crop
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function teamMemberForm() {
        return {
            croppedImageBase64: '',
            cropModalOpen: false,
            cropperInstance: null,

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (!file.type.match('image.*')) {
                    alert('Please select a valid image file.');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.openCropModal(e.target.result);
                };
                reader.readAsDataURL(file);
            },

            openCropModal(imgSrc) {
                this.cropModalOpen = true;
                
                // Set modal image element src
                const imgEl = this.$refs.cropImageElement;
                imgEl.src = imgSrc;

                // Re-init cropper on next tick when DOM is populated
                this.$nextTick(() => {
                    if (this.cropperInstance) {
                        this.cropperInstance.destroy();
                    }

                    this.cropperInstance = new Cropper(imgEl, {
                        aspectRatio: 1, // FORCE SQUARE ASPECT RATIO
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                    });
                });
            },

            rotateImage(deg) {
                if (this.cropperInstance) {
                    this.cropperInstance.rotate(deg);
                }
            },

            applyCrop() {
                if (!this.cropperInstance) return;

                // Grab cropped square canvas at high resolutions
                const canvas = this.cropperInstance.getCroppedCanvas({
                    width: 500, // Ideal square profile width
                    height: 500,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                if (canvas) {
                    // Export to highly compressed, optimized WebP string (~100KB-150KB)
                    this.croppedImageBase64 = canvas.toDataURL('image/webp', 0.85);
                }

                this.closeCropModal();
            },

            closeCropModal() {
                this.cropModalOpen = false;
                if (this.cropperInstance) {
                    this.cropperInstance.destroy();
                    this.cropperInstance = null;
                }
                // Clear file input value to allow selecting same file again if needed
                this.$refs.fileInput.value = '';
            }
        };
    }
</script>
@endsection
