@extends('layouts.admin')

@section('content')

<div x-data="{ 
    viewType: localStorage.getItem('repoViewType') || 'list',
    searchQuery: '{{ $search }}',
    sortOrder: '{{ $sort }}',
    isLoading: false,
    fetchResults() {
        this.isLoading = true;
        const url = new URL(window.location.href);
        if (this.searchQuery) {
            url.searchParams.set('search', this.searchQuery);
        } else {
            url.searchParams.delete('search');
        }
        if (this.sortOrder) {
            url.searchParams.set('sort', this.sortOrder);
        } else {
            url.searchParams.delete('sort');
        }
        url.searchParams.delete('page');

        window.history.replaceState({}, '', url.toString());

        fetch(url.toString())
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const target = document.getElementById('search-results-zone');
                const source = doc.getElementById('search-results-zone');
                if (target && source) {
                    target.innerHTML = source.innerHTML;
                }
                this.isLoading = false;
            })
            .catch(err => {
                console.error(err);
                this.isLoading = false;
            });
    }
}" class="h-full flex flex-col overflow-hidden gap-5">
    
    <!-- Non-scrollable Top Section -->
    <div class="shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200/60 pb-5">
        <div>
            <h1 class="text-3xl font-bold brand-font flex items-center gap-3">
                <span class="bg-blue-100 p-2 rounded-xl text-blue-600 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                </span>
                <span class="text-slate-900">Client Document Center</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1.5 font-medium">Browse, manage and organize secure client repository folders.</p>
        </div>

        <div class="flex items-center gap-4 w-full sm:w-auto">
            <!-- Search Form -->
            <form action="" method="GET" class="relative flex-1 sm:flex-initial group" @submit.prevent>
                <input type="text" name="search" x-model="searchQuery" @input.debounce.250ms="fetchResults()" placeholder="Search client folders..." 
                       class="w-full sm:w-80 bg-white text-slate-900 border border-slate-200 rounded-xl pl-11 pr-10 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm transition group-hover:border-blue-300">
                <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3.5 group-hover:text-blue-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                
                <!-- Loader spinner -->
                <div x-show="isLoading" class="absolute right-3.5 top-3.5" x-cloak>
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </form>

            <!-- Sort Selector -->
            <div class="relative group shrink-0">
                <select x-model="sortOrder" @change="fetchResults()" 
                        class="appearance-none bg-white text-slate-700 border border-slate-200 rounded-xl pl-10 pr-10 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm transition font-bold text-sm hover:border-blue-300 cursor-pointer">
                    <option value="latest">Latest</option>
                    <option value="name">Name (A-Z)</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400 group-hover:text-blue-500 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                </div>
                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400 group-hover:text-blue-500 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>

            <!-- Layout Switches -->
            <div class="flex items-center bg-slate-200/50 p-1 rounded-xl border border-slate-200/60 shrink-0 shadow-inner">
                <button @click="viewType = 'list'; localStorage.setItem('repoViewType', 'list')" 
                        :class="viewType === 'list' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                        class="p-2 rounded-lg font-bold text-xs transition flex items-center gap-1.5 px-3.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    List
                </button>
                <button @click="viewType = 'grid'; localStorage.setItem('repoViewType', 'grid')" 
                        :class="viewType === 'grid' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                        class="p-2 rounded-lg font-bold text-xs transition flex items-center gap-1.5 px-3.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Grid
                </button>
            </div>
        </div>
    </div>

    <!-- Live Search Results Zone -->
    <div id="search-results-zone" class="flex-1 min-h-0 flex flex-col overflow-hidden">
        @if(!$users->isEmpty())
            <!-- Main Results Container -->
            <div :class="viewType === 'list' ? 'flex-1 min-h-0 flex flex-col' : 'flex-1 min-h-0 overflow-y-auto pr-1'">
                
                <!-- List/Table View Container -->
                <div x-show="viewType === 'list'" class="flex-1 min-h-0 bg-white rounded-2xl border border-slate-200 shadow flex flex-col overflow-hidden pt-0.5 mb-2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="overflow-x-auto overflow-y-auto flex-1 min-h-0">
                        <table class="w-full text-left border-collapse text-sm text-slate-600">
                            <thead class="bg-slate-50 bg-white text-slate-700 uppercase text-xs tracking-wider border-b border-slate-200 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-6 py-4 font-bold text-center w-16">S.No.</th>
                                    <th class="px-6 py-4 font-bold">Client Name</th>
                                    <th class="px-6 py-4 font-bold">Phone Number</th>
                                    <th class="px-6 py-4 font-bold">Status</th>
                                    <th class="px-6 py-4 font-bold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($users as $user)
                                <tr class="hover:bg-slate-50/80 transition group">
                                    <td class="px-6 py-4 text-center font-mono text-slate-400 font-bold w-16">
                                        {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2.5 bg-blue-50 rounded-xl text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                            </div>
                                            <div>
                                                <a href="{{ url('/client-documents/'.$user->id) }}" class="font-bold text-slate-800 hover:text-blue-600 transition block text-base">{{ $user->name }}</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-sm text-slate-500 flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            +91 {{ $user->phone }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($user->is_active)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 bg-rose-500 rounded-full mr-1.5"></span>
                                                Blocked
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ url('/client-documents/'.$user->id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-white btn-emerald px-4 py-2.5 rounded-xl shadow transition-all hover:scale-105">
                                            Open Repository
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grid View Container -->
                <div x-show="viewType === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    @foreach($users as $user)
                    <a href="{{ url('/client-documents/'.$user->id) }}" 
                       class="group block bg-white p-6 rounded-2xl border border-slate-200 hover:border-blue-500 transition shadow-sm hover:shadow-xl relative overflow-hidden transform hover:-translate-y-1">
                        
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 to-purple-500 opacity-0 group-hover:opacity-100 transition"></div>
                        
                        <div class="absolute top-[-20px] right-[-20px] opacity-[0.03] group-hover:opacity-10 transition transform group-hover:scale-125 rotate-12">
                            <svg class="w-40 h-40 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        </div>

                        <div class="relative z-10 flex items-start gap-4">
                            <div class="p-4 bg-blue-50 rounded-xl text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition shadow-sm group-hover:shadow-emerald-500/30">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            </div>
                            
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-slate-800 group-hover:text-blue-600 transition mb-1 brand-font">{{ $user->name }}</h3>
                                <p class="text-sm text-slate-500 font-mono flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    +91 {{ $user->phone }}
                                </p>
                                
                                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                                     <div class="flex items-center gap-2">
                                         <span class="text-xs font-semibold bg-slate-100 text-slate-500 px-2.5 py-1 rounded-lg border border-slate-200 group-hover:border-blue-200 transition">
                                            Client Folder
                                         </span>
                                         @if($user->is_active)
                                             <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                 Active
                                             </span>
                                         @else
                                             <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                                 Blocked
                                             </span>
                                         @endif
                                     </div>
                                     <span class="flex items-center gap-1 text-xs font-bold text-blue-600 group-hover:underline">
                                        Open <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                     </span>
                                </div>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                
                <!-- Dynamic Pagination Block inside scrolling zone to ensure perfect scrolling context -->
                <div class="mt-6 py-2 shrink-0">
                    {{ $users->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-20 bg-white border-2 border-dashed border-slate-200 rounded-2xl mt-6 pt-0.5">
                <div class="bg-slate-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 brand-font">No Folders Found</h3>
                <p class="text-slate-500 text-sm mt-2">We couldn't find any client repository matching "<span x-text="searchQuery"></span>"</p>
            </div>
        @endif
    </div>

</div>

@endsection