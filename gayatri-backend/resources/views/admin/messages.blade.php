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

<div class="h-full flex flex-col overflow-hidden gap-5" x-data="{ selectAll: false, selected: [] }">
    <!-- Non-scrollable Top Section -->
    <div class="shrink-0 flex items-center justify-between">
        <h1 class="text-3xl font-bold brand-font text-slate-900">Website Inquiries</h1>
    </div>

    <!-- Main Results Card - Scrollable -->
    <div class="bg-white rounded-xl border border-slate-200 shadow flex-1 min-h-0 flex flex-col overflow-hidden pt-0.5">
        <form action="{{ route('admin.messages.delete') }}" method="POST" id="messages-form" class="h-full flex flex-col min-h-0">
            @csrf
            @method('DELETE')
            
            <!-- Action Row - Non-scrollable -->
            <div class="bg-slate-50 border-b border-slate-200 p-4 flex justify-between items-center shrink-0">
                <div class="text-sm text-slate-600 font-semibold uppercase tracking-wider">
                    <span x-show="selected.length > 0" x-text="selected.length + ' selected'" class="text-blue-600 bg-blue-50 px-2 py-1 rounded-md border border-blue-100"></span>
                    <span x-show="selected.length === 0">Select items to delete</span>
                </div>
                <div class="flex gap-2">
                    <button type="submit" name="delete_selected" value="1" x-show="selected.length > 0" class="px-4 py-2 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 rounded-lg text-sm font-semibold transition flex items-center gap-2 shadow-sm" onclick="return confirm('Are you sure you want to delete the selected inquiries?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Delete Selected
                    </button>
                    <button type="submit" name="delete_all" value="1" class="btn-navy text-white px-4 py-2 border border-transparent rounded-lg text-sm font-semibold transition flex items-center gap-2 shadow" onclick="return confirm('WARNING: This will delete ALL inquiries in the system. Are you absolutely sure?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Delete All
                    </button>
                </div>
            </div>

            <!-- Table Scroll Container -->
            <div class="overflow-x-auto overflow-y-auto flex-1">
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="bg-slate-50 text-slate-700 uppercase text-xs tracking-wider border-b border-slate-200 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 w-10">
                                <input type="checkbox" x-model="selectAll" @change="selected = selectAll ? Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value) : []" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="px-6 py-4 font-bold">Date</th>
                            <th class="px-6 py-4 font-bold">Name</th>
                            <th class="px-6 py-4 font-bold">Contact</th>
                            <th class="px-6 py-4 font-bold">Subject</th>
                            <th class="px-6 py-4 font-bold">Message</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        @forelse($messages as $msg)
                        <tr class="hover:bg-slate-50 transition" :class="selected.includes('{{ $msg->id }}') ? 'bg-blue-50/50' : ''">
                            <td class="px-6 py-4">
                                <input type="checkbox" name="ids[]" value="{{ $msg->id }}" x-model="selected" class="row-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                                {{ $msg->created_at->format('d M, Y') }}<br>
                                <span class="text-xs">{{ $msg->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $msg->name }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-blue-600 font-semibold hover:underline cursor-pointer">{{ $msg->email }}</span>
                                    <span class="text-slate-500 text-xs mt-1 font-medium">{{ $msg->phone }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-md text-xs font-bold border border-blue-100 uppercase tracking-wide">
                                    {{ $msg->subject }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 min-w-[300px] max-w-[500px] whitespace-normal break-words leading-relaxed">
                                {{ $msg->message }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <span class="font-medium">No inquiries found yet.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Anchored Pagination -->
            @if($messages->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex-shrink-0">
                {{ $messages->links() }}
            </div>
            @endif
        </form>
    </div>
</div>

@endsection
