@extends('layouts.admin')

@section('content')

<style>
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .animate-slide-up { animation: slideUp 0.5s ease-out; }
    .animate-fade-in { animation: fadeIn 0.6s ease-out; }

    .overflow-y-auto::-webkit-scrollbar { width: 6px; height: 6px; }
    .overflow-y-auto::-webkit-scrollbar-track { background: transparent; }
    .overflow-y-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<!-- Wrapper to fit full height of the viewport minus nav headers -->
<div x-data="{
    searchQuery: '{{ request('search') }}',
    isLoading: false,
    showImportModal: false,
    isImporting: false,
    importLogs: [],
    importSummary: null,
    importError: null,
    fetchResults() {
        this.isLoading = true;
        const url = new URL(window.location.href);
        if (this.searchQuery) {
            url.searchParams.set('search', this.searchQuery);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.delete('page');

        window.history.replaceState({}, '', url.toString());

        fetch(url.toString())
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const target = document.getElementById('clients-results-zone');
                const source = doc.getElementById('clients-results-zone');
                if (target && source) {
                    target.innerHTML = source.innerHTML;
                }
                this.isLoading = false;
            })
            .catch(err => {
                console.error(err);
                this.isLoading = false;
            });
    },
    uploadFile(e) {
        const file = e.target.files[0];
        if (!file) return;

        this.isImporting = true;
        this.importSummary = null;
        this.importError = null;
        this.importLogs = ['[INFO] Reading file and starting upload...'];

        const formData = new FormData();
        formData.append('csv_file', file);
        formData.append('dry_run', document.getElementById('import_dry_run').checked ? '1' : '0');
        formData.append('welcome_email', document.getElementById('import_welcome_email').checked ? '1' : '0');
        formData.append('overwrite_credentials', document.getElementById('import_overwrite_credentials').checked ? '1' : '0');

        fetch('{{ route('user.import') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Import failed');
            }
            return data;
        })
        .then(data => {
            this.isImporting = false;
            this.importSummary = {
                total: data.total_rows,
                created: data.created_count,
                updated: data.updated_count,
                skipped: data.skipped_count
            };
            this.importLogs = data.logs || [];
        })
        .catch(err => {
            this.isImporting = false;
            this.importError = err.message;
            this.importLogs.push('[ERROR] Import process halted: ' + err.message);
        });
    }
}" class="h-full flex flex-col overflow-hidden gap-5">
    <!-- Non-scrollable Top Section -->
    <div class="shrink-0 flex flex-col gap-5">
        <!-- Header Row -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold brand-font flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--navy, #0F2C4A);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <span class="text-slate-900">Manage Users</span>
                </h1>
                <p class="text-slate-600 text-sm mt-2">Manage client accounts, staff access &amp; permissions</p>
            </div>

            <div class="flex gap-3 shrink-0">
                <button type="button" @click="showImportModal = true"
                    class="btn-emerald px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import CSV
                </button>

                <a href="{{ route('user.form', ['role' => 'client']) }}"
                    class="btn-emerald px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    Add Client
                </a>

                @if(Auth::user()->role === 'admin')
                <a href="{{ route('user.form', ['role' => 'staff']) }}"
                    class="btn-navy px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Add Staff
                </a>
                @endif
            </div>
        </div>

        <!-- Stats Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="stat-card p-5 hover:shadow-lg transition hover:-translate-y-0.5 duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Users</p>
                        <h3 class="text-2xl font-bold mt-1" style="color: var(--navy, #0F2C4A);">{{ $totalCount }}</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">All registered</p>
                    </div>
                    <div class="p-2 rounded-lg shrink-0" style="background: var(--emerald-light, #EAF6EF); color: var(--emerald, #1B7A52);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5 hover:shadow-lg transition hover:-translate-y-0.5 duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Active</p>
                        <h3 class="text-2xl font-bold mt-1" style="color: var(--navy, #0F2C4A);">{{ $activeCount }}</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">Currently active</p>
                    </div>
                    <div class="p-2 rounded-lg shrink-0" style="background: var(--emerald-light, #EAF6EF); color: var(--emerald, #1B7A52);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5 hover:shadow-lg transition hover:-translate-y-0.5 duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Blocked</p>
                        <h3 class="text-2xl font-bold mt-1" style="color: var(--navy, #0F2C4A);">{{ $blockedCount }}</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">Inactive accounts</p>
                    </div>
                    <div class="p-2 rounded-lg shrink-0 bg-red-50 text-red-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5 hover:shadow-lg transition hover:-translate-y-0.5 duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">This Month</p>
                        <h3 class="text-2xl font-bold mt-1" style="color: var(--navy, #0F2C4A);">+{{ $thisMonthCount }}</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">New registrations</p>
                    </div>
                    <div class="p-2 rounded-lg shrink-0 bg-amber-50 text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filters Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1">
                <h3 class="text-base font-bold text-slate-800 shrink-0">
                    {{ Auth::user()->role === 'admin' ? 'User Directory' : 'Client Directory' }}
                </h3>

                @if(Auth::user()->role === 'admin')
                <!-- Filter Pills - Minimal (Admin Only) -->
                <div class="flex gap-1.5 flex-wrap">
                    <a href="{{ route('manage.clients', ['search' => request('search')]) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ !request('role_filter') ? 'btn-navy shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        All Users
                    </a>
                    <a href="{{ route('manage.clients', ['role_filter' => 'client', 'search' => request('search')]) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('role_filter') == 'client' ? 'btn-navy shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Clients
                    </a>
                    <a href="{{ route('manage.clients', ['role_filter' => 'staff', 'search' => request('search')]) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('role_filter') == 'staff' ? 'btn-navy shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Staff
                    </a>
                    <a href="{{ route('manage.clients', ['role_filter' => 'admin', 'search' => request('search')]) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('role_filter') == 'admin' ? 'btn-navy shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Admins
                    </a>
                </div>
                @endif
            </div>

            <!-- Search Bar Form -->
            <form action="{{ route('manage.clients') }}" method="GET" class="relative w-full lg:w-80 shrink-0" @submit.prevent>
                @if(request('role_filter'))
                    <input type="hidden" name="role_filter" value="{{ request('role_filter') }}">
                @endif
                <input type="text" name="search" x-model="searchQuery" @input.debounce.250ms="fetchResults()" placeholder="Search by name, email, phone..."
                       class="w-full pl-9 pr-10 py-2 bg-white border border-slate-300 rounded-lg text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>

                <div x-show="isLoading" class="absolute right-3 top-2" x-cloak>
                    <svg class="animate-spin h-4 w-4" style="color: var(--emerald, #1B7A52);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table Card - Scrollable List -->
    <div id="clients-results-zone" class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <!-- Table Scroll Container -->
        <div class="overflow-x-auto overflow-y-auto flex-1">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-700 uppercase text-xs tracking-wider font-bold sticky top-0 z-10 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-center w-16">S.No.</th>
                    <th class="px-6 py-4">User Profile</th>
                    <th class="px-6 py-4">Contact</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Joined</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm" id="clientTableBody">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4 text-center font-mono text-slate-400 font-bold w-16">
                        {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm"
                                style="background: {{ $user->role === 'admin' ? '#0F2C4A' : ($user->role === 'staff' ? '#173A5E' : '#1B7A52') }};">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-slate-700 text-sm">+91-{{ $user->phone }}</span>
                            <a href="https://wa.me/91{{ $user->phone }}?text=Hello {{ $user->name }}," target="_blank"
                               class="text-green-600 hover:text-green-700 p-1.5 bg-green-100 rounded-lg transition" title="WhatsApp">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.017-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            </a>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        @if($user->role === 'admin')
                            <span class="inline-flex items-center gap-1.5 text-white px-3 py-1 rounded-md text-xs font-semibold" style="background:#0F2C4A;">
                                Admin
                            </span>
                        @elseif($user->role === 'staff')
                            <span class="inline-flex items-center gap-1.5 text-white px-3 py-1 rounded-md text-xs font-semibold" style="background:#173A5E;">
                                Staff
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-white px-3 py-1 rounded-md text-xs font-semibold" style="background:#1B7A52;">
                                Client
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        @if($user->is_active)
                            <span class="inline-flex items-center gap-1.5 text-green-700 font-semibold text-xs bg-green-100 px-3 py-1 rounded-md">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Active
                            </span>
                        @else
                            @if(($user->login_attempts ?? 0) >= 5)
                                <span class="inline-flex items-center gap-1.5 text-orange-700 font-semibold text-xs bg-orange-100 px-3 py-1 rounded-md" title="Exceeded login attempts">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    LOCKED
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-red-700 font-semibold text-xs bg-red-100 px-3 py-1 rounded-md">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span> Blocked
                                </span>
                            @endif
                        @endif
                    </td>

                    <td class="px-6 py-4 text-slate-600 text-xs">
                        {{ $user->created_at->format('d M, Y') }}
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex justify-center gap-2">
                            @if($user->role === 'client')
                            <a href="{{ route('documents.view', $user->id) }}" class="p-2 rounded-lg transition" style="background: var(--emerald-light, #EAF6EF); color: var(--emerald, #1B7A52);" title="View Documents">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path></svg>
                            </a>
                            @endif

                            @if($user->id !== Auth::id() && $user->role !== 'client')
                            <a href="{{ route('chat.index', ['user_id' => $user->id]) }}" class="p-2 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-lg transition" title="Direct Chat">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            </a>
                            @endif

                            <a href="{{ route('user.edit', $user->id) }}" class="p-2 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-lg transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>

                            @if(Auth::user()->role === 'admin' && $user->id !== Auth::id())
                                <form action="{{ route('user.status', $user->id) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="p-2 rounded-lg transition {{ !$user->is_active ? 'bg-orange-600 text-white hover:bg-orange-700' : 'bg-yellow-100 text-yellow-600 hover:bg-yellow-200' }}"
                                        title="{{ !$user->is_active ? 'Unlock & Activate' : 'Block User' }}">
                                        @if(!$user->is_active)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        @endif
                                    </button>
                                </form>
                                <form action="{{ route('user.delete', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                        <div class="flex flex-col items-center">
                            <svg class="w-16 h-16 mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <p class="text-lg font-semibold">No users found</p>
                            <p class="text-sm text-slate-400 mt-1">Try adjusting your search or filters</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">
            Showing {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
        </div>
        <div class="flex-shrink-0">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- CSV Import Modal -->
<div x-show="showImportModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm animate-fade-in"
     x-cloak
     @keydown.escape.window="showImportModal = false"
     style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]"
         @click.away="showImportModal = false">

        <!-- Modal Header -->
        <div class="px-6 py-4 text-white flex justify-between items-center shrink-0" style="background: var(--navy, #0F2C4A);">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: var(--emerald, #1B7A52);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                </div>
                <div class="text-left">
                    <h3 class="text-lg font-bold brand-font text-white">Import Clients</h3>
                    <p class="text-xs text-white/60">Batch upload using a structured CSV file</p>
                </div>
            </div>
            <button @click="showImportModal = false" class="text-white/50 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 flex flex-col gap-5">
            <!-- Instructions and Format download -->
            <div class="rounded-xl p-4 flex gap-4 items-start text-left border" style="background: var(--emerald-light, #EAF6EF); border-color: rgba(27,122,82,0.2);">
                <div class="p-2 rounded-lg shrink-0" style="background: rgba(27,122,82,0.15); color: var(--emerald, #1B7A52);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="text-xs flex-1" style="color: var(--navy, #0F2C4A);">
                    <p class="font-bold mb-1">CSV Format Requirements:</p>
                    <p class="mb-2">Your CSV must match the column layout specified in <code class="bg-white/60 px-1 rounded">format.txt</code>. Use the sample template below to prepare your file.</p>
                    <a href="{{ route('user.import.sample') }}" class="inline-flex items-center gap-1.5 font-bold underline" style="color: var(--emerald-deep, #145C3F);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Sample CSV Template
                    </a>
                </div>
            </div>

            <!-- Import Settings Options -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-left">
                <label class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                    <input type="checkbox" id="import_dry_run" class="mt-1 rounded border-slate-300" style="accent-color: #1B7A52;">
                    <div class="text-xs">
                        <p class="font-bold text-slate-800">Dry Run (Simulation)</p>
                        <p class="text-slate-500 mt-0.5">Simulate parsing without writing to DB</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                    <input type="checkbox" id="import_welcome_email" checked class="mt-1 rounded border-slate-300" style="accent-color: #1B7A52;">
                    <div class="text-xs">
                        <p class="font-bold text-slate-800">Send Welcome Email</p>
                        <p class="text-slate-500 mt-0.5">Email login password to new clients</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                    <input type="checkbox" id="import_overwrite_credentials" checked class="mt-1 rounded border-slate-300" style="accent-color: #1B7A52;">
                    <div class="text-xs">
                        <p class="font-bold text-slate-800">Overwrite Existing</p>
                        <p class="text-slate-500 mt-0.5">Overwrite matching existing records</p>
                    </div>
                </label>
            </div>

            <!-- Drag & Drop Upload Zone -->
            <div class="relative border-2 border-dashed border-slate-300 rounded-2xl hover:border-emerald-500 transition duration-300 bg-slate-50 flex flex-col items-center justify-center p-8 text-center group">
                <input type="file" accept=".csv,text/csv,text/plain" @change="uploadFile($event)"
                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" :disabled="isImporting">

                <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-emerald-600 group-hover:border-emerald-200 transition duration-300">
                    <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                </div>
                <p class="mt-3 text-sm font-bold text-slate-800 group-hover:text-emerald-600 transition">Click or Drag &amp; Drop CSV File here</p>
                <p class="text-xs text-slate-500 mt-1">Accepts standard .csv file format</p>
            </div>

            <!-- Importing Loading State -->
            <div x-show="isImporting" class="flex flex-col items-center justify-center py-6" x-cloak style="display: none;">
                <svg class="animate-spin h-10 w-10" style="color: var(--emerald, #1B7A52);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm font-bold text-slate-700 mt-3">Processing and importing records...</p>
                <p class="text-xs text-slate-500 mt-1">Please do not close this window</p>
            </div>

            <!-- Import Summary Cards -->
            <div x-show="importSummary" class="grid grid-cols-4 gap-3 bg-slate-50 border border-slate-200 rounded-xl p-4" x-cloak style="display: none;">
                <div class="text-center border-r border-slate-200">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Parsed</p>
                    <p class="text-lg font-bold text-slate-900 mt-1" x-text="importSummary ? importSummary.total : 0"></p>
                </div>
                <div class="text-center border-r border-slate-200">
                    <p class="text-[10px] font-bold text-green-600 uppercase tracking-wider">Created</p>
                    <p class="text-lg font-bold text-green-600 mt-1" x-text="importSummary ? importSummary.created : 0"></p>
                </div>
                <div class="text-center border-r border-slate-200">
                    <p class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--emerald, #1B7A52);">Updated</p>
                    <p class="text-lg font-bold mt-1" style="color: var(--emerald, #1B7A52);" x-text="importSummary ? importSummary.updated : 0"></p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider">Skipped</p>
                    <p class="text-lg font-bold text-red-500 mt-1" x-text="importSummary ? importSummary.skipped : 0"></p>
                </div>
            </div>

            <!-- Console Logging Feed -->
            <div x-show="importLogs.length > 0" class="flex flex-col flex-1 min-h-[200px]" x-cloak style="display: none;">
                <p class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 text-left">Process Logs:</p>
                <div class="bg-slate-900 text-slate-300 font-mono text-xs rounded-xl p-4 overflow-y-auto max-h-[250px] flex-1 border border-slate-800 shadow-inner flex flex-col gap-1 text-left">
                    <template x-for="log in importLogs">
                        <div :class="{
                            'text-green-400': log.includes('[SUCCESS]'),
                            'text-red-400': log.includes('[ERROR]'),
                            'text-yellow-400': log.includes('[WARNING]'),
                            'text-blue-300': log.includes('[INFO]'),
                            'text-purple-300': log.includes('[SIMULATION]')
                        }" x-text="log"></div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 shrink-0">
            <button @click="showImportModal = false" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-bold transition">
                Close
            </button>
            <button x-show="importSummary && (importSummary.created > 0 || importSummary.updated > 0)"
                    onclick="window.location.reload()"
                    class="btn-emerald px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow"
                    style="display: none;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2M7 9a7 7 0 00-7 7h3a4 4 0 014-4h2.5"></path></svg>
                Reload Directory
            </button>
        </div>
    </div>
</div>

</div>
</div>

@if(session('generated_password'))
<div id="passwordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm animate-fade-in">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 transform transition-all scale-100">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">Account Created!</h3>
            <div class="mt-2">
                <p class="text-sm text-slate-500">Here are the login credentials for the new user.</p>
                <div class="mt-4 bg-slate-100 p-4 rounded-xl border border-slate-200 text-left">
                    <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">Password</p>
                    <div class="flex justify-between items-center">
                        <code class="text-lg font-mono font-bold text-slate-800" id="passwordText">{{ session('generated_password') }}</code>
                        <button onclick="copyPassword()" class="text-xs font-bold transition" style="color: var(--emerald, #1B7A52);">COPY</button>
                    </div>
                </div>
                <p class="text-xs text-red-500 mt-2 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    Copy this now. It will not be shown again.
                </p>
            </div>
        </div>
        <div class="mt-6">
            <button type="button" onclick="document.getElementById('passwordModal').remove()" class="btn-emerald w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-3 text-base font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:text-sm transition">
                I have copied it
            </button>
        </div>
    </div>
</div>
<script>
    function copyPassword() {
        const password = document.getElementById('passwordText').innerText;
        navigator.clipboard.writeText(password).then(() => {
            alert('Password copied to clipboard!');
        });
    }
</script>
@endif

@endsection
