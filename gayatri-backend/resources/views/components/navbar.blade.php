<nav class="bg-white shadow-md fixed w-full top-0 z-50 transition-all duration-300" style="border-bottom: 3px solid #1B7A52;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center gap-4 lg:gap-8">

            <a href="{{ route('home') }}" class="flex items-center gap-2 lg:gap-3 flex-shrink-0">
                <span class="h-9 w-9 rounded-lg flex items-center justify-center text-white font-bold text-sm" style="background:#0F2C4A;">GE</span>
                <span class="font-bold text-base md:text-lg lg:text-xl text-slate-900 leading-tight whitespace-nowrap">Gayatri Enterprises</span>
            </a>

            <div class="hidden md:flex space-x-4 lg:space-x-6 items-center h-full">
                @auth
                    <div class="flex items-center gap-3">
                        @php
                            $isOnDashboard = request()->is('admin/*') || request()->is('staff/*') || request()->routeIs('client.dashboard') || request()->routeIs('manage.clients') || request()->routeIs('admin.profile') || request()->routeIs('documents.manage');
                        @endphp

                        @if(!$isOnDashboard)
                            @php
                                $dashboardRoute = route('portal.login');
                                if(Auth::user()->role === 'client') {
                                    $dashboardRoute = route('client.dashboard');
                                } elseif(Auth::user()->role === 'staff') {
                                    $dashboardRoute = route('staff.dashboard');
                                } elseif(Auth::user()->role === 'admin') {
                                    $dashboardRoute = route('admin.dashboard');
                                }
                            @endphp

                            <a href="{{ $dashboardRoute }}"
                               class="text-white px-4 py-2 rounded shadow transition flex items-center gap-2 text-sm font-bold transform hover:scale-105 relative" style="background:#1B7A52;" onmouseover="this.style.background='#145C3F'" onmouseout="this.style.background='#1B7A52'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                Dashboard
                                <span class="chat-badge hidden absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full border border-white"></span>
                            </a>

                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                               class="text-slate-400 hover:text-red-600 p-2 rounded transition" title="Logout">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </a>
                        @else
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                               class="bg-red-600 text-white px-4 py-2 rounded shadow hover:bg-red-700 transition flex items-center gap-2 text-sm font-bold transform hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Logout
                            </a>
                        @endif
                    </div>
                @else
                    <a href="{{ route('portal.login') }}" class="text-white px-5 py-2 rounded-lg shadow transition flex items-center gap-2 text-sm font-semibold" style="background:#1B7A52;" onmouseover="this.style.background='#145C3F'" onmouseout="this.style.background='#1B7A52'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Login
                    </a>
                @endauth
            </div>

            <div class="md:hidden">
                <button onclick="toggleMobileMenu()" class="text-slate-800 focus:outline-none p-2">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100 shadow-lg absolute w-full">
        @auth
            <div class="px-6 py-3 bg-slate-50 border-b border-gray-100">
                <p class="text-xs text-slate-500">Logged in as:</p>
                <p class="font-bold text-slate-900">{{ Auth::user()->name }}</p>
            </div>

            @php
                $isOnDashboard = request()->is('admin/*') || request()->is('staff/*') || request()->routeIs('client.dashboard') || request()->routeIs('manage.clients');
            @endphp

            @if(!$isOnDashboard)
                @php
                    $dashboardRoute = route('portal.login');
                    if(Auth::user()->role === 'client') $dashboardRoute = route('client.dashboard');
                    elseif(Auth::user()->role === 'staff') $dashboardRoute = route('staff.dashboard');
                    elseif(Auth::user()->role === 'admin') $dashboardRoute = route('admin.dashboard');
                @endphp

                <a href="{{ $dashboardRoute }}"
                   class="block w-full text-left px-6 py-3 font-bold hover:bg-slate-50 border-b border-gray-100" style="color:#1B7A52;">
                   Go to Dashboard
                </a>
            @endif

            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="block w-full text-left px-6 py-3 text-red-600 font-bold hover:bg-red-50 border-b border-gray-100">
               Logout
            </a>
        @else
            <a href="{{ route('portal.login') }}" class="block w-full text-left px-6 py-3 font-bold hover:bg-slate-50 border-b border-gray-100" style="color:#1B7A52;">Login</a>
        @endauth
    </div>
</nav>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}

@auth
    setInterval(function() {
        fetch('{{ route('notifications.unread') }}')
            .then(res => res.json())
            .then(data => {
                const count = data.unread_count;
                const badges = document.querySelectorAll('.chat-badge');

                badges.forEach(badge => {
                    if(count > 0) {
                        badge.classList.remove('hidden');
                        badge.innerText = count > 9 ? '9+' : count;
                        badge.parentElement.classList.add('animate-pulse');
                    } else {
                        badge.classList.add('hidden');
                        badge.parentElement.classList.remove('animate-pulse');
                    }
                });
            })
            .catch(console.error);
    }, 5000);
@endauth
</script>
