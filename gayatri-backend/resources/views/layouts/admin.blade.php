<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0F2C4A">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucfirst(Auth::check() ? Auth::user()->role : 'Admin') }} Panel - Gayatri Enterprises</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Pusher & Echo -->
    <script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.0/dist/echo.iife.js"></script>
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: '{{ env("BROADCAST_CONNECTION", "pusher") }}',
            key: '{{ env("PUSHER_APP_KEY") ?: env("REVERB_APP_KEY") }}',
            cluster: '{{ env("PUSHER_APP_CLUSTER", "mt1") }}',
            forceTLS: true,
            wsHost: '{{ env("REVERB_HOST") ?: env("APP_DOMAIN", "localhost") }}',
            wsPort: {{ env("REVERB_PORT") ?: 443 }},
            wssPort: {{ env("REVERB_PORT") ?: 443 }},
            enabledTransports: ['ws', 'wss'],
        });
    </script>

    <style>
        :root {
            --navy: #0F2C4A;
            --navy-light: #173A5E;
            --emerald: #1B7A52;
            --emerald-deep: #145C3F;
            --emerald-light: #EAF6EF;
            --soft-bg: #F7FAF9;
        }

        html, body {
            height: 100%;
            height: 100dvh;
            overflow: hidden;
        }
        body {
            font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif;
            background-color: var(--soft-bg) !important;
            display: flex;
            flex-direction: column;
            color: #1e293b;
        }

        h1, h2, h3, .brand-font {
            font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif;
            font-weight: 600;
        }

        .sidebar-nav { background: var(--navy); }
        .sidebar-active { background: var(--emerald-light) !important; color: var(--emerald-deep) !important; border: 1px solid rgba(27,122,82,0.2) !important; }
        .sidebar-link { color: rgba(255,255,255,0.65); }
        .sidebar-link:hover { background: rgba(255,255,255,0.06); color: #fff; }

        .btn-emerald { background: var(--emerald); color: #fff; transition: background-color .2s ease; }
        .btn-emerald:hover { background: var(--emerald-deep); }
        .btn-navy { background: var(--navy); color: #fff; transition: background-color .2s ease; }
        .btn-navy:hover { background: var(--navy-light); }

        .stat-card { background: #fff; border: 1px solid #e6ebe9; border-radius: 1rem; }

        .text-brand-navy { color: var(--navy); }
        .text-brand-emerald { color: var(--emerald); }
        .bg-brand-emerald-light { background: var(--emerald-light); }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.5s ease-out; }

        /* Hide Scrollbar */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* Force hide WhatsApp button on dashboards */
        .whatsapp-float, .whatsapp-pulse, #whatsapp-float, #whatsapp_float {
            display: none !important;
        }
    </style>
    <!-- PWA -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/png" href="{{ asset('pwa-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('pwa-icon.png') }}">
</head>

<body class="text-slate-800 bg-slate-50" x-data="{ sidebarOpen: false }">

    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

    <!-- Vertical Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 sidebar-nav border-r border-white/10 transition-transform duration-300 lg:translate-x-0 flex flex-col shadow-2xl">
        @php
            $dashRoute = 'admin.dashboard';
            if(Auth::user()->role === 'staff') $dashRoute = 'staff.dashboard';
            if(Auth::user()->role === 'client') $dashRoute = 'client.dashboard';

            $isFullscreen = request()->routeIs([
                'chat.index',
                'mailbox.index',
                'manage.clients',
                'tasks.manage',
                'admin.reviews',
                'activity.logs',
                'admin.messages',
                'documents.manage',
                'admin.team.index',
                'whatsapp.portal'
            ]);
        @endphp
        <div class="h-16 flex items-center px-6 border-b border-white/10 shrink-0">
            <a href="{{ route($dashRoute) }}" class="flex items-center gap-3 group">
                <span class="h-9 w-9 rounded-lg bg-white/10 ring-1 ring-white/15 flex items-center justify-center text-white font-bold text-sm group-hover:bg-white/15 transition">GE</span>
                <div>
                    <h1 class="text-sm font-bold text-white leading-none tracking-wide">
                        Gayatri Enterprises
                    </h1>
                    <span class="text-[10px] font-semibold text-emerald-300/80 uppercase tracking-widest">{{ Auth::user()->role }} Portal</span>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 scrollbar-hide">

            @php
                $userRole = Auth::user()->role;
                $dashboardRoute = 'admin.dashboard';
                if ($userRole === 'staff') { $dashboardRoute = 'staff.dashboard'; }
                elseif ($userRole === 'client') { $dashboardRoute = 'client.dashboard'; }

                $isDashboardActive = request()->routeIs($dashboardRoute);
            @endphp

            <a href="{{ route($dashboardRoute) }}" class="{{ $isDashboardActive ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2.5 rounded-lg font-medium text-sm transition-all flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Overview
            </a>

            @if(Auth::user()->role === 'admin' || Auth::user()->hasPermission('manage_clients'))
            <a href="{{ route('manage.clients') }}" class="{{ request()->routeIs('manage.clients') && !request()->is('admin/dashboard') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2.5 rounded-lg font-medium text-sm transition-all flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                {{ Auth::user()->role === 'admin' ? 'Manage Users' : 'Manage Clients' }}
            </a>
            @endif

            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'staff')
            <a href="{{ route('filament.admin.resources.clients.index') }}" class="{{ request()->is('admin/clients*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2.5 rounded-lg font-medium text-sm transition-all flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 2v8m0 0v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Credit &amp; Ledger
            </a>
            @endif

            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'staff')
            <a href="{{ route('filament.admin.pages.reports') }}" class="{{ request()->is('admin/reports*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2.5 rounded-lg font-medium text-sm transition-all flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2v0"/></svg>
                Reports
            </a>
            @endif

            @if(Auth::user()->hasPermission('view_files'))
            <a href="{{ route('documents.manage') }}" class="{{ request()->routeIs('documents.*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2.5 rounded-lg font-medium text-sm transition-all flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Documents
            </a>
            @endif

            @if(Auth::user()->hasPermission('upload_files'))
            <a href="{{ route('file.form') }}" class="{{ request()->routeIs('file.*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2.5 rounded-lg font-medium text-sm transition-all flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Upload File
            </a>
            @endif

            <!-- WhatsApp Standalone Web Portal -->
            <a href="{{ route('whatsapp.portal') }}" class="{{ request()->routeIs('whatsapp.portal') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2.5 rounded-lg font-medium text-sm transition-all flex items-center gap-3 group">
                <svg class="w-5 h-5 opacity-70 group-hover:text-emerald-300 transition" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                WhatsApp
            </a>

            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'staff')
            <div x-data="{ open: {{ request()->is('admin/products*', 'admin/batches*', 'admin/brands*', 'admin/categories*', 'admin/suppliers*', 'admin/purchase-orders*', 'admin/goods-receipts*') ? 'true' : 'false' }} }" class="space-y-1">
                <button type="button" @click="open = !open" class="w-full sidebar-link px-3 py-2.5 rounded-lg font-bold text-xs sm:text-sm transition-all flex items-center justify-between group">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 opacity-70 text-emerald-300 group-hover:scale-110 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span class="font-bold tracking-wider uppercase text-[10px] text-white/40 group-hover:text-white">Inventory</span>
                    </div>
                    <svg class="w-4 h-4 text-white/40 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-show="open" x-transition class="pl-2 space-y-1" style="display: none;">
                    <a href="{{ route('filament.admin.resources.products.index') }}" class="{{ request()->is('admin/products*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Products
                    </a>
                    <a href="{{ route('filament.admin.resources.batches.index') }}" class="{{ request()->is('admin/batches*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12V8H6a2 2 0 01-2-2c0-1.1.9-2 2-2h12v4M4 6v12a2 2 0 002 2h14v-4M18 18a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        Batches &amp; Stock
                    </a>
                    <a href="{{ route('filament.admin.resources.goods-receipts.index') }}" class="{{ request()->is('admin/goods-receipts*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Goods Receipts
                    </a>
                    <a href="{{ route('filament.admin.resources.purchase-orders.index') }}" class="{{ request()->is('admin/purchase-orders*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Purchase Orders
                    </a>
                    <a href="{{ route('filament.admin.resources.suppliers.index') }}" class="{{ request()->is('admin/suppliers*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4 8 4v14M9 9v.01M9 12v.01M9 15v.01M15 9v.01M15 12v.01M15 15v.01"/></svg>
                        Suppliers
                    </a>
                    <a href="{{ route('filament.admin.resources.brands.index') }}" class="{{ request()->is('admin/brands*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Brands
                    </a>
                    <a href="{{ route('filament.admin.resources.categories.index') }}" class="{{ request()->is('admin/categories*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Categories
                    </a>
                </div>
            </div>

            <div x-data="{ open: {{ request()->routeIs(['admin.gallery.*', 'admin.team.*', 'tasks.manage', 'chat.index', 'mailbox.*', 'attendance.*']) ? 'true' : 'false' }} }" class="space-y-1">
                <button type="button" @click="open = !open" class="w-full sidebar-link px-3 py-2 rounded-lg font-bold text-xs sm:text-sm transition-all flex items-center justify-between group">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 opacity-70 text-emerald-300 group-hover:scale-110 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        <span class="font-bold tracking-wider uppercase text-[10px] text-white/40 group-hover:text-white">Workspace</span>
                    </div>
                    <svg class="w-4 h-4 text-white/40 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-show="open" x-transition class="pl-2 space-y-1" style="display: none;">
                    @if(Auth::user()->role === 'admin' || Auth::user()->hasPermission('manage_gallery'))
                    <a href="{{ route('admin.gallery.index') }}" class="{{ request()->routeIs('admin.gallery.*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Gallery Manager
                    </a>
                    @endif

                    @if(Auth::user()->role === 'admin' || Auth::user()->hasPermission('manage_team'))
                    <a href="{{ route('admin.team.index') }}" class="{{ request()->routeIs('admin.team.*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Team Manager
                    </a>
                    @endif

                    <a href="{{ route('tasks.manage') }}" class="{{ request()->routeIs('tasks.manage') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3 relative">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        Task Center
                    </a>

                    <a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.index') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3 relative">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                        Team Chat
                        <span id="team-chat-badge" class="hidden absolute top-1/2 -mt-2 right-2 bg-red-600 text-white min-w-[18px] h-4.5 rounded-full text-[10px] flex items-center justify-center px-1 font-bold shadow-md"></span>
                    </a>

                    <a href="{{ route('mailbox.index') }}" class="{{ request()->routeIs('mailbox.*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        Mailbox
                    </a>

                    <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Attendance
                    </a>

                </div>
            </div>
            @endif

             @if(Auth::user()->role === 'admin')
             <div x-data="{ open: {{ request()->routeIs(['tasks.assign', 'admin.reviews', 'admin.notices', 'activity.logs', 'admin.messages', 'admin.settings.advanced']) ? 'true' : 'false' }} }" class="space-y-1">
                 <button type="button" @click="open = !open" class="w-full sidebar-link px-3 py-2.5 rounded-lg font-bold text-xs sm:text-sm transition-all flex items-center justify-between group">
                     <div class="flex items-center gap-2.5">
                         <svg class="w-4 h-4 opacity-70 text-emerald-300 group-hover:scale-110 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                         <span class="font-bold tracking-wider uppercase text-[10px] text-white/40 group-hover:text-white">Administration</span>
                     </div>
                     <svg class="w-4 h-4 text-white/40 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                 </button>
                 <div x-show="open" x-transition class="pl-2 space-y-1" style="display: none;">
                     <a href="{{ route('tasks.assign') }}" class="{{ request()->routeIs('tasks.assign') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                         <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                         Assign Task
                     </a>

                     <a href="{{ route('admin.reviews') }}" class="{{ request()->routeIs('admin.reviews') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                         <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                         Client Reviews
                     </a>

                     <a href="{{ route('admin.notices') }}" class="{{ request()->routeIs('admin.notices') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                         <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                         Broadcasts
                     </a>

                     <a href="{{ route('activity.logs') }}" class="{{ request()->routeIs('activity.logs') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                         <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                         Activity Logs
                     </a>

                     <a href="{{ route('admin.messages') }}" class="{{ request()->routeIs('admin.messages') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                         <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                         Inquiries
                     </a>

                     <a href="{{ route('admin.settings.advanced') }}" class="{{ request()->routeIs('admin.settings.advanced') ? 'sidebar-active' : 'sidebar-link' }} px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                         <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                         </svg>
                         Advanced Settings
                     </a>
                 </div>
             </div>
             @endif
        </nav>

        <!-- PWA Banner in Sidebar -->
        <div id="pwa-install-banner" class="hidden mx-4 my-4 relative group">
            <div class="relative bg-emerald-700 p-5 rounded-2xl text-white shadow-lg flex flex-col justify-between border border-white/10 overflow-hidden text-center">
                <button id="pwa-install-close" title="Dismiss" class="absolute top-2 right-2 text-emerald-100 hover:text-white transition p-1 z-10 rounded hover:bg-white/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h4 class="text-white font-extrabold mb-1.5 text-xs sm:text-sm tracking-wide flex items-center justify-center gap-1.5 uppercase tracking-widest">
                    <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Get the App
                </h4>
                <p class="text-[11px] text-emerald-100 mb-4 font-medium opacity-90 leading-relaxed max-w-[180px] mx-auto text-center">Install on your device for offline-ready quick access.</p>
                <button id="pwa-install-btn" class="w-full bg-white hover:bg-slate-50 text-emerald-700 active:scale-95 px-3 py-2 rounded-lg text-xs sm:text-sm font-extrabold transition-all duration-300 shadow-md">
                    Install Now
                </button>
            </div>
        </div>

        <!-- Sticky view website block -->
        <div class="px-4 py-3 shrink-0 border-t border-white/10 space-y-2">
            <a href="{{ route('home') }}" target="_blank" class="sidebar-link px-3 py-2 rounded-lg font-medium text-xs sm:text-sm transition-all flex items-center gap-3">
                <svg class="w-4 h-4 opacity-70 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                View Website
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="lg:ml-64 flex-1 flex flex-col {{ $isFullscreen ? 'h-[100dvh] overflow-hidden' : 'min-h-screen' }} transition-all">

        <!-- Top Header -->
        <header class="bg-white h-16 shrink-0 sticky top-0 z-40 px-4 sm:px-6 lg:px-8 {{ request()->routeIs('chat.index') || request()->routeIs('mailbox.index') || request()->routeIs('whatsapp.portal') ? 'hidden lg:flex' : 'flex' }} items-center justify-between border-b border-slate-200">
            <!-- Mobile Menu Toggle -->
            <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-slate-500 hover:bg-slate-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <div class="flex-1"></div>

            <div class="flex items-center gap-4">
                <a href="{{ route('admin.profile') }}" class="flex items-center gap-3 pl-4 hover:opacity-80 transition group">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-900 group-hover:text-brand-emerald">{{ Auth::user()->name }}</p>
                        <span class="text-xs uppercase font-bold bg-brand-emerald-light text-brand-emerald px-2 py-0.5 rounded-full">{{ Auth::user()->role }}</span>
                    </div>
                    <img src="{{ Auth::user()->avatar_url }}" class="w-9 h-9 rounded-lg object-cover shadow-sm border border-slate-200">
                </a>

                <div class="h-6 w-px bg-slate-200"></div>

                <!-- PIP Button (Admin & Staff Only) -->
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'staff')
                <button @click="window.roleHubInstance?.requestPiP()" class="p-2 text-brand-emerald hover:bg-brand-emerald-light rounded-lg transition" title="Open PiP Window">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 12l-1 1m11 1l-1-1m-4 5v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2h2m3 9V9a2 2 0 012-2h5a2 2 0 012 2v7a2 2 0 01-2 2h-5a2 2 0 01-2-2z"></path></svg>
                </button>
                @endif

                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="p-2 text-red-500 hover:bg-red-50 hover:text-red-600 rounded-lg transition" title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </header>


        <main class="{{ $isFullscreen ? 'flex-1 min-h-0 flex flex-col p-0' : 'flex-1 flex flex-col py-6 px-4 sm:px-6 lg:px-8 overflow-y-auto' }}">
        <div class="{{ $isFullscreen ? 'max-w-full h-full flex flex-col min-h-0 overflow-hidden ' . (request()->routeIs('mailbox.index') || request()->routeIs('whatsapp.portal') ? 'p-0' : 'p-6') : 'max-w-7xl mx-auto w-full' }}">
            {{-- Flash Messages --}}
            @if(session('success') || session('error'))
            <div class="mb-6 animate-fade-in global-flash-alert transition-all duration-500 ease-in-out origin-top overflow-hidden" style="max-height: 200px;">
                @if(session('success'))
                    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-sm flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 text-brand-emerald">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            </div>
                            <div class="ml-3 font-bold text-emerald-800 text-sm">{{ session('success') }}</div>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl shadow-sm flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 text-red-500">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                            </div>
                            <div class="ml-3 font-bold text-red-800 text-sm">{{ session('error') }}</div>
                        </div>
                    </div>
                @endif
            </div>
            @endif

            <div class="{{ $isFullscreen ? 'flex-1 min-h-0 flex flex-col overflow-hidden' : 'bg-white rounded-2xl p-6 shadow-sm border border-slate-200' }}">
                @yield('content')
            </div>
        </div>
    </main>

    </div> <!-- Close Main Layout Wrapper -->

    @if(in_array(strtolower(auth()->user()->role), ['admin', 'staff']))
        <x-floating-hub />

        <!-- PiP Team Chat Floating Window -->
        <div id="pip-chat-container" class="hidden fixed bottom-24 right-6 w-80 h-96 bg-white/95 backdrop-blur-md border border-slate-200 rounded-2xl shadow-2xl z-50 flex-col overflow-hidden transition-all duration-300" style="display: none;">
            <div class="h-12 bg-brand-navy border-b border-white/10 px-4 flex items-center justify-between text-white flex-shrink-0 cursor-pointer" style="background: var(--navy);" onclick="togglePipChat()">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                    <span class="font-bold text-sm tracking-wide">Team Quick Chat</span>
                </div>
                <button class="text-white/60 hover:text-white transition" onclick="event.stopPropagation(); closePipChat()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div id="pip-chat-messages" class="flex-1 overflow-y-auto p-3 space-y-2 bg-slate-50/50 text-sm">
                <div class="text-xs text-center text-slate-400 my-2">Select a conversation in the <a href="{{ route('chat.index') }}" class="text-brand-emerald underline">Team Chat</a> to start.</div>
            </div>
            <div class="p-2 border-t border-slate-200 bg-white shrink-0">
                <div class="flex gap-2">
                    <input type="text" id="pip-new-message" placeholder="Type a reply..." class="w-full bg-slate-100 border-none rounded-lg text-sm px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <button id="pip-send-btn" class="btn-emerald rounded-lg px-3 py-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- In-app Chat Toast Notification -->
        <div id="chat-toast-container" class="fixed bottom-6 right-6 z-[60] flex flex-col gap-2 max-w-xs transition-all pointer-events-none"></div>

        <script>
            // PiP Logic
            function openPipChat(conversationId, name) {
                const container = document.getElementById('pip-chat-container');
                container.style.display = 'flex';
                container.classList.remove('hidden');
                document.querySelector('#pip-chat-container .font-bold.text-sm').innerText = name;
                container.dataset.convoId = conversationId;
                fetchPipMessages(conversationId);
            }
            function closePipChat() {
                const container = document.getElementById('pip-chat-container');
                container.style.display = 'none';
                container.classList.add('hidden');
            }
            function togglePipChat() {
                const messages = document.getElementById('pip-chat-messages');
                const inputArea = document.querySelector('#pip-chat-container .border-t');
                if(messages.style.display === 'none') {
                    messages.style.display = 'block';
                    inputArea.style.display = 'block';
                    document.getElementById('pip-chat-container').style.height = '384px';
                } else {
                    messages.style.display = 'none';
                    inputArea.style.display = 'none';
                    document.getElementById('pip-chat-container').style.height = '48px';
                }
            }

            // Simple Web Notifications
            function showWebNotification(title, body) {
                if (!("Notification" in window)) return;
                if (Notification.permission === "granted") {
                    new Notification(title, { body: body, icon: "{{ asset('pwa-icon.png') }}" });
                } else if (Notification.permission !== "denied") {
                    Notification.requestPermission().then(permission => {
                        if (permission === "granted") new Notification(title, { body: body, icon: "{{ asset('pwa-icon.png') }}" });
                    });
                }
            }

            if("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
                Notification.requestPermission();
            }
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.global-flash-alert');
            alerts.forEach(alert => {
                const text = alert.textContent ? alert.textContent.trim() : "";
                if (!text) return;

                const delay = Math.min(10000, Math.max(3500, text.length * 60));

                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'scaleY(0.95) translateY(-10px)';
                    alert.style.maxHeight = '0px';
                    alert.style.paddingTop = '0px';
                    alert.style.paddingBottom = '0px';
                    alert.style.marginTop = '0px';
                    alert.style.marginBottom = '0px';

                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, delay);
            });

            const badge = document.getElementById('team-chat-badge');

            function updateBadge() {
                fetch('{{ route("chat.unread") }}')
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.count > 0) {
                            badge.innerText = data.count > 99 ? '99+' : data.count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    });
            }

            @auth
            updateBadge();

            if (window.Echo) {
                window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
                    .notification((notification) => {
                        updateBadge();
                    });
            }
            @endauth
        });

        // Service Worker Registration for PWA & Push
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('SW registered');
                }).catch(err => {
                    console.log('SW registration failed: ', err);
                });
            });
        }
    </script>
</body>
<script>
    // Global Notification Poller (Admin)
    function checkNotifications() {
        fetch('{{ route('notifications.unread') }}')
            .then(res => res.json())
            .then(data => {
                const count = data.unread_count;
                const badge = document.getElementById('admin-chat-badge');
                if(badge) {
                    if(count > 0) {
                        badge.classList.remove('hidden');
                        badge.innerText = count > 9 ? '9+' : count;
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            })
            .catch(err => console.error('Notification Error:', err));

        fetch('/api/chat/unread')
            .then(res => res.json())
            .then(data => {
                const count = data.count;
                const badge = document.getElementById('team-chat-badge');
                if(badge) {
                    if(count > 0) {
                        badge.classList.remove('hidden');
                        badge.innerText = count > 9 ? '9+' : count;

                        const lastCount = localStorage.getItem('last_chat_count') || 0;
                        if(count > lastCount) {
                            showChatToast('New Message', 'You have unread team messages');
                        }
                        localStorage.setItem('last_chat_count', count);
                    } else {
                        badge.classList.add('hidden');
                        localStorage.setItem('last_chat_count', 0);
                    }
                }
            });
    }

    function showChatToast(title, message) {
        const container = document.getElementById('chat-toast-container');
        if(!container) return;

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto bg-slate-900 text-white p-4 rounded-xl shadow-2xl flex items-center gap-3 animate-fade-in border border-slate-700 cursor-pointer hover:bg-slate-800 transition-all';
        toast.innerHTML = `
            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background: var(--emerald, #1B7A52);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold">${title}</p>
                <p class="text-xs text-slate-400">${message}</p>
            </div>
            <button class="text-slate-500 hover:text-white">&times;</button>
        `;

        toast.onclick = () => {
            window.location.href = "{{ route('chat.index') }}";
        };

        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    setInterval(checkNotifications, 5000);
    checkNotifications();

    // PWA Service Worker & Install Logic
    let deferredPrompt;
    const installBanner = document.getElementById('pwa-install-banner');
    const installBtn = document.getElementById('pwa-install-btn');

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').then(registration => {
                console.log('Service Worker registered');
                @auth
                    subscribeUserToPush(registration);
                @endauth
            });
        });
    }

    async function subscribeUserToPush(registration) {
        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') return;

            const vapidKey = '{{ env("VAPID_PUBLIC_KEY") }}';
            if (!vapidKey) {
                console.error('VAPID Public Key missing from .env');
                return;
            }

            const subscribeOptions = {
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidKey)
            };

            const subscription = await registration.pushManager.subscribe(subscribeOptions);

            await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(subscription)
            });
            console.log('Push Subscription successful');
        } catch (error) {
            console.error('Push Subscription failed:', error);
        }
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;

        if (!localStorage.getItem('pwa_banner_dismissed') && installBanner) {
            installBanner.classList.remove('hidden');
        }

        const installCloseBtn = document.getElementById('pwa-install-close');
        if (installCloseBtn) {
            installCloseBtn.addEventListener('click', (ev) => {
                ev.preventDefault();
                installBanner.classList.add('hidden');
                localStorage.setItem('pwa_banner_dismissed', '1');
            });
        }

        if(installBtn) {
            installBtn.addEventListener('click', () => {
                installBanner.classList.add('hidden');
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the A2HS prompt');
                    } else {
                        console.log('User dismissed the A2HS prompt');
                    }
                    deferredPrompt = null;
                });
            });
        }
    });

    window.addEventListener('appinstalled', () => {
        if(installBanner) installBanner.classList.add('hidden');
        console.log('PWA was installed');
    });

    // Audio Unlock Logic
    window.audioUnlocked = false;
    function unlockAudio() {
        if (window.audioUnlocked) return;
        const silentAudio = new Audio();
        silentAudio.play().then(() => {
            window.audioUnlocked = true;
            console.log('Audio system unlocked by user gesture.');
            window.removeEventListener('mousedown', unlockAudio);
            window.removeEventListener('touchstart', unlockAudio);
        }).catch(() => {});
    }
    window.addEventListener('mousedown', unlockAudio);
    window.addEventListener('touchstart', unlockAudio);

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
</html>
