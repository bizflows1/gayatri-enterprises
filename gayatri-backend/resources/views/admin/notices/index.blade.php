@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight brand-font text-slate-900">Firm Broadcast Center</h2>
            <p class="text-slate-500 font-sans mt-1">Manage official announcements, alerts, and targeted client updates.</p>
        </div>
        <button onclick="document.getElementById('noticeModal').classList.remove('hidden')" class="btn-emerald text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-emerald-200 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            <span>Broadcast New Notice</span>
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-6 py-4 rounded-2xl mb-8 flex items-center gap-3 animate-fade-in shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-200 text-red-700 px-6 py-4 rounded-2xl mb-8 flex items-center gap-3 animate-fade-in shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            <span class="font-bold">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-600 px-6 py-4 rounded-2xl mb-8 shadow-sm">
            <p class="font-bold mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Active Notices Table -->
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Type & Title</th>
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Target Audience</th>
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Expires</th>
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($notices as $notice)
                    <tr class="hover:bg-slate-50/50 transition group">
                        <td class="px-8 py-6">
                            <div class="flex items-start gap-4">
                                @php
                                    $bg = $notice->type == 'urgent' ? 'bg-red-100 text-red-600' : ($notice->type == 'warning' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600');
                                @endphp
                                <div class="w-10 h-10 rounded-xl {{ $bg }} flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($notice->type == 'urgent')
                                            <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        @else
                                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        @endif
                                    </svg>
                                </div>
                                <div class="overflow-hidden">
                                    <h4 class="font-bold text-slate-800 text-sm group-hover:text-amber-600 transition">{{ $notice->title }}</h4>
                                    <p class="text-xs text-slate-400 mt-0.5 truncate max-w-xs">{{ $notice->content }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">
                                {{ $notice->target_type == 'all' ? 'Globally Broadcasted' : ($notice->target_type == 'staff' ? 'Staff Members Only' : 'Targeted Clients') }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                @if($notice->expires_at)
                                    <span class="text-xs font-bold text-slate-700">{{ $notice->expires_at->format('d M, Y') }}</span>
                                    <span class="text-[9px] text-slate-400 uppercase font-bold tracking-tighter">{{ $notice->expires_at->diffForHumans() }}</span>
                                @else
                                    <span class="text-xs font-bold text-slate-400 italic">No Expiry</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <form action="{{ route('notice.destroy', $notice->id) }}" method="POST" onsubmit="return confirm('Retract this broadcast? Clients will no longer see it.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-700 p-2 hover:bg-red-50 rounded-lg transition" title="Retract Notice">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                </div>
                                <h3 class="text-slate-900 font-bold">Silence is Golden</h3>
                                <p class="text-slate-400 text-sm">No active broadcasts currently running.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notices->hasPages())
        <div class="px-8 py-5 bg-slate-50 border-t border-slate-100">
            {{ $notices->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Broadcast Modal -->
<div id="noticeModal" class="hidden fixed inset-0 z-[100] overflow-y-auto bg-slate-900/60 backdrop-blur-sm animate-fade-in p-4 flex justify-center items-start md:py-12">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full my-auto">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white z-10">
            <div>
                <h3 class="text-xl font-bold text-slate-800">New Broadcast</h3>
                <p class="text-xs text-slate-400 font-medium tracking-wide mt-0.5">DRAFT AN OFFICE-WIDE ANNOUNCEMENT</p>
            </div>
            <button onclick="document.getElementById('noticeModal').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            </button>
        </div>

        <form action="{{ route('notice.store') }}" method="POST" class="p-8 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Subject Title</label>
                    <input type="text" name="title" required placeholder="e.g. Audit Season 2025" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none transition font-sans">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Priority Level</label>
                    <select name="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none transition cursor-pointer">
                        <option value="info">General Information</option>
                        <option value="warning">Compliance Warning</option>
                        <option value="urgent">Urgent / High Priority</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Message Content</label>
                <textarea name="content" rows="4" required placeholder="Write your professional announcement here..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none transition font-sans resize-none"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Target Audience</label>
                    <select name="target_type" id="targetType" onchange="toggleUserSelection(this.value)" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none transition cursor-pointer">
                        <option value="all">Every Registered Client</option>
                        <option value="staff">All Staff Members</option>
                        <option value="specific">Selected Client(s)</option>
                        <option value="specific_staff">Selected Staff Member(s)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Auto-Expire On (Default 1 Week)</label>
                    <input type="date" name="expires_at" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 outline-none transition font-sans">
                </div>
            </div>

            <div id="userSelection" class="hidden animate-fade-in">
                <label class="block text-sm font-bold text-slate-700 mb-4">Select Target Clients</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    @foreach($users as $user)
                    <label class="flex items-center gap-3 p-3 bg-white rounded-xl border border-slate-100 hover:border-amber-400 cursor-pointer transition group">
                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="w-5 h-5 rounded text-amber-600 focus:ring-amber-500">
                        <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">{{ $user->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div id="staffSelection" class="hidden animate-fade-in">
                <label class="block text-sm font-bold text-slate-700 mb-4">Select Staff Members</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    @foreach(\App\Models\User::where('role', 'staff')->orderBy('name')->get() as $staff)
                    <label class="flex items-center gap-3 p-3 bg-white rounded-xl border border-slate-100 hover:border-amber-400 cursor-pointer transition group">
                        <input type="checkbox" name="user_ids[]" value="{{ $staff->id }}" class="w-5 h-5 rounded text-amber-600 focus:ring-amber-500">
                        <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">{{ $staff->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full btn-navy text-white font-bold py-4 rounded-2xl shadow-xl flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    Send Broadcast Now
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleUserSelection(type) {
        const clientSel = document.getElementById('userSelection');
        const staffSel = document.getElementById('staffSelection');
        clientSel.classList.add('hidden');
        staffSel.classList.add('hidden');
        if (type === 'specific') clientSel.classList.remove('hidden');
        if (type === 'specific_staff') staffSel.classList.remove('hidden');
    }
</script>
@endsection
