@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">

    {{-- Back Button --}}
    <a href="{{ route('mailbox.index', ['account' => $activeKey]) }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600 mb-6 transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Inbox
    </a>

    @if($error)
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl px-6 py-5 mb-6">
        <p class="font-bold text-sm">Could not load email</p>
        <p class="text-xs mt-1 opacity-75">{{ $error }}</p>
    </div>
    @elseif($email)

    {{-- Email Header --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        <div class="px-8 py-6 border-b border-slate-50">
            <h1 class="text-xl font-bold text-slate-800 mb-4 leading-tight">{{ $email['subject'] }}</h1>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-sm">
                        {{ strtoupper(substr($email['from'], 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-slate-800">{{ $email['from'] }}</p>
                        <p class="text-xs text-slate-400">{{ $email['date'] }}</p>
                    </div>
                </div>
                <span class="text-xs bg-slate-100 text-slate-500 px-3 py-1 rounded-full font-medium">
                    {{ $accounts[$activeKey]['label'] }}
                </span>
            </div>
        </div>

        {{-- Email Body --}}
        <div class="px-8 py-6">
            <div class="border border-slate-100 rounded-2xl overflow-hidden bg-white shadow-sm">
                <iframe srcdoc="{{ htmlspecialchars($email['body_html'], ENT_QUOTES, 'UTF-8') }}" 
                        sandbox="allow-popups allow-popups-to-escape-sandbox" 
                        class="w-full border-0 min-h-[500px] max-h-[80vh] overflow-y-auto"
                        style="width: 100%; display: block;">
                </iframe>
            </div>
        </div>
    </div>

    {{-- Reply Form --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/60 flex items-center gap-3">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
            </svg>
            <h3 class="font-bold text-sm text-slate-700">Reply</h3>
        </div>

        <form action="{{ route('mailbox.reply') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <input type="hidden" name="account" value="{{ $activeKey }}">

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">To</label>
                <input type="email" name="to" value="{{ $email['reply_to'] }}"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Subject</label>
                <input type="text" name="subject" value="Re: {{ $email['subject'] }}"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Message</label>
                <textarea name="body" rows="6" placeholder="Write your reply..."
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition resize-none"></textarea>
            </div>

            <div class="flex items-center justify-between pt-2">
                {{-- Delete button --}}
                <form action="{{ route('mailbox.delete', $email['uid']) }}" method="POST"
                      onsubmit="return confirm('Delete this email?')">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="account" value="{{ $activeKey }}">
                    <button type="submit"
                            class="flex items-center gap-2 text-sm text-red-500 hover:text-red-700 font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Email
                    </button>
                </form>

                {{-- Send Reply --}}
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Send Reply
                </button>
            </div>
        </form>
    </div>

    @endif
</div>
@endsection
