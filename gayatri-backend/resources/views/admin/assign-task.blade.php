@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200 overflow-hidden animate-fade-in">
        <div class="bg-slate-50/50 border-b border-slate-200 px-6 py-4">
            <h2 class="text-xl font-bold text-slate-900 brand-font">Assign New Task</h2>
            <p class="text-slate-500 text-xs">Assign a new task to a staff member.</p>
        </div>

        <form action="{{ route('tasks.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl text-xs relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Task Title</label>
                    <input type="text" name="title" required
                           placeholder="e.g. Audit Report for Client X"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Description</label>
                    <textarea name="description" rows="1"
                              placeholder="Enter task details..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition text-xs"></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Select Staff Member(s)</label>
                    <div class="grid grid-cols-1 gap-1.5 bg-white p-3 rounded-xl border border-slate-200 h-32 overflow-y-auto">
                        @foreach($users as $user)
                        <label class="flex items-center gap-2.5 p-2 rounded-lg border border-slate-50 hover:bg-blue-50/50 hover:border-blue-200 cursor-pointer transition group">
                            <input type="checkbox" name="staff_ids[]" value="{{ $user->id }}" 
                                   class="w-4 h-4 border-2 border-slate-300 rounded text-blue-600 focus:ring-blue-500 transition checked:border-blue-600 cursor-pointer">
                            <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-800">{{ $user->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Priority</label>
                        <select name="priority"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-white text-xs cursor-pointer">
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="high">High Priority</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Deadline Date</label>
                        <input type="date" name="due_date" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition text-xs">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center gap-2 text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                    Confirm Assignment
                </button>
            </div>
        </form>
    </div>

    <div class="mt-12 bg-white/90 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200 overflow-hidden animate-fade-in mb-12">
        <div class="bg-slate-50/50 border-b border-slate-200 px-8 py-4">
            <h3 class="text-xl font-bold text-slate-900 brand-font">Current Task Status</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-slate-100/50">
                    <th class="px-6 py-4 text-sm font-semibold text-slate-700">Task Title</th>
                    <th class="px-6 py-4 text-sm font-semibold text-slate-700">Assigned To</th>
                    <th class="px-6 py-4 text-sm font-semibold text-slate-700">Deadline</th>
                    <th class="px-6 py-4 text-sm font-semibold text-slate-700">Status</th>
                    <th class="px-6 py-4 text-sm font-semibold text-slate-700 text-center">Priority</th>
                    <th class="px-6 py-4 text-sm font-semibold text-slate-700 text-right">Action</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @foreach($tasks as $task)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $task->title }}</p>
                            <p class="text-xs text-slate-500">{{ Str::limit($task->description, 30) }}</p>
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $task->assignees->pluck('name')->join(', ') ?: 'N/A' }}
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ \Carbon\Carbon::parse($task->due_date)->format('d M, Y') }}
                        </td>

                        <td class="px-6 py-4">
                            @if($task->status == 'pending')
                                <span class="px-3 py-1 text-xs font-bold bg-yellow-100 text-yellow-700 rounded-full uppercase">
                                    Pending
                                </span>
                            @elseif($task->status == 'completed')
                                <span class="px-3 py-1 text-xs font-bold bg-green-100 text-green-700 rounded-full uppercase">
                                    Completed
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-bold bg-blue-100 text-blue-700 rounded-full uppercase">
                                    {{ $task->status }}
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span class="text-xs font-bold {{ $task->priority == 'high' ? 'text-red-600' : ($task->priority == 'medium' ? 'text-orange-500' : 'text-green-600') }}">
                                ● {{ strtoupper($task->priority) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('task.view', $task->id) }}"
                               class="relative inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-bold text-xs bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 transition hover:bg-blue-100">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                Chat
                                
                                @if($task->unread_chats_count > 0)
                                    <span class="absolute -top-2 -right-2 bg-red-600 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center border-2 border-white animate-pulse">
                                        {{ $task->unread_chats_count }}
                                    </span>
                                @endif
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
