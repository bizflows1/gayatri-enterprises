@extends('layouts.admin')

@section('content')

<div class="mb-10 animate-fade-in">
    <div class="relative overflow-hidden text-white rounded-3xl p-8 shadow-xl border border-white/10" style="background: linear-gradient(135deg, #0F2C4A, #173A5E);">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-400/10 rounded-full blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-emerald-400/10 rounded-full blur-3xl"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-3.5 mb-2.5">
                    <div class="p-2 bg-white/10 rounded-xl backdrop-blur-md border border-white/10 shadow-inner">
                        <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight brand-font">
                        Overview
                    </h1>
                </div>
                <p class="text-white/60 font-medium text-sm md:text-base max-w-xl leading-relaxed">
                    Welcome back, {{ auth()->user()->name }}.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-3 bg-white/5 border border-white/10 px-4 py-2.5 rounded-2xl backdrop-blur-md shadow-lg">
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                    </span>
                    <span class="text-xs font-bold text-white/80 uppercase tracking-widest">Active System</span>
                </div>

                <div class="flex items-center gap-2.5 bg-white/5 border border-white/10 px-4 py-2.5 rounded-2xl backdrop-blur-md shadow-lg text-white/80">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-xs font-bold tracking-wider uppercase">{{ now()->format('D, d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12 animate-fade-in">
    <div class="stat-card p-6 flex flex-col justify-between hover:shadow-lg hover:-translate-y-0.5 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 rounded-xl" style="background: var(--emerald-light, #EAF6EF); color: var(--emerald, #1B7A52);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Database</span>
        </div>
        <div>
            <h3 class="text-3xl font-bold tracking-tight" style="color: var(--navy, #0F2C4A);">{{ $stats['total_users'] }}</h3>
            <p class="text-sm font-medium text-slate-500 mt-1">Registered Users</p>
        </div>
    </div>

    <div class="stat-card p-6 flex flex-col justify-between hover:shadow-lg hover:-translate-y-0.5 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 rounded-xl" style="background: var(--emerald-light, #EAF6EF); color: var(--emerald, #1B7A52);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</span>
        </div>
        <div>
            <h3 class="text-3xl font-bold tracking-tight" style="color: var(--navy, #0F2C4A);">{{ $stats['active_users'] }}</h3>
            <p class="text-sm font-medium text-slate-500 mt-1">Active Accounts</p>
        </div>
    </div>

    <div class="stat-card p-6 flex flex-col justify-between hover:shadow-lg hover:-translate-y-0.5 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 rounded-xl bg-amber-50 text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Growth</span>
        </div>
        <div>
            <h3 class="text-3xl font-bold tracking-tight" style="color: var(--navy, #0F2C4A);">+{{ $stats['new_users'] }}</h3>
            <p class="text-sm font-medium text-slate-500 mt-1">Added This Month</p>
        </div>
    </div>

    <div class="stat-card p-6 flex flex-col justify-between hover:shadow-lg hover:-translate-y-0.5 transition duration-300">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 rounded-xl bg-red-50 text-red-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Security</span>
        </div>
        <div>
            <h3 class="text-3xl font-bold tracking-tight" style="color: var(--navy, #0F2C4A);">{{ $stats['blocked_users'] }}</h3>
            <p class="text-sm font-medium text-slate-500 mt-1">Inactive Users</p>
        </div>
    </div>
</div>

<!-- Quick Management Hub -->
<div class="mb-12">
    <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2 brand-font px-2">
        Management Shortcuts
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in">
        <a href="{{ route('manage.clients') }}" class="group btn-navy p-8 rounded-2xl text-white shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-125 transition duration-500">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
            </div>
            <div class="relative z-10">
                <h3 class="text-2xl font-bold mb-2">Manage Clients</h3>
                <p class="text-white/70 text-sm leading-relaxed">Add new clients, edit profiles, and control account access settings.</p>
                <div class="mt-6 flex items-center gap-2 font-bold text-sm">
                    Open Client List
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
            </div>
        </a>

        <a href="{{ route('documents.manage') }}" class="group btn-emerald p-8 rounded-2xl text-white shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-125 transition duration-500">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
            </div>
            <div class="relative z-10">
                <h3 class="text-2xl font-bold mb-2">Document Center</h3>
                <p class="text-white/70 text-sm leading-relaxed">Organize shared folders, manage client uploads, and check vault history.</p>
                <div class="mt-6 flex items-center gap-2 font-bold text-sm">
                    Open Vault
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
            </div>
        </a>

        <a href="{{ route('tasks.assign') }}" class="group p-8 rounded-2xl text-white shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden" style="background: #173A5E;">
            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-125 transition duration-500">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"/></svg>
            </div>
            <div class="relative z-10">
                <h3 class="text-2xl font-bold mb-2">Assign Tasks</h3>
                <p class="text-white/70 text-sm leading-relaxed">Communication center for sharing notices and assigning specific client tasks.</p>
                <div class="mt-6 flex items-center gap-2 font-bold text-sm">
                    Open Task Center
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Recent Activity -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
    <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-3">
            <div class="p-2 bg-slate-200 rounded-lg">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            Recent Audit Logs
        </h3>
        <a href="{{ route('activity.logs') }}" class="text-sm font-bold text-brand-emerald hover:text-brand-navy transition">View All Logs &rarr;</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead class="bg-slate-50/80 text-slate-400 uppercase text-[10px] font-bold tracking-[0.2em]">
                <tr>
                    <th class="px-8 py-5">User Account</th>
                    <th class="px-8 py-5">Action Type</th>
                    <th class="px-8 py-5">Summary</th>
                    <th class="px-8 py-5 text-right">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-slate-600">
                @forelse($recent_logs as $log)
                <tr class="hover:bg-brand-emerald-light transition duration-300">
                    <td class="px-8 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-brand-emerald-light text-brand-emerald rounded-full flex items-center justify-center font-bold text-[10px]">
                                {{ strtoupper(substr($log->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="font-bold text-slate-900">{{ $log->user->name ?? 'System' }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-5">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase {{ Str::contains($log->action, 'Deleted') ? 'bg-red-50 text-red-600' : 'bg-brand-emerald-light text-brand-emerald' }}">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-8 py-5 text-slate-500 leading-relaxed">{{ $log->description }}</td>
                    <td class="px-8 py-5 text-right font-medium text-slate-400">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-8 py-12 text-center text-slate-400 italic">No administrative activity recorded in this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
