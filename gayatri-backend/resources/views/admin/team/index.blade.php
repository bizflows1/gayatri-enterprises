@extends('layouts.admin')

@php
    $isFullscreen = true;
@endphp

@section('content')

<style>
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .animate-slide-up { animation: slideUp 0.5s ease-out; }
    .animate-fade-in { animation: fadeIn 0.6s ease-out; }

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

</style>

<div class="h-full flex flex-col gap-6 animate-fade-in">
    <!-- Non-scrollable Top Section -->
    <div class="shrink-0 flex flex-col gap-5">
        <!-- Header Row -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold brand-font flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--navy, #0F2C4A);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <span class="text-slate-900">Team Members</span>
                </h1>
                <p class="text-slate-600 text-sm mt-2">Manage the profiles, roles, qualifications, and display ordering of staff on the public Team page.</p>
            </div>

            <div class="flex gap-3 shrink-0">
                <a href="{{ route('admin.team.create') }}"
                    class="btn-emerald px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Team Member
                </a>
                <a href="{{ rtrim(explode(',', env('FRONTEND_URLS', 'http://localhost:3010'))[0], '/') }}/team" target="_blank"
                    class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    Preview Live
                </a>
            </div>
        </div>

        <!-- Stats Cards Grid (Only displaying Total Members as requested) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stat-card p-5 hover:shadow-lg transition duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-sans">Total Members</p>
                        <h3 class="text-2xl font-bold mt-1" style="color: var(--navy, #0F2C4A);">{{ count($members) }}</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">Active profiles published</p>
                    </div>
                    <div class="p-2 rounded-lg shrink-0" style="background: var(--emerald-light, #EAF6EF); color: var(--emerald, #1B7A52);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-sm font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('warning'))
    <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span class="text-sm font-semibold">{{ session('warning') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3">
        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span class="text-sm font-semibold">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Main Listing View (White, Clean Card Pattern) -->
    <div class="flex-1 min-h-0 flex flex-col bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Active Profiles Directory
            </h2>
            <span class="text-xs text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200">
                Sorted by Display Sequence
            </span>
        </div>

        @if(empty($members) || count($members) === 0)
        <!-- Empty State -->
        <div class="flex-1 flex flex-col items-center justify-center p-12 text-center">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4 border border-blue-100">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">No Members Listed</h3>
            <p class="text-slate-500 text-sm max-w-md mx-auto mt-1 mb-6">
                The database is currently empty. The public Team page is safely displaying the <strong>fallback team</strong> so it never looks blank!
            </p>
            <a href="{{ route('admin.team.create') }}" class="btn-emerald inline-flex items-center gap-2 font-semibold px-6 py-3 rounded-lg shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Your First Member
            </a>
        </div>
        @else
        <!-- Table Area -->
        <div class="flex-1 overflow-x-auto overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 text-xs font-bold uppercase tracking-wider sticky top-0 z-10">
                    <tr>
                        <th class="py-4 px-6 text-center w-24">Order</th>
                        <th class="py-4 px-6">Profile Details</th>
                        <th class="py-4 px-6">Tags</th>
                        <th class="py-4 px-6 text-center w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 text-sm">
                    @foreach($members as $index => $member)
                    <tr class="hover:bg-slate-50/80 transition-all">
                        <!-- Reorder actions -->
                        <td class="py-4 px-6 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <span class="bg-slate-100 text-slate-700 w-7 h-7 rounded-md flex items-center justify-center border border-slate-200 font-mono text-xs font-bold">
                                    {{ $member->display_order }}
                                </span>
                                <div class="flex flex-col gap-0.5">
                                    @if(!$loop->first)
                                    <button onclick="moveMember({{ $member->id }}, 'up')" class="p-0.5 hover:bg-slate-200 rounded text-slate-500 hover:text-blue-600 transition" title="Move Up">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
                                    </button>
                                    @endif
                                    @if(!$loop->last)
                                    <button onclick="moveMember({{ $member->id }}, 'down')" class="p-0.5 hover:bg-slate-200 rounded text-slate-500 hover:text-blue-600 transition" title="Move Down">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Photo & Info -->
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 flex-shrink-0 relative">
                                    @if($member->image_path)
                                    <img src="{{ asset($member->image_path) }}" class="w-full h-full object-cover" alt="{{ $member->name }}">
                                    @else
                                    <div class="w-full h-full flex items-center justify-center bg-blue-50 text-blue-600 font-bold text-sm border border-blue-100">
                                        {{ collect(explode(' ', $member->name))->map(fn($n) => substr($n, 0, 1))->take(2)->implode('') }}
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-base leading-tight">{{ $member->name }}</h4>
                                    <p class="text-slate-500 text-xs mt-1">
                                        {{ $member->role }}
                                        @if($member->qualification)
                                        <span class="text-blue-600 font-semibold">({{ $member->qualification }})</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>

                        <!-- Tags list -->
                        <td class="py-4 px-6">
                            <div class="flex flex-wrap gap-1 max-w-[250px]">
                                @if($member->tags && count($member->tags) > 0)
                                    @foreach($member->tags as $tag)
                                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold border border-slate-200 uppercase">
                                        {{ $tag }}
                                    </span>
                                    @endforeach
                                @else
                                    <span class="text-slate-400 text-xs italic">No expertise tags</span>
                                @endif
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-6 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.team.edit', $member->id) }}" class="p-2 bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200 hover:border-blue-200 rounded-lg transition" title="Edit Profile">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('admin.team.destroy', $member->id) }}" method="POST" onsubmit="return confirmDelete(event, '{{ $member->name }}')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-slate-50 hover:bg-red-50 text-slate-600 hover:text-red-600 border border-slate-200 hover:border-red-200 rounded-lg transition" title="Delete Profile">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<script>
    function confirmDelete(e, name) {
        e.preventDefault();
        if (confirm(`Are you absolutely sure you want to delete ${name}'s team profile? This action is permanent!`)) {
            e.target.submit();
        }
    }

    function moveMember(memberId, direction) {
        const ids = @json(collect($members)->pluck('id'));

        const currentIndex = ids.indexOf(memberId);
        if (currentIndex === -1) return;

        let targetIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;
        if (targetIndex < 0 || targetIndex >= ids.length) return;

        // Swap
        const temp = ids[currentIndex];
        ids[currentIndex] = ids[targetIndex];
        ids[targetIndex] = temp;

        // Send AJAX to reorder
        fetch("{{ route('admin.team.reorder') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ order: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Reorder failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error('Reorder error:', err);
            alert('Reorder server error.');
        });
    }
</script>

@endsection
