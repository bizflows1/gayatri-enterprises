@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Slim Header Row -->
    <div class="flex justify-between items-center border-b border-slate-200 pb-3.5 mb-5">
        <div>
            <h2 class="text-xl font-bold text-slate-900 brand-font">Assign New Task</h2>
            <p class="text-slate-500 text-xs mt-0.5">Assign a new professional task to staff members.</p>
        </div>
        <a href="{{ route('tasks.manage') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
            View All Tasks
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    <form id="assignTaskForm" action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
        @csrf

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 animate-fade-in">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 animate-fade-in">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('error') }}
            </div>
        @endif

        <!-- Two Column Fields Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Left Column: Title & Dates & Selects -->
            <div class="space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Task Title</label>
                    <input type="text" name="title" required
                           placeholder="e.g. Income Tax Return - Client ID 204"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition text-xs font-medium">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Priority Level</label>
                        <select name="priority"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-white text-xs font-medium cursor-pointer">
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="high">High Priority</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Deadline</label>
                        <input type="date" name="due_date" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition text-xs font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="2"
                              placeholder="Provide detailed instructions for the staff..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition text-xs font-medium resize-none"></textarea>
                </div>
            </div>

            <!-- Right Column: Staff Checkboxes List -->
            <div class="flex flex-col h-full">
                <label class="block text-xs font-bold text-slate-700 mb-1">Assign Staff Member(s)</label>
                <div id="staffCheckboxes" class="flex-1 min-h-[160px] max-h-[175px] overflow-y-auto bg-slate-50 rounded-xl border border-slate-200 p-2 space-y-1">
                    @foreach($users as $user)
                    <label class="flex items-center gap-2.5 p-2 bg-white rounded-lg border border-slate-100 hover:bg-blue-50 hover:border-blue-200 cursor-pointer transition group">
                        <input type="checkbox" name="staff_ids[]" value="{{ $user->id }}" 
                               class="staff-checkbox w-4 h-4 border-2 border-slate-300 rounded text-blue-600 focus:ring-blue-500 transition">
                        <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-800">{{ $user->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-150 flex justify-end">
            <button type="submit"
                    class="w-full md:w-auto btn-emerald text-white font-bold px-8 py-3 rounded-xl shadow-lg shadow-emerald-100 flex items-center justify-center gap-2 text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                Confirm Assignment
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('assignTaskForm').addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.staff-checkbox:checked');
    if (checked.length === 0) {
        e.preventDefault();
        alert('Employee selection necessary: Please select at least one staff member.');
    }
});
</script>
@endsection
