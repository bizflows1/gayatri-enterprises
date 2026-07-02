<div id="staff-floating-hub" class="fixed bottom-10 right-10 z-[99999]" x-data="roleHub()">
    <!-- THE BUBBLE (Hidden as requested, triggered via Header) -->
    <div @click="toggle"
         class="hidden cursor-pointer w-14 h-14 bg-slate-900 rounded-full shadow-2xl items-center justify-center transition-transform hover:scale-110 relative border-2 border-slate-700"
         style="box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.6);">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        <template x-if="data.unread > 0">
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-white animate-bounce" x-text="data.unread"></span>
        </template>
    </div>

    <!-- THE PIP OVERLAY -->
    <div x-show="open" 
         style="display: none;"
         @click.away="open = false"
         class="absolute bottom-20 right-0 w-80 bg-white shadow-2xl rounded-2xl overflow-hidden border border-slate-200">
        
        <div class="p-4 bg-slate-900 border-b border-slate-800 flex justify-between items-center text-white">
            <div>
                <h3 class="font-bold text-sm tracking-wide brand-font" x-text="data.admin_mode ? 'Command Center' : 'Task Assistant'"></h3>
                <p class="text-xs text-slate-400 font-medium" x-text="data.admin_mode ? 'Admin Control' : 'Your Workspace'"></p>
            </div>
            <div class="flex items-center gap-1 text-slate-400">
                <button @click="requestPiP" class="p-1.5 hover:text-white transition" title="Always on Top (PiP)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 12l-1 1m11 1l-1-1m-4 5v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2h2m3 9V9a2 2 0 012-2h5a2 2 0 012 2v7a2 2 0 01-2 2h-5a2 2-0 01-2-2z"></path></svg>
                </button>
                <button @click="open = false" class="p-1.5 hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
            </div>
        </div>

        <div class="p-4 overflow-y-auto max-h-[450px] bg-slate-50">
            

            <!-- ADMIN VIEW -->
            <template x-if="data.admin_mode">
                <div>
                    <div class="grid grid-cols-2 gap-2.5 mb-4">
                        <!-- Done Today -->
                        <div class="relative group overflow-hidden rounded-xl">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                            <div class="relative grad-green animate-gradient p-3 rounded-xl text-white shadow-sm flex flex-col justify-between hover:shadow-md transition duration-300 text-center">
                                <span class="text-[9px] font-bold text-green-100 uppercase tracking-wider">Done Today</span>
                                <h3 class="text-xl font-bold text-white mt-1" x-text="data.completed_today">0</h3>
                            </div>
                        </div>

                        <!-- Due Today -->
                        <div class="relative group overflow-hidden rounded-xl">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                            <div class="relative grad-red animate-gradient p-3 rounded-xl text-white shadow-sm flex flex-col justify-between hover:shadow-md transition duration-300 text-center">
                                <span class="text-[9px] font-bold text-red-100 uppercase tracking-wider">Due Today</span>
                                <h3 class="text-xl font-bold text-white mt-1" x-text="data.due_today || 0">0</h3>
                            </div>
                        </div>

                        <!-- Chat Unread -->
                        <div class="relative group overflow-hidden rounded-xl">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                            <div class="relative grad-purple animate-gradient p-3 rounded-xl text-white shadow-sm flex flex-col justify-between hover:shadow-md transition duration-300 text-center">
                                <span class="text-[9px] font-bold text-purple-100 uppercase tracking-wider font-semibold">Chats</span>
                                <h3 class="text-xl font-bold text-white mt-1" x-text="data.team_chat_unread || 0">0</h3>
                            </div>
                        </div>

                        <!-- Mail Unread -->
                        <div class="relative group overflow-hidden rounded-xl">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                            <div class="relative grad-blue animate-gradient p-3 rounded-xl text-white shadow-sm flex flex-col justify-between hover:shadow-md transition duration-300 text-center">
                                <span class="text-[9px] font-bold text-blue-100 uppercase tracking-wider font-semibold">Mails</span>
                                <h3 class="text-xl font-bold text-white mt-1" x-text="data.mailbox_unread || 0">0</h3>
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2 px-1">Active Staff</h4>
                    <div class="space-y-2 mb-4">
                        <template x-for="user in data.active_staff" :key="user.id">
                            <div class="flex items-center justify-between bg-white p-2 rounded-xl border border-slate-100 shadow-sm">
                                <div class="flex items-center gap-2">
                                    <div class="relative flex items-center justify-center w-6 h-6 rounded-md bg-slate-100 text-[10px] font-bold text-slate-600" x-text="user.name.charAt(0)"></div>
                                    <span class="text-xs font-bold text-slate-700" x-text="user.name"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div>
                                        <span class="text-[10px] font-bold text-green-600 uppercase tracking-widest">Online</span>
                                    </div>
                                    <a :href="`/team-chat?user_id=${user.id}`" class="p-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg transition-all hover:scale-105" title="Direct Chat">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </template>
                        <template x-if="!data.active_staff || data.active_staff.length === 0">
                            <div class="text-[10px] text-center text-slate-400 py-2 font-medium">No staff presently online</div>
                        </template>
                    </div>

                    <h4 class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2 px-1">Firm Pending Operations</h4>
                    <div class="space-y-2 mb-4">
                        <template x-for="task in data.tasks" :key="task.id">
                            <div @click="window.location.href = '/task/' + task.id" class="cursor-pointer group bg-white p-3 rounded-xl border border-slate-100 shadow-sm relative hover:border-indigo-400 transition-all hover:shadow-md duration-300">
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-[8px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded border bg-slate-50 text-slate-600 border-slate-200"
                                          x-text="task.assignees && task.assignees.length > 0 ? task.assignees[0].name : 'Unassigned'">
                                    </span>
                                    <span class="text-[8px] font-bold text-slate-400 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                        <span x-text="task.due_date || 'No Deadline'"></span>
                                    </span>
                                </div>
                                <h4 class="text-xs font-bold text-slate-800 leading-tight" x-text="task.title"></h4>
                            </div>
                        </template>
                        <template x-if="data.tasks.length === 0">
                            <div class="text-[10px] text-center text-slate-400 py-4 font-medium border border-dashed border-slate-200 rounded-xl">No pending tasks firm-wide!</div>
                        </template>
                    </div>

                    
                    <a href="{{ route('tasks.assign') }}" class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl font-bold text-xs transition shadow shadow-blue-500/20">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add New Task
                    </a>
                </div>
            </template>

            <!-- STAFF VIEW -->
            <template x-if="!data.admin_mode">
                <div>
                    <div class="grid grid-cols-2 gap-2.5 mb-4">
                        <!-- Pending Tasks -->
                        <div class="relative group overflow-hidden rounded-xl">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                            <div class="relative grad-red animate-gradient p-3 rounded-xl text-white shadow-sm flex flex-col justify-between hover:shadow-md transition duration-300 text-center">
                                <span class="text-[9px] font-bold text-red-100 uppercase tracking-wider">Pending</span>
                                <h3 class="text-xl font-bold text-white mt-1" x-text="data.pending || 0">0</h3>
                            </div>
                        </div>

                        <!-- Done Today -->
                        <div class="relative group overflow-hidden rounded-xl">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                            <div class="relative grad-green animate-gradient p-3 rounded-xl text-white shadow-sm flex flex-col justify-between hover:shadow-md transition duration-300 text-center">
                                <span class="text-[9px] font-bold text-green-100 uppercase tracking-wider">Done Today</span>
                                <h3 class="text-xl font-bold text-white mt-1" x-text="data.completed_today || 0">0</h3>
                            </div>
                        </div>

                        <!-- Chat Unread -->
                        <div class="relative group overflow-hidden rounded-xl">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                            <div class="relative grad-purple animate-gradient p-3 rounded-xl text-white shadow-sm flex flex-col justify-between hover:shadow-md transition duration-300 text-center">
                                <span class="text-[9px] font-bold text-purple-100 uppercase tracking-wider font-semibold">Chats</span>
                                <h3 class="text-xl font-bold text-white mt-1" x-text="data.team_chat_unread || 0">0</h3>
                            </div>
                        </div>

                        <!-- Mail Unread -->
                        <div class="relative group overflow-hidden rounded-xl">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                            <div class="relative grad-blue animate-gradient p-3 rounded-xl text-white shadow-sm flex flex-col justify-between hover:shadow-md transition duration-300 text-center">
                                <span class="text-[9px] font-bold text-blue-100 uppercase tracking-wider font-semibold">Mails</span>
                                <h3 class="text-xl font-bold text-white mt-1" x-text="data.mailbox_unread || 0">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2 mb-4">
                        <template x-for="task in data.tasks" :key="task.id">
                            <div @click="window.location.href = '/task/' + task.id" class="cursor-pointer group bg-white p-3 rounded-xl border border-slate-100 shadow-sm hover:border-indigo-400 transition-all hover:shadow-md duration-300">
                                <div class="flex justify-between items-center mb-1.5">
                                    <span :class="{
                                        'bg-red-50 text-red-600 border-red-100': task.priority === 'urgent' || task.priority === 'high',
                                        'bg-blue-50 text-blue-600 border-blue-100': task.priority === 'medium',
                                        'bg-slate-50 text-slate-600 border-slate-200': task.priority === 'low'
                                    }" class="text-[8px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded border" x-text="task.priority"></span>
                                    
                                    <span class="text-[8px] font-bold text-slate-400 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                        <span x-text="task.due_date || 'No Deadline'"></span>
                                    </span>
                                </div>
                                <div class="flex justify-between items-start gap-4">
                                    <h4 class="text-xs font-bold text-slate-800 leading-tight pr-1" x-text="task.title"></h4>
                                    <button @click.stop="markDone(task.id)" class="shrink-0 p-1 bg-green-50 hover:bg-green-500 text-green-600 hover:text-white rounded-lg transition-all border border-green-100" title="Mark Done">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="data.tasks.length === 0">
                            <div class="text-[10px] text-center text-slate-400 py-4 font-medium border border-dashed border-slate-200 rounded-xl">Clear inbox. Great job!</div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Hub Footer - Controls removed as per request -->
    </div>
</div>

<script>
function roleHub() {
    return {
        open: false,
        data: { pending: 0, completed_today: 0, due_today: 0, unread: 0, tasks: [], active_staff: [], admin_mode: false },
        pipWindow: null,
        syncChannel: null,
        audioPlayer: null,
        lastChatUnread: 0,
        lastMailUnread: 0,

        init() {
            window.roleHubInstance = this;
            window.openDirectChatFromPiP = (userId) => {
                window.location.href = '/team-chat?user_id=' + userId;
                window.focus();
            };
            window.openTaskDetailFromPiP = (taskId) => {
                window.location.href = '/task/' + taskId;
                window.focus();
            };
            
            // Sounds disabled
            this.audioPlayer = null;

            this.fetchData();
            
            // 5-second Polling for high-frequency updates
            setInterval(() => this.fetchData(), 5000);
            
            // Heartbeat for presence
            this.pingPresence();
            setInterval(() => this.pingPresence(), 60000);

            // BroadcastChannel for Instant Sync between tabs
            this.syncChannel = new BroadcastChannel('portal_sync');
            this.syncChannel.onmessage = (event) => {
                if (event.data === 'refresh') {
                    this.fetchData();
                }
            };

            // Auto PiP on tab switch/minimize (Requires prior interaction on the page)
            document.addEventListener('visibilitychange', () => {
                if (document.hidden && !this.pipWindow) {
                    this.requestPiP().catch(e => console.log('PiP auto-open required user gesture or failed', e));
                }
            });
        },

        dispatchSync() {
            if (this.syncChannel) this.syncChannel.postMessage('refresh');
        },

        pingPresence() {
            fetch('/api/ping', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).catch(() => {});
        },

        async fetchData() {
            try {
                const res = await fetch('/api/staff/summary');
                if (!res.ok) throw new Error("Fetch failed");
                const newData = await res.json();
                
                // Track unread counts (sounds disabled)
                const currentChat = newData.team_chat_unread || 0;
                const currentMail = newData.mailbox_unread || 0;
                
                this.lastChatUnread = currentChat;
                this.lastMailUnread = currentMail;
                this.data = newData;
                
                // Only update PIP if data actually changed to avoid flickering
                if (this.pipWindow) {
                    this.updatePiPContent();
                }
            } catch (e) { console.error('Hub sync error'); }
        },

        toggle() { this.open = !this.open; },

        async markDone(id) {
            try {
                const res = await fetch(`/api/task/${id}/status`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                if (res.ok) {
                    this.dispatchSync(); // Notify all tabs
                    this.fetchData();
                }
            } catch (e) { console.error("Update failed"); }
        },

        async requestPiP() {
            if (!window.documentPictureInPicture) {
                console.log("PiP requires Chrome/Edge/Safari on Desktop.");
                return;
            }
            try {
                this.pipWindow = await window.documentPictureInPicture.requestWindow({ width: 340, height: 480 });
                this.updatePiPContent();
                this.pipWindow.addEventListener("pagehide", () => this.pipWindow = null);

                // Copy styles to PIP window for better font rendering
                const style = document.createElement("style");
                style.textContent = `
                    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
                    body { font-family: 'Inter', sans-serif; margin: 0; background: #f8fafc; color: #0f172a; -webkit-font-smoothing: antialiased; }
                    .scrollbar-hide::-webkit-scrollbar { display: none; }
                    .task-card { background: #ffffff !important; border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
                    .task-card:hover { background: #f1f5f9 !important; transform: translateY(-2px); border-color: #cbd5e1 !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
                    .staff-card { background: #ffffff !important; border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
                    .staff-card:hover { background: #f1f5f9 !important; }
                    
                    @keyframes gradient-move {
                        0% { background-position: 0% 50%; }
                        50% { background-position: 100% 50%; }
                        100% { background-position: 0% 50%; }
                    }
                    .animate-gradient {
                        background-size: 300% 300%;
                        animation: gradient-move 8s ease infinite;
                    }
                    .grad-blue { background: linear-gradient(-45deg, #1e3a8a, #3b82f6, #1d4ed8, #2563eb); }
                    .grad-green { background: linear-gradient(-45deg, #064e3b, #10b981, #047857, #059669); }
                    .grad-purple { background: linear-gradient(-45deg, #4c1d95, #8b5cf6, #6d28d9, #7c3aed); }
                    .grad-red { background: linear-gradient(-45deg, #7f1d1d, #ef4444, #b91c1c, #dc2626); }
                    
                    /* Card wrapper styles for high-fidelity PiP display */
                    .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
                    .stat-card {
                        position: relative;
                        overflow: hidden;
                        padding: 10px;
                        border-radius: 12px;
                        border: 1px solid rgba(255,255,255,0.15);
                        text-align: center;
                        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
                        transition: all 0.3s ease;
                    }
                    .stat-card:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
                    }
                    .stat-title { font-size: 8px; font-weight: 800; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em; }
                    .stat-value { font-size: 20px; font-weight: 800; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.1); }
                `;
                this.pipWindow.document.head.appendChild(style);

            } catch (e) { console.error("PiP Failed:", e); }
        },

        updatePiPContent() {
            if (!this.pipWindow) return;
            const doc = this.pipWindow.document;
            const isAdmin = this.data.admin_mode;
            
            doc.body.innerHTML = `
                <div style="display:flex; flex-direction:column; height: 100vh; overflow:hidden; box-sizing:border-box;">
                    <!-- HEADER -->
                    <div style="background: #ffffff; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; flex-shrink:0; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <img src="/pwa-icon.png" style="width:20px; height:20px; border-radius:4px; border:1px solid #cbd5e1;">
                                <div>
                                    <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.05em; color: #0f172a;">${isAdmin ? 'COMMAND CENTER' : 'TASK ASSISTANT'}</div>
                                    <div style="font-size: 8px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-top:1px;">Firm Workspace</div>
                                </div>
                            </div>
                            <div style="background: #2563eb; width: 6px; height: 6px; border-radius: 50%; box-shadow: 0 0 8px #2563eb;"></div>
                        </div>
                    </div>

                    <!-- CONTENT -->
                    <div style="flex:1; overflow-y:auto; padding: 16px; background: #f8fafc;" class="scrollbar-hide">
                        ${isAdmin ? `
                            <!-- ADMIN STATS -->
                            <div class="stat-grid">
                                <!-- Done Today -->
                                <div class="stat-card grad-green animate-gradient">
                                    <div class="stat-title" style="color: #a7f3d0;">Done Today</div>
                                    <div class="stat-value">${this.data.completed_today}</div>
                                </div>
                                <!-- Due Today -->
                                <div class="stat-card grad-red animate-gradient">
                                    <div class="stat-title" style="color: #fecaca;">Due Today</div>
                                    <div class="stat-value">${this.data.due_today || 0}</div>
                                </div>
                                <!-- Chat Notifications -->
                                <div class="stat-card grad-purple animate-gradient">
                                    <div class="stat-title" style="color: #ddd6fe;">Chats</div>
                                    <div class="stat-value">${this.data.team_chat_unread || 0}</div>
                                </div>
                                <!-- Mail Notifications -->
                                <div class="stat-card grad-blue animate-gradient">
                                    <div class="stat-title" style="color: #bfdbfe;">Mails</div>
                                    <div class="stat-value">${this.data.mailbox_unread || 0}</div>
                                </div>
                            </div>

                            <!-- ACTIVE STAFF -->
                            <div style="font-size: 10px; font-weight:800; color: #64748b; margin-bottom: 10px; text-transform:uppercase; letter-spacing: 0.03em;">Active Staff</div>
                            ${this.data.active_staff?.length ? this.data.active_staff.map(u => `
                                <div class="staff-card" style="padding: 10px; margin-bottom: 8px; display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-size:12px; font-weight:600; color:#0f172a;">${u.name}</span>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="display:flex; align-items:center; gap:4px; margin-right:4px;">
                                            <div style="width:5px; height:5px; background:#22c55e; border-radius:50%;"></div>
                                            <span style="font-size:9px; color:#22c55e; font-weight:800;">LIVE</span>
                                        </div>
                                        <button onclick="if(window.opener) { window.opener.openDirectChatFromPiP(${u.id}); }" style="cursor:pointer; outline:none; border:none; background:rgba(99,102,241,0.1); color:#4f46e5; padding:5px 8px; border-radius:6px; font-size:10px; font-weight:800; display:flex; align-items:center; gap:4px; transition:all 0.2s;" onmouseover="this.style.background='rgba(99,102,241,0.2)';" onmouseout="this.style.background='rgba(99,102,241,0.1)';">
                                            Chat
                                        </button>
                                    </div>
                                </div>
                            `).join('') : '<div style="font-size:11px; color:#64748b; text-align:center; padding:10px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px;">No staff online</div>'}

                            <!-- PENDING OPERATIONS (ADMIN) -->
                            <div style="font-size: 10px; font-weight:800; color: #64748b; margin-top:20px; margin-bottom: 10px; text-transform:uppercase; letter-spacing: 0.03em;">Firm Pending Tasks</div>
                            ${this.data.tasks?.length ? this.data.tasks.map(t => {
                                const assigneeName = t.assignees && t.assignees.length > 0 ? t.assignees[0].name : 'Unassigned';
                                return `
                                    <div class="task-card" onclick="if(window.opener) { window.opener.openTaskDetailFromPiP(${t.id}); }" style="padding: 12px; margin-bottom: 10px; cursor:pointer;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                            <span style="font-size: 8px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 2px 6px; border-radius: 4px; font-weight:800; text-transform:uppercase;">${assigneeName}</span>
                                            <span style="font-size: 9px; color: #64748b; font-weight:800;">${t.due_date || 'NO DATE'}</span>
                                        </div>
                                        <div style="font-size: 12px; font-weight:700; color:#0f172a; line-height:1.4;">${t.title}</div>
                                    </div>
                                `;
                            }).join('') : '<div style="font-size:11px; color:#64748b; text-align:center; padding:10px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px;">No pending tasks</div>'}
                        ` : `
                            <!-- STAFF STATS -->
                            <div class="stat-grid">
                                <!-- Pending Tasks -->
                                <div class="stat-card grad-red animate-gradient">
                                    <div class="stat-title" style="color: #fecaca;">Pending</div>
                                    <div class="stat-value">${this.data.pending || 0}</div>
                                </div>
                                <!-- Done Today -->
                                <div class="stat-card grad-green animate-gradient">
                                    <div class="stat-title" style="color: #a7f3d0;">Done Today</div>
                                    <div class="stat-value">${this.data.completed_today || 0}</div>
                                </div>
                                <!-- Chat Notifications -->
                                <div class="stat-card grad-purple animate-gradient">
                                    <div class="stat-title" style="color: #ddd6fe;">Chats</div>
                                    <div class="stat-value">${this.data.team_chat_unread || 0}</div>
                                </div>
                                <!-- Mail Notifications -->
                                <div class="stat-card grad-blue animate-gradient">
                                    <div class="stat-title" style="color: #bfdbfe;">Mails</div>
                                    <div class="stat-value">${this.data.mailbox_unread || 0}</div>
                                </div>
                            </div>

                            <!-- STAFF TASKS -->
                            <div style="font-size: 10px; font-weight:800; color: #64748b; margin-top:20px; margin-bottom: 12px; text-transform:uppercase; letter-spacing: 0.03em;">Pending Operations</div>
                            ${this.data.tasks.length ? this.data.tasks.map(t => {
                                const pColor = t.priority === 'urgent' || t.priority === 'high'
                                    ? 'background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;'
                                    : (t.priority === 'medium'
                                        ? 'background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;'
                                        : 'background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;');
                                return `
                                    <div class="task-card" onclick="if(window.opener) { window.opener.openTaskDetailFromPiP(${t.id}); }" style="padding: 12px; margin-bottom: 10px; cursor:pointer;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                            <span style="font-size: 8px; padding: 2px 6px; border-radius: 4px; font-weight:800; text-transform:uppercase; ${pColor}">${t.priority}</span>
                                            <span style="font-size: 9px; color: #64748b; font-weight:800;">${t.due_date || 'NO DATE'}</span>
                                        </div>
                                        <div style="font-size: 12px; font-weight:700; color:#0f172a; line-height:1.4;">${t.title}</div>
                                    </div>
                                `;
                            }).join('') : '<div style="font-size:11px; color:#64748b; text-align:center; padding:10px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px;">No pending tasks</div>'}
                        `}
                    </div>

                    <!-- FOOTER -->
                    <div style="background: #ffffff; padding: 12px; border-top: 1px solid #e2e8f0; text-align:center; flex-shrink:0;">
                         <div style="font-size: 9px; color: #64748b; font-weight:800;">Refreshed: ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'})}</div>
                    </div>
                </div>
            `;
        }
    };
}
</script>
