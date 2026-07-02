@extends('layouts.admin')

@section('content')
<div class="w-full h-full flex flex-col overflow-hidden bg-white relative" x-data="{ loading: true }" id="mailbox-container">
    
    {{-- Elegant skeleton loader while the iframe loads --}}
    <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-[#f8fafc] z-50 flex flex-col items-center justify-center">
        <div class="relative flex items-center justify-center">
            {{-- Spinning glowing gradient border --}}
            <div class="w-20 h-20 rounded-full border-4 border-slate-200 border-t-blue-600 animate-spin"></div>
            <div class="absolute w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6 text-blue-600 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
        <h3 class="text-slate-800 font-extrabold tracking-tight mt-6 text-base brand-font">Gayatri Enterprises</h3>
        <p class="text-xs text-slate-400 font-semibold tracking-wider uppercase mt-1">Connecting to your secure mail workspace</p>
    </div>

    {{-- Interactive IFrame (Stretches perfectly edge-to-edge, invisible during boot and fades in beautifully) --}}
    <iframe 
        id="mailbox-iframe"
        src="/webmail/index.php" 
        class="w-full h-full border-0 flex-1 transition-all duration-500 ease-out" 
        :style="loading ? 'opacity: 0; transform: scale(0.995); pointer-events: none;' : 'opacity: 1; transform: scale(1); pointer-events: auto;'"
        allow="clipboard-read; clipboard-write; geolocation; microphone; camera">
    </iframe>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const iframe = document.getElementById('mailbox-iframe');
        const container = document.getElementById('mailbox-container');
        if (!iframe || !container) return;

        // Function to resolve loading state once SnappyMail's DOM loader is physically gone
        const checkSnappyMailLoaded = () => {
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                if (iframeDoc) {
                    const loader = iframeDoc.getElementById('rl-loading');
                    const content = iframeDoc.getElementById('rl-content');
                    
                    // If loader is deleted, hidden, or content is displayed, SnappyMail is fully loaded
                    if (!loader || loader.style.display === 'none' || loader.hasAttribute('hidden') || (content && !content.hasAttribute('hidden'))) {
                        // Access Alpine data and set loading to false
                        const alpineData = Alpine.$data(container);
                        if (alpineData) {
                            alpineData.loading = false;
                        }
                        clearInterval(pollInterval);
                    }
                }
            } catch (e) {
                // Fallback for cross-origin or security exceptions (though same-origin is active here)
            }
        };

        // Poll every 50ms to monitor SnappyMail's internal loader state
        const pollInterval = setInterval(checkSnappyMailLoaded, 50);

        // Fallback safety: force hide loader after 5 seconds if polling fails or is slow
        setTimeout(() => {
            const alpineData = Alpine.$data(container);
            if (alpineData && alpineData.loading) {
                alpineData.loading = false;
            }
            clearInterval(pollInterval);
        }, 5000);
    });
</script>
@endsection


