@extends('layouts.admin')

@section('content')

    <div class="max-w-4xl mx-auto">
        
        <div class="flex justify-between items-center mb-4 border-b border-slate-200 pb-2">
            <div>
                <h1 class="text-2xl font-bold brand-font flex items-center gap-2">
                    <span class="bg-blue-100 p-1.5 rounded-xl text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    </span>
                    <span class="text-slate-800">Upload Files</span>
                </h1>
                <p class="text-slate-500 text-xs mt-0.5 font-medium">Send documents securely to client repositories.</p>
            </div>
            @if(Auth::user()->hasPermission('view_files'))
            <a href="{{ route('documents.manage') }}" class="text-blue-600 hover:text-blue-800 font-bold text-xs flex items-center gap-1.5 transition bg-blue-50 px-3 py-1.5 rounded-lg">
                View Repository <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 border border-green-200 p-3 rounded-xl mb-4 text-xs font-bold flex items-center gap-2 shadow-sm animate-fade-in">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xl relative overflow-hidden">

                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl -mr-12 -mt-12"></div>

                <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 relative z-10">
                    <span class="bg-blue-100 text-blue-600 p-2 rounded-xl"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg></span>
                    Upload Documents
                </h2>

                <form action="{{ route('file.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4 relative z-10">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Select Client</label>
                            <select name="user_id" onchange="fetchFolders(this.value)" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm cursor-pointer" required>
                                <option value="">-- Choose Client --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} (+91 {{ $user->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Select Target Folder</label>
                            <select name="folder_id" id="folderSelect" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm cursor-pointer">
                                <option value="">-- General / Root Repository --</option>
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1">Files will be uploaded to this specific folder inside Wasabi.</p>
                        </div>
                    </div>

                    <!-- Hidden Category Input for Backend Compatibility -->
                    <input type="hidden" name="category" id="hiddenCategory" value="General">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Attachments</label>
                        <div class="relative border-2 border-dashed border-slate-300 rounded-xl p-5 hover:border-blue-500 hover:bg-blue-50 transition text-center group bg-slate-50 cursor-pointer" onclick="document.getElementById('uploadInput').click()">
                            <input type="file" name="files[]" multiple id="uploadInput" class="hidden" required onchange="updateFileName(this)">
                            <div class="text-slate-500 group-hover:text-blue-600 transition flex flex-col items-center">
                                <svg class="w-8 h-8 mb-1.5 text-slate-400 group-hover:text-blue-500 transition transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <span class="font-bold text-xs block" id="uploadText">Click to Browse</span>
                                <span class="text-[10px] mt-0.5" id="uploadSubtext">or drag and drop files here</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-blue-500/20 flex justify-center gap-2 transform active:scale-95 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg> 
                        Upload Files
                    </button>
                </form>
            </div>


        </div>
    </div>

    <script>
        function updateFileName(input) {
            const text = document.getElementById('uploadText');
            const subtext = document.getElementById('uploadSubtext');
            
            if (input.files && input.files.length > 0) {
                let fileNames = Array.from(input.files).map(file => file.name).join(', ');
                if(fileNames.length > 50) fileNames = fileNames.substring(0, 50) + '...';

                text.innerText = "Selected: " + fileNames;
                text.classList.add('text-blue-600');
                subtext.innerText = input.files.length + " file(s) ready to upload";
            } else {
                text.innerText = "Click to Browse";
                text.classList.remove('text-blue-600');
                subtext.innerText = "or drag and drop files here";
            }
        }

        function fetchFolders(userId) {
            const select = document.getElementById('folderSelect');
            
            if(!userId) {
                select.innerHTML = '<option value="">-- First Choose Client --</option>';
                return;
            }

            select.innerHTML = '<option value="">Loading folders...</option>';

            fetch(`/files/folders/${userId}`)
                .then(response => response.json())
                .then(data => {
                    select.innerHTML = '<option value="">-- General / Root Repository --</option>';
                    if(data.length > 0) {
                        data.forEach(folder => {
                            select.innerHTML += `<option value="${folder.id}">${folder.name}</option>`;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching folders:', error);
                    select.innerHTML = '<option value="">-- General / Root Repository --</option>';
                });
        }
    </script>

@endsection