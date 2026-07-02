@extends('layouts.admin')

@section('content')
<!-- Cropper.js CDN dependencies -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<div class="max-w-7xl mx-auto px-1 py-4 animate-fade-in" x-data="galleryManager()">
    <!-- Header Summary Block -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 brand-font flex items-center gap-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Dynamic Gallery Manager
            </h1>
            <p class="text-slate-500 text-sm mt-1">Upload, crop, rotate, and optimize public website photos.</p>
        </div>
        
        <!-- Live Statistics Counters -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
            <div class="px-4 py-2 border-r border-slate-200 text-center sm:text-left">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Total Photos</span>
                <span class="text-lg font-bold text-slate-700" x-text="imagesList.length"></span>
            </div>
            <div class="px-4 py-2 sm:border-r border-slate-200 text-center sm:text-left">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Format</span>
                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full mt-1 inline-block">WebP Optimized</span>
            </div>
            <div class="col-span-2 sm:col-span-1 px-4 py-2 text-center sm:text-left">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Target Weight</span>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full mt-1 inline-block">~100KB–150KB</span>
            </div>
        </div>
    </div>

    <!-- Drag & Drop Interactive Upload Box -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-8 transition hover:border-blue-400 duration-300">
        <div class="border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-xl p-8 text-center cursor-pointer transition relative"
             @dragover.prevent="dragOver = true"
             @dragleave.prevent="dragOver = false"
             @drop.prevent="handleFileDrop($event)"
             @click="$refs.fileInput.click()"
             :class="dragOver ? 'border-blue-500 bg-blue-50/50' : 'bg-slate-50/30'">
            
            <input type="file" x-ref="fileInput" class="hidden" accept="image/*" @change="handleFileSelect($event)">
            
            <div class="flex flex-col items-center justify-center gap-3">
                <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shadow-inner group">
                    <svg class="w-8 h-8 group-hover:scale-110 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-700">Drag & Drop office or event photos here</h3>
                    <p class="text-xs text-slate-400 mt-1">or click to browse your computer (all formats supported)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Gallery Grid -->
    <div class="mb-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h2 class="text-xl font-bold text-slate-800 brand-font flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Active Gallery Photos
            </h2>
            <span class="text-xs text-slate-500 bg-slate-100 border border-slate-200 px-3.5 py-1.5 rounded-full flex items-center gap-1.5 self-start">
                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3"></path></svg>
                Drag cards to sort/reorder live!
            </span>
        </div>
        
        <!-- Empty State inside Alpine -->
        <div x-show="imagesList.length === 0" class="text-center p-16 bg-white border border-slate-200 rounded-2xl" style="display: none;">
            <div class="w-16 h-16 mx-auto bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h4 class="font-bold text-slate-700">No photos in the gallery</h4>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Upload and edit images above to showcase them instantly on your public website.</p>
        </div>

        <!-- Dynamic Drag & Drop Card Grid -->
        <div x-show="imagesList.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="gallery-grid" style="display: none;">
            <template x-for="(img, idx) in imagesList" :key="img.filename">
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 relative group cursor-grab active:cursor-grabbing hover:border-blue-400"
                     draggable="true"
                     @dragstart="dragStart(idx)"
                     @dragover="dragOverCard($event)"
                     @drop="dropCard(idx)"
                     :class="draggingIndex === idx ? 'opacity-30 border-blue-500 bg-blue-50/10 scale-95' : ''">
                    
                    <div class="aspect-[4/3] bg-slate-900 flex items-center justify-center overflow-hidden relative select-none pointer-events-none">
                        <img :src="img.url" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                        
                        <!-- Actions Overlay (Re-enabled via pointer-events-auto) -->
                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center gap-3 pointer-events-auto">
                            <button type="button" @click="openZoom(img.url, img.filename)" class="p-2.5 bg-white text-slate-700 hover:text-blue-600 rounded-full shadow-lg hover:scale-110 transition duration-200" title="View Fullscreen">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                            </button>
                            <button type="button" @click="confirmDelete(img.filename)" class="p-2.5 bg-red-600 hover:bg-red-700 text-white rounded-full shadow-lg hover:scale-110 transition duration-200" title="Delete Photo">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-16v1a3 3 0 003 3h10M9 4h6m4 0a1 1 0 00-1-1H6a1 1 0 00-1 1M14 11v6"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-medium select-none">
                        <span class="truncate pr-2" x-text="img.filename"></span>
                        <span class="shrink-0 bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded font-bold" x-text="img.size + ' KB'"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Creative Image Editor Modal (Cropper, Rotate & Auto-Correction Filters) -->
    <div x-show="editorOpen" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-md" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-5xl h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-fade-in" @click.away="closeEditor()">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-lg font-bold brand-font">Dynamic Photo Adjustments</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Crop, rotate, and enhance your photo prior to system optimization & WebP export.</p>
                </div>
                <button type="button" @click="closeEditor()" class="text-slate-400 hover:text-white transition p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Content (Split-screen on desktop) -->
            <div class="flex-1 overflow-hidden flex flex-col md:flex-row min-h-0 bg-slate-50">
                <!-- Left: Interactive Canvas Workspace -->
                <div class="flex-1 p-6 flex flex-col min-h-0">
                    <div class="flex-1 bg-slate-900 rounded-xl relative flex items-center justify-center p-4 overflow-hidden border border-slate-800 shadow-inner">
                        <img x-ref="editImg" :src="editorImgSrc" class="max-w-full max-h-full object-contain" style="display: block;">
                    </div>
                    
                    <!-- Basic Editor Action Toolbar -->
                    <div class="flex flex-wrap items-center justify-between gap-4 mt-4 shrink-0 bg-white p-3 rounded-xl border border-slate-200">
                        <!-- Rotate controls -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-2">Rotate</span>
                            <button type="button" @click="rotateLeft()" class="p-2 rounded bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-600 transition flex items-center gap-1.5 text-xs font-bold" title="Rotate 90° Left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a10 10 0 11-1.3-4.9"></path></svg>
                                90° CCW
                            </button>
                            <button type="button" @click="rotateRight()" class="p-2 rounded bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-600 transition flex items-center gap-1.5 text-xs font-bold" title="Rotate 90° Right">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.934 12.8a1 1 0 000-1.6l-5.334-4A1 1 0 005 8v8a1 1 0 001.6.8l5.334-4z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a10 10 0 101.3-4.9"></path></svg>
                                90° CW
                            </button>
                        </div>

                        <!-- Crop Aspect Ratio Selectors -->
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-2">Aspect Ratio</span>
                            <button type="button" @click="setAspectRatio(null)" class="px-2.5 py-1.5 rounded text-xs font-bold transition" :class="aspectRatio === null ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'">Free</button>
                            <button type="button" @click="setAspectRatio(1)" class="px-2.5 py-1.5 rounded text-xs font-bold transition" :class="aspectRatio === 1 ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'">1:1 (Square)</button>
                            <button type="button" @click="setAspectRatio(4/3)" class="px-2.5 py-1.5 rounded text-xs font-bold transition" :class="aspectRatio === 4/3 ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'">4:3 (Office)</button>
                            <button type="button" @click="setAspectRatio(16/9)" class="px-2.5 py-1.5 rounded text-xs font-bold transition" :class="aspectRatio === 16/9 ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'">16:9 (Landscape)</button>
                        </div>
                    </div>
                </div>

                <!-- Right: AI Magic & Manual Filter Tuning Sidebar -->
                <div class="w-full md:w-80 p-6 border-t md:border-t-0 md:border-l border-slate-200 flex flex-col min-h-0 bg-white">
                    <div class="flex-1 overflow-y-auto pr-1 space-y-6 scrollbar-hide">
                        <!-- Smart Auto-Correction Engine Box -->
                        @php
                            $isImageEditorEnabled = env('AI_IMAGE_EDITOR_ENABLED', true);
                        @endphp
                        <div class="relative {{ !$isImageEditorEnabled ? 'opacity-50 grayscale pointer-events-none' : '' }}">
                            @if(!$isImageEditorEnabled)
                                <div class="absolute inset-0 bg-slate-100/10 backdrop-blur-[1px] rounded-xl flex items-center justify-center z-20 pointer-events-auto cursor-not-allowed">
                                    <div class="bg-slate-900/90 text-white text-[10px] uppercase font-bold tracking-widest px-2.5 py-1 rounded-full shadow-lg flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        AI Feature Disabled
                                    </div>
                                </div>
                            @endif
                            <label class="block text-xs font-bold text-blue-600 uppercase tracking-widest mb-2 flex items-center gap-1">
                                Smart Auto-Correction
                            </label>
                            <div class="relative bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border border-blue-100">
                                <p class="text-[11px] text-slate-500 leading-relaxed mb-3">Enter the visual style or adjustments you want to achieve, and the system will automatically configure matching sliders.</p>
                                <div class="flex gap-2">
                                    <input type="text" x-model="aiPrompt" @keydown.enter.prevent="applyAiFilters()" placeholder="e.g. fix lighting, warm vintage, cinematic b&w" class="w-full bg-white border border-slate-200 rounded-lg text-xs p-2.5 outline-none focus:ring-2 focus:ring-blue-500 transition" :disabled="{{ !$isImageEditorEnabled ? 'true' : 'false' }}">
                                    <button type="button" @click="applyAiFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-3.5 rounded-lg font-bold transition-all shrink-0 flex items-center justify-center hover:scale-105" :disabled="aiLoading || {{ !$isImageEditorEnabled ? 'true' : 'false' }}">
                                        <template x-if="!aiLoading">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </template>
                                        <template x-if="aiLoading">
                                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Filter Sliders (Real-time Canvas feedback) -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Manual Adjustment Sliders</span>
                                <button type="button" @click="resetSliders()" class="text-[10px] text-blue-500 hover:underline font-bold">Reset All</button>
                            </div>
                            
                            <div class="space-y-4">
                                <!-- Brightness -->
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                        <span class="text-slate-600">Brightness</span>
                                        <span class="text-slate-400 text-[10px] font-bold" x-text="filters.brightness + '%'"></span>
                                    </div>
                                    <input type="range" min="50" max="150" x-model="filters.brightness" @input="updatePreview()" class="w-full accent-blue-600 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                                </div>

                                <!-- Contrast -->
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                        <span class="text-slate-600">Contrast</span>
                                        <span class="text-slate-400 text-[10px] font-bold" x-text="filters.contrast + '%'"></span>
                                    </div>
                                    <input type="range" min="50" max="150" x-model="filters.contrast" @input="updatePreview()" class="w-full accent-blue-600 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                                </div>

                                <!-- Saturation -->
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                        <span class="text-slate-600">Saturation</span>
                                        <span class="text-slate-400 text-[10px] font-bold" x-text="filters.saturate + '%'"></span>
                                    </div>
                                    <input type="range" min="0" max="200" x-model="filters.saturate" @input="updatePreview()" class="w-full accent-blue-600 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                                </div>

                                <!-- Sepia -->
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                        <span class="text-slate-600">Warm Sepia</span>
                                        <span class="text-slate-400 text-[10px] font-bold" x-text="filters.sepia + '%'"></span>
                                    </div>
                                    <input type="range" min="0" max="100" x-model="filters.sepia" @input="updatePreview()" class="w-full accent-blue-600 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                                </div>

                                <!-- Grayscale -->
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                        <span class="text-slate-600">Grayscale</span>
                                        <span class="text-slate-400 text-[10px] font-bold" x-text="filters.grayscale + '%'"></span>
                                    </div>
                                    <input type="range" min="0" max="100" x-model="filters.grayscale" @input="updatePreview()" class="w-full accent-blue-600 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                                </div>

                                <!-- Invert -->
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                        <span class="text-slate-600">Invert Colours</span>
                                        <span class="text-slate-400 text-[10px] font-bold" x-text="filters.invert + '%'"></span>
                                    </div>
                                    <input type="range" min="0" max="100" x-model="filters.invert" @input="updatePreview()" class="w-full accent-blue-600 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                                </div>

                                <!-- Hue rotate -->
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                        <span class="text-slate-600">Hue Color-Shift</span>
                                        <span class="text-slate-400 text-[10px] font-bold" x-text="filters.hue_rotate + '°'"></span>
                                    </div>
                                    <input type="range" min="0" max="360" x-model="filters.hue_rotate" @input="updatePreview()" class="w-full accent-blue-600 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                                </div>

                                <!-- Blur -->
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                        <span class="text-slate-600">Soft Focus (Blur)</span>
                                        <span class="text-slate-400 text-[10px] font-bold" x-text="filters.blur + 'px'"></span>
                                    </div>
                                    <input type="range" min="0" max="10" x-model="filters.blur" @input="updatePreview()" class="w-full accent-blue-600 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm and Export Area -->
                    <div class="pt-4 border-t border-slate-100 shrink-0 space-y-2">
                        <button type="button" @click="saveAndUpload()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition shadow-lg flex justify-center items-center gap-2" :disabled="uploading">
                            <template x-if="!uploading">
                                <span class="flex items-center gap-2">
                                    Apply & Compress Upload
                                </span>
                            </template>
                            <template x-if="uploading">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    Compressing into WebP...
                                </span>
                            </template>
                        </button>
                        <button type="button" @click="closeEditor()" class="w-full border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold py-2.5 rounded-xl text-xs transition">
                            Discard Edits
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox Zoom Modal (Esc key to close) -->
    <div x-show="zoomOpen" 
         @keydown.window.escape="closeZoom()" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-md" 
         style="display: none;" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <div class="relative max-w-5xl max-h-[90vh] flex flex-col items-center justify-center" @click.away="closeZoom()">
            <!-- Zoomed Image Container -->
            <div class="relative bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden p-2 shadow-2xl">
                <img :src="zoomSrc" class="max-w-full max-h-[80vh] object-contain rounded-lg" :alt="zoomFilename">
                
                <!-- Close Floating Button -->
                <button type="button" @click="closeZoom()" class="absolute top-4 right-4 bg-slate-950/70 hover:bg-red-600 text-white p-2.5 rounded-full transition shadow-lg border border-slate-800" title="Close (Esc)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Image Title Caption -->
            <div class="text-center mt-3 text-sm text-slate-400 font-medium select-none">
                <span x-text="zoomFilename"></span>
                <span class="text-slate-600 mx-2">|</span>
                <span class="text-xs bg-slate-800 px-2 py-1 rounded text-slate-500 font-semibold border border-slate-700/50">Esc to Close</span>
            </div>
        </div>
    </div>
</div>

<script>
    function galleryManager() {
        return {
            imagesList: @json($images),
            draggingIndex: null,
            zoomOpen: false,
            zoomSrc: '',
            zoomFilename: '',

            dragOver: false,
            editorOpen: false,
            uploading: false,
            aiLoading: false,
            editorImgSrc: '',
            aiPrompt: '',
            aspectRatio: null,
            cropperInstance: null,
            
            // Default filter settings
            filters: {
                brightness: 100,
                contrast: 100,
                saturate: 100,
                sepia: 0,
                grayscale: 0,
                invert: 0,
                hue_rotate: 0,
                blur: 0
            },

            // Native Drag and Drop logic
            dragStart(idx) {
                this.draggingIndex = idx;
            },

            dragOverCard(event) {
                event.preventDefault();
            },

            dropCard(idx) {
                if (this.draggingIndex === null || this.draggingIndex === idx) return;

                // Move items inside current array reactively
                const dragItem = this.imagesList[this.draggingIndex];
                this.imagesList.splice(this.draggingIndex, 1);
                this.imagesList.splice(idx, 0, dragItem);

                this.draggingIndex = null;

                // Save sorted state directly to backend
                this.saveOrder();
            },

            saveOrder() {
                const fileOrder = this.imagesList.map(img => img.filename);

                fetch('{{ route("admin.gallery.reorder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: fileOrder })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert('Failed to save sorted order: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(err => {
                    console.error('Order save error:', err);
                });
            },

            // Image zoomed lightbox methods
            openZoom(url, filename) {
                this.zoomSrc = url;
                this.zoomFilename = filename;
                this.zoomOpen = true;
            },

            closeZoom() {
                this.zoomOpen = false;
                this.zoomSrc = '';
                this.zoomFilename = '';
            },

            /**
             * Initialize the drag-and-drop or select uploader.
             */
            handleFileSelect(event) {
                const files = event.target.files;
                if (files.length > 0) {
                    this.initEditor(files[0]);
                }
            },

            handleFileDrop(event) {
                this.dragOver = false;
                const files = event.dataTransfer.files;
                if (files.length > 0) {
                    this.initEditor(files[0]);
                }
            },

            /**
             * Open Editor with the selected image loaded into CropperJS.
             */
            initEditor(file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.editorImgSrc = e.target.result;
                    this.resetSliders();
                    this.editorOpen = true;
                    
                    // Allow DOM update, then mount Cropper
                    setTimeout(() => {
                        this.destroyCropper();
                        this.cropperInstance = new Cropper(this.$refs.editImg, {
                            aspectRatio: this.aspectRatio,
                            viewMode: 1,
                            autoCropArea: 1,
                            ready: () => {
                                this.updatePreview();
                            }
                        });
                    }, 100);
                };
                reader.readAsDataURL(file);
            },

            setAspectRatio(ratio) {
                this.aspectRatio = ratio;
                if (this.cropperInstance) {
                    this.cropperInstance.setAspectRatio(ratio === null ? NaN : ratio);
                }
            },

            rotateLeft() {
                if (this.cropperInstance) {
                    this.cropperInstance.rotate(-90);
                }
            },

            rotateRight() {
                if (this.cropperInstance) {
                    this.cropperInstance.rotate(90);
                }
            },

            /**
             * Updates the Cropper Canvas CSS preview with live sliders / AI filter values.
             */
            updatePreview() {
                const filterString = `
                    brightness(${this.filters.brightness}%) 
                    contrast(${this.filters.contrast}%) 
                    saturate(${this.filters.saturate}%) 
                    sepia(${this.filters.sepia}%) 
                    grayscale(${this.filters.grayscale}%) 
                    invert(${this.filters.invert}%) 
                    hue-rotate(${this.filters.hue_rotate}deg) 
                    blur(${this.filters.blur}px)
                `;
                
                // Query cropper elements to apply live styling filter to visual DOM
                const cropperCanvas = document.querySelector('.cropper-container .cropper-canvas');
                const cropperViewbox = document.querySelector('.cropper-container .cropper-view-box img');
                
                if (cropperCanvas) cropperCanvas.style.filter = filterString;
                if (cropperViewbox) cropperViewbox.style.filter = filterString;
            },

            resetSliders() {
                this.filters = {
                    brightness: 100,
                    contrast: 100,
                    saturate: 100,
                    sepia: 0,
                    grayscale: 0,
                    invert: 0,
                    hue_rotate: 0,
                    blur: 0
                };
                this.aiPrompt = '';
                this.updatePreview();
            },

            /**
             * Auto-Correction Filter Parameter Fetch Engine
             */
            applyAiFilters() {
                if (!this.aiPrompt.trim()) return;
                this.aiLoading = true;
                
                fetch('{{ route("admin.gallery.ai_adjust") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ prompt: this.aiPrompt })
                })
                .then(res => res.json())
                .then(data => {
                    this.aiLoading = false;
                    if (data.success && data.filters) {
                        // Apply calculated attributes
                        this.filters = { ...this.filters, ...data.filters };
                        this.updatePreview();
                    } else if (data.error) {
                        alert(data.error);
                    } else {
                        alert('Could not apply corrections. Please try another visual adjustment prompt.');
                    }
                })
                .catch(err => {
                    this.aiLoading = false;
                    console.error('AI Filters Adjust Error:', err);
                    alert('Process failed to invoke AI filter adjustment.');
                });
            },

            /**
             * Heavy on-the-fly client processing:
             * 1. Get Cropped pixel bounds from Cropper.js.
             * 2. Paint onto a dynamic `<canvas>`.
             * 3. Apply the CSS/Canvas adjustments instantly to pixel streams.
             * 4. Downscale & Compress into a WebP blob (~100KB).
             * 5. Upload base64 encoded payload to AdminGalleryController.
             */
            saveAndUpload() {
                if (!this.cropperInstance || this.uploading) return;
                this.uploading = true;

                // Get high-res cropped HTML5 canvas element from Cropper.js
                const canvas = this.cropperInstance.getCroppedCanvas({
                    maxWidth: 1200, // Safe bounding box to prevent gigantic slow raw files
                    maxHeight: 900,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                if (!canvas) {
                    this.uploading = false;
                    alert('Failed to crop canvas.');
                    return;
                }

                // Create offscreen adjustment canvas to apply pixel adjustments
                const offscreenCanvas = document.createElement('canvas');
                offscreenCanvas.width = canvas.width;
                offscreenCanvas.height = canvas.height;
                const ctx = offscreenCanvas.getContext('2d');

                // Apply active filters directly to the Canvas 2D context
                ctx.filter = `
                    brightness(${this.filters.brightness}%) 
                    contrast(${this.filters.contrast}%) 
                    saturate(${this.filters.saturate}%) 
                    sepia(${this.filters.sepia}%) 
                    grayscale(${this.filters.grayscale}%) 
                    invert(${this.filters.invert}%) 
                    hue-rotate(${this.filters.hue_rotate}deg) 
                    blur(${this.filters.blur}px)
                `;
                
                // Paint the cropped visual elements onto the adjusted canvas
                ctx.drawImage(canvas, 0, 0, canvas.width, canvas.height);

                // Export to high-quality compressed WebP data URI
                const base64Data = offscreenCanvas.toDataURL('image/webp', 0.85);

                // Call AJAX upload
                fetch('{{ route("admin.gallery.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ image: base64Data })
                })
                .then(res => res.json())
                .then(data => {
                    this.uploading = false;
                    if (data.success) {
                        this.closeEditor();
                        // Reload page to show beautiful new entry in live masonry grid
                        window.location.reload();
                    } else if (data.error) {
                        alert(data.error);
                    }
                })
                .catch(err => {
                    this.uploading = false;
                    console.error('Save and Upload Error:', err);
                    alert('Connection issue saving the image.');
                });
            },

            confirmDelete(filename) {
                if (confirm('Are you completely sure you want to permanently delete this gallery image? This will remove it from the public website immediately.')) {
                    fetch('{{ route("admin.gallery.destroy") }}', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ filename: filename })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Filter reactively
                            this.imagesList = this.imagesList.filter(img => img.filename !== filename);
                        } else {
                            alert(data.error || 'Failed to delete photo.');
                        }
                    })
                    .catch(err => {
                        console.error('Delete photo error:', err);
                        alert('Connection error. Failed to delete.');
                    });
                }
            },

            closeEditor() {
                this.editorOpen = false;
                this.destroyCropper();
            },

            destroyCropper() {
                if (this.cropperInstance) {
                    this.cropperInstance.destroy();
                    this.cropperInstance = null;
                }
            }
        };
    }
</script>
@endsection
