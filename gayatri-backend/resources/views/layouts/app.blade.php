<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gayatri Enterprises')</title>
    <meta name="description" content="Gayatri Enterprises — B2B chemical and laboratory reagent distributor.">
    <meta name="keywords" content="chemical distributor, laboratory reagents, B2B chemical supply, Gayatri Enterprises">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Gayatri Enterprises">
    <meta property="og:description" content="B2B chemical and laboratory reagent distributor.">
    <meta property="og:image" content="{{ asset('assets/img/og-image.jpg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="Gayatri Enterprises">
    <meta property="twitter:description" content="B2B chemical and laboratory reagent distributor.">
    <meta property="twitter:image" content="{{ asset('assets/img/og-image.jpg') }}">

    {{-- Global Schema Stack --}}
    @stack('schema')

    <link rel="icon" type="image/png" href="{{ asset('pwa-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('pwa-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1B7A52">
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XYBDV19TF4"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-XYBDV19TF4');
    </script>

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-PMXN25LC');</script>
    <!-- End Google Tag Manager -->

    {{-- Meta Pixel Setup (Deferred by user) --}}
    {{-- 
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', 'REPLACE_WITH_PIXEL_ID');
    fbq('track', 'PageView');
    </script>
    --}}

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6, .brand-font { font-family: 'Playfair Display', serif; }

        @keyframes gradient-move {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .btn-moving-gradient {
            background: linear-gradient(-45deg, #2563eb, #3b82f6, #1d4ed8, #4f46e5);
            background-size: 300% 300%;
            animation: gradient-move 8s ease infinite;
            transition: all 0.3s ease;
        }
        .btn-moving-gradient:hover {
            filter: brightness(1.1);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-moving-gradient-slate {
            background: linear-gradient(-45deg, #334155, #475569, #1e293b, #475569);
            background-size: 300% 300%;
            animation: gradient-move 8s ease infinite;
            transition: all 0.3s ease;
        }
        .btn-moving-gradient-slate:hover {
            filter: brightness(1.1);
            box-shadow: 0 4px 12px rgba(71, 85, 105, 0.15);
        }

        .moving-text-gradient {
            background: linear-gradient(-45deg, #1e40af, #3b82f6, #4f46e5, #0891b2, #1e40af);
            background-size: 400% 400%;
            animation: gradient-move 8s ease infinite;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        .table-header-gradient {
            background: linear-gradient(90deg, #f8fafc, #f1f5f9, #eff6ff, #f8fafc) !important;
            background-size: 300% 300%;
            animation: gradient-move 10s ease infinite;
        }
        .table-glow-border {
            position: relative;
        }
        .table-glow-border::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(-45deg, #3b82f6, #6366f1, #2563eb, #4f46e5);
            background-size: 300% 300%;
            animation: gradient-move 6s ease infinite;
            z-index: 10;
        }
        
        .animate-pulse-subtle {
            animation: pulse-subtle 3s infinite;
        }
        @keyframes pulse-subtle {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
            70% { transform: scale(1.03); box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }
        
        /* WhatsApp Floating Button Styles */
        .whatsapp-float { 
            position: fixed; 
            bottom: 30px; 
            right: 30px; 
            z-index: 999; 
            transition: all 0.5s ease;
        }
        .whatsapp-btn {
            background-color: #25d366;
            color: white;
            border-radius: 50px;
            text-align: center;
            font-size: 30px;
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            transition: all 0.3s ease;
        }
        .whatsapp-btn:hover {
            background-color: #20ba5a;
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 30px rgba(37, 211, 102, 0.6);
        }
        .whatsapp-pulse {
            position: absolute;
            width: 60px;
            height: 60px;
            background: #25d366;
            border-radius: 50%;
            z-index: -1;
            animation: whatsapp-pulse 3s infinite;
            opacity: 0.2;
        }
        @keyframes whatsapp-pulse {
            0% { transform: scale(1); opacity: 0.2; }
            100% { transform: scale(1.4); opacity: 0; }
        }
        @media (max-width: 768px) {
            .whatsapp-float { bottom: calc(20px + env(safe-area-inset-bottom)); right: 20px; }
            .whatsapp-btn { width: 50px; height: 50px; font-size: 24px; }
            .whatsapp-pulse { width: 50px; height: 50px; }
            
            /* Mobile Input Zoom Fix */
            input, select, textarea { font-size: 16px !important; }
        }

        /* iOS Safari 100vh Fix */
        .min-h-screen {
            min-height: 100vh;
            min-height: -webkit-fill-available;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-white text-slate-800 antialiased">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PMXN25LC"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- Header / Navbar -->
    @include('components.navbar')

    <!-- Main Content -->
    <main class="flex-grow pt-20">
        {{-- Flash Messages --}}
        @if(session('success') || session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4 animate-fade-in relative z-[100] global-flash-alert transition-all duration-500 ease-in-out origin-top overflow-hidden" style="max-height: 200px;">
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="ml-3 font-bold text-green-800 text-sm">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="ml-3 font-bold text-red-800 text-sm">
                            {{ session('error') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.footer')

    <!-- Chart.js for Tools Page -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Global Auto-Dismiss for Flash Alert Banners based on text length
            const alerts = document.querySelectorAll('.global-flash-alert');
            alerts.forEach(alert => {
                const text = alert.textContent ? alert.textContent.trim() : "";
                if (!text) return;
                
                // Average reading rate: 12-15 characters per second.
                // We allocate 60ms per character with a robust minimum of 3.5 seconds and maximum of 10 seconds.
                const delay = Math.min(10000, Math.max(3500, text.length * 60));
                
                setTimeout(() => {
                    // Smooth slide up & collapse height transition
                    alert.style.opacity = '0';
                    alert.style.transform = 'scaleY(0.95) translateY(-10px)';
                    alert.style.maxHeight = '0px';
                    alert.style.paddingTop = '0px';
                    alert.style.paddingBottom = '0px';
                    alert.style.marginTop = '0px';
                    alert.style.marginBottom = '0px';
                    
                    // Cleanup from DOM completely after transition completes
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, delay);
            });
        });
    </script>

    @stack('scripts')



    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80, // Offset for fixed navbar
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
