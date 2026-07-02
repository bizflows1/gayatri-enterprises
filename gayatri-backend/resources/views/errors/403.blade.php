@extends('layouts.app')

@section('title', 'Access Denied - Gayatri Enterprises')

@section('content')
<section class="min-h-[80vh] flex items-center justify-center bg-slate-50 relative overflow-hidden">
    <!-- Background Patterns -->
    <div class="absolute inset-0 opacity-30 pointer-events-none" 
         style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 24px 24px;">
    </div>
    <div class="absolute top-20 right-20 w-72 h-72 bg-blue-400 rounded-full opacity-10 blur-3xl"></div>
    <div class="absolute bottom-20 left-20 w-96 h-96 bg-slate-400 rounded-full opacity-10 blur-3xl"></div>

    <div class="text-center relative z-10 px-4">
        <!-- Shield/Lock Icon with Glow -->
        <div class="mb-6 relative inline-block">
            <div class="absolute inset-0 bg-blue-600 blur-2xl opacity-20 transform scale-150"></div>
            <div class="relative bg-white p-6 rounded-3xl shadow-xl border border-slate-100 group transition hover:scale-105">
                <svg class="h-16 w-16 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
        </div>

        <h1 class="text-9xl font-bold text-slate-200 brand-font mb-4 tracking-tighter">403</h1>
        <h2 class="text-3xl md:text-4xl font-bold text-slate-800 brand-font mb-4">Access Denied</h2>
        <p class="text-slate-600 mb-8 max-w-md mx-auto text-lg leading-relaxed font-medium">
            You don't have permission to access this page. If you believe this is an error, please contact your firm administrator.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ url()->previous() }}" class="px-8 py-3.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-500/20 flex items-center gap-2 group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Go Back
            </a>
            <a href="/portal" class="px-8 py-3.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-bold hover:bg-slate-50 hover:text-blue-600 transition shadow-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Return Home
            </a>
        </div>
    </div>
</section>
@endsection
