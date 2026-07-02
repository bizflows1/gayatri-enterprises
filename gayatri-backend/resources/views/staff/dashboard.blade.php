@extends('layouts.admin') 

@section('title', 'Staff Workspace - Gayatri Enterprises')

@section('content')

<div class="bg-slate-50 min-h-screen pb-12">
    
    <div class="bg-white border-b border-slate-200 pt-8 pb-12 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 brand-font">
                        Welcome Back, <span class="text-blue-700">{{ Auth::user()->name }}</span>
                    </h1>
                    <p class="text-slate-500 mt-1">Here is your work overview for today.</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 flex gap-4 text-xs font-mono text-blue-800">
                    <div>
                        <span class="block text-blue-400 uppercase text-[10px]">ROLE</span>
                        STAFF MEMBER
                    </div>
                    <div>
                        <span class="block text-blue-400 uppercase text-[10px]">TODAY</span>
                        {{ date('d M, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 -mt-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <!-- Pending Tasks -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                <div class="relative grad-blue animate-gradient p-6 rounded-2xl text-white shadow-sm flex flex-col justify-between h-full hover:shadow-lg hover:-translate-y-0.5 transition duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-white/10 text-white rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-blue-100 uppercase tracking-widest">Active Operations</span>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-white tracking-tight">{{ $pendingTasks->count() }}</h3>
                        <p class="text-sm font-medium text-blue-100 mt-1">Pending Tasks</p>
                    </div>
                </div>
            </div>

            <!-- Work Completed -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                <div class="relative grad-green animate-gradient p-6 rounded-2xl text-white shadow-sm flex flex-col justify-between h-full hover:shadow-lg hover:-translate-y-0.5 transition duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-white/10 text-white rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-green-100 uppercase tracking-widest">Compliance</span>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-white tracking-tight">{{ $completedCount }}</h3>
                        <p class="text-sm font-medium text-green-100 mt-1">Work Completed</p>
                    </div>
                </div>
            </div>

            <!-- Notice Board -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl blur opacity-20 group-hover:opacity-30 transition duration-1000"></div>
                <div class="relative grad-purple animate-gradient p-6 rounded-2xl text-white shadow-sm flex flex-col justify-between h-full hover:shadow-lg hover:-translate-y-0.5 transition duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-white/10 text-white rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-purple-100 uppercase tracking-widest">Broadcasts</span>
                    </div>
                    <div class="bg-white/10 rounded-lg p-3 backdrop-blur-sm border border-white/10 min-h-[100px] flex flex-col justify-between w-full">
                        @if($notices->isNotEmpty())
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-indigo-200 mb-1">{{ $notices->first()->type }}</p>
                                <p class="text-xs leading-tight text-white font-bold mb-1">{{ Str::limit($notices->first()->title, 35) }}</p>
                                <p class="text-[10px] leading-relaxed text-purple-100 line-clamp-2 italic">"{{ $notices->first()->content }}"</p>
                            </div>
                            <button @click="$dispatch('notice-open', { index: 0 })" class="mt-2 text-[10px] uppercase font-bold tracking-widest text-white hover:text-indigo-200 transition flex items-center gap-1 self-start">
                                Read All 
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        @else
                            <p class="text-xs leading-relaxed text-purple-200 italic">No important notices for now.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(Auth::user()->hasPermission('manage_clients') || Auth::user()->hasPermission('view_files') || Auth::user()->hasPermission('upload_files'))
        <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2 brand-font mt-8">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Quick Tools
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            @if(Auth::user()->hasPermission('manage_clients'))
            <a href="{{ route('manage.clients') }}" class="group bg-white p-6 rounded-xl border border-slate-200 shadow-sm hover:shadow-md hover:border-purple-400 transition flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 group-hover:text-purple-700">Manage Users</h4>
                    <p class="text-xs text-slate-500">Add or edit clients</p>
                </div>
            </a>
            @endif

            @if(Auth::user()->hasPermission('view_files'))
            <a href="{{ route('documents.manage') }}" class="group bg-white p-6 rounded-xl border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-400 transition flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 group-hover:text-blue-700">Documents</h4>
                    <p class="text-xs text-slate-500">View client files</p>
                </div>
            </a>
            @endif

            @if(Auth::user()->hasPermission('upload_files'))
            <a href="{{ route('file.form') }}" class="group bg-white p-6 rounded-xl border border-slate-200 shadow-sm hover:shadow-md hover:border-green-400 transition flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 group-hover:text-green-700">Upload File</h4>
                    <p class="text-xs text-slate-500">Add new documents</p>
                </div>
            </a>
            @endif
        </div>
        @endif
      
    <div class="max-w-7xl mx-auto px-4 -mt-6" x-data="{
        openDetail: false,
        selectedTask: null,
        submitting: false,
        showTaskDetail(task) {
            this.selectedTask = task;
            this.openDetail = true;
        },
        async markCompletedFromModal() {
            if(!this.selectedTask || this.submitting) return;
            this.submitting = true;
            try {
                const res = await fetch('/tasks/' + this.selectedTask.id + '/update-status', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                if(res.ok) {
                    if(window.roleHubInstance) window.roleHubInstance.dispatchSync();
                    else new BroadcastChannel('portal_sync').postMessage('refresh');
                    window.location.reload();
                }
            } catch(e) { console.error('Update failed'); }
            this.submitting = false;
        }
    }">
        
        <!-- Active Tasks (Existing) -->
        <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2 brand-font">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            My Assigned Tasks
        </h2>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-12 animate-fade-in">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-700">Active Work List (Click any task to view details)</h3>
                <span class="text-xs text-slate-500">Sorted by Deadline</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="px-6 py-3">Task Title</th>
                            <th class="px-6 py-3">Priority</th>
                            <th class="px-6 py-3">Deadline</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($pendingTasks as $task)
                        <tr class="hover:bg-slate-50/80 transition cursor-pointer group" @click="showTaskDetail(@js($task))">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800 group-hover:text-blue-600 transition">{{ $task->title }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ Str::limit($task->description, 50) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs font-bold 
                                    {{ $task->priority == 'high' ? 'bg-red-100 text-red-700' : ($task->priority == 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                    {{ strtoupper($task->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ \Carbon\Carbon::parse($task->due_date)->format('d M, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3" @click.stop>
                                    <a href="{{ route('task.view', $task->id) }}" class="relative text-slate-400 hover:text-blue-600 transition p-2 hover:bg-slate-100 rounded-full" title="Discussion">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                        @php
                                            $unreadCount = \App\Models\Chat::where('task_id', $task->id)->where('user_id', '!=', Auth::id())->where('is_read', false)->count();
                                        @endphp
                                        @if($unreadCount > 0)
                                            <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white text-[9px] font-bold flex items-center justify-center rounded-full border-2 border-white animate-pulse">{{ $unreadCount }}</span>
                                        @endif
                                    </a>
                                    <div x-data="{ 
                                        submittingRow: false,
                                        async accomplish() {
                                            if(this.submittingRow) return;
                                            this.submittingRow = true;
                                            try {
                                                const res = await fetch('{{ route('tasks.updateStatus', $task->id) }}', {
                                                    method: 'POST',
                                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                                });
                                                if(res.ok) {
                                                    if(window.roleHubInstance) window.roleHubInstance.dispatchSync();
                                                    else new BroadcastChannel('portal_sync').postMessage('refresh');
                                                    window.location.reload();
                                                }
                                            } catch(e) { console.error('Update failed'); }
                                            this.submittingRow = false;
                                        }
                                    }">
                                        <button @click="accomplish" 
                                                :disabled="submittingRow"
                                                class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-2 rounded-lg font-medium transition shadow-sm shadow-blue-200 disabled:opacity-50">
                                            <span x-show="!submittingRow">Mark as Done</span>
                                            <span x-show="submittingRow">Processing...</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <p>No pending tasks! Enjoy your day.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Task History (New Section) -->
        <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2 brand-font">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Task History
        </h2>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-700">Recently Completed (Click any task to view details)</h3>
                <span class="text-xs text-slate-500">Last 10 Tasks</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="px-6 py-3">Task Title</th>
                            <th class="px-6 py-3">Completed On</th>
                            <th class="px-6 py-3 text-right">View</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($completedTasks as $task)
                        <tr class="hover:bg-slate-50/80 transition opacity-75 hover:opacity-100 cursor-pointer group" @click="showTaskDetail(@js($task))">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-700 line-through decoration-slate-400 group-hover:text-blue-600 transition">{{ $task->title }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ Str::limit($task->description, 50) }}</p>
                            </td>
                            <td class="px-6 py-4 text-green-600 font-medium">
                                {{ $task->updated_at->format('d M, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div @click.stop>
                                    <a href="{{ route('task.view', $task->id) }}" class="text-slate-400 hover:text-blue-600 transition font-bold text-xs border border-slate-200 px-3 py-1.5 rounded-lg hover:border-blue-400">
                                        View Chat
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-slate-400">
                                <p>No completed tasks yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Task Detail Modal -->
        <div x-show="openDetail" 
             x-cloak
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md transition-all duration-500"
             @keydown.escape.window="openDetail = false"
        >
            <div class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 flex flex-col transform transition-all"
                 x-show="openDetail"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="openDetail = false"
            >
                <template x-if="selectedTask">
                    <div class="h-full flex flex-col">
                        <!-- Header -->
                        <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-start"
                             :class="selectedTask.priority === 'high' ? 'bg-red-50/50' : (selectedTask.priority === 'medium' ? 'bg-amber-50/50' : 'bg-emerald-50/50')">
                            <div class="flex-1 pr-4">
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] px-2.5 py-1 rounded-lg"
                                      :class="selectedTask.priority === 'high' ? 'bg-red-100 text-red-700' : (selectedTask.priority === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')"
                                      x-text="selectedTask.priority.toUpperCase() + ' Priority'">
                                </span>
                                <h3 class="text-2xl font-bold text-slate-800 mt-3 brand-font leading-tight" x-text="selectedTask.title"></h3>
                            </div>
                            <button @click="openDetail = false" class="p-2 text-slate-400 hover:text-slate-600 transition bg-white/80 rounded-full hover:bg-white shadow-sm flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="p-8 space-y-6">
                            <div>
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1.5">Description</h4>
                                <div class="text-slate-600 leading-relaxed text-sm bg-slate-50 p-4 rounded-xl border border-slate-100 max-h-[160px] overflow-y-auto whitespace-pre-line" x-text="selectedTask.description"></div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1.5">Due Date</h4>
                                    <p class="text-slate-700 font-bold text-sm flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span x-text="new Date(selectedTask.due_date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })"></span>
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1.5">Status</h4>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-lg"
                                          :class="selectedTask.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700 border border-yellow-200 animate-pulse'">
                                        <span class="w-2 h-2 rounded-full" :class="selectedTask.status === 'completed' ? 'bg-green-500' : 'bg-yellow-500'"></span>
                                        <span x-text="selectedTask.status === 'completed' ? 'Completed' : 'In Progress'"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div class="pt-6 border-t border-slate-100 flex gap-3">
                                <template x-if="selectedTask.status !== 'completed'">
                                    <button @click="markCompletedFromModal()"
                                            :disabled="submitting"
                                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-emerald-100 flex items-center justify-center gap-2 disabled:opacity-50">
                                        <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span x-show="submitting" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                                        <span x-text="submitting ? 'Processing...' : 'Mark as Done'"></span>
                                    </button>
                                </template>
                                <a :href="'/task/' + selectedTask.id"
                                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-blue-100 flex items-center justify-center gap-2 text-center text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    <span>Discussion & Chat</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

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
            const hasUnread = this.notices.some(n => {
                const pivot = n.users && n.users.find(u => u.id === {{ Auth::id() }})?.pivot;
                return pivot ? !pivot.is_read : true;
            });
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
            if (notice && notice.users) {
                const userNotice = notice.users.find(u => u.id === {{ Auth::id() }});
                if (userNotice && userNotice.pivot) userNotice.pivot.is_read = true;
                else if (userNotice) userNotice.pivot = { is_read: true };
            }
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
                        <div class="flex items-center gap-1" x-show="notices.length > 1">
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


<style>
    .animate-fade-in { animation: fadeIn 0.5s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* Moving gradient animation helper */
    .animate-gradient {
        background-size: 300% 300%;
        animation: gradient-move 8s ease infinite;
    }

    /* Brand-aligned gradients for cards */
    .grad-blue { background: linear-gradient(-45deg, #0F2C4A, #173A5E, #0F2C4A, #1d3a5c); }
    .grad-green { background: linear-gradient(-45deg, #145C3F, #1B7A52, #0d4a32, #1B7A52); }
    .grad-purple { background: linear-gradient(-45deg, #173A5E, #1B7A52, #0F2C4A, #145C3F); }
    .grad-red { background: linear-gradient(-45deg, #7f1d1d, #ef4444, #b91c1c, #dc2626); }

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