@extends('layouts.admin')

@section('content')
<div class="h-[calc(100vh-140px)] w-full flex items-center justify-center p-6">
    <div class="max-w-2xl w-full bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden relative">
        
        <!-- Decorative Background -->
        <div class="absolute inset-0 z-0 pointer-events-none opacity-20" style="background: radial-gradient(circle at 100% 0%, #25d366 0%, transparent 40%), radial-gradient(circle at 0% 100%, #128c7e 0%, transparent 40%);"></div>

        <div class="relative z-10 p-10 md:p-16 flex flex-col items-center text-center">
            
            <!-- Icon -->
            <div class="w-32 h-32 bg-gradient-to-tr from-[#128c7e] to-[#25d366] rounded-full flex items-center justify-center shadow-lg shadow-green-500/30 mb-8 animate-bounce-slow">
                <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </div>

            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800 mb-4 tracking-tight">WhatsApp Web Secured</h1>
            
            <p class="text-slate-500 text-lg max-w-lg mb-10 leading-relaxed">
                For security reasons, WhatsApp protects its interface from being embedded. Click below to securely open WhatsApp Web in a new tab.
            </p>

            <a href="https://web.whatsapp.com" target="_blank" 
               class="group relative inline-flex items-center justify-center px-8 py-4 font-bold text-white transition-all duration-200 bg-[#25d366] font-pj rounded-xl hover:bg-[#128c7e] hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#25d366] shadow-[0_8px_30px_rgb(37,211,102,0.3)]">
                <span class="mr-3">Open WhatsApp Web</span>
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>

            <div class="mt-8 flex items-center gap-2 text-sm text-slate-400 font-medium">
                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                End-to-end Encrypted Connection
            </div>
            
        </div>
    </div>
</div>

<style>
    @keyframes bounce-slow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .animate-bounce-slow {
        animation: bounce-slow 3s ease-in-out infinite;
    }
</style>
@endsection
