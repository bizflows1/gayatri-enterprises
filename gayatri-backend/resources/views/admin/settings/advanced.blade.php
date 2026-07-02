@extends('layouts.admin')

@section('content')
<div class="h-full overflow-y-auto p-6" x-data="advancedSettings()">
    <!-- Header -->
    <div class="mb-8">
        <span class="text-xs uppercase font-bold tracking-widest text-indigo-600">Administrative Hub</span>
        <h1 class="text-3xl font-extrabold text-slate-900 brand-font mt-1">Advanced Portal Settings</h1>
        <p class="text-sm text-slate-500 mt-1">Control platform-wide AI features, secure API integrations, storage migrations, and cloud backups.</p>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 shadow-sm animate-fade-in">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-sm font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 shadow-sm animate-fade-in">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-sm font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-8 max-w-5xl" autocomplete="off">
        @csrf
        <!-- Dummy inputs to prevent browser autofill -->
        <input type="text" style="display:none" autocomplete="off" />
        <input type="password" style="display:none" autocomplete="new-password" />

        <!-- 1. AI SUITE TOGGLES -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                AI Suite & Feature Control
            </h2>
            <p class="text-xs text-slate-500 mb-6">Toggle specific AI agents and utilities. Disabled features will be greyed out gracefully across the administrative dashboard while remaining secure.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Toggle 1: AI Image Editor -->
                <label class="flex items-start justify-between p-4 rounded-2xl border transition cursor-pointer group"
                       :class="settings.ai_image_editor ? 'border-indigo-200 bg-indigo-50/20 hover:bg-indigo-50/40' : 'border-slate-200 bg-white hover:bg-slate-50'">
                    <div class="flex gap-3">
                        <div class="p-2.5 rounded-xl text-indigo-600" :class="settings.ai_image_editor ? 'bg-indigo-100' : 'bg-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold text-slate-800 text-sm block">AI Image Editor</span>
                            <span class="text-xs text-slate-500">Gemini-assisted contrast and resolution slider adjustments.</span>
                        </div>
                    </div>
                    <div class="relative flex items-center">
                        <input type="checkbox" name="ai_image_editor" value="1" class="sr-only" x-model="settings.ai_image_editor">
                        <div class="w-10 h-6 bg-slate-200 rounded-full transition-colors" :class="settings.ai_image_editor ? 'bg-indigo-600' : 'bg-slate-200'"></div>
                        <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform" :class="settings.ai_image_editor ? 'translate-x-4' : 'translate-x-0'"></div>
                    </div>
                </label>

                <!-- Toggle 2: AI Acts -->
                <label class="flex items-start justify-between p-4 rounded-2xl border transition cursor-pointer group"
                       :class="settings.ai_acts ? 'border-indigo-200 bg-indigo-50/20 hover:bg-indigo-50/40' : 'border-slate-200 bg-white hover:bg-slate-50'">
                    <div class="flex gap-3">
                        <div class="p-2.5 rounded-xl text-indigo-600" :class="settings.ai_acts ? 'bg-indigo-100' : 'bg-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold text-slate-800 text-sm block">AI Acts Curation Hub</span>
                            <span class="text-xs text-slate-500">Draft legal structures, tax chapters, and statutory summaries automatically.</span>
                        </div>
                    </div>
                    <div class="relative flex items-center">
                        <input type="checkbox" name="ai_acts" value="1" class="sr-only" x-model="settings.ai_acts">
                        <div class="w-10 h-6 bg-slate-200 rounded-full transition-colors" :class="settings.ai_acts ? 'bg-indigo-600' : 'bg-slate-200'"></div>
                        <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform" :class="settings.ai_acts ? 'translate-x-4' : 'translate-x-0'"></div>
                    </div>
                </label>

                <!-- Toggle 3: AI Services -->
                <label class="flex items-start justify-between p-4 rounded-2xl border transition cursor-pointer group"
                       :class="settings.ai_services ? 'border-indigo-200 bg-indigo-50/20 hover:bg-indigo-50/40' : 'border-slate-200 bg-white hover:bg-slate-50'">
                    <div class="flex gap-3">
                        <div class="p-2.5 rounded-xl text-indigo-600" :class="settings.ai_services ? 'bg-indigo-100' : 'bg-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold text-slate-800 text-sm block">AI Services Copywriter</span>
                            <span class="text-xs text-slate-500">Automate sales descriptions and professional benefits drafting.</span>
                        </div>
                    </div>
                    <div class="relative flex items-center">
                        <input type="checkbox" name="ai_services" value="1" class="sr-only" x-model="settings.ai_services">
                        <div class="w-10 h-6 bg-slate-200 rounded-full transition-colors" :class="settings.ai_services ? 'bg-indigo-600' : 'bg-slate-200'"></div>
                        <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform" :class="settings.ai_services ? 'translate-x-4' : 'translate-x-0'"></div>
                    </div>
                </label>

                <!-- Toggle 4: AI Assistant -->
                <label class="flex items-start justify-between p-4 rounded-2xl border transition cursor-pointer group"
                       :class="settings.ai_assistant ? 'border-indigo-200 bg-indigo-50/20 hover:bg-indigo-50/40' : 'border-slate-200 bg-white hover:bg-slate-50'">
                    <div class="flex gap-3">
                        <div class="p-2.5 rounded-xl text-indigo-600" :class="settings.ai_assistant ? 'bg-indigo-100' : 'bg-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold text-slate-800 text-sm block">CA AI Assistant</span>
                            <span class="text-xs text-slate-500">Intelligent chat helper for statutory compliances and client notifications.</span>
                        </div>
                    </div>
                    <div class="relative flex items-center">
                        <input type="checkbox" name="ai_assistant" value="1" class="sr-only" x-model="settings.ai_assistant">
                        <div class="w-10 h-6 bg-slate-200 rounded-full transition-colors" :class="settings.ai_assistant ? 'bg-indigo-600' : 'bg-slate-200'"></div>
                        <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform" :class="settings.ai_assistant ? 'translate-x-4' : 'translate-x-0'"></div>
                    </div>
                </label>
            </div>
        </div>

        <!-- 2. GEMINI ENGINE CREDENTIALS -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
                Gemini LLM Engine Keys
            </h2>
            <p class="text-xs text-slate-500 mb-6">Set up your Google Gemini AI engine credentials safely. Ensure the key has permissions to access Gemini Pro APIs.</p>

            <div class="max-w-xl">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Gemini API Key</label>
                <div class="relative rounded-xl shadow-sm">
                    <input :type="showGeminiKey ? 'text' : 'password'" 
                           name="gemini_api_key" 
                           x-model="settings.gemini_api_key"
                           class="block w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono placeholder-slate-400"
                           placeholder="AIzaSy...">
                    <button type="button" 
                            @click="showGeminiKey = !showGeminiKey" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!showGeminiKey">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="showGeminiKey" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. STORAGE MIGRATION HUB -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-teal-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                Core Storage Infrastructure Migration
            </h2>
            <p class="text-xs text-slate-500 mb-6">Switch file storage dynamically from local Hostinger SSD drives to fully secure, fast Wasabi Cloud S3 compatible buckets.</p>

            <!-- Storage Selection Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Hostinger -->
                <div class="p-5 rounded-2xl border transition cursor-pointer flex justify-between items-start"
                     :class="settings.filesystem_disk === 'public' ? 'border-teal-500 bg-teal-50/10' : 'border-slate-200'"
                     @click="settings.filesystem_disk = 'public'">
                    <div class="flex gap-3">
                        <div class="p-2.5 rounded-xl text-teal-600 bg-teal-100" :class="settings.filesystem_disk === 'public' ? 'bg-teal-100 text-teal-600' : 'bg-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold text-slate-800 text-sm block">Hostinger Local Disk</span>
                            <span class="text-xs text-slate-500">Stores all document attachments and images on local Webserver drives.</span>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition"
                         :class="settings.filesystem_disk === 'public' ? 'border-teal-500 bg-teal-500' : 'border-slate-300'">
                        <div class="w-2 h-2 bg-white rounded-full" x-show="settings.filesystem_disk === 'public'"></div>
                    </div>
                </div>

                <!-- Wasabi S3 -->
                <div class="p-5 rounded-2xl border transition cursor-pointer flex justify-between items-start"
                     :class="settings.filesystem_disk === 'wasabi' ? 'border-teal-500 bg-teal-50/10' : 'border-slate-200'"
                     @click="settings.filesystem_disk = 'wasabi'">
                    <div class="flex gap-3">
                        <div class="p-2.5 rounded-xl text-teal-600 bg-teal-100" :class="settings.filesystem_disk === 'wasabi' ? 'bg-teal-100 text-teal-600' : 'bg-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold text-slate-800 text-sm block">Wasabi Cloud S3</span>
                            <span class="text-xs text-slate-500">Enable premium off-site storage. Fast, highly scalable, and extremely secure object storage.</span>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition"
                         :class="settings.filesystem_disk === 'wasabi' ? 'border-teal-500 bg-teal-500' : 'border-slate-300'">
                        <div class="w-2 h-2 bg-white rounded-full" x-show="settings.filesystem_disk === 'wasabi'"></div>
                    </div>
                </div>
            </div>

            <!-- Hidden radio input supporting native POST -->
            <input type="hidden" name="filesystem_disk" :value="settings.filesystem_disk">

            <!-- Wasabi S3 Parameter Inputs -->
            <div class="p-6 bg-slate-50/80 rounded-2xl border border-slate-100 space-y-4" 
                 x-show="settings.filesystem_disk === 'wasabi'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Wasabi Access Key ID <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="wasabi_key" 
                               x-model="settings.wasabi_key"
                               class="w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl focus:ring-teal-500 focus:border-teal-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Wasabi Secret Access Key <span class="text-red-500">*</span></label>
                        <input type="password" 
                               name="wasabi_secret" 
                               x-model="settings.wasabi_secret"
                               class="w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl focus:ring-teal-500 focus:border-teal-500 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Wasabi Default Region <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="wasabi_region" 
                               x-model="settings.wasabi_region"
                               class="w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl focus:ring-teal-500 focus:border-teal-500 text-sm" 
                               placeholder="ap-southeast-1">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Wasabi Bucket Name <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="wasabi_bucket" 
                               x-model="settings.wasabi_bucket"
                               class="w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl focus:ring-teal-500 focus:border-teal-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Wasabi API Endpoint <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="wasabi_endpoint" 
                               x-model="settings.wasabi_endpoint"
                               class="w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl focus:ring-teal-500 focus:border-teal-500 text-sm" 
                               placeholder="https://s3.ap-southeast-1.wasabisys.com">
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. ONEDRIVE CLOUD BACKUP -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex items-start justify-between mb-2">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                    </svg>
                    OneDrive Cloud Backup Curation
                </h2>
                <!-- Active Toggle -->
                <div class="relative flex items-center">
                    <input type="checkbox" name="onedrive_enabled" value="1" class="sr-only" x-model="settings.onedrive_enabled">
                    <div class="w-10 h-6 bg-slate-200 rounded-full transition-colors" :class="settings.onedrive_enabled ? 'bg-blue-600' : 'bg-slate-200'"></div>
                    <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform" :class="settings.onedrive_enabled ? 'translate-x-4' : 'translate-x-0'"></div>
                </div>
            </div>
            <p class="text-xs text-slate-500 mb-6">Schedule and secure daily automatic backups of statutory documents, GST logs, and clients' files directly into Microsoft OneDrive.</p>

            <!-- OneDrive API Configuration Parameters -->
            <div class="p-6 bg-slate-50/80 rounded-2xl border border-slate-100 space-y-4" 
                 x-show="settings.onedrive_enabled"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Microsoft App Client ID <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="onedrive_client_id" 
                               x-model="settings.onedrive_client_id"
                               class="w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Microsoft App Client Secret <span class="text-red-500">*</span></label>
                        <input type="password" 
                               name="onedrive_client_secret" 
                               x-model="settings.onedrive_client_secret"
                               class="w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Azure Tenant ID <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="onedrive_tenant_id" 
                               x-model="settings.onedrive_tenant_id"
                               class="w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm" 
                               placeholder="common">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">OAuth Redirect URI <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="onedrive_redirect_uri" 
                               x-model="settings.onedrive_redirect_uri"
                               class="w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm" 
                               placeholder="https://gayatrient.com/admin/backup/onedrive/callback">
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('admin.dashboard') }}" class="px-5 py-3 border border-slate-200 text-slate-600 rounded-2xl hover:bg-slate-50 text-sm font-bold transition">
                Discard Changes
            </a>
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 text-sm font-bold shadow-md shadow-indigo-600/10 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                Save Advanced Settings
            </button>
        </div>
    </form>
</div>

<script>
function advancedSettings() {
    return {
        showGeminiKey: false,
        settings: @json($settings)
    }
}
</script>
@endsection
