@extends('layouts.app')

@section('title', 'My Dashboard - Gayatri Enterprises')

@section('content')

<div class="bg-slate-50 min-h-screen pb-12">
    
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 pt-16 pb-32 px-4 brand-gradient">
        <!-- Decorative Background Blobs -->
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="animate-fade-in">
                    <nav class="flex mb-4 text-blue-400 text-xs font-bold tracking-widest uppercase">
                        <span class="opacity-50">Secure Client Portal</span>
                        <span class="mx-2 opacity-30">/</span>
                        <span>Overview</span>
                    </nav>
                    <h1 class="text-4xl md:text-5xl font-bold text-white brand-font leading-tight">
                        Welcome back, <br class="md:hidden">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-blue-300">
                            {{ Auth::user()->name }}
                        </span>
                    </h1>
                    <p class="text-slate-400 mt-4 max-w-xl text-lg font-medium leading-relaxed">
                        Your central hub for financial documents, compliance tracking, and direct firm communication.
                    </p>
                </div>
                
                @if(Auth::user()->gst_number || Auth::user()->pan_number)
                <div class="grid grid-cols-1 gap-3 animate-fade-in" style="animation-delay: 0.1s;">
                    @if(Auth::user()->gst_number)
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4 flex items-center gap-4 min-w-[200px]">
                            <div class="w-10 h-10 bg-blue-500/20 text-blue-400 rounded-xl flex items-center justify-center font-bold text-xs uppercase">GST</div>
                            <div>
                                <span class="block text-slate-500 uppercase text-[10px] font-bold tracking-wider">GST Number</span>
                                <span class="text-white font-mono text-sm uppercase tracking-wider">{{ Auth::user()->gst_number }}</span>
                            </div>
                        </div>
                    @endif
                    @if(Auth::user()->pan_number)
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-4 flex items-center gap-4 min-w-[200px]">
                            <div class="w-10 h-10 bg-blue-600/20 text-blue-400 rounded-xl flex items-center justify-center font-bold text-xs uppercase">PAN</div>
                            <div>
                                <span class="block text-slate-500 uppercase text-[10px] font-bold tracking-wider">PAN Number</span>
                                <span class="text-white font-mono text-sm uppercase tracking-wider">{{ Auth::user()->pan_number }}</span>
                            </div>
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 -mt-24 relative z-20">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-12">
            
            <!-- Quick Feed -->
            <div class="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Recent Uploads -->
                <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center gap-3">
                            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            </div>
                            Latest Documents
                        </h3>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @forelse($recentDocuments as $doc)
                            <div class="px-8 py-4 flex items-center justify-between hover:bg-slate-50 transition group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[10px] uppercase border border-slate-200 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition duration-300">
                                        {{ pathinfo($doc->filename, PATHINFO_EXTENSION) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-sm font-bold text-slate-700 truncate w-32 md:w-40" title="{{ $doc->filename }}">{{ $doc->filename }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $doc->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if(Str::lower(pathinfo($doc->filename, PATHINFO_EXTENSION)) === 'pdf')
                                    <a href="{{ route('file.view', $doc->id) }}" target="_blank" class="p-2 text-slate-300 hover:text-blue-600 transition" title="View PDF">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    @endif
                                    <a href="{{ route('file.download', $doc->id) }}" class="p-2 text-slate-300 hover:text-blue-600 transition" title="Download">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="px-8 py-12 text-center text-slate-400 text-sm italic">No recent uploads found.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Views -->
                <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden animate-fade-in" style="animation-delay: 0.3s;">
                    <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center gap-3">
                            <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            Vault Activity
                        </h3>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @forelse($recentViews as $log)
                            <div class="px-8 py-4 flex items-center gap-4 hover:bg-slate-50 transition">
                                <div class="w-2 h-10 rounded-full {{ str_contains($log->action, 'Download') ? 'bg-blue-100' : 'bg-purple-100' }} flex-shrink-0"></div>
                                <div class="overflow-hidden">
                                    <p class="text-sm font-bold text-slate-700 truncate w-40 md:w-48" title="{{ $log->description }}">
                                        {{ $log->description }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ str_contains($log->action, 'Download') ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }} uppercase tracking-tighter whitespace-nowrap">{{ $log->action }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-8 py-12 text-center text-slate-400 text-sm italic">Your vault history is clear.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Notice Board -->
            <div class="lg:col-span-4 lg:row-span-1" x-data="{ noticeCount: {{ $unreadNoticeCount ?? 0 }} }">
                <div class="grad-purple animate-gradient rounded-3xl shadow-2xl text-white p-8 relative overflow-hidden group h-full border border-white/5">
                    <!-- Decorative Background Blobs -->
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/5 rounded-full blur-2xl group-hover:scale-125 transition duration-500"></div>
                    
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/10">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold leading-none">Firm Notifications</h3>
                                    <p class="text-blue-300 text-[10px] mt-1 uppercase font-bold tracking-widest">Central Briefing</p>
                                </div>
                            </div>
                            <template x-if="noticeCount > 0">
                                <span class="bg-red-600 text-white text-[10px] font-black px-2.5 py-1 rounded-lg animate-pulse">NEW</span>
                            </template>
                        </div>

                        <div class="flex-grow space-y-4 max-h-[220px] overflow-y-auto pr-2 custom-scrollbar">
                            @forelse($notices->take(3) as $notice)
                                <div class="p-4 rounded-2xl {{ $notice->type === 'urgent' ? 'bg-red-500/10 border-red-500/30 glow-red' : 'bg-white/5 border-white/10' }} border backdrop-blur-sm transition hover:bg-white/10">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 w-2 h-2 rounded-full {{ $notice->type === 'urgent' ? 'bg-red-500' : ($notice->type === 'warning' ? 'bg-amber-500' : 'bg-blue-400') }}"></div>
                                        <div>
                                            <h4 class="text-xs font-bold {{ $notice->type === 'urgent' ? 'text-red-400' : 'text-blue-200' }} tracking-wide">{{ $notice->title }}</h4>
                                            <p class="text-[11px] text-slate-300 mt-1 leading-relaxed font-medium line-clamp-2">
                                                {{ $notice->content }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white/5 rounded-2xl p-6 text-center border border-white/5">
                                    <p class="text-xs text-slate-400 font-medium italic">All systems clear. Check back later for updates.</p>
                                </div>
                            @endforelse
                        </div>

                        @if($notices->count() > 0)
                        <button onclick="openNoticeModal()" class="mt-6 w-full py-3 bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl text-[11px] font-bold uppercase tracking-widest transition">
                            View All Broadcasts
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <h2 class="text-xl font-bold text-slate-900 mb-8 flex items-center gap-3 brand-font px-2">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            Document Crypt
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6 mb-12 animate-fade-in" style="animation-delay: 0.4s;">
            @foreach($folders as $folder)
            <button onclick="openFolder('{{ $folder->id }}')" 
                class="bg-white p-6 rounded-3xl border border-slate-100 shadow-lg shadow-slate-200/50 hover:shadow-xl hover:border-blue-400 hover:-translate-y-1 transition-all duration-300 text-center group focus:outline-none ring-offset-4 focus:ring-2 focus:ring-blue-600">
                <div class="w-20 h-20 mx-auto bg-slate-50 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition duration-500 relative">
                    <svg class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                    <div class="absolute -top-1 -right-1 w-6 h-6 bg-blue-600 text-white rounded-lg flex items-center justify-center text-[10px] font-bold border-2 border-white">
                        {{ $folder->documents->count() }}
                    </div>
                </div>
                <h4 class="font-bold text-slate-800 group-hover:text-blue-700 truncate px-2 text-sm">{{ $folder->name }}</h4>
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest mt-1">Financial Year</p>
            </button>
            @endforeach
            
            @if($folders->isEmpty() && $rootDocuments->isEmpty())
                <div class="col-span-full py-16 text-center border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50">
                    <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                         <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">No documents available yet</p>
                </div>
            @endif
        </div>

        @foreach($folders as $folder)
        <div id="folder-{{ $folder->id }}" class="folder-content hidden bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6 animate-fade-in">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-700 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                    Files in {{ $folder->name }}
                </h3>
                <button onclick="closeFolders()" class="text-slate-400 hover:text-red-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="px-6 py-3">File Name</th>
                            <th class="px-6 py-3">Uploaded</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($folder->documents as $doc)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                {{ $doc->filename }}
                            </td>
                            <td class="px-6 py-3 text-slate-500">{{ $doc->created_at->format('d M, Y') }}</td>
                            <td class="px-6 py-3 text-right flex justify-end gap-3">
                                @if(Str::lower(pathinfo($doc->filename, PATHINFO_EXTENSION)) === 'pdf')
                                <a href="{{ route('file.view', $doc->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold text-xs">View</a>
                                @endif
                                <a href="{{ route('file.download', $doc->id) }}" class="text-blue-600 hover:text-blue-800 font-bold text-xs">Download</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-slate-400 text-xs">No files in this folder.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

        @if($rootDocuments->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="font-bold text-slate-700 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Other Documents
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="px-6 py-3">File Name</th>
                            <th class="px-6 py-3">Uploaded</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($rootDocuments as $doc)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                {{ $doc->filename }}
                            </td>
                            <td class="px-6 py-3 text-slate-500">{{ $doc->created_at->format('d M, Y') }}</td>
                            <td class="px-6 py-3 text-right flex justify-end gap-3">
                                @if(Str::lower(pathinfo($doc->filename, PATHINFO_EXTENSION)) === 'pdf')
                                <a href="{{ route('file.view', $doc->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold text-xs">View</a>
                                @endif
                                <a href="{{ route('file.download', $doc->id) }}" class="text-blue-600 hover:text-blue-800 font-bold text-xs">Download</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>

<!-- Notice Modal (Alpine Component) -->
<div id="noticeModal" 
     x-data="{ 
        open: false,
        notices: @js($notices),
        currentIndex: 0,
        init() {
            // Auto-open if there are unread notices
            const hasUnread = this.notices.some(n => n.pivot && !n.pivot.is_read);
            if (hasUnread) {
                setTimeout(() => { this.open = true; }, 1000);
            }
        },
        markAsRead(id) {
            fetch(`/notices/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
            // Update local state
            const notice = this.notices.find(n => n.id === id);
            if (notice && notice.pivot) notice.pivot.is_read = true;
        }
     }"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md transition-all duration-500"
     @notice-open.window="open = true; currentIndex = $event.detail.index || 0"
>
    <div class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 flex flex-col transform transition-all"
         x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
    >
        <template x-if="notices.length > 0">
            <div class="h-full flex flex-col">
                <!-- Header -->
                <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center" 
                     :class="notices[currentIndex].type === 'urgent' ? 'bg-red-50' : 'bg-slate-50/50'">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] px-2 py-0.5 rounded"
                              :class="notices[currentIndex].type === 'urgent' ? 'bg-red-600 text-white' : 'bg-blue-600 text-white'"
                              x-text="notices[currentIndex].type">
                        </span>
                        <h3 class="text-xl font-bold text-slate-800 mt-2 brand-font" x-text="notices[currentIndex].title"></h3>
                    </div>
                    <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-8">
                    <p class="text-slate-600 leading-relaxed font-medium text-sm" x-text="notices[currentIndex].content"></p>
                    
                    <div class="mt-8 flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            <template x-for="(n, index) in notices">
                                <button @click="currentIndex = index" 
                                        class="w-2 h-2 rounded-full transition-all"
                                        :class="currentIndex === index ? 'w-6 bg-blue-600' : 'bg-slate-200'">
                                </button>
                            </template>
                        </div>
                        <button @click="markAsRead(notices[currentIndex].id); open = notices.length > currentIndex + 1 ? true : false; if(notices.length > currentIndex + 1) currentIndex++;" 
                                class="px-6 py-2.5 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition">
                            <span x-text="notices.length > currentIndex + 1 ? 'Next Notice' : 'Dismiss All'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
    function openFolder(id) {
        document.querySelectorAll('.folder-content').forEach(el => el.classList.add('hidden'));
        const folder = document.getElementById('folder-' + id);
        if(folder) {
            folder.classList.remove('hidden');
            folder.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function closeFolders() {
        document.querySelectorAll('.folder-content').forEach(el => el.classList.add('hidden'));
    }

    function openNoticeModal() {
        window.dispatchEvent(new CustomEvent('notice-open', { detail: { index: 0 } }));
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    .animate-fade-in { animation: fadeIn 0.5s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    .glow-red {
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.2);
        animation: glowRed 2s infinite alternate;
    }
    @keyframes glowRed {
        from { box-shadow: 0 0 10px rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); }
        to { box-shadow: 0 0 20px rgba(239, 68, 68, 0.4); border-color: rgba(239, 68, 68, 0.5); }
    }

    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

    /* Moving gradient animation */
    @keyframes gradient-move {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    .animate-gradient {
        background-size: 300% 300%;
        animation: gradient-move 8s ease infinite;
    }
    .brand-gradient {
        background: linear-gradient(-45deg, #0f172a, #1e1b4b, #311042, #0f172a);
        background-size: 300% 300%;
        animation: gradient-move 12s ease infinite;
    }

    /* Brand-aligned gradients for cards */
    .grad-blue { background: linear-gradient(-45deg, #0F2C4A, #173A5E, #0F2C4A, #1d3a5c); }
    .grad-green { background: linear-gradient(-45deg, #145C3F, #1B7A52, #0d4a32, #1B7A52); }
    .grad-purple { background: linear-gradient(-45deg, #173A5E, #1B7A52, #0F2C4A, #145C3F); }
    .grad-red { background: linear-gradient(-45deg, #7f1d1d, #ef4444, #b91c1c, #dc2626); }

    /* Metallic sweep shimmer on card hover */
    .grad-blue, .grad-purple, .grad-green, .grad-red {
        position: relative;
        overflow: hidden;
    }
    .grad-blue::after, .grad-purple::after, .grad-green::after, .grad-red::after {
        content: '';
        position: absolute;
        top: 0;
        left: -150%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.15),
            transparent
        );
        transform: skewX(-20deg);
        transition: none;
        pointer-events: none;
    }
    .grad-blue:hover::after, .grad-purple:hover::after, .grad-green:hover::after, .grad-red:hover::after {
        left: 150%;
        transition: left 1.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Moving gradient buttons */
    .btn-moving-gradient {
        background: linear-gradient(-45deg, #145C3F, #1B7A52, #0d4a32, #1B7A52);
        background-size: 300% 300%;
        animation: gradient-move 6s ease infinite;
        transition: all 0.3s ease;
    }
    .btn-moving-gradient:hover {
        filter: brightness(1.1);
        box-shadow: 0 4px 12px rgba(27, 122, 82, 0.25);
    }
    .btn-moving-gradient-slate {
        background: linear-gradient(-45deg, #0F2C4A, #173A5E, #0F2C4A, #173A5E);
        background-size: 300% 300%;
        animation: gradient-move 8s ease infinite;
        transition: all 0.3s ease;
    }
    .btn-moving-gradient-slate:hover {
        filter: brightness(1.1);
        box-shadow: 0 4px 12px rgba(15, 44, 74, 0.25);
    }
</style>

@endsection