@extends('layouts.admin')

@section('content')
<div x-data="advancedChat()" class="h-full w-full flex flex-col overflow-hidden bg-white" style="min-height:0;">
<div class="flex-1 flex flex-col md:flex-row bg-white md:bg-white/90 md:backdrop-blur-md md:rounded-2xl md:shadow-2xl md:border md:border-slate-200 overflow-hidden" style="min-height:0;">
    
    <!-- LEFT PANE: Conversations List -->
    <div class="w-full md:w-80 md:border-r border-slate-200 bg-white flex flex-col z-10" :class="{'hidden md:flex': activeChat}">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-1.5 -ml-1.5 text-slate-500 hover:bg-slate-200 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h2 class="text-xl font-bold brand-font text-slate-800">Chats</h2>
            </div>
            <button @click="openModal('newChat')" class="text-white btn-premium-indigo p-2.5 rounded-full transition shadow-md" title="New Message">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </button>
        </div>

        
        <div class="p-3">
            <input type="text" x-model="searchQuery" placeholder="Search conversations..." class="w-full bg-slate-100/80 border-none rounded-xl text-sm px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition shadow-inner">
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-hide divide-y divide-slate-100/60">
            <template x-for="convo in filteredConversations" :key="convo.id">
                <div @click="selectConversation(convo)" @contextmenu.prevent="openConvoMenu($event, convo)" 
                     class="flex items-center gap-3 p-3.5 cursor-pointer transition-all duration-300 relative" 
                     :class="activeChat?.id === convo.id ? 'premium-active-chat' : 'premium-inactive-chat'">
                    <div class="relative shrink-0">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-extrabold text-lg shadow-sm overflow-hidden"
                             :class="{
                                 'bg-gradient-to-tr from-indigo-500 to-purple-500': convo.id % 5 === 0,
                                 'bg-gradient-to-tr from-teal-400 to-emerald-500': convo.id % 5 === 1,
                                 'bg-gradient-to-tr from-orange-400 to-amber-500': convo.id % 5 === 2,
                                 'bg-gradient-to-tr from-pink-500 to-rose-500': convo.id % 5 === 3,
                                 'bg-gradient-to-tr from-sky-400 to-indigo-500': convo.id % 5 === 4,
                             }">
                            <template x-if="convo.icon">
                                <img :src="convo.icon.startsWith('/') ? convo.icon : '/chat/file/'+convo.icon" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!convo.icon">
                                <div class="w-full h-full flex items-center justify-center">
                                    <template x-if="!convo.is_group && convo.users.find(u => u.id !== userId)?.avatar_url">
                                        <img :src="convo.users.find(u => u.id !== userId).avatar_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="convo.is_group || !convo.users.find(u => u.id !== userId)?.avatar_url">
                                        <span x-text="getConvoInitial(convo)"></span>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <template x-if="!convo.is_group && isUserOnline(convo)">
                            <div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full premium-online-indicator"></div>
                        </template>
                    </div>
                    
                    <div class="flex-1 overflow-hidden">
                        <div class="flex justify-between items-center mb-1">
                            <h4 class="font-bold text-slate-800 truncate text-sm" x-text="getConvoName(convo)"></h4>
                            <template x-if="convo.is_pinned_chat">
                                <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/></svg>
                            </template>
                        </div>
                        <div class="flex items-center gap-1 overflow-hidden">
                            <template x-if="convo.latest_message && convo.latest_message.user_id == userId">
                                <span class="shrink-0 flex items-center">
                                    <template x-if="convo.latest_message.is_read">
                                        <svg class="w-3.5 h-3.5 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 12l4 4L15 5" />
                                            <path d="M8 12l4 4L20 5" />
                                        </svg>
                                    </template>
                                    <template x-if="!convo.latest_message.is_read">
                                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12l4 4L17 5" />
                                        </svg>
                                    </template>
                                </span>
                            </template>
                            <p class="text-xs text-slate-500 truncate flex-1" x-text="convo.latest_message ? (convo.latest_message.body || 'Attachment') : 'No messages yet'"></p>
                        </div>
                    </div>

                    <template x-if="convo.unread_count > 0">
                        <div class="absolute top-1/2 -translate-y-1/2 right-4 premium-unread-badge text-white text-[10px] w-6 h-6 flex items-center justify-center rounded-full font-black shadow-md" x-text="convo.unread_count"></div>
                    </template>
                </div>
            </template>

        </div>
    </div>

    <!-- RIGHT PANE: Chat Window -->
    <div class="flex-1 flex flex-col bg-slate-50/50" :class="{'hidden md:flex': !activeChat}" style="min-height:0;">
        
        <template x-if="!activeChat">
            <div class="flex h-full items-center justify-center flex-col text-slate-400 p-8 text-center">
                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                </div>
                <h3 class="text-xl font-bold mb-2 text-slate-800">Welcome to Team Chat</h3>
                <p class="text-sm max-w-xs mb-6">Select a colleague from the left to start messaging, or click the <span class="text-blue-600 font-bold px-1">+</span> button to start a new chat or group.</p>
                <button @click="openModal('newChat')" class="btn-moving-gradient text-white px-6 py-2.5 rounded-xl font-bold shadow-lg transition">
                    Start New Conversation
                </button>
            </div>
        </template>

        <template x-if="activeChat">
            <div class="flex flex-col h-full w-full relative" style="min-height:0;" @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false" @drop.prevent="handleDrop($event)">
                
                <!-- Drag Drop Overlay -->
                <div x-show="dragOver" class="absolute inset-0 z-[60] bg-blue-500/10 backdrop-blur-sm border-4 border-dashed border-blue-500 rounded-2xl flex items-center justify-center pointer-events-none" x-transition x-cloak>
                    <div class="bg-white px-6 py-4 rounded-xl shadow-2xl flex flex-col items-center">
                        <svg class="w-12 h-12 text-blue-500 mb-2 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <h3 class="font-bold text-lg text-slate-800">Drop files to attach</h3>
                    </div>
                </div>
                <!-- Header -->
                <div class="h-16 px-6 border-b border-slate-200 bg-white/80 backdrop-blur-md flex items-center justify-between shadow-sm sticky top-0 z-20 shrink-0">
                    <div class="flex items-center gap-4">
                        <button @click="goBackToList()" class="md:hidden text-slate-500 hover:text-slate-800 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold shadow-inner overflow-hidden">
                            <template x-if="activeChat.icon">
                                <img :src="activeChat.icon.startsWith('/') ? activeChat.icon : '/chat/file/'+activeChat.icon" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!activeChat.icon">
                                <div class="w-full h-full flex items-center justify-center">
                                    <template x-if="!activeChat.is_group && activeChat.users.find(u => u.id !== userId)?.avatar_url">
                                        <img :src="activeChat.users.find(u => u.id !== userId).avatar_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="activeChat.is_group || !activeChat.users.find(u => u.id !== userId)?.avatar_url">
                                        <span x-text="getConvoInitial(activeChat)"></span>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-slate-800 truncate text-sm sm:text-base" x-text="getConvoName(activeChat)"></h3>
                            <span class="text-[10px] text-slate-400 block truncate" x-text="getPresenceText(activeChat)"></span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-1">
                        <!-- Search Icon -->
                        <button @click="showSearchBar = !showSearchBar" class="p-2 text-slate-400 hover:text-blue-600 transition" title="Search">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                        <!-- Starred Messages -->
                        <button @click="openStarredDrawer()" class="p-2 text-slate-400 hover:text-yellow-500 transition" title="Starred Messages">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </button>
                        <!-- Clear Group Chats (Admin only) -->
                        <template x-if="currentUserRole === 'admin'">
                            <button @click="showClearGroupModal = true" class="p-2 text-slate-400 hover:text-red-500 transition" title="Clear All Messages">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </template>
                        <!-- Group Settings Icon (Admin/Creator only) -->
                        <template x-if="activeChat.is_group && (activeChat.created_by == userId || currentUserRole === 'admin')">
                            <button @click="openGroupSettings()" class="p-2 text-slate-400 hover:text-blue-600 transition" title="Group Settings">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Message Search Bar -->
                <div x-show="showSearchBar" x-transition class="p-2 bg-white border-b border-slate-200 flex gap-2">
                    <input type="text" x-model="messageSearchQuery" @input.debounce.500ms="searchMessages" placeholder="Search in this chat..." class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-1 focus:ring-blue-500">
                    <button @click="showSearchBar = false; messageSearchQuery = ''; searchResults = []" class="text-slate-400 hover:text-red-500 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Search Results Overlay -->
                <div x-show="searchResults.length > 0" class="absolute top-32 left-0 w-full max-h-60 overflow-y-auto bg-white shadow-lg border-b border-slate-200 z-30 p-2 space-y-2">
                    <h4 class="text-xs font-bold text-slate-400 px-2">Search Results</h4>
                    <template x-for="res in searchResults" :key="res.id">
                        <div @click="jumpToMessage(res.id)" class="p-2 hover:bg-slate-50 rounded-lg cursor-pointer border border-transparent hover:border-slate-100 transition">
                            <p class="text-xs text-slate-800" x-text="res.body"></p>
                            <span class="text-[10px] text-slate-400" x-text="formatTime(res.created_at)"></span>
                        </div>
                    </template>
                </div>

                <!-- Pinned Message Banner -->
                <template x-if="pinnedMessage">
                    <div @click="jumpToMessage(pinnedMessage.id)" class="bg-amber-50 border-b border-amber-200 px-4 py-2 flex items-center gap-3 cursor-pointer hover:bg-amber-100 transition">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-2.786-1.115V11a1 1 0 11-2 0V6.396L6.237 5.012l-1.233.616a1 1 0 11-.894-1.79l1.599-.8L9 4.323V3a1 1 0 011-1z"></path></svg>
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Pinned</span>
                            <p class="text-xs text-slate-700 truncate" x-text="pinnedMessage.body || 'Attachment'"></p>
                        </div>
                        <button @click.stop="togglePin(pinnedMessage)" class="text-amber-400 hover:text-red-500 p-1 shrink-0" title="Unpin">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </template>

                <!-- Messages -->
                <div id="advanced-messages-box" @scroll="checkScrollPosition()" 
                    class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 bg-slate-50 relative pb-12 sm:pb-6" 
                    style="overflow-x:hidden; min-height:0; background-image: radial-gradient(#e0e7ff 0.8px, transparent 0.8px); background-size: 24px 24px;">
                    <template x-for="(msg, index) in messages" :key="msg.id">
                        <div>
                            <template x-if="shouldShowDivider(msg, index)">
                                <div class="flex justify-center my-4">
                                    <span class="px-3 py-1 bg-white border border-slate-200/60 shadow-sm rounded-full text-[10px] font-extrabold text-slate-500 uppercase tracking-widest" x-text="formatDateDivider(msg.created_at)"></span>
                                </div>
                            </template>
                            <div :id="'msg-'+msg.id" class="flex" :class="isMe(msg) ? 'justify-end' : 'justify-start'">
                                <div class="max-w-[75%] group relative">
                                
                                <template x-if="msg.parent">
                                    <div @click="jumpToMessage(msg.parent.id)" class="mb-1 text-[10px] text-slate-400 px-2 truncate border-l-2 border-indigo-300 ml-2 cursor-pointer hover:text-indigo-500 hover:border-indigo-500 transition">
                                        Replying to: <span class="font-semibold" x-text="msg.parent.body || 'Attachment'"></span>
                                    </div>
                                </template>

                                <div class="flex gap-2 relative">
                                    <template x-if="!isMe(msg)">
                                        <img :src="msg.user ? msg.user.avatar_url : 'https://ui-avatars.com/api/?name=User&background=6366f1&color=fff&bold=true'" class="w-6 h-6 rounded-full object-cover mt-auto shrink-0 shadow-sm border border-white">
                                    </template>
                                    
                                    <div class="p-3 shadow-[0_4px_16px_-4px_rgba(0,0,0,0.06)] transition-all duration-300 relative group" :class="isMe(msg) ? 'premium-own-bubble text-white' : 'premium-other-bubble text-slate-800'">
                                        <!-- Star Icon -->
                                        <template x-if="msg.is_starred">
                                            <div class="absolute -top-1 -right-1 text-yellow-500 fill-current bg-white rounded-full p-0.5 shadow-sm">
                                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            </div>
                                        </template>

                                        <template x-if="msg.is_deleted_globally_flag">
                                            <div class="flex items-center gap-2 italic text-slate-400 py-1">
                                                <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"></circle><path d="M4.93 4.93l14.14 14.14" stroke-width="2"></path></svg>
                                                <span class="text-sm" x-text="msg.body"></span>
                                            </div>
                                        </template>
                                        <template x-if="!msg.is_deleted_globally_flag">
                                            <div>
                                                <template x-if="!isMe(msg) && activeChat.is_group && msg.user">
                                                    <div class="text-[10px] font-black tracking-wider mb-1 select-none"
                                                         :class="{
                                                             'text-indigo-600': msg.user.id % 5 === 0,
                                                             'text-teal-600': msg.user.id % 5 === 1,
                                                             'text-orange-600': msg.user.id % 5 === 2,
                                                             'text-pink-600': msg.user.id % 5 === 3,
                                                             'text-sky-600': msg.user.id % 5 === 4,
                                                         }" x-text="msg.user.name"></div>
                                                </template>
                                                <p class="text-sm whitespace-pre-wrap leading-relaxed" x-text="msg.body"></p>
                                            </div>
                                        </template>                                            
                                        
                                        <template x-if="msg.attachment">
                                            <div class="mt-2">
                                                <template x-if="isImage(msg.attachment)">
                                                    <div class="rounded-lg overflow-hidden border border-slate-200 max-w-[250px]">
                                                        <img :src="getAttachmentUrl(msg.attachment)" class="w-full h-auto cursor-pointer hover:opacity-90 transition lg:max-h-[300px] object-cover" @click.stop="previewImage = getAttachmentUrl(msg.attachment)">
                                                    </div>
                                                </template>
                                                <template x-if="!isImage(msg.attachment)">
                                                    <a :href="getAttachmentUrl(msg.attachment) + (getAttachmentUrl(msg.attachment).includes('?') ? '&' : '?') + 'download=1'" download class="flex items-center gap-2 bg-slate-100 hover:bg-slate-200 p-2.5 rounded-xl text-xs font-bold transition border border-slate-200" :class="isMe(msg) ? 'text-blue-900' : 'text-slate-700'">
                                                        <!-- PDF -->
                                                        <template x-if="isAttachmentPdf(msg.attachment)">
                                                            <div class="w-9 h-9 rounded-lg bg-red-100 flex flex-col items-center justify-center shrink-0">
                                                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 7V3.5L18.5 9H13zm-3 5h1.5v.5H10v.5h1.5v1H10v1H8.5v-4H10v1zm3.5 1.5c0 .83-.67 1.5-1.5 1.5H11v-4h1c.83 0 1.5.67 1.5 1.5v1zm3.5-1.5h-1v1h1v1h-1v1H15v-4h2.5v1z"/></svg>
                                                                <span class="text-[8px] font-black text-red-600 leading-none">PDF</span>
                                                            </div>
                                                        </template>
                                                        <!-- Word/Doc -->
                                                        <template x-if="isAttachmentWord(msg.attachment)">
                                                            <div class="w-9 h-9 rounded-lg bg-indigo-100 flex flex-col items-center justify-center shrink-0">
                                                                <svg class="w-5 h-5 text-indigo-700" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 7V3.5L18.5 9H13zM8 16l1.5-6 1.5 4 1.5-4 1.5 6h-1l-.85-3.4L10.5 16l-1.15-3.4L8.7 16H8z"/></svg>
                                                                <span class="text-[8px] font-black text-indigo-700 leading-none">DOC</span>
                                                            </div>
                                                        </template>
                                                        <!-- Other files -->
                                                        <template x-if="isAttachmentOther(msg.attachment)">
                                                            <div class="w-9 h-9 rounded-lg bg-slate-200 flex flex-col items-center justify-center shrink-0">
                                                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                                <span class="text-[8px] font-black text-slate-500 leading-none">FILE</span>
                                                            </div>
                                                        </template>
                                                        <div class="flex-1 overflow-hidden">
                                                            <p class="truncate" x-text="getAttachmentName(msg.attachment)"></p>
                                                            <p class="text-[9px] opacity-60">Click to view document</p>
                                                        </div>
                                                        <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5 5 5-5M12 3v12"></path></svg>
                                                    </a>
                                                </template>
                                            </div>
                                        </template>

                                            <div class="flex items-center justify-end gap-1 mt-1.5 opacity-80">
                                                <span class="text-[9px]" x-text="formatTime(msg.created_at)"></span>
                                                <template x-if="isMe(msg)">
                                                    <div class="flex items-center">
                                                        <template x-if="msg.is_read">
                                                            <!-- Sky blue / cyan for double-ticks inside bubbles -->
                                                            <svg class="w-3.5 h-3.5 text-sky-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M3 12l4 4L15 5" />
                                                                <path d="M8 12l4 4L20 5" />
                                                            </svg>
                                                        </template>
                                                        <template x-if="!msg.is_read">
                                                            <!-- Semi-translucent white for single tick on sender message -->
                                                            <svg class="w-3.5 h-3.5 text-white/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M5 12l4 4L17 5" />
                                                            </svg>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                    </div>
                                    
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute top-0 text-slate-400 flex gap-0.5 z-30" :class="isMe(msg) ? 'right-full mr-1' : 'left-full ml-1'">
                                        <template x-if="!msg.is_deleted_globally_flag">
                                            <div class="flex gap-1 sm:gap-0.5 bg-white shadow-xl rounded-full px-2 py-1 sm:px-1 sm:py-0.5 border border-slate-200">
                                                <button @click="setReply(msg)" class="hover:text-blue-600 p-2 sm:p-1" title="Reply"><svg class="w-5 h-5 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg></button>
                                                <button @click="toggleStar(msg)" class="hover:text-yellow-500 p-2 sm:p-1" :class="{'text-yellow-500': msg.is_starred}" title="Star"><svg class="w-5 h-5 sm:w-3.5 sm:h-3.5" :fill="msg.is_starred ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg></button>
                                                <button @click="openForwardModal(msg)" class="hover:text-green-600 p-2 sm:p-1" title="Forward"><svg class="w-5 h-5 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg></button>
                                                <template x-if="activeChat && activeChat.is_group">
                                                    <button @click="togglePin(msg)" class="hover:text-amber-600 p-2 sm:p-1" :class="{'text-amber-600': msg.is_pinned}" title="Pin"><svg class="w-5 h-5 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg></button>
                                                </template>
                                                 <template x-if="isMe(msg)">
                                                     <button @click="openDeleteModal(msg)" class="hover:text-red-600 p-2 sm:p-1" title="Delete"><svg class="w-5 h-5 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                                 </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </template>
                </div>

                <!-- Scroll to Bottom Button -->
                <button x-show="showScrollBtn" x-transition @click="scrollToBottom()" 
                    class="absolute bottom-24 right-4 z-20 w-10 h-10 btn-premium-indigo text-white rounded-full shadow-xl flex items-center justify-center transition-all hover:scale-110" x-cloak>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                </button>

                <!-- Reply Preview -->
                <template x-if="replyingTo">
                    <div class="shrink-0 bg-slate-100 border-t border-slate-200 px-4 py-2 flex justify-between items-center text-xs text-slate-600 z-10">
                        <div class="flex items-center gap-2 border-l-2 border-blue-500 pl-2">
                            <span class="font-bold">Replying to:</span>
                            <span class="truncate max-w-xs" x-text="replyingTo.body || 'Attachment'"></span>
                        </div>
                        <button @click="replyingTo = null" class="text-red-500 font-bold hover:text-red-700">Cancel</button>
                    </div>
                </template>

                <!-- Input Area -->
                <div class="shrink-0 px-4 py-3 bg-white border-t border-slate-100 shadow-[0_-4px_15px_-4px_rgba(0,0,0,0.05)] z-20">
                    <form @submit.prevent="sendMessage" class="flex items-center gap-3">
                        <input type="file" class="hidden" x-ref="attachment_input" 
                            accept="image/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar"
                            @change="
                                if ($event.target.files[0]) {
                                    if (validateFile($event.target.files[0])) {
                                        attachmentLabel = $event.target.files[0].name;
                                    }
                                } else {
                                    attachmentLabel = '';
                                }
                            "
                            :disabled="isSending">
                        <button type="button" @click.prevent="!isSending && $refs.attachment_input.click()"
                            :disabled="isSending"
                            :class="isSending ? 'opacity-50 cursor-not-allowed bg-slate-50' : 'cursor-pointer bg-white hover:bg-indigo-50/50 hover:text-indigo-600 hover:border-indigo-200'"
                            class="self-center p-3 text-slate-500 transition rounded-xl shadow-[0_2px_10px_-3px_rgba(0,0,0,0.07)] border border-slate-200 flex items-center justify-center shrink-0">
                            <svg class="w-5.5 h-5.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        </button>
                        <div class="flex-1 flex flex-col group">
                            <!-- Live Upload Progress Bar -->
                            <template x-if="isSending && uploadProgress !== null">
                                <div class="w-full mb-2">
                                    <div class="flex justify-between items-center text-[10px] font-bold text-indigo-600 mb-1">
                                        <span class="flex items-center gap-1">
                                            <svg class="animate-spin h-3 w-3 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Uploading attachment...
                                        </span>
                                        <span x-text="uploadProgress + '%'"></span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden shadow-inner">
                                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-1.5 rounded-full transition-all duration-300" :style="'width: ' + uploadProgress + '%'"></div>
                                    </div>
                                </div>
                            </template>

                            <!-- Sending Message indicator -->
                            <template x-if="isSending && uploadProgress === null">
                                <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500 mb-1.5">
                                    <svg class="animate-spin h-3 w-3 text-slate-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Sending message...
                                </div>
                            </template>

                            <template x-if="attachmentLabel">
                                <div class="text-[10px] text-indigo-600 font-bold mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    <span x-text="attachmentLabel"></span>
                                    <button x-show="!isSending" @click.prevent="$refs.attachment_input.value=''; attachmentLabel=''" class="text-red-500 ml-1 font-bold">×</button>
                                </div>
                            </template>

                            <!-- Mentions Dropdown -->
                            <div x-show="mentionQuery !== null && filteredMentionUsers.length > 0" class="absolute bottom-full left-0 w-64 max-h-48 overflow-y-auto bg-white border border-slate-200 shadow-xl rounded-xl mb-2 z-50 p-1" x-transition x-cloak>
                                <template x-for="user in filteredMentionUsers" :key="user.id">
                                    <button @click.prevent="insertMention(user)" class="w-full text-left px-3 py-2 hover:bg-indigo-50/50 rounded-lg text-sm transition flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 text-white flex items-center justify-center text-[10px] font-bold" x-text="user.name.charAt(0)"></div>
                                        <span class="font-bold text-slate-700" x-text="user.name"></span>
                                    </button>
                                </template>
                            </div>

                            <div class="relative w-full">
                                <template x-if="typingUsers.length > 0">
                                    <div class="absolute -top-6 left-2 text-[10px] font-bold text-indigo-500 bg-white/90 backdrop-blur px-2 py-0.5 rounded-t-lg shadow-sm border border-b-0 border-slate-100 flex items-center gap-1">
                                        <span class="flex gap-0.5">
                                            <span class="w-1 h-1 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                            <span class="w-1 h-1 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                            <span class="w-1 h-1 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                        </span>
                                        <span x-text="typingUsers.join(', ') + (typingUsers.length > 1 ? ' are typing...' : ' is typing...')"></span>
                                    </div>
                                </template>
                                <textarea x-model="newMessage" @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); sendMessage(); }" @input="handleInput($event)" @paste="handlePaste($event)" placeholder="Type a message..." rows="1" 
                                    :disabled="isSending"
                                    :class="isSending ? 'opacity-65 cursor-not-allowed bg-slate-100' : 'bg-[#f8fafc] focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400'"
                                    class="w-full border border-slate-200 rounded-xl text-sm px-4 py-3.5 outline-none transition-all shadow-inner resize-none"
                                    style="max-height: 150px; height: auto;"></textarea>
                            </div>
                        </div>
                        <button type="submit" 
                            :disabled="isSending"
                            :class="isSending ? 'opacity-50 cursor-not-allowed' : 'hover:scale-105'"
                            class="self-center btn-premium-indigo text-white rounded-xl px-5 py-3.5 shadow-md transition font-bold shrink-0 flex items-center justify-center min-w-[52px] min-h-[52px]">
                            <template x-if="isSending">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <template x-if="!isSending">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </template>
                        </button>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <!-- MODALS -->
    <!-- New Chat / Group Modal -->
    <div x-show="modals.newChat" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-slide-up">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-slate-800" x-text="isCreatingGroup ? 'Create Team Group' : 'New Direct Message'"></h3>
                <button @click="closeModal('newChat')" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            
            <div class="p-4">
                <!-- Tab switcher: Only shown if user is Admin (since they can create groups) -->
                <template x-if="currentUserRole === 'admin'">
                    <div class="flex gap-2 mb-4">
                        <button @click="isCreatingGroup = false" class="flex-1 py-2 px-4 rounded-xl text-sm font-bold transition" :class="!isCreatingGroup ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">Direct</button>
                        <button @click="isCreatingGroup = true" class="flex-1 py-2 px-4 rounded-xl text-sm font-bold transition" :class="isCreatingGroup ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">Group</button>
                    </div>
                </template>

                <template x-if="isCreatingGroup">
                    <div class="space-y-4">
                        <input type="text" x-model="groupName" placeholder="Group Name..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Group Icon (Optional)</label>
                            <input type="file" @change="groupIcon = $event.target.files[0]" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                        </div>
                        <p class="text-xs font-bold text-slate-400">Select Team Members</p>
                    </div>
                </template>

                <div class="mb-4">
                    <input type="text" x-model="staffSearchQuery" placeholder="Search team members..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 shadow-sm transition">
                </div>

                <div class="mt-4 max-h-60 overflow-y-auto space-y-2 pr-1">
                    <template x-if="filteredStaffList.length === 0">
                        <div class="p-8 text-center text-slate-400">
                            <p class="text-sm">No team members found.</p>
                        </div>
                    </template>
                    <template x-for="staff in filteredStaffList" :key="staff.id">
                        <div @click="toggleStaffSelection(staff)" class="p-3 border rounded-xl flex items-center gap-3 cursor-pointer transition" :class="isSelected(staff) ? 'bg-blue-50 border-blue-200' : 'bg-white border-slate-100 hover:bg-slate-50'">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center shadow-inner overflow-hidden">
                            <img :src="staff.avatar_url" class="w-full h-full object-cover">
                        </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-slate-800" x-text="staff.name"></p>
                                <p class="text-[10px] text-slate-400 uppercase tracking-tighter" x-text="staff.role"></p>
                            </div>
                            <template x-if="isSelected(staff)">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            </template>
                        </div>
                    </template>
                </div>

                <button @click="processNewChat" class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed" :disabled="!canSubmitNewChat">
                    <span x-text="isCreatingGroup ? 'Create Group' : 'Start Chat'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Group Management Modal -->
    <div x-show="modals.groupSettings" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-slide-up">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-slate-800">Group Settings</h3>
                <button @click="closeModal('groupSettings')" class="text-slate-400 hover:text-slate-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="p-6">
                <div class="mb-6 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 mx-auto mb-2 text-2xl font-bold" x-text="getConvoInitial(activeChat)"></div>
                    <h4 class="font-bold text-slate-800 text-lg" x-text="activeChat?.name"></h4>
                    <p class="text-xs text-slate-400">Created by <span x-text="activeChat?.creator?.name || 'Admin'"></span></p>
                </div>

                <div class="space-y-4">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Manage Members</p>
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Change Group Icon</label>
                        <input type="file" @change="groupIcon = $event.target.files[0]" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                    </div>
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                        <template x-for="staff in staffList" :key="staff.id">
                            <div @click="toggleStaffSelection(staff)" class="p-3 border rounded-xl flex items-center gap-3 cursor-pointer transition" :class="isSelected(staff) ? 'bg-blue-50 border-blue-200' : 'bg-white border-slate-100 hover:bg-slate-50'">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shadow-inner overflow-hidden">
                                    <img :src="staff.avatar_url" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-slate-800" x-text="staff.name"></p>
                                </div>
                                <template x-if="isSelected(staff)">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="flex gap-3 pt-4 border-t">
                        <button @click="updateGroupMembers" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg transition">Save Changes</button>
                        <button @click="archiveGroup" class="py-3 px-4 rounded-xl text-red-500 hover:bg-red-50 transition border border-red-100 font-bold" title="Delete Group">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Toast Notification Overlay -->
    <div x-show="toastMessage" class="fixed top-24 right-4 z-[99]" x-transition.opacity x-cloak>
        <div class="p-4 bg-white rounded-xl shadow-2xl border flex items-center gap-4 animate-slide-up max-w-sm" :class="(toastTitle.toLowerCase().includes('authorized') || toastTitle.toLowerCase().includes('error')) ? 'border-red-200' : 'border-blue-100'">
            <div class="w-12 h-12 text-white rounded-xl flex items-center justify-center shadow-lg shrink-0" :class="(toastTitle.toLowerCase().includes('authorized') || toastTitle.toLowerCase().includes('error')) ? 'bg-red-500 shadow-red-100' : 'bg-blue-600 shadow-blue-200'">
                <svg x-show="toastTitle.toLowerCase().includes('authorized') || toastTitle.toLowerCase().includes('error')" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                <svg x-show="!toastTitle.toLowerCase().includes('authorized') && !toastTitle.toLowerCase().includes('error')" class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </div>
            <div>
                <h4 class="font-bold text-sm text-slate-800" x-text="toastTitle"></h4>
                <p class="text-xs text-slate-500" x-text="toastMessage"></p>
            </div>
            <button @click="toastMessage = null" class="text-slate-400 hover:text-slate-600 ml-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>
    
    <!-- Right-click Context Menu for Conversations -->
    <div x-show="convoMenuOpen" @click.away="convoMenuOpen = false" x-transition
         class="fixed z-[95] bg-white rounded-xl shadow-2xl border border-slate-200 py-1 w-48 overflow-hidden"
         :style="'top:'+convoMenuY+'px;left:'+convoMenuX+'px'" x-cloak>
        <button @click="pinConvoToggle()" class="w-full px-4 py-2.5 text-left text-sm hover:bg-blue-50 flex items-center gap-3 transition">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/></svg>
            <span x-text="convoMenuTarget?.is_pinned_chat ? 'Unpin Chat' : 'Pin Chat'"></span>
        </button>

        <button @click="deleteConvo()" class="w-full px-4 py-2.5 text-left text-sm hover:bg-red-50 text-red-600 flex items-center gap-3 transition">
            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            Delete Chat
        </button>
    </div>

    <!-- Lightbox Overlay with Zoom -->
    <template x-if="previewImage">
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/95 backdrop-blur-md animate-fade-in" @click.self="previewImage = null; imageZoom = 1" x-cloak>
            <div class="relative max-w-5xl w-full flex flex-col items-center pointer-events-none">
                <!-- Close Button -->
                <button @click="previewImage = null; imageZoom = 1" class="absolute -top-12 right-0 p-3 text-white hover:text-red-400 transition transform hover:scale-110 pointer-events-auto z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                
                <!-- Download Button -->
                <a :href="previewImage + (previewImage.includes('?') ? '&' : '?') + 'download=1'" download class="absolute -top-12 right-12 p-3 text-white hover:text-blue-400 transition transform hover:scale-110 pointer-events-auto z-10" title="Download Image">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5 5 5-5M12 3v12"></path></svg>
                </a>

                <!-- Zoom Controls -->
                <div class="absolute -top-12 left-0 p-1 flex items-center gap-1 bg-black/40 backdrop-blur-md rounded-full border border-white/20 text-white pointer-events-auto z-10 shadow-xl">
                    <button @click="imageZoom = Math.max(0.3, imageZoom - 0.25)" class="p-2 hover:bg-white/20 rounded-full transition" title="Zoom Out">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
                    </button>
                    <span class="text-xs font-bold w-12 text-center" x-text="Math.round(imageZoom * 100) + '%'"></span>
                    <button @click="imageZoom = Math.min(5, imageZoom + 0.25)" class="p-2 hover:bg-white/20 rounded-full transition" title="Zoom In">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                    <div class="w-px h-6 bg-white/20 mx-1"></div>
                    <button @click="imageZoom = 1" class="px-3 py-1 hover:bg-white/20 rounded-lg text-[10px] uppercase font-bold transition">Reset</button>
                </div>

                <!-- Image container with scroll and button zoom -->
                <div class="bg-white/5 p-1 rounded-2xl shadow-2xl border border-white/10 overflow-auto pointer-events-auto max-h-[85vh] max-w-full scrollbar-hide" @wheel.prevent="handleImageZoom($event)">
                    <img :src="previewImage" class="rounded-xl object-contain shadow-2xl transition-transform duration-150 ease-out" :style="'transform: scale(' + imageZoom + '); transform-origin: center center; max-height: 80vh;'">
                </div>
                
                <div class="mt-4 text-white/60 text-sm font-medium pointer-events-auto">Tap +/- to zoom · Tap outside to close</div>
            </div>
        </div>
    </template>

    <!-- Forward Modal -->
    <template x-if="showForwardModal">
        <div class="fixed inset-0 z-[90] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showForwardModal = false" x-cloak>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-slide-up" @click.stop>
                <div class="p-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Forward Message</h3>
                    <button @click="showForwardModal = false" class="text-slate-400 hover:text-red-500"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                <div class="p-3 border-b border-slate-100 bg-slate-50">
                    <p class="text-xs text-slate-500 truncate flex items-center gap-2">
                        <i data-lucide="forward" class="w-3 h-3"></i>
                        <span class="font-bold">Message:</span> <span x-text="forwardingMsg?.body || 'Attachment'"></span>
                    </p>
                </div>
                <!-- Search in forward list -->
                <div class="p-2 border-b border-slate-100">
                    <input type="text" x-model="forwardSearchQuery" placeholder="Search chats..." class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="max-h-72 overflow-y-auto p-2 space-y-1">
                    <template x-for="convo in forwardFilteredConvos" :key="convo.id">
                        <button @click.stop="forwardToChat(convo.id)" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 transition text-left">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm shadow-sm" x-text="getConvoInitial(convo)"></div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-sm text-slate-800 truncate" x-text="getConvoName(convo)"></p>
                                <p class="text-[10px] text-slate-400" x-text="convo.is_group ? 'Group' : 'Direct'"></p>
                            </div>
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </button>
                    </template>
                    <template x-if="forwardFilteredConvos.length === 0">
                        <p class="text-center text-sm text-slate-400 py-6">No chats found</p>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <!-- Delete Confirmation Modal -->
    <template x-if="showDeleteModal">
        <div class="fixed inset-0 z-[90] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showDeleteModal = false" x-cloak>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden animate-slide-up">
                <div class="p-5 text-center">
                    <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800 mb-1">Delete Message</h3>
                    <p class="text-sm text-slate-500 mb-5">Choose how you want to delete this message</p>
                    <div class="flex flex-col gap-2">
                        <template x-if="deletingMsg && deletingMsg.user_id == userId">
                            <div class="flex flex-col gap-2 w-full">
                                <button @click="deleteMessage('for_me')" class="w-full py-3 px-4 bg-slate-100 hover:bg-slate-200 rounded-xl text-sm font-bold text-slate-700 transition flex items-center justify-center gap-2">
                                    <i data-lucide="trash-2" class="w-4 h-4 text-slate-400"></i>
                                    Delete for Me
                                </button>
                                <button @click="deleteMessage('for_everyone')" class="w-full py-3 px-4 bg-red-500 hover:bg-red-600 rounded-xl text-sm font-bold text-white transition flex items-center justify-center gap-2">
                                    <i data-lucide="ban" class="w-4 h-4"></i>
                                    Delete for Everyone
                                </button>
                            </div>
                        </template>
                        <template x-if="deletingMsg && deletingMsg.user_id != userId">
                            <div class="text-sm text-red-500 font-bold py-3 bg-red-50 rounded-xl border border-red-100 px-4">
                                Not authorized. Only the sender can delete this message.
                            </div>
                        </template>
                        <button @click="showDeleteModal = false" class="w-full py-2 text-sm text-slate-400 hover:text-slate-600 transition">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Starred Messages Drawer -->
    <template x-if="showStarredDrawer">
        <div class="fixed inset-0 z-[90] flex justify-end bg-black/40 backdrop-blur-sm" @click.self="showStarredDrawer = false" x-cloak>
            <div class="w-full max-w-sm bg-white shadow-2xl h-full flex flex-col animate-slide-left">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-amber-50 to-yellow-50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2"><svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg> Starred Messages</h3>
                    <button @click="showStarredDrawer = false" class="text-slate-400 hover:text-red-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    <template x-if="starredMessages.length === 0">
                        <div class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <p class="text-sm font-medium">No starred messages yet</p>
                            <p class="text-xs mt-1">Star important messages to find them here</p>
                        </div>
                    </template>
                    <template x-for="sm in starredMessages" :key="sm.id">
                        <div @click="showStarredDrawer = false; jumpToMessage(sm.id)" class="p-3 bg-slate-50 hover:bg-amber-50 rounded-xl cursor-pointer border border-slate-100 hover:border-amber-200 transition">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <span class="text-xs font-bold text-slate-700" x-text="sm.user?.name || 'Unknown'"></span>
                                <span class="text-[10px] text-slate-400 ml-auto" x-text="formatTime(sm.created_at)"></span>
                            </div>
                            <p class="text-sm text-slate-600 line-clamp-2" x-text="sm.body || 'Attachment'"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <!-- Thread Drawer -->
    <template x-if="showThreadDrawer">
        <div class="fixed inset-0 z-[90] flex justify-end bg-black/40 backdrop-blur-sm" @click.self="showThreadDrawer = false" x-cloak>
            <div class="w-full max-w-md bg-white shadow-2xl h-full flex flex-col animate-slide-left">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white shadow-sm z-10">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">Thread</h3>
                    <button @click="showThreadDrawer = false" class="text-slate-400 hover:text-red-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50">
                    <template x-if="threadParent">
                        <div class="p-3 bg-white rounded-xl shadow-sm border border-slate-100 mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <img :src="threadParent.user ? threadParent.user.avatar_url : 'https://ui-avatars.com/api/?name=User&background=6366f1&color=fff&bold=true'" class="w-6 h-6 rounded-full">
                                <span class="font-bold text-sm text-slate-800" x-text="threadParent.user ? threadParent.user.name : 'Unknown User'"></span>
                                <span class="text-[10px] text-slate-400 ml-auto" x-text="formatTime(threadParent.created_at)"></span>
                            </div>
                            <p class="text-sm text-slate-700" x-text="threadParent.body"></p>
                            <template x-if="threadParent.attachment">
                                <img :src="getAttachmentUrl(threadParent.attachment)" class="mt-2 rounded-lg max-h-32 object-cover">
                            </template>
                        </div>
                    </template>
                    <div class="px-2 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Replies</div>
                    <template x-for="msg in threadMessages" :key="msg.id">
                        <template x-if="msg.id !== threadParent?.id">
                            <div class="flex gap-2">
                                <img :src="msg.user ? msg.user.avatar_url : 'https://ui-avatars.com/api/?name=User&background=6366f1&color=fff&bold=true'" class="w-6 h-6 rounded-full mt-1">
                                <div class="bg-white p-3 rounded-xl rounded-tl-none border border-slate-100 shadow-sm flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-bold text-xs text-slate-800" x-text="msg.user ? msg.user.name : 'Unknown User'"></span>
                                        <span class="text-[10px] text-slate-400 ml-auto" x-text="formatTime(msg.created_at)"></span>
                                    </div>
                                    <p class="text-sm text-slate-700" x-text="msg.body"></p>
                                    <template x-if="msg.attachment">
                                        <img :src="getAttachmentUrl(msg.attachment)" class="mt-2 rounded-lg max-h-32 object-cover">
                                    </template>
                                </div>
                            </div>
                        </template>
                    </template>
                </div>
                <div class="p-3 bg-white border-t border-slate-100 shrink-0">
                    <form @submit.prevent="sendThreadReply" class="flex items-end gap-2 relative">
                        <textarea x-model="threadReplyMessage" @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); sendThreadReply(); }" placeholder="Reply in thread..." rows="1" 
                            :disabled="isSending"
                            :class="isSending ? 'opacity-65 cursor-not-allowed bg-slate-100' : 'bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400'"
                            class="flex-1 border border-slate-200 rounded-xl text-sm px-3 py-2 outline-none resize-none transition-all shadow-inner"
                            style="max-height: 120px; height: auto;"></textarea>
                        <button type="submit" 
                            :disabled="isSending"
                            :class="isSending ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                            class="bg-blue-600 text-white rounded-xl p-2.5 shadow-md transition shrink-0 flex items-center justify-center min-w-[38px] min-h-[38px]">
                            <template x-if="isSending">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <template x-if="!isSending">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </template>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Clear Group Chats Modal (Admin password confirmation) -->
    <template x-if="showClearGroupModal">
        <div class="fixed inset-0 z-[90] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showClearGroupModal = false" x-cloak>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden animate-slide-up">
                <div class="p-5 text-center">
                    <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800 mb-1">Clear All Messages</h3>
                    <p class="text-sm text-slate-500 mb-4">This will delete all messages in this chat permanently. Enter your password to confirm.</p>
                    <div class="relative mb-2">
                        <input :type="showClearPassword ? 'text' : 'password'" x-model="clearGroupPassword" placeholder="Enter your password" class="w-full bg-slate-100 border border-slate-200 rounded-xl pl-4 pr-11 py-3 text-sm outline-none focus:ring-2 focus:ring-red-500">
                        <button type="button" @click="showClearPassword = !showClearPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                            <svg x-show="!showClearPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            <svg x-show="showClearPassword" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.12-5.4M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a10.024 10.024 0 01-4.12 5.4m-1.28-1.28A3.001 3.001 0 1111.306 11.3" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" /></svg>
                        </button>
                    </div>
                    <p class="text-xs text-red-500 mb-3" x-show="clearGroupError" x-text="clearGroupError"></p>
                    <div class="flex gap-2">
                        <button @click="showClearGroupModal = false; clearGroupPassword = ''; clearGroupError = ''" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 rounded-xl text-sm font-bold text-slate-600 transition">Cancel</button>
                        <button @click="clearGroupChats()" class="flex-1 py-3 bg-red-500 hover:bg-red-600 rounded-xl text-sm font-bold text-white transition">Delete All</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
</div>

<script>
    // Safe Standalone Helper Functions to prevent any 'this' context binding or TypeError crashes
    function parseSafeDate(d) {
        if (!d) return null;
        if (d instanceof Date) return isNaN(d.getTime()) ? null : d;
        let dateStr = String(d).trim();
        
        // If it's already a parseable date string
        if (!isNaN(Date.parse(dateStr))) {
            return new Date(dateStr);
        }
        
        // Handle YYYY-MM-DD HH:MM:SS format
        if (dateStr.match(/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}/)) {
            dateStr = dateStr.replace(' ', 'T');
        }
        
        // Handle DD/MM/YYYY HH:MM:SS or DD-MM-YYYY format
        const dmYMatch = dateStr.match(/^(\d{2})[/-](\d{2})[/-](\d{4})(?:\s+(\d{2}):(\d{2})(?::(\d{2}))?)?/);
        if (dmYMatch) {
            const day = parseInt(dmYMatch[1], 10);
            const month = parseInt(dmYMatch[2], 10) - 1; // 0-indexed
            const year = parseInt(dmYMatch[3], 10);
            const hour = dmYMatch[4] ? parseInt(dmYMatch[4], 10) : 0;
            const minute = dmYMatch[5] ? parseInt(dmYMatch[5], 10) : 0;
            const second = dmYMatch[6] ? parseInt(dmYMatch[6], 10) : 0;
            return new Date(year, month, day, hour, minute, second);
        }

        const parsed = new Date(dateStr);
        return isNaN(parsed.getTime()) ? null : parsed;
    }

    function shouldShowDivider(msg, index, messages) {
        if (!msg) return false;
        if (index === 0) return true;
        const prevMsg = messages ? messages[index - 1] : null;
        if (!prevMsg) return false;
        const dateCur = parseSafeDate(msg.created_at);
        const datePrev = parseSafeDate(prevMsg.created_at);
        if (!dateCur || !datePrev) return false;
        return dateCur.toDateString() !== datePrev.toDateString();
    }

    function formatTime(d) {
        const parsed = parseSafeDate(d);
        if (!parsed) return '00:00';
        return parsed.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function formatDateDivider(dateStr) {
        const date = parseSafeDate(dateStr);
        if (!date) return '';
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        if (date.toDateString() === today.toDateString()) {
            return 'Today';
        } else if (date.toDateString() === yesterday.toDateString()) {
            return 'Yesterday';
        } else {
            return date.toLocaleDateString('en-US', { 
                weekday: 'short', 
                month: 'short', 
                day: 'numeric', 
                year: today.getFullYear() !== date.getFullYear() ? 'numeric' : undefined 
            });
        }
    }

    function getAttachmentUrl(path) {
        if (!path || typeof path !== 'string') return '';
        return path.startsWith('/') ? path : '/chat/file/' + path;
    }

    function isAttachmentPdf(path) {
        return path && typeof path === 'string' && path.toLowerCase().endsWith('.pdf');
    }

    function isAttachmentWord(path) {
        return path && typeof path === 'string' && !!path.match(/\.(doc|docx)$/i);
    }

    function isAttachmentOther(path) {
        return path && typeof path === 'string' && !path.match(/\.(pdf|doc|docx)$/i);
    }

    function getAttachmentName(path) {
        if (!path || typeof path !== 'string') return '';
        return path.split('/').pop();
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('advancedChat', () => ({
            conversations: [],
            messages: [],
            staffList: [],
            selectedStaff: [],
            activeChat: null,
            newMessage: '',
            searchQuery: '',
            messageSearchQuery: '',
            searchResults: [],
            showSearchBar: false,
            replyingTo: null,
            attachmentLabel: '',
            modals: { newChat: false, groupSettings: false },
            isCreatingGroup: false,
            groupName: '',
            groupIcon: null,
            onlineUsers: [],
            notificationPermission: 'default',
            toastTitle: '',
            toastMessage: null,
            userId: {{ auth()->id() }},
            currentUserRole: '{{ auth()->user()->role }}',
            poller: null,
            totalUnreadCount: 0,
            staffSearchQuery: '',
            previewImage: null,
            imageZoom: 1,
            showForwardModal: false,
            forwardSearchQuery: '',
            convoMenuOpen: false,
            convoMenuX: 0,
            convoMenuY: 0,
            convoMenuTarget: null,
            forwardingMsg: null,
            showDeleteModal: false,
            deletingMsg: null,
            showStarredDrawer: false,
            starredMessages: [],
            showClearGroupModal: false,
            clearGroupPassword: '',
            clearGroupError: '',
            showClearPassword: false,
            showScrollBtn: false,
            
            // Send State
            isSending: false,
            uploadProgress: null,

            // New Advanced Features Data
            dragOver: false,
            typingUsers: [],
            typingTimer: null,
            mentionQuery: null,
            filteredMentionUsers: [],
            showThreadDrawer: false,
            threadParent: null,
            threadMessages: [],
            threadReplyMessage: '',
            threadAttachmentLabel: '',

            validateFile(file) {
                if (file && file.size > 10 * 1024 * 1024) {
                    this.toastTitle = 'File Too Large';
                    this.toastMessage = 'Maximum allowed size is 10MB. Please choose a smaller file.';
                    setTimeout(() => { if (this.toastMessage === 'Maximum allowed size is 10MB. Please choose a smaller file.') this.toastMessage = null; }, 5000);
                    
                    const input = (this.$refs && this.$refs.attachment_input) ? this.$refs.attachment_input : document.querySelector('input[type="file"]');
                    if (input) input.value = '';
                    this.attachmentLabel = '';
                    return false;
                }
                return true;
            },

            init() {
                this.$watch('newMessage', value => {
                    const textarea = document.querySelector('textarea[x-model="newMessage"]');
                    if (textarea) {
                        textarea.style.height = 'auto';
                        if (value !== '') {
                            textarea.style.height = textarea.scrollHeight + 'px';
                        }
                    }
                });

                this.$watch('threadReplyMessage', value => {
                    const textarea = document.querySelector('textarea[x-model="threadReplyMessage"]');
                    if (textarea) {
                        textarea.style.height = 'auto';
                        if (value !== '') {
                            textarea.style.height = textarea.scrollHeight + 'px';
                        }
                    }
                });

                this.fetchConversations();
                this.fetchStaff();

                // Check for user_id in URL to auto-open direct chat
                const urlParams = new URLSearchParams(window.location.search);
                const queryUserId = urlParams.get('user_id');
                if (queryUserId) {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('user_id', queryUserId);
                    fetch('/api/conversations/dm', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(convo => {
                        // Clear the query parameter from the URL to avoid looping or re-opening on refresh
                        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
                        
                        // Select the conversation
                        this.selectConversation(convo);
                    })
                    .catch(err => console.error('Error auto-starting DM:', err));
                }

                // History Sync for Mobile Back Button
                window.addEventListener('popstate', (event) => {
                    if (this.activeChat) {
                        this.activeChat = null;
                        // Avoid actually navigating away if it was a chat state
                    }
                });
                
                // Real-time listener for current user's unread updates
                if (window.Echo) {
                    window.Echo.join('chat.presence')
                        .here((users) => { this.onlineUsers = users; })
                        .joining((user) => { this.onlineUsers.push(user); })
                        .leaving((user) => { this.onlineUsers = this.onlineUsers.filter(u => u.id !== user.id); });

                    window.Echo.private(`App.Models.User.${this.userId}`)
                        .notification((notification) => {
                            this.fetchConversations();
                        });
                }

                // Request notification permission
                if ('Notification' in window) {
                    this.notificationPermission = Notification.permission;
                    if (Notification.permission === 'default') {
                        Notification.requestPermission().then(permission => {
                            this.notificationPermission = permission;
                        });
                    }
                }

                this.poller = setInterval(() => {
                    this.fetchConversations();
                }, 3000); // Back to 3 seconds for better real-time feel

                // Event Bus for Websockets safely attached to Alpine Context
                window.addEventListener('chat-message-received', (evt) => this.handleNewMessage(evt.detail));
                window.addEventListener('chat-message-read', (evt) => this.handleMessageRead(evt.detail));
            },

            handleNewMessage(e) {
                if (this.activeChat && this.activeChat.id == e.conversation_id) {
                    if (e.id && !this.messages.find(m => m.id == e.id)) {
                        this.messages.push(e);
                        this.scrollToBottom();
                        if (e.user_id != this.userId) {
                            this.markAsRead(e);
                        }
                    }
                } else {
                    if (e.user_id != this.userId) {
                        this.playSound();
                        this.showNotification(e);
                        const c = this.conversations.find(c => c.id == e.conversation_id);
                        if (c) {
                            c.latest_message = e;
                            c.unread_count = (parseInt(c.unread_count) || 0) + 1;
                        }
                    }
                }
            },

            handleMessageRead(e) {
                if (this.activeChat && this.activeChat.id == e.conversation_id) {
                    this.messages.forEach(m => {
                        if (m.id == e.message_id) {
                            if(!m.reads) m.reads = [];
                            if(!m.reads.some(r => r.user_id == e.user_id)) m.reads.push(e);
                        }
                    });
                }
                this.fetchConversations();
            },

            get filteredConversations() {
                if(!this.searchQuery) return this.conversations;
                return this.conversations.filter(c => this.getConvoName(c).toLowerCase().includes(this.searchQuery.toLowerCase()));
            },

            get filteredStaffList() {
                if(!this.staffSearchQuery) return this.staffList;
                return this.staffList.filter(s => s.name.toLowerCase().includes(this.staffSearchQuery.toLowerCase()));
            },

            fetchConversations() {
                // Update my own last seen while fetching
                fetch('/api/conversations')
                    .then(res => {
                        if(!res.ok) throw new Error('Database Error');
                        return res.json();
                    })
                    .then(data => {
                        // Check for new messages by comparing unread counts (skip on first load)
                        const newTotalUnread = data.reduce((acc, c) => acc + (parseInt(c.unread_count) || 0), 0);
                        if (this.conversations.length > 0 && newTotalUnread > this.totalUnreadCount) {
                            try {
                                this.playSound();
                                const increasedConvo = data.find(c => {
                                    const old = this.conversations.find(oc => oc.id === c.id);
                                    return c.unread_count > (old ? old.unread_count : 0);
                                });
                                if (increasedConvo && increasedConvo.latest_message) {
                                    this.showNotification(increasedConvo.latest_message);
                                }
                            } catch (err) { console.error('Notification trigger error:', err); }
                        }
                        this.totalUnreadCount = newTotalUnread;
                        
                        // Get pinned chats from localStorage
                        const pinnedChatIds = JSON.parse(localStorage.getItem('pinnedChats') || '[]');
                        
                        data.forEach(c => {
                            c.is_pinned_chat = pinnedChatIds.includes(c.id);
                        });

                        // Sort by latest message date (descending) and pins
                        this.conversations = data.sort((a, b) => {
                            if (a.is_pinned_chat && !b.is_pinned_chat) return -1;
                            if (!a.is_pinned_chat && b.is_pinned_chat) return 1;
                            const dateA = parseSafeDate(a.latest_message ? a.latest_message.created_at : a.created_at);
                            const dateB = parseSafeDate(b.latest_message ? b.latest_message.created_at : b.created_at);
                            const timeA = dateA ? dateA.getTime() : 0;
                            const timeB = dateB ? dateB.getTime() : 0;
                            return timeB - timeA;
                        });
                        this.bindGlobalListeners();
                        
                        // Sync activeChat data if open
                        if (this.activeChat) {
                            const updated = data.find(c => c.id == this.activeChat.id);
                            if (updated) this.activeChat.users = updated.users;
                        }
                    })
                    .catch(err => console.error('Convo fetch error:', err));
            },

            bindGlobalListeners() {
                if (!window.Echo) return;
                this.conversations.forEach(convo => {
                    const channelName = `chat.${convo.id}`;
                    if (!window.Echo.connector.channels[channelName]) {
                        window.Echo.private(channelName)
                            .listen('MessageSent', (e) => {
                                window.dispatchEvent(new CustomEvent('chat-message-received', { detail: e }));
                            })
                            .listen('MessageRead', (e) => {
                                window.dispatchEvent(new CustomEvent('chat-message-read', { detail: { ...e, conversation_id: convo.id } }));
                            });
                    }
                });
            },

            fetchStaff() {
                fetch('/api/staff/list')
                    .then(res => {
                        if(!res.ok) throw new Error('Staff Fetch Error');
                        return res.json();
                    })
                    .then(data => this.staffList = data)
                    .catch(err => console.error('Staff fetch error:', err));
            },

            activeChatPoller: null,

            selectConversation(convo) {
                // Leave previous whisper channel if any
                if (window.Echo && this.activeChat) {
                    window.Echo.leave(`chat.${this.activeChat.id}`);
                }

                this.activeChat = convo;
                this.replyingTo = null;
                this.messages = [];
                this.showSearchBar = false;
                this.typingUsers = [];
                this.fetchMessages();
                
                // Join new whisper channel for typing indicators
                if (window.Echo) {
                    window.Echo.private(`chat.${this.activeChat.id}`)
                        .listenForWhisper('typing', (e) => {
                            if (e.user && e.user.name) {
                                if (!this.typingUsers.includes(e.user.name)) {
                                    this.typingUsers.push(e.user.name);
                                }
                            }
                            if (this.typingTimer) clearTimeout(this.typingTimer);
                            this.typingTimer = setTimeout(() => {
                                this.typingUsers = [];
                            }, 2000);
                        });
                }

                // Push state for mobile back button sync
                if (window.innerWidth < 768) {
                    history.pushState({ chat_id: convo.id }, '', '#chat');
                }

                // Fallback Active Chat Polling (Same as Notification System)
                if (this.activeChatPoller) clearInterval(this.activeChatPoller);
                this.activeChatPoller = setInterval(() => {
                    if (this.activeChat && this.activeChat.id === convo.id) {
                        this.fetchMessages(true);
                    }
                }, 3000); // 3 seconds fast-poll
            },

            goBackToList() {
                if (window.innerWidth < 768) {
                    history.back(); // This triggers the popstate listener in init()
                } else {
                    this.activeChat = null;
                }
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    setTimeout(() => {
                        const box = document.getElementById('advanced-messages-box');
                        if(box) {
                            box.scrollTop = box.scrollHeight;
                            this.showScrollBtn = false;
                        }
                    }, 100);
                });
            },

            checkScrollPosition() {
                const box = document.getElementById('advanced-messages-box');
                if (!box) return;
                const distanceFromBottom = box.scrollHeight - box.scrollTop - box.clientHeight;
                this.showScrollBtn = distanceFromBottom > 200;
            },

            fetchMessages(isBackground = false) {
                if(!this.activeChat) return;
                fetch(`/api/conversations/${this.activeChat.id}/messages`)
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Server returned error ' + res.status);
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (!Array.isArray(data)) {
                            console.error('Messages response is not an array:', data);
                            return;
                        }
                        const oldLength = this.messages.length;
                        this.messages = data;
                        if(data.length > oldLength) {
                            setTimeout(() => {
                                const box = document.getElementById('advanced-messages-box');
                                if(box) box.scrollTop = box.scrollHeight;
                            }, 50);
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching messages:', err);
                    });
            },

            markAsRead(msg) {
                // Background call to mark as read so unread count stays accurate
                fetch(`/api/conversations/${this.activeChat.id}/messages`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
            },

            sendMessage() {
                if (this.isSending) return;

                const textBody = this.newMessage.trim();
                const attachmentFile = this.$refs.attachment_input.files[0];
                
                if (attachmentFile && !this.validateFile(attachmentFile)) {
                    return;
                }
                
                if(!textBody && !attachmentFile) return;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                
                if(textBody) {
                    formData.append('body', textBody);
                    
                    // Extract Mentions
                    const mentionMatches = textBody.match(/@([a-zA-Z0-9_]+)/g);
                    if (mentionMatches && this.activeChat && this.activeChat.users) {
                        const mentionedNames = mentionMatches.map(m => m.substring(1).toLowerCase());
                        const mentionedIds = this.activeChat.users
                            .filter(u => mentionedNames.includes(u.name.toLowerCase()))
                            .map(u => u.id);
                        
                        mentionedIds.forEach(id => {
                            formData.append('mentioned_ids[]', id);
                        });
                    }
                }
                
                if(this.replyingTo) formData.append('parent_id', this.replyingTo.id);
                if(attachmentFile) {
                    formData.append('attachment', attachmentFile);
                }
                
                // Set sending state and clear any old error toast
                this.isSending = true;
                this.uploadProgress = attachmentFile ? 0 : null;

                // Create and configure XHR for upload tracking
                const xhr = new XMLHttpRequest();
                xhr.open('POST', `/api/conversations/${this.activeChat.id}/messages`);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                // Progress tracking (only if file attachment exists)
                if (attachmentFile && xhr.upload) {
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percent = Math.round((e.loaded / e.total) * 100);
                            this.uploadProgress = percent;
                        }
                    });
                }

                xhr.onload = () => {
                    this.isSending = false;
                    this.uploadProgress = null;

                    if (xhr.status >= 200 && xhr.status < 300) {
                        // Success! Only now do we clear the inputs
                        this.newMessage = '';
                        this.replyingTo = null;
                        this.attachmentLabel = '';
                        this.$refs.attachment_input.value = '';
                        this.filteredMentionUsers = [];
                        
                        this.fetchMessages();
                    } else {
                        // Error handling
                        let errorMessage = 'Failed to send message.';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.errors) {
                                errorMessage = Object.values(response.errors).flat().join('\n');
                            } else if (response.error || response.message) {
                                errorMessage = response.error || response.message;
                            }
                        } catch (e) {}

                        if (xhr.status === 413) {
                            errorMessage = 'File is too large. Maximum allowed size is 10MB.';
                        }

                        this.toastTitle = 'Send Failed';
                        this.toastMessage = errorMessage;
                        setTimeout(() => { if (this.toastMessage === errorMessage) this.toastMessage = null; }, 5000);
                    }
                };

                xhr.onerror = () => {
                    this.isSending = false;
                    this.uploadProgress = null;
                    
                    this.toastTitle = 'Network Error';
                    this.toastMessage = 'An error occurred during upload. Please check your internet connection.';
                    setTimeout(() => { if (this.toastMessage === 'An error occurred during upload. Please check your internet connection.') this.toastMessage = null; }, 5000);
                };

                xhr.send(formData);
            },

            searchMessages() {
                if(!this.messageSearchQuery || !this.activeChat) {
                    this.searchResults = [];
                    return;
                }
                fetch(`/api/conversations/${this.activeChat.id}/search?q=${this.messageSearchQuery}`)
                    .then(res => res.json())
                    .then(data => this.searchResults = data);
            },

            jumpToMessage(id) {
                const el = document.getElementById('msg-'+id);
                if(el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    el.classList.add('ring-2', 'ring-blue-400', 'rounded-lg');
                    setTimeout(() => el.classList.remove('ring-2', 'ring-blue-400'), 2000);
                    this.searchResults = [];
                    this.messageSearchQuery = '';
                    this.showSearchBar = false;
                }
            },

            setReply(msg) {
                this.replyingTo = msg;
                setTimeout(() => {
                    const input = document.querySelector('textarea[x-model="newMessage"]');
                    if(input) input.focus();
                }, 50);
            },

            toggleStar(msg) {
                fetch(`/api/messages/${msg.id}/star`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(res => res.json()).then(data => {
                    msg.is_starred = data.is_starred;
                });
            },

            openModal(key) {
                this.modals[key] = true;
                this.selectedStaff = [];
                this.groupName = '';
                this.groupIcon = null;
            },

            closeModal(key) {
                this.modals[key] = false;
            },

            toggleStaffSelection(staff) {
                if(this.isCreatingGroup) {
                    const idx = this.selectedStaff.indexOf(staff.id);
                    if(idx > -1) this.selectedStaff.splice(idx, 1);
                    else this.selectedStaff.push(staff.id);
                } else {
                    this.selectedStaff = [staff.id];
                }
            },

            isSelected(staff) {
                return this.selectedStaff.includes(staff.id);
            },

            get canSubmitNewChat() {
                if(this.isCreatingGroup) return this.groupName.trim() && this.selectedStaff.length > 0;
                return this.selectedStaff.length === 1;
            },

            processNewChat() {
                const endpoint = this.isCreatingGroup ? '/api/conversations/group' : '/api/conversations/dm';
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                
                if (this.isCreatingGroup) {
                    formData.append('name', this.groupName);
                    if (this.groupIcon) formData.append('icon', this.groupIcon);
                    this.selectedStaff.forEach(id => formData.append('staff_ids[]', id));
                } else {
                    formData.append('user_id', this.selectedStaff[0]);
                }

                fetch(endpoint, {
                    method: 'POST',
                    body: formData
                }).then(res => {
                    if(!res.ok) throw new Error('Failed to create chat.');
                    return res.json();
                }).then(convo => {
                    this.closeModal('newChat');
                    this.fetchConversations();
                    setTimeout(() => {
                        const c = this.conversations.find(x => x.id === convo.id);
                        if(c) this.selectConversation(c);
                        else this.fetchConversations(); 
                    }, 500);
                }).catch(err => {
                    alert(err.message);
                });
            },

            getPresenceText(convo) {
                if (convo.is_group) return `${convo.users.length} members`;
                const otherUser = convo.users.find(u => u.id !== this.userId);
                if (!otherUser) return '';
                
                // If in presence channel, show Online
                if (this.isUserOnline(convo)) return 'Online';
                
                // Otherwise show Last Seen
                if (otherUser.last_seen_at) {
                    return 'Last seen ' + this.formatTimeAgo(otherUser.last_seen_at);
                }
                return 'Offline';
            },

            formatTimeAgo(date) {
                const now = new Date();
                const past = parseSafeDate(date);
                if (!past) return 'Offline';
                const diff = Math.floor((now - past) / 1000);
                if (isNaN(diff)) return 'Offline';
                
                if (diff < 60) return 'just now';
                if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
                if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
                return past.toLocaleDateString();
            },

            openGroupSettings() {
                this.selectedStaff = this.activeChat.users.map(u => u.id).filter(id => id !== this.userId);
                this.modals.groupSettings = true;
            },

            updateGroupMembers() {
                let formData = new FormData();
                if (this.groupIcon) formData.append('icon', this.groupIcon);
                this.selectedStaff.forEach(id => formData.append('staff_ids[]', id));

                fetch(`/api/conversations/${this.activeChat.id}/members`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.activeChat.users = data.users;
                    if (data.icon) this.activeChat.icon = data.icon;
                    this.closeModal('groupSettings');
                    this.fetchConversations();
                    this.groupIcon = null;
                });
            },

            archiveGroup() {
                if(!confirm('Are you sure you want to delete this group?')) return;
                fetch(`/api/conversations/${this.activeChat.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(() => {
                    this.activeChat = null;
                    this.modals.groupSettings = false;
                    this.fetchConversations();
                });
            },

            isMe(msg) { return msg.user_id == this.userId; },
            isOnline(user) {
                // Real-time check via Presence Channel
                return this.onlineUsers.some(u => u.id == user.id);
            },
            isImage(path) {
                return path && path.match(/\.(jpg|jpeg|png|gif|webp)$/i);
            },
            playSound() {
                // Sounds disabled
                return;
            },
            showNotification(msg) {
                const title = `New message from ${msg.user ? msg.user.name : 'Unknown User'}`;
                const body = msg.body || 'Sent an attachment';
                
                // Fallback UI toast for all devices (especially mobile PWA)
                this.toastTitle = title;
                this.toastMessage = body;
                setTimeout(() => this.toastMessage = null, 4000);

                if (this.notificationPermission === 'granted' && document.hidden) {
                    const n = new Notification(title, { body: body, icon: '/pwa-icon.png' });
                    n.onclick = () => {
                        window.focus();
                        this.selectConversation(this.conversations.find(c => c.id === msg.conversation_id));
                    };
                }
            },
            getReads(msg) { return msg.reads ? msg.reads.filter(r => r.user_id !== msg.user_id).length : 0; },
            parseSafeDate(d) { return parseSafeDate(d); },
            shouldShowDivider(msg, index) { return shouldShowDivider(msg, index, this.messages); },
            formatTime(d) { return formatTime(d); },
            getAttachmentUrl(path) { return getAttachmentUrl(path); },
            isAttachmentPdf(path) { return isAttachmentPdf(path); },
            isAttachmentWord(path) { return isAttachmentWord(path); },
            isAttachmentOther(path) { return isAttachmentOther(path); },
            getAttachmentName(path) { return getAttachmentName(path); },
            getConvoName(c) {
                if (!c) return 'Chat';
                if(c.is_group) return c.name || 'Group Chat';
                const u = c.users ? c.users.find(x => x.id !== this.userId) : null;
                return u ? u.name : 'Unknown';
            },
            getConvoInitial(c) { 
                if (!c) return 'C';
                return this.getConvoName(c).charAt(0).toUpperCase(); 
            },
            isUserOnline(c) {
                if (!c) return false;
                if (c.is_group) return false;
                const u = c.users ? c.users.find(x => x.id != this.userId) : null;
                if (!u) return false;
                // Check either Socket Presence (instant) OR Database Flag (reliable fallback)
                return u.is_online || this.onlineUsers.some(on => on.id == u.id);
            },
            getPresenceText(c) {
                if (!c) return '';
                if(c.is_group) return (c.users ? c.users.length : 0) + ' members';
                if (this.isUserOnline(c)) return 'Online';
                const u = c.users ? c.users.find(x => x.id != this.userId) : null;
                if (u && u.last_seen_at) {
                    return 'Last seen ' + this.formatTimeAgo(u.last_seen_at);
                }
                return 'Offline';
            },

            // ===== FORWARD SEARCH COMPUTED =====
            get forwardFilteredConvos() {
                const list = this.conversations.filter(c => c.id !== this.activeChat?.id);
                if (!this.forwardSearchQuery) return list;
                return list.filter(c => this.getConvoName(c).toLowerCase().includes(this.forwardSearchQuery.toLowerCase()));
            },

            // ===== PINNED MESSAGE COMPUTED =====
            get pinnedMessage() {
                return this.messages.find(m => m.is_pinned && !m.is_deleted_globally_flag);
            },

            // ===== IMAGE ZOOM =====
            handleImageZoom(event) {
                if (event.deltaY < 0) {
                    this.imageZoom = Math.min(this.imageZoom + 0.15, 5);
                } else {
                    this.imageZoom = Math.max(this.imageZoom - 0.15, 0.3);
                }
            },

            // ===== FORWARD =====
            openForwardModal(msg) {
                this.forwardingMsg = msg;
                this.forwardSearchQuery = '';
                this.showForwardModal = true;
            },
            forwardToChat(convoId) {
                const msgId = this.forwardingMsg.id;
                this.showForwardModal = false;
                this.forwardingMsg = null;
                fetch(`/api/messages/${msgId}/forward`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ conversation_id: convoId })
                }).then(res => {
                    if (!res.ok) throw new Error('Forward failed');
                    return res.json();
                }).then(() => {
                    this.toastTitle = 'Message Forwarded';
                    this.toastMessage = 'Successfully sent to conversation';
                    if(window.lucide) setTimeout(() => lucide.createIcons(), 100);
                    setTimeout(() => this.toastMessage = null, 3000);
                    this.fetchConversations();
                }).catch(err => {
                    console.error('Forward Error:', err);
                    this.toastTitle = 'Forward Failed';
                    this.toastMessage = err.message;
                    setTimeout(() => this.toastMessage = null, 3000);
                });
            },

            // ===== DELETE =====
            openDeleteModal(msg) {
                this.deletingMsg = msg;
                this.showDeleteModal = true;
            },
            deleteMessage(type) {
                fetch(`/api/messages/${this.deletingMsg.id}/delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: type })
                }).then(res => res.json()).then(() => {
                    if (type === 'for_me') {
                        this.messages = this.messages.filter(m => m.id !== this.deletingMsg.id);
                    } else {
                        const m = this.messages.find(m => m.id === this.deletingMsg.id);
                        if (m) { 
                            m.body = 'This message was deleted.'; 
                            m.attachment = null; 
                            m.is_deleted_globally_flag = true; 
                        }
                    }
                    this.showDeleteModal = false;
                    this.deletingMsg = null;
                    if(window.lucide) setTimeout(() => lucide.createIcons(), 50);
                });
            },

            // ===== PIN/UNPIN =====
            togglePin(msg) {
                fetch(`/api/messages/${msg.id}/pin`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(res => res.json()).then(data => {
                    // Unpin all first, then set the new one
                    this.messages.forEach(m => m.is_pinned = false);
                    if (data.is_pinned) {
                        msg.is_pinned = true;
                    }
                });
            },

            // ===== STARRED DRAWER =====
            openStarredDrawer() {
                this.showStarredDrawer = true;
                this.starredMessages = [];
                fetch(`/api/conversations/${this.activeChat.id}/starred`)
                    .then(res => res.json())
                    .then(data => this.starredMessages = data);
            },

            // ===== CLEAR GROUP =====
            clearGroupChats() {
                this.clearGroupError = '';
                fetch(`/api/conversations/${this.activeChat.id}/clear`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ password: this.clearGroupPassword })
                }).then(res => {
                    if (!res.ok) return res.json().then(d => { throw new Error(d.error || 'Failed'); });
                    return res.json();
                }).then(() => {
                    this.messages = [];
                    this.showClearGroupModal = false;
                    this.clearGroupPassword = '';
                    this.toastTitle = 'Cleared';
                    this.toastMessage = 'All messages deleted successfully';
                    setTimeout(() => this.toastMessage = null, 3000);
                    this.fetchConversations();
                }).catch(err => {
                    this.clearGroupError = err.message;
                });
            },

            // ===== CONVERSATION CONTEXT MENU =====
            openConvoMenu(event, convo) {
                this.convoMenuTarget = convo;
                this.convoMenuX = event.clientX;
                this.convoMenuY = event.clientY;
                this.convoMenuOpen = true;
            },
            pinConvoToggle() {
                if (this.convoMenuTarget) {
                    this.convoMenuTarget.is_pinned_chat = !this.convoMenuTarget.is_pinned_chat;
                    
                    // Save to localStorage
                    let pinnedChatIds = JSON.parse(localStorage.getItem('pinnedChats') || '[]');
                    if (this.convoMenuTarget.is_pinned_chat) {
                        if (!pinnedChatIds.includes(this.convoMenuTarget.id)) pinnedChatIds.push(this.convoMenuTarget.id);
                    } else {
                        pinnedChatIds = pinnedChatIds.filter(id => id !== this.convoMenuTarget.id);
                    }
                    localStorage.setItem('pinnedChats', JSON.stringify(pinnedChatIds));

                    // Sort: pinned first, then by latest message
                    this.conversations.sort((a, b) => {
                        if (a.is_pinned_chat && !b.is_pinned_chat) return -1;
                        if (!a.is_pinned_chat && b.is_pinned_chat) return 1;
                        const dateA = parseSafeDate(a.latest_message ? a.latest_message.created_at : a.created_at);
                        const dateB = parseSafeDate(b.latest_message ? b.latest_message.created_at : b.created_at);
                        const timeA = dateA ? dateA.getTime() : 0;
                        const timeB = dateB ? dateB.getTime() : 0;
                        return timeB - timeA;
                    });
                    this.toastTitle = this.convoMenuTarget.is_pinned_chat ? 'Pinned to Top' : 'Chat Unpinned';
                    this.toastMessage = 'Pin status updated';
                    if(window.lucide) setTimeout(() => lucide.createIcons(), 100);
                    setTimeout(() => this.toastMessage = null, 2000);
                }
                this.convoMenuOpen = false;
            },
            archiveConvoToggle() {
                if (this.convoMenuTarget) {
                    this.conversations = this.conversations.filter(c => c.id !== this.convoMenuTarget.id);
                    this.toastTitle = 'Chat Archived';
                    this.toastMessage = 'Conversation moved to archive';
                    if(window.lucide) setTimeout(() => lucide.createIcons(), 100);
                    setTimeout(() => this.toastMessage = null, 2000);
                    if (this.activeChat?.id === this.convoMenuTarget.id) {
                        this.activeChat = null;
                        this.messages = [];
                    }
                }
                this.convoMenuOpen = false;
            },
            deleteConvo() {
                if (!this.convoMenuTarget) return;
                if (!confirm('Delete this entire chat?')) { this.convoMenuOpen = false; return; }
                fetch(`/api/conversations/${this.convoMenuTarget.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(res => res.json()).then(data => {
                    if (data.error) {
                        this.toastTitle = 'Not Authorized';
                        this.toastMessage = data.error;
                        setTimeout(() => this.toastMessage = null, 4000);
                        this.convoMenuOpen = false;
                        return;
                    }
                    this.conversations = this.conversations.filter(c => c.id !== this.convoMenuTarget.id);
                    if (this.activeChat?.id === this.convoMenuTarget.id) { this.activeChat = null; this.messages = []; }
                    this.convoMenuOpen = false;
                    this.toastTitle = 'Deleted';
                    this.toastMessage = 'Chat deleted successfully';
                    setTimeout(() => this.toastMessage = null, 3000);
                    this.fetchConversations();
                }).catch(err => {
                    this.toastTitle = 'Error';
                    this.toastMessage = 'Failed to delete chat';
                    setTimeout(() => this.toastMessage = null, 3000);
                    this.convoMenuOpen = false;
                });
            },

            // ===== CLIPBOARD IMAGE PASTE =====
            handlePaste(event) {
                const items = (event.clipboardData || event.originalEvent.clipboardData).items;
                for (let index in items) {
                    const item = items[index];
                    if (item.kind === 'file' && item.type.startsWith('image/')) {
                        const blob = item.getAsFile();
                        const file = new File([blob], `screenshot_${Date.now()}.png`, { type: item.type });
                        if (this.validateFile(file)) {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            this.$refs.attachment_input.files = dataTransfer.files;
                            this.attachmentLabel = file.name;
                        }
                        event.preventDefault();
                        break;
                    }
                }
            },

            // ===== DRAG AND DROP =====
            handleDrop(event) {
                this.dragOver = false;
                if (event.dataTransfer.files.length > 0) {
                    const file = event.dataTransfer.files[0];
                    if (this.validateFile(file)) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        this.$refs.attachment_input.files = dataTransfer.files;
                        this.attachmentLabel = file.name;
                    }
                }
            },

            // ===== TYPING & MENTIONS =====
            // ===== TYPING & MENTIONS =====
            handleInput(event) {
                // Typing Indicator
                if (window.Echo && this.activeChat) {
                    window.Echo.private(`chat.${this.activeChat.id}`)
                        .whisper('typing', {
                            user: { id: this.userId, name: '{{ auth()->user()->name }}' }
                        });
                }

                // Mentions Logic
                const cursorPosition = event.target.selectionStart;
                const textBeforeCursor = this.newMessage.substring(0, cursorPosition);
                const lastAtMatch = textBeforeCursor.match(/@(\w*)$/);

                if (lastAtMatch) {
                    this.mentionQuery = lastAtMatch[1].toLowerCase();
                    this.filteredMentionUsers = this.activeChat.users.filter(u => 
                        u.id !== this.userId && u.name.toLowerCase().includes(this.mentionQuery)
                    );
                } else {
                    this.mentionQuery = null;
                    this.filteredMentionUsers = [];
                }
            },

            insertMention(user) {
                const cursorPosition = document.querySelector('textarea[x-model="newMessage"]').selectionStart;
                const textBeforeCursor = this.newMessage.substring(0, cursorPosition);
                const textAfterCursor = this.newMessage.substring(cursorPosition);
                
                const lastAtMatch = textBeforeCursor.match(/@([a-zA-Z0-9_]*)$/);
                if (lastAtMatch) {
                    const newTextBefore = textBeforeCursor.substring(0, lastAtMatch.index);
                    this.newMessage = newTextBefore + '@' + user.name + ' ' + textAfterCursor;
                }
                
                this.mentionQuery = null;
                this.filteredMentionUsers = [];
                document.querySelector('textarea[x-model="newMessage"]').focus();
            },

            // ===== THREADING =====
            openThreadDrawer(msg) {
                this.threadParent = msg;
                this.showThreadDrawer = true;
                this.fetchThreadMessages(msg.id);
            },

            fetchThreadMessages(id) {
                fetch(`/api/messages/${id}/thread`)
                    .then(res => res.json())
                    .then(data => {
                        this.threadMessages = data;
                    });
            },

            formatDateDivider(dateStr) { return formatDateDivider(dateStr); },

            sendThreadReply() {
                if (this.isSending || !this.threadReplyMessage.trim() || !this.threadParent) return;
                
                const textBody = this.threadReplyMessage.trim();
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('body', textBody);
                formData.append('parent_id', this.threadParent.id);
                
                this.isSending = true;
                
                fetch(`/api/conversations/${this.threadParent.conversation_id}/messages`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                }).then(async res => {
                    this.isSending = false;
                    if (!res.ok) {
                        const errData = await res.json().catch(() => ({}));
                        throw new Error(errData.message || errData.error || 'Failed to send reply.');
                    }
                    return res.json();
                }).then(() => {
                    this.threadReplyMessage = '';
                    this.fetchThreadMessages(this.threadParent.id);
                }).catch(err => {
                    this.toastTitle = 'Reply Failed';
                    this.toastMessage = err.message;
                    setTimeout(() => { if (this.toastMessage === err.message) this.toastMessage = null; }, 5000);
                });
            }
        }));
    });
</script>

<style>
    [x-cloak] { display: none !important; }
    .animate-slide-up { animation: slideUp 0.3s ease-out; }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .animate-slide-left { animation: slideLeft 0.3s ease-out; }
    @keyframes slideLeft { from { transform: translateX(100%); } to { transform: translateX(0); } }
    .animate-fade-in { animation: fadeIn 0.2s ease-out; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    /* Premium Chat Module Styling */
    .premium-own-bubble {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        background-size: 200% 200%;
        animation: premiumGradientMove 6s ease infinite;
        border-radius: 16px 16px 2px 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    @keyframes premiumGradientMove {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .premium-other-bubble {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 16px 16px 16px 2px;
    }

    .premium-active-chat {
        background: rgba(79, 70, 229, 0.05) !important;
        border-left: 4px solid #4f46e5 !important;
        padding-left: calc(0.875rem - 4px) !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .premium-inactive-chat {
        border-left: 4px solid transparent !important;
        padding-left: calc(0.875rem - 4px) !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .premium-inactive-chat:hover {
        background: rgba(241, 245, 249, 0.7) !important;
        transform: translateX(4px);
    }

    .premium-online-indicator {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
        animation: onlinePulse 2s infinite;
    }
    @keyframes onlinePulse {
        0% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        70% {
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    .premium-unread-badge {
        background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%) !important;
        animation: badgeBounce 2.5s ease infinite;
    }
    @keyframes badgeBounce {
        0%, 100% { transform: translateY(-50%) scale(1); }
        50% { transform: translateY(-50%) scale(1.1); }
    }

    .btn-premium-indigo {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3) !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .btn-premium-indigo:hover {
        background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%) !important;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4) !important;
        transform: translateY(-1px) !important;
    }
    
    /* Mobile Layout Fixes */
    @media (max-width: 768px) {
        .h-\[85vh\] { height: 100% !important; width: 100% !important; margin: 0 !important; border-radius: 0 !important; }
        #staff-floating-hub, #pip-chat-container, .pip-trigger { display: none !important; }
        
        /* Force message box to leave space for input */
        #advanced-messages-box { 
            height: 0 !important;
            flex-grow: 1 !important;
            padding-bottom: 20px !important;
        }

        /* Ensure input area is always visible */
        .shrink-0.px-4.py-3.bg-white {
            position: relative;
            bottom: 0;
            width: 100%;
        }
    }
    /* Desktop Overlap Fix */
    @media (min-width: 1024px) {
        #staff-floating-hub { bottom: 120px !important; transition: bottom 0.3s; }
    }
</style>
@endsection
