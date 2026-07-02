@extends('layouts.admin')

@section('content')

<div class="max-w-6xl mx-auto">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('documents.manage') }}" class="p-2.5 bg-white border border-slate-200 rounded-xl text-slate-500 hover:text-blue-600 hover:border-blue-500 transition shadow-sm group">
                <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold brand-font flex items-center gap-2">
                    <span class="text-slate-900">{{ $user->name }}</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 bg-blue-100 px-2.5 py-1 rounded-md ml-2 border border-blue-200">Repository</span>
                </h1>
                <p class="text-slate-500 text-sm mt-1 flex items-center gap-2 font-medium">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    +91 {{ $user->phone }}
                </p>
            </div>
        </div>

        <div class="flex gap-3">
             <button onclick="document.getElementById('createFolderModal').classList.remove('hidden')" class="bg-white border-2 border-slate-200 hover:border-blue-500 text-slate-700 hover:text-blue-600 px-4 py-3 rounded-xl font-bold flex items-center gap-2 shadow-sm transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                New Folder
            </button>
            <button onclick="document.getElementById('uploadFileModal').classList.remove('hidden')" class="btn-emerald text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 shadow-lg shadow-emerald-500/30 transition transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                @if($currentFolder)
                    Upload to {{ Str::limit($currentFolder->name, 15) }}
                @else
                    Upload File
                @endif
            </button>
        </div>
    </div>

    @if(session('success'))
        <div id="successBanner" class="bg-gradient-to-r from-green-500 to-emerald-500 text-white p-4 rounded-xl mb-6 border border-green-400 flex items-center justify-between gap-3 font-semibold shadow-lg animate-fade-in">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('success') }}
            </div>
            <button onclick="this.parentElement.remove()" class="text-white hover:text-green-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif
    
    @if(session('error'))
        <div id="errorBanner" class="bg-gradient-to-r from-red-500 to-rose-500 text-white p-4 rounded-xl mb-6 border border-red-400 flex items-center justify-between gap-3 font-semibold shadow-lg animate-fade-in">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('error') }}
            </div>
            <button onclick="this.parentElement.remove()" class="text-white hover:text-red-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <!-- Breadcrumbs -->
    <nav class="flex mb-6 text-sm font-medium text-slate-500 bg-white px-4 py-3 rounded-xl shadow-sm border border-slate-100 overflow-x-auto whitespace-nowrap">
        <a href="{{ route('documents.view', $user->id) }}" class="hover:text-blue-600 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Home
        </a>
        @foreach($breadcrumbs as $crumb)
            <svg class="w-4 h-4 mx-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <a href="{{ route('documents.view', $user->id) }}?folder={{ $crumb->id }}" class="hover:text-blue-600">
                {{ $crumb->name }}
            </a>
        @endforeach
    </nav>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden mb-8 animate-fade-in ring-4 ring-slate-50 min-h-[400px] pt-0.5">
        
        <!-- Folders Section -->
        @if($folders->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 border-b border-slate-100 bg-slate-50/50">
            @foreach($folders as $folder)
            <div class="relative bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-300 transition group">
                <a href="{{ route('documents.view', $user->id) }}?folder={{ $folder->id }}" class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center mb-3 group-hover:scale-110 transition duration-300">
                         <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                    </div>
                    <span class="font-bold text-slate-700 text-sm group-hover:text-blue-600 truncate w-full">{{ $folder->name }}</span>
                </a>
                <button onclick="openRenameFolderModal('{{ $folder->id }}', '{{ $folder->name }}')" class="absolute top-2 right-2 p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition opacity-0 group-hover:opacity-100" title="Rename Folder">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Files List -->
        <div class="divide-y divide-slate-100">
            @forelse($documents as $doc)
            <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition group">
                
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs uppercase border border-blue-100 shadow-sm group-hover:scale-105 transition">
                        {{ pathinfo($doc->filename, PATHINFO_EXTENSION) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition cursor-default">
                            {{ $doc->filename }}
                        </p>
                        <div class="flex items-center gap-3 mt-1">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                {{ $doc->category }}
                            </span>
                            <span class="text-xs text-slate-400 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> 
                                {{ $doc->created_at->format('d M, Y • h:i A') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Rename Button -->
                    <button onclick="openRenameModal('{{ $doc->id }}', '{{ $doc->filename }}')" class="p-2.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition border border-transparent hover:border-amber-200" title="Rename">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>

                    <a href="{{ route('file.download', $doc->id) }}" class="p-2.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition border border-transparent hover:border-blue-200" title="Download">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4-4m0 0l-4 4m4-4v12"></path></svg>
                    </a>
                    
                    <form action="{{ route('file.delete', $doc->id) }}" method="POST" onsubmit="return confirm('Permanently delete {{ $doc->filename }}?');">
                        @csrf 
                        <!-- Handling Delete with Match Route -->
                        <button type="submit" class="p-2.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition border border-transparent hover:border-red-200" title="Delete">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
                @if($folders->count() == 0)
                <div class="text-center py-16">
                    <div class="bg-slate-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">No Documents Found</h3>
                    <p class="text-slate-500 text-sm mt-1 mb-4">This folder is empty.</p>
                    <button onclick="document.getElementById('uploadFileModal').classList.remove('hidden')" class="inline-flex items-center gap-2 btn-emerald text-white px-6 py-3 rounded-xl font-bold shadow-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Upload Files Here
                    </button>
                </div>
                @endif
            @endforelse
        </div>
        
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $documents->links() }}
        </div>
    </div>

</div>

<!-- Upload File Modal -->
<div id="uploadFileModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm animate-fade-in">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform scale-100 transition-all">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-blue-50 to-blue-50">
            <div>
                <h3 class="font-bold text-xl text-slate-800">Upload Files</h3>
                <p class="text-sm text-slate-600 mt-1">
                    @if($currentFolder)
                        Uploading to: <span class="font-semibold text-blue-600">{{ $currentFolder->name }}</span>
                    @else
                        Uploading to: <span class="font-semibold text-blue-600">Root</span>
                    @endif
                </p>
            </div>
            <button onclick="document.getElementById('uploadFileModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('file.upload') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <input type="hidden" name="folder_id" value="{{ $currentFolder ? $currentFolder->id : '' }}">
            <input type="hidden" name="category" value="General">
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-3">Select Files</label>
                <div class="relative border-2 border-dashed border-slate-300 rounded-xl p-8 hover:border-blue-500 hover:bg-blue-50 transition text-center group bg-slate-50 cursor-pointer" onclick="document.getElementById('fileInput').click()">
                    <input type="file" name="files[]" multiple id="fileInput" class="hidden" required onchange="updateFileList(this)">
                    <div class="text-slate-500 group-hover:text-blue-600 transition flex flex-col items-center">
                        <svg class="w-16 h-16 mb-3 text-slate-400 group-hover:text-blue-500 transition transform group-hover:-translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <span class="font-bold text-base block" id="uploadText">Click to Browse Files</span>
                        <span class="text-sm mt-1" id="uploadSubtext">or drag and drop files here</span>
                        <span class="text-xs text-slate-400 mt-2">PDF, JPG, PNG, DOC, DOCX, XLS, XLSX (Max 10MB each)</span>
                    </div>
                </div>
                <div id="fileList" class="mt-4 hidden">
                    <p class="text-sm font-bold text-slate-700 mb-2">Selected Files:</p>
                    <div id="fileListContent" class="space-y-2"></div>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('uploadFileModal').classList.add('hidden')" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 rounded-xl transition">Cancel</button>
                <button type="submit" class="flex-1 btn-emerald text-white font-bold py-3 rounded-xl transition shadow-lg shadow-emerald-500/30 flex justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    Upload Files
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Create Folder Modal -->
<div id="createFolderModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm animate-fade-in">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform scale-100 transition-all">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-lg text-slate-800">Create New Folder</h3>
            <button onclick="document.getElementById('createFolderModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('folder.create') }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <input type="hidden" name="parent_id" value="{{ $currentFolder ? $currentFolder->id : '' }}">
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">Folder Name</label>
                <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="e.g. Invoices 2025" autofocus>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-emerald-500/30">Create Folder</button>
        </form>
    </div>
</div>

<!-- Rename File Modal -->
<div id="renameFileModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm animate-fade-in">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform scale-100 transition-all">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-lg text-slate-800">Rename File</h3>
            <button onclick="document.getElementById('renameFileModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="renameForm" action="" method="POST" class="p-6">
            @csrf
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">New Name</label>
                <input type="text" id="renameInput" name="name" required class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                <p class="text-xs text-slate-500 mt-2">Note: Extension will be preserved automatically.</p>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-emerald-500/30">Rename File</button>
        </form>
    </div>
</div>

<!-- Rename Folder Modal -->
<div id="renameFolderModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm animate-fade-in">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform scale-100 transition-all">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-lg text-slate-800">Rename Folder</h3>
            <button onclick="document.getElementById('renameFolderModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="renameFolderForm" action="" method="POST" class="p-6">
            @csrf
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">New Folder Name</label>
                <input type="text" id="renameFolderInput" name="name" required class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" autofocus>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-emerald-500/30">Rename Folder</button>
        </form>
    </div>
</div>

<!-- Rename File Modal -->
<div id="renameFileModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm animate-fade-in">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform scale-100 transition-all">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-lg text-slate-800">Rename File</h3>
            <button onclick="document.getElementById('renameFileModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="renameForm" action="" method="POST" class="p-6">
            @csrf
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">New Name</label>
                <input type="text" id="renameInput" name="name" required class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                <p class="text-xs text-slate-500 mt-2">Note: Extension will be preserved automatically.</p>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-emerald-500/30">Rename File</button>
        </form>
    </div>
</div>

<script>
    // Auto-dismiss banners after 4 seconds
    setTimeout(() => {
        const successBanner = document.getElementById('successBanner');
        const errorBanner = document.getElementById('errorBanner');
        if (successBanner) {
            successBanner.style.transition = 'opacity 0.5s';
            successBanner.style.opacity = '0';
            setTimeout(() => successBanner.remove(), 500);
        }
        if (errorBanner) {
            errorBanner.style.transition = 'opacity 0.5s';
            errorBanner.style.opacity = '0';
            setTimeout(() => errorBanner.remove(), 500);
        }
    }, 4000);

    function updateFileList(input) {
        const fileList = document.getElementById('fileList');
        const fileListContent = document.getElementById('fileListContent');
        const uploadText = document.getElementById('uploadText');
        const uploadSubtext = document.getElementById('uploadSubtext');
        
        if (input.files && input.files.length > 0) {
            fileList.classList.remove('hidden');
            fileListContent.innerHTML = '';
            
            Array.from(input.files).forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200';
                fileItem.innerHTML = `
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">${file.name}</p>
                        <p class="text-xs text-slate-500">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    </div>
                `;
                fileListContent.appendChild(fileItem);
            });
            
            uploadText.textContent = `${input.files.length} file(s) selected`;
            uploadText.classList.add('text-blue-600');
            uploadSubtext.textContent = 'Click to change selection';
        } else {
            fileList.classList.add('hidden');
            uploadText.textContent = 'Click to Browse Files';
            uploadText.classList.remove('text-blue-600');
            uploadSubtext.textContent = 'or drag and drop files here';
        }
    }

    function openRenameFolderModal(id, currentName) {
        const modal = document.getElementById('renameFolderModal');
        const form = document.getElementById('renameFolderForm');
        const input = document.getElementById('renameFolderInput');
        
        form.action = "/rename-folder/" + id;
        input.value = currentName;
        
        modal.classList.remove('hidden');
        input.focus();
    }

    function openRenameModal(id, currentName) {
        const modal = document.getElementById('renameFileModal');
        const form = document.getElementById('renameForm');
        const input = document.getElementById('renameInput');
        
        // Remove extension for easier editing
        const nameWithoutExt = currentName.substring(0, currentName.lastIndexOf('.')) || currentName;
        
        form.action = "/rename-file/" + id;
        input.value = nameWithoutExt;
        
        modal.classList.remove('hidden');
        input.focus();
    }
</script>

<style>
    .animate-fade-in { animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
</style>

@endsection