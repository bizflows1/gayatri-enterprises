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
    <div class="shrink-0 flex flex-col gap-4">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 brand-font">Client Reviews</h1>
                <p class="text-slate-500 text-sm mt-1">Moderate reviews submitted by visitors before they appear on the website.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1.5 rounded-full">
                    {{ $reviews->where('status', 'pending')->count() }} Pending
                </span>
                <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded-full">
                    {{ $reviews->where('status', 'approved')->count() }} Approved
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(!$reviews->isEmpty())
            {{-- Tab Filters --}}
            <div class="flex gap-2 border-b border-slate-200">
                @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
                    <button onclick="filterReviews('{{ $key }}')" id="tab-{{ $key }}"
                        class="tab-btn px-4 py-2 text-sm font-semibold transition -mb-px border-b-2 {{ $key === 'all' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Scrollable content area -->
    @if($reviews->isEmpty())
        <div class="flex-1 flex items-center justify-center bg-white rounded-2xl border border-slate-200 shadow-sm p-12">
            <div class="text-center py-12 text-slate-400">
                <i data-lucide="message-square" class="w-12 h-12 mx-auto mb-4 opacity-30"></i>
                <p class="font-medium">No reviews yet.</p>
            </div>
        </div>
    @else
        <div class="flex-1 min-h-0 overflow-y-auto pr-1">
            <div class="grid gap-4" id="reviews-grid">
                @foreach($reviews as $review)
                <div class="review-item review-{{ $review->status }} bg-white border border-slate-200 rounded-2xl p-6 flex flex-col md:flex-row gap-4 items-start hover:shadow-md transition">
                    
                    {{-- Avatar --}}
                    <div class="w-12 h-12 {{ $review->avatarColor }} rounded-full flex-shrink-0 flex items-center justify-center text-white font-bold text-sm">
                        {{ $review->initials }}
                    </div>

                    {{-- Content --}}
                    <div class="flex-grow min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <span class="font-bold text-slate-900">{{ $review->name }}</span>
                            @if($review->designation)
                                <span class="text-slate-400 text-xs">{{ $review->designation }}</span>
                            @endif
                            {{-- Stars --}}
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'fill-current' : 'fill-slate-200 dark:fill-slate-600' }}" viewBox="0 0 24 24">
                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                    </svg>
                                @endfor
                            </div>
                            {{-- Status Badge --}}
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                {{ $review->status === 'approved' ? 'bg-green-100 text-green-700' : ($review->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst($review->status) }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-slate-600 text-sm leading-relaxed italic">"{{ $review->body }}"</p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 flex-shrink-0 self-center md:self-start">
                        @if($review->status !== 'approved')
                            <form action="{{ route('reviews.approve', $review->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-3 py-2 rounded-lg transition flex items-center gap-1 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Approve
                                </button>
                            </form>
                        @endif
                        @if($review->status !== 'rejected')
                            <form action="{{ route('reviews.reject', $review->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-3 py-2 rounded-lg transition flex items-center gap-1 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> Reject
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Delete this review?')">
                            @csrf @method('DELETE')
                            <button class="bg-red-100 hover:bg-red-200 text-red-700 text-xs font-bold p-2.5 rounded-lg transition flex items-center justify-center shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
function filterReviews(status) {
    // Update tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-600', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-slate-500');
    });
    document.getElementById('tab-' + status).classList.add('border-blue-600', 'text-blue-600');
    document.getElementById('tab-' + status).classList.remove('border-transparent', 'text-slate-500');

    // Filter items
    document.querySelectorAll('.review-item').forEach(el => {
        if (status === 'all' || el.classList.contains('review-' + status)) {
            el.style.display = 'flex';
        } else {
            el.style.display = 'none';
        }
    });
}
</script>
@endsection
