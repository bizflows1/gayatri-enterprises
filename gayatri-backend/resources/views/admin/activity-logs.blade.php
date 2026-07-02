@extends('layouts.admin')

@section('content')
<style>
    /* Premium minimalist scrollbar styling */
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
<div class="h-full flex flex-col overflow-hidden gap-5">
    <!-- Non-scrollable Top Section -->
    <div class="shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200/60 pb-5">
        <div>
            <h1 class="text-3xl font-bold brand-font flex items-center gap-3">
                <span class="bg-blue-100 p-2 rounded-xl text-blue-600 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </span>
                <span class="text-slate-900">System Activity Logs</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1.5 font-medium">Track and audit real-time user actions, logins, and downloads.</p>
        </div>
    </div>
    
    <!-- Filters Bar -->
    <div class="shrink-0 bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <!-- Filter by Activity Type -->
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Activity Type</label>
                <select name="activity_type" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-xs text-slate-700 cursor-pointer transition">
                    <option value="all">All Activities</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}" {{ request('activity_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter by User Role -->
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">User Role</label>
                <select name="role_filter" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-xs text-slate-700 cursor-pointer transition">
                    <option value="all">All Roles</option>
                    <option value="client" {{ request('role_filter') === 'client' ? 'selected' : '' }}>Client</option>
                    <option value="staff" {{ request('role_filter') === 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="admin" {{ request('role_filter') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 btn-emerald text-white text-xs font-bold py-3 rounded-xl transition">Apply Filters</button>
                <a href="{{ request()->url() }}" class="p-3 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition flex items-center justify-center" title="Reset Filters">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Main Results Card - Scrollable -->
    <div class="bg-white rounded-xl border border-slate-200 shadow flex-1 min-h-0 flex flex-col overflow-hidden pt-0.5">
        <!-- Scrollable Table Body Wrapper -->
        <div class="overflow-x-auto overflow-y-auto flex-1">
            <table class="w-full text-sm text-left text-slate-500 border-collapse">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th class="px-6 py-4 text-center w-16">S.No.</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Action</th>
                        <th class="px-6 py-4">Description</th>
                        <th class="px-6 py-4">IP Address</th>
                        <th class="px-6 py-4">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse($logs as $log)
                    <tr class="bg-white hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 text-center font-mono text-slate-400 font-bold w-16">
                            {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-900">
                            {{ $log->user->name ?? 'Unknown User' }}
                            <span class="block text-xs text-slate-400 font-normal mt-0.5">{{ $log->user->role ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-md text-xs font-bold inline-block
                                {{ $log->action == 'Downloaded Document' ? 'bg-green-100 text-green-700' : 'bg-blue-50 text-blue-600' }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 leading-relaxed max-w-md">
                            {{ $log->description }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-500">
                            {{ $log->ip_address }}
                        </td>
                        <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                            {{ $log->created_at->format('d M, Y • h:i A') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                            No activity logs found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Anchored Pagination -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex-shrink-0">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection