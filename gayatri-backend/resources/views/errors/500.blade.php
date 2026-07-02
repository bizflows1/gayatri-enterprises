@extends('layouts.app')

@section('title', 'Server Error - Gayatri Enterprises')

@section('content')
<section class="min-h-[80vh] flex items-center justify-center bg-slate-50 relative overflow-hidden">
    <!-- Background Patterns -->
    <div class="absolute inset-0 opacity-30 pointer-events-none" 
         style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 24px 24px;">
    </div>
    <div class="absolute top-20 right-20 w-72 h-72 bg-red-400 rounded-full opacity-10 blur-3xl"></div>
    <div class="absolute bottom-20 left-20 w-96 h-96 bg-indigo-400 rounded-full opacity-10 blur-3xl"></div>

    <div class="text-center relative z-10 px-4">
        <h1 class="text-9xl font-bold text-slate-200 brand-font mb-4 tracking-tighter">500</h1>
        <h2 class="text-3xl md:text-4xl font-bold text-slate-800 brand-font mb-4">Internal Server Error</h2>
        <p class="text-slate-600 mb-8 max-w-md mx-auto text-lg leading-relaxed">
            We're sorry, but something went wrong on our end. Our technical team has been notified. Please try again later.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button onclick="window.location.reload()" class="px-8 py-3.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Try Again
            </button>
            <a href="/portal" class="px-8 py-3.5 bg-white text-slate-700 border border-slate-300 rounded-lg font-semibold hover:bg-slate-50 hover:text-blue-600 transition shadow-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Return Home
            </a>
        </div>
    </div>
</section>
@endsection
