@extends('layouts.admin')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div class="h-[calc(100vh-140px)] flex gap-6">
    
    <div class="hidden md:flex flex-col w-1/3 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm h-full overflow-y-auto">
        
        <div class="mb-6">
            <span class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wider
                {{ $task->priority == 'high' ? 'bg-red-100 text-red-700' : ($task->priority == 'medium' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700') }}">
                {{ $task->priority }} Priority
            </span>
            <h1 class="text-2xl font-bold text-slate-900 mt-3 brand-font leading-tight">{{ $task->title }}</h1>
        </div>

        <div class="space-y-6">
            <div x-data="{ editing: false }">
                <div class="flex justify-between items-center mb-1.5">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide">Assigned To</h4>
                    @if(Auth::user()->role === 'admin')
                    <button @click="editing = !editing" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 transition uppercase tracking-wider">
                        <span x-show="!editing">Transfer Task</span>
                        <span x-show="editing">Cancel</span>
                    </button>
                    @endif
                </div>
                
                <div x-show="!editing" class="flex flex-wrap gap-2">
                    @forelse($task->assignees as $assignee)
                    <div class="flex items-center gap-2 bg-blue-50 px-2 py-1 rounded-lg border border-blue-100">
                        <img src="{{ $assignee->avatar_url }}" class="w-6 h-6 rounded-full object-cover shadow-sm bg-blue-100">
                        <span class="text-slate-700 font-medium text-sm">{{ $assignee->name }}</span>
                    </div>
                    @empty
                    <span class="text-slate-400 text-sm italic">Unassigned</span>
                    @endforelse
                </div>

                @if(Auth::user()->role === 'admin')
                <div x-show="editing" x-cloak class="bg-slate-50 p-3 rounded-xl border border-slate-200 mt-2">
                    <form action="{{ route('tasks.transfer', $task->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">Select Assignee(s)</label>
                            <div class="max-h-[140px] overflow-y-auto space-y-1 pr-1">
                                @foreach($users as $staff)
                                <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-100 hover:bg-blue-50 hover:border-blue-200 cursor-pointer transition">
                                    <input type="checkbox" name="staff_ids[]" value="{{ $staff->id }}"
                                        {{ $task->isAssignedTo($staff->id) ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 border-slate-350 rounded focus:ring-blue-500 transition">
                                    <span class="text-xs font-semibold text-slate-700">{{ $staff->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            Confirm Transfer
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Due Date</h4>
                <p class="text-slate-700 font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ \Carbon\Carbon::parse($task->due_date)->format('d M, Y') }}
                </p>
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Description</h4>
                <p class="text-sm text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
                    {{ $task->description }}
                </p>
            </div>

            <div class="pt-6 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3">Status</h4>
                
                <div class="flex items-center gap-2 mb-4">
                    @if($task->status == 'completed')
                        <div class="w-full bg-green-100 text-green-800 py-2 text-center rounded-lg font-bold text-sm border border-green-200">
                            ✓ Completed on {{ $task->updated_at->format('d M') }}
                        </div>
                    @else
                        <div class="w-full bg-yellow-100 text-yellow-800 py-2 text-center rounded-lg font-bold text-sm animate-pulse border border-yellow-200">
                            ⟳ In Progress
                        </div>
                    @endif
                </div>

                @if($task->status !== 'completed' && Auth::user()->role === 'staff')
                <div x-data="{ 
                    submitting: false,
                    async accomplish() {
                        if(this.submitting) return;
                        this.submitting = true;
                        try {
                            const res = await fetch('{{ route('tasks.updateStatus', $task->id) }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                            });
                            if(res.ok) {
                                // Notify Hub instantly
                                if(window.roleHubInstance) window.roleHubInstance.dispatchSync();
                                else new BroadcastChannel('portal_sync').postMessage('refresh');
                                
                                window.location.reload();
                            }
                        } catch(e) { console.error('Status update failed'); }
                        this.submitting = false;
                    }
                }">
                    <button @click="accomplish" 
                            :disabled="submitting"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-blue-200 flex items-center justify-center gap-2 group disabled:opacity-50">
                        <div class="bg-white/20 p-1 rounded-full group-hover:bg-white/30 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span x-show="!submitting">Mark as Completed</span>
                        <span x-show="submitting">Processing...</span>
                    </button>
                </div>
                @endif

                @if($task->status === 'completed' && Auth::user()->role === 'admin')
                <div x-data="{ 
                    submitting: false,
                    async accomplish() {
                        if(this.submitting) return;
                        this.submitting = true;
                        try {
                            const res = await fetch('{{ route('tasks.updateStatus', $task->id) }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                            });
                            if(res.ok) {
                                // Notify Hub instantly
                                if(window.roleHubInstance) window.roleHubInstance.dispatchSync();
                                else new BroadcastChannel('portal_sync').postMessage('refresh');
                                
                                window.location.reload();
                            }
                        } catch(e) { console.error('Status update failed'); }
                        this.submitting = false;
                    }
                }">
                    <button @click="accomplish" 
                            :disabled="submitting"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-orange-200 flex items-center justify-center gap-2 group disabled:opacity-50">
                        <div class="bg-white/20 p-1 rounded-full group-hover:bg-white/30 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </div>
                        <span x-show="!submitting">Reopen Task (Revoke Done)</span>
                        <span x-show="submitting">Processing...</span>
                    </button>
                </div>
                @endif
            </div>

        </div>
    </div> 

    <div class="flex-1 flex flex-col bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden h-full relative">
        
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center z-10">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                Discussion
            </h3>
            <div class="flex items-center gap-3">
                <button id="muteBtn" class="p-2 rounded-full transition text-slate-400 hover:bg-slate-100" title="Mute Notifications">
                    <svg id="muteIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <!-- Icons injected by JS -->
                    </svg>
                </button>
                <span class="text-xs text-slate-400">{{ $task->chats->count() }} messages</span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50" id="chatContainer">
            @forelse($task->chats as $chat)
                @php $isMe = $chat->user_id == Auth::id(); @endphp
                
                <div class="flex w-full {{ $isMe ? 'justify-end' : 'justify-start' }}">
                    <div class="flex max-w-[80%] md:max-w-[70%] gap-2 {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                        
                        <img src="{{ $chat->user->avatar_url }}" class="w-8 h-8 rounded-full flex-shrink-0 object-cover shadow-sm
                            {{ $isMe ? 'border-2 border-blue-600' : 'border-2 border-slate-200' }}">

                        <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                            <div class="px-4 py-2 rounded-2xl text-sm shadow-sm
                                {{ $isMe ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-white border border-slate-200 text-slate-700 rounded-tl-none' }}">
                                
                                @if($chat->attachment)
                                    <div class="mb-2">
                                        @php $ext = pathinfo($chat->attachment, PATHINFO_EXTENSION); @endphp
                                        
                                        @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                            <img src="/chat/file/{{ $chat->attachment }}" alt="Chat Attachment" class="cursor-pointer rounded-lg max-h-40 border border-white/20 hover:opacity-90 transition bg-white/10" onclick="openImageModal('/chat/file/{{ $chat->attachment }}')">
                                        @else
                                            <a href="/chat/file/{{ $chat->attachment }}" target="_blank" class="flex items-center gap-2 bg-black/10 px-3 py-2 rounded-lg hover:bg-black/20 transition text-inherit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                <span class="underline text-xs font-bold">View File ({{ strtoupper($ext) }})</span>
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                @if($chat->message)
                                    <p class="whitespace-pre-wrap">{{ $chat->message }}</p>
                                @endif
                            </div>
                            
                            <p class="text-[10px] text-slate-400 mt-1 flex gap-1 items-center {{ $isMe ? 'justify-end' : 'justify-start' }}">
                                {{ $chat->created_at->format('h:i A') }} 
                                @if($isMe)
                                    <span>•</span>
                                    @if($chat->is_read)
                                        <span class="text-blue-500 font-bold">Read</span>
                                    @else
                                        <span>Sent</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-slate-400">
                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    <p class="text-sm">No messages yet. Start the conversation!</p>
                </div>
            @endforelse
            <div id="scrollAnchor"></div>
        </div>

        <div class="p-4 bg-white border-t border-slate-200">
            <form action="{{ route('task.chat', $task->id) }}" method="POST" enctype="multipart/form-data" class="flex gap-2 items-end">
                @csrf
                
                <label class="cursor-pointer p-2.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Attach File">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                    <input type="file" name="attachment" class="hidden" onchange="document.getElementById('fileName').innerText = 'Attached: ' + this.files[0].name; document.getElementById('fileName').classList.remove('hidden');">
                </label>

                <div class="flex-1">
                    @error('attachment')
                        <p class="text-[11px] text-red-600 font-bold px-1 mb-1">{{ $message }}</p>
                    @enderror
                    @error('message')
                        <p class="text-[11px] text-red-600 font-bold px-1 mb-1">{{ $message }}</p>
                    @enderror
                    <p id="fileName" class="hidden text-[10px] text-blue-600 font-bold px-1 mb-1 animate-pulse"></p>
                    <textarea name="message" id="task-message-textarea" placeholder="Type message..." autocomplete="off" rows="1"
                        class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 resize-none overflow-y-auto"
                        style="max-height: 120px; height: auto;"></textarea>
                </div>
                
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-2.5 rounded-lg transition shadow-md flex items-center justify-center min-w-[3rem]">
                    <svg class="w-5 h-5 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>

    </div>
</div>

<!-- Image Preview Modal -->
<div id="imagePreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 hidden" onclick="closeImageModal()">
    <img id="imagePreviewImg" src="" class="max-w-full max-h-full rounded-lg shadow-2xl" onclick="event.stopPropagation()">
    <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-red-500 bg-black/50 rounded-full p-2 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>

<script>
    function openImageModal(url) {
        document.getElementById('imagePreviewImg').src = url;
        document.getElementById('imagePreviewModal').classList.remove('hidden');
    }
    function closeImageModal() {
        document.getElementById('imagePreviewModal').classList.add('hidden');
        document.getElementById('imagePreviewImg').src = '';
    }
</script>

<script>
    // Auto Scroll to bottom
    const chatContainer = document.getElementById("chatContainer");
    chatContainer.scrollTop = chatContainer.scrollHeight;

    // Real-time Chat Polling
    let lastMessageId = {{ $task->chats->last()->id ?? 0 }};
    const taskId = {{ $task->id }};
    const currentUserId = {{ Auth::id() }};

    // Sound Notification (disabled)
    const notificationSound = null;
    
    // Mute Logic
    let isMuted = localStorage.getItem('chat_muted') === 'true';
    const muteBtn = document.getElementById('muteBtn');
    const muteIcon = document.getElementById('muteIcon');

    function updateMuteUI() {
        if(isMuted) {
            muteIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />';
            muteBtn.classList.add('text-red-500', 'bg-red-50');
            muteBtn.classList.remove('text-slate-400', 'hover:bg-slate-100');
            muteBtn.title = "Unmute Notifications";
        } else {
            muteIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />';
            muteBtn.classList.remove('text-red-500', 'bg-red-50');
            muteBtn.classList.add('text-slate-400', 'hover:bg-slate-100');
            muteBtn.title = "Mute Notifications";
        }
    }

    muteBtn.addEventListener('click', function() {
        isMuted = !isMuted;
        localStorage.setItem('chat_muted', isMuted);
        updateMuteUI();
        
        // Try playing sound immediately to request permission if unmuting
        if(!isMuted) {
             // Just a silent init or short ping could go here, but for now we just toggle state
        }
    });

    // Initialize UI
    updateMuteUI();

    // Keydown handler and auto-resizing for Task Chat message input
    const taskTextarea = document.getElementById('task-message-textarea');
    if (taskTextarea) {
        taskTextarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                if (e.shiftKey || e.ctrlKey) {
                    // Let default behavior add a new line
                } else {
                    e.preventDefault();
                    // If message is empty or only whitespace, don't submit unless there's an attachment
                    const hasAttachment = document.querySelector('input[name="attachment"]').files.length > 0;
                    if (this.value.trim() !== '' || hasAttachment) {
                        this.form.submit();
                    }
                }
            }
        });

        const adjustHeight = function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        };

        taskTextarea.addEventListener('input', adjustHeight);
        taskTextarea.addEventListener('paste', function(e) {
            setTimeout(adjustHeight.bind(this), 0);
            
            // Handle image paste
            if (e.clipboardData && e.clipboardData.files && e.clipboardData.files.length > 0) {
                const file = e.clipboardData.files[0];
                if (file.type.startsWith('image/')) {
                    const fileInput = document.querySelector('input[name="attachment"]');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    
                    // Update UI to show attached file
                    document.getElementById('fileName').innerText = 'Pasted Image: ' + file.name; 
                    document.getElementById('fileName').classList.remove('hidden');
                }
            }
        });
    }

    setInterval(function() {
        fetch(`/task/${taskId}/fetch-messages?last_id=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if(data.messages.length > 0) {
                    let hasNewMessageFromOthers = false;

                    data.messages.forEach(msg => {
                        appendMessage(msg);
                        lastMessageId = msg.id;
                        
                        // Check if message is from someone else
                        if (!msg.is_me) {
                            hasNewMessageFromOthers = true;
                        }
                    });

                    // Sound notification disabled

                    // Scroll to bottom on new message
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            })
            .catch(error => console.error('Error fetching messages:', error));
    }, 3000); // Check every 3 seconds

    function appendMessage(msg) {
        const isMe = msg.is_me;
        const alignment = isMe ? 'justify-end' : 'justify-start';
        const innerAlign = isMe ? 'flex-row-reverse' : 'flex-row';
        const textAlign = isMe ? 'items-end' : 'items-start';
        const bubbleColor = isMe ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-white border border-slate-200 text-slate-700 rounded-tl-none';
        const avatarColor = isMe ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 text-slate-600';
        const sender = isMe ? 'Sent' : 'Read'; // Simplified logic for realtime

        let attachmentHtml = '';
        if(msg.attachment_url) {
            if(msg.is_image) {
                attachmentHtml = `<div class="mb-2">
                    <img src="${msg.attachment_url}" alt="${msg.attachment_name || 'Attachment'}" class="cursor-pointer rounded-lg max-h-40 border border-white/20 hover:opacity-90 transition bg-white/10" onclick="openImageModal('${msg.attachment_url}')">
                </div>`;
            } else {
                attachmentHtml = `<div class="mb-2">
                    <a href="${msg.attachment_url}" target="_blank" class="flex items-center gap-2 bg-black/10 px-3 py-2 rounded-lg hover:bg-black/20 transition text-inherit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        <span class="underline text-xs font-bold">View File</span>
                    </a>
                </div>`;
            }
        }

        const html = `
            <div class="flex w-full ${alignment} animate-fade-in">
                <div class="flex max-w-[80%] md:max-w-[70%] gap-2 ${innerAlign}">
                    <img src="${msg.user_avatar}" class="w-8 h-8 rounded-full flex-shrink-0 object-cover shadow-sm ${avatarColor}">
                    <div class="flex flex-col ${textAlign}">
                        <div class="px-4 py-2 rounded-2xl text-sm shadow-sm ${bubbleColor}">
                            ${attachmentHtml}
                            ${msg.message ? `<p class="whitespace-pre-wrap">${msg.message}</p>` : ''}
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 flex gap-1 items-center ${isMe ? 'justify-end' : 'justify-start'}">
                            ${msg.time}
                        </p>
                    </div>
                </div>
            </div>
        `;
        
        chatContainer.insertAdjacentHTML('beforeend', html);
    }
</script>

@endsection