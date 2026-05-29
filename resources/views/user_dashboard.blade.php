<x-layouts.app title="User Portal | {{ $branding['app_full_name'] }}" loaderText="{{ $branding['loader_text'] }}">
    <div class="flex h-screen overflow-hidden flex-1"
         x-data="{
            currentTab: localStorage.getItem('userActiveTab') || 'main',
            sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
            mobileOpen: false,
            switchTab(tabName) {
                this.currentTab = tabName;
                localStorage.setItem('userActiveTab', tabName);
                this.mobileOpen = false;
                this.$nextTick(() => {
                    const main = document.querySelector('main');
                    if (main) main.scrollTop = 0;
                });
            },
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
                localStorage.setItem('sidebarOpen', this.sidebarOpen);
            },
            recentLogs: [],
            recentLoading: true,
            fetchRecent() {
                this.recentLoading = true;
                fetch('/admin/recent-activity')
                    .then(res => res.json())
                    .then(data => { this.recentLogs = data; this.recentLoading = false; })
                    .catch(() => { this.recentLoading = false; });
            },
            appColor(app)      { return window.logstackHelpers.appColor(app); },
            actionColor(action) { return window.logstackHelpers.actionColor(action); },
            actionIcon(action)  { return window.logstackHelpers.actionIcon(action); },
            formatAction(action){ return window.logstackHelpers.formatAction(action); }
         }"
         x-init="fetchRecent()">

        {{-- MOBILE OVERLAY --}}
        <div x-show="mobileOpen"
             x-transition.opacity
             @click="mobileOpen = false"
             class="fixed inset-0 bg-black/50 z-30 md:hidden"
             x-cloak></div>

        {{-- SIDEBAR --}}
        <aside x-bind:style="{ width: sidebarOpen ? '16rem' : '4rem' }"
               x-bind:class="mobileOpen ? 'sidebar-mobile-show' : 'sidebar-mobile-hidden'"
               class="sidebar-desktop fixed inset-y-0 left-0 z-40 h-screen bg-slate-50 dark:bg-[#111827] border-r border-slate-100 dark:border-slate-800 flex flex-col justify-between flex-shrink-0 transition-all duration-300 overflow-y-auto overflow-x-visible">

            <div :class="sidebarOpen ? 'p-5' : 'p-3'" class="transition-all duration-300">

                {{-- Logo --}}
                <div class="flex justify-center mb-10">
                    <a href="/" class="flex items-center gap-3 group min-w-0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white shadow-lg flex-shrink-0"
                             style="background-color: {{ $branding['primary_color'] }}">
                            <i class="fas {{ $branding['app_logo_icon'] }} text-sm"></i>
                        </div>
                        <span x-show="sidebarOpen" x-transition.opacity class="text-lg font-bold tracking-tighter uppercase italic text-slate-900 dark:text-white whitespace-nowrap">{{ $branding['app_name'] }}<span style="color: {{ $branding['primary_color'] }}">{{ substr($branding['app_full_name'], strlen($branding['app_name'])) }}</span></span>
                    </a>
                </div>

                {{-- Main Menu --}}
                <div class="space-y-1 pt-4">
                    <div class="flex items-center justify-between px-3 mb-4">
                        <p x-show="sidebarOpen" x-transition.opacity class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Main Menu</p>
                        <button @click="toggleSidebar()" :class="sidebarOpen ? '' : 'mx-auto'" class="w-6 h-6 flex items-center justify-center rounded-md text-slate-400 dark:text-gray-500 hover:bg-slate-200 dark:hover:bg-gray-700 hover:text-slate-700 dark:hover:text-white transition-all flex-shrink-0">
                            <i class="fas text-[10px]" :class="sidebarOpen ? 'fa-chevron-left' : 'fa-bars'"></i>
                        </button>
                    </div>

                    <div class="relative group/tip">
                        <button @click="switchTab('main')" :class="currentTab === 'main' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors" :class="sidebarOpen ? 'space-x-3' : 'justify-center'">
                            <i class="fas fa-home w-4 flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">Main Dashboard</span>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">Main Dashboard</div>
                    </div>
                    <div class="relative group/tip">
                        <button @click="switchTab('documents')" :class="currentTab === 'documents' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors" :class="sidebarOpen ? 'space-x-3' : 'justify-center'">
                            <i class="fas fa-file-alt w-4 flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">Documents</span>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">Documents</div>
                    </div>
                    <div class="relative group/tip">
                        <button @click="switchTab('nextcloud')" :class="currentTab === 'nextcloud' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors" :class="sidebarOpen ? 'space-x-3' : 'justify-center'">
                            <i class="fas fa-cloud w-4 flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">Nextcloud Storage</span>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">Nextcloud Storage</div>
                    </div>
                    <div class="relative group/tip">
                        <button @click="switchTab('odoo')" :class="currentTab === 'odoo' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors" :class="sidebarOpen ? 'space-x-3' : 'justify-center'">
                            <i class="fas fa-briefcase w-4 flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">Odoo ERP System</span>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">Odoo ERP System</div>
                    </div>
                    <div class="relative group/tip">
                        <button @click="switchTab('sogo')" :class="currentTab === 'sogo' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors" :class="sidebarOpen ? 'space-x-3' : 'justify-center'">
                            <i class="fas fa-envelope w-4 flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">Sogo Mail</span>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">Sogo Mail</div>
                    </div>
                    <div class="relative group/tip">
                        <button @click="switchTab('profile')" :class="currentTab === 'profile' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors" :class="sidebarOpen ? 'space-x-3' : 'justify-center'">
                            <i class="fas fa-user w-4 flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">Profile Akun</span>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">Profile Akun</div>
                    </div>
                </div>
            </div>

            {{-- BOTTOM: User Info --}}
            <div :class="sidebarOpen ? 'p-5' : 'p-3'" class="border-t border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-[#0d131f] transition-all duration-300">
                <div class="flex items-center" :class="sidebarOpen ? 'gap-3' : 'justify-center'">
                    <div class="w-8 h-8 rounded-full bg-emerald-600/10 dark:bg-emerald-600/20 border border-emerald-200 dark:border-emerald-800/40 flex items-center justify-center text-emerald-600 flex-shrink-0">
                        <i class="fas fa-user text-xs"></i>
                    </div>
                    <div x-show="sidebarOpen" x-transition.opacity class="truncate min-w-0">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $user->name ?? Auth::user()->name }}</p>
                        <span class="text-[9px] bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-gray-700/60 px-1.5 py-0.5 rounded font-bold uppercase tracking-wide">User Account</span>
                    </div>
                </div>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col h-screen overflow-hidden min-w-0">
            <header class="h-16 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-[#111827]/60 backdrop-blur-md flex items-center justify-between px-4 sm:px-8 z-20 flex-shrink-0 sticky top-0 transition-colors duration-200">
                <div class="flex items-center gap-3">
                    {{-- Hamburger: mobile only --}}
                    <button @click="mobileOpen = true" class="md:hidden w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <i class="fas fa-bars text-sm"></i>
                    </button>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest"><span>{{ $branding['app_full_name'] }}</span></div>
                </div>
                <div class="flex items-center gap-4">
                    <button @click="isDark = !isDark" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 text-slate-500 dark:text-yellow-400 border border-slate-200 dark:border-slate-700 hover:scale-105 transition-all shadow-sm">
                        <i class="fas fa-sun text-xs" x-show="!isDark" x-cloak></i>
                        <i class="fas fa-moon text-xs" x-show="isDark" x-cloak></i>
                    </button>
                    <a href="{{ route('logout') }}" class="h-9 px-4 flex items-center justify-center bg-red-50 dark:bg-red-950 hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/40 text-xs font-bold rounded-xl transition-all shadow-sm uppercase tracking-wider">
                        <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                    </a>
                </div>
            </header>
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto bg-white dark:bg-[#0b0e14]">
                <div x-show="currentTab === 'main'" x-transition x-cloak class="space-y-6">
                    @include('admin.dashboard.user_main')
                </div>

                <div x-show="currentTab === 'documents'" x-transition x-cloak class="space-y-6">@include('admin.dashboard.documents')</div>
                <div x-show="currentTab === 'nextcloud'" x-transition x-cloak class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div><h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Nextcloud Storage Drive</h1><p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Manajemen file dan sinkronisasi storage cloud.</p></div>
                        <a href="{{ route('open.nextcloud') }}" target="_blank" class="bg-blue-50 dark:bg-blue-950 hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-600 dark:text-white border border-blue-200 dark:border-blue-800 font-bold text-xs px-4 py-2 rounded-xl transition-colors flex items-center gap-2 self-start sm:self-auto"><i class="fas fa-external-link-alt"></i> Launch Portal</a>
                    </div>
                    @include('admin.nextcloud.panel')
                </div>
                <div x-show="currentTab === 'odoo'" x-transition x-cloak class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div><h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Odoo ERP System</h1><p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Sistem perencanaan sumber daya perusahaan terintegrasi.</p></div>
                        <a href="{{ route('open.odoo') }}" target="_blank" class="bg-blue-50 dark:bg-blue-950 hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-600 dark:text-white border border-blue-200 dark:border-blue-800 font-bold text-xs px-4 py-2 rounded-xl transition-colors flex items-center gap-2 self-start sm:self-auto"><i class="fas fa-external-link-alt"></i> Launch Portal</a>
                    </div>
                    @include('admin.dashboard.odoo')
                </div>
                <div x-show="currentTab === 'sogo'" x-transition x-cloak class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div><h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">SOGo Webmail</h1><p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Layanan email korporat, kalender, dan buku alamat tersinkronisasi.</p></div>
                        <a href="{{ route('open.sogo') }}" target="_blank" class="bg-orange-50 dark:bg-orange-950 hover:bg-orange-100 dark:hover:bg-orange-900 text-orange-600 dark:text-white border border-orange-200 dark:border-orange-900/40 font-bold text-xs px-4 py-2 rounded-xl transition-colors flex items-center gap-2 self-start sm:self-auto"><i class="fas fa-external-link-alt"></i> Launch Portal</a>
                    </div>
                    @include('admin.dashboard.sogo')
                </div>
                <div x-show="currentTab === 'profile'" x-transition x-cloak class="space-y-6">@include('admin.dashboard.profile')</div>
            </main>
            {{-- FOOTER --}}
            <footer class="flex-shrink-0 border-t border-slate-100 dark:border-slate-800 bg-white/80 dark:bg-[#111827]/80 px-4 sm:px-8 py-3 flex items-center justify-between gap-4">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-600 uppercase tracking-widest">&copy; 2026 {{ strtoupper($branding['footer_text']) }}</p>
                <p class="text-[10px] text-slate-300 dark:text-gray-700 font-mono hidden sm:block">{{ $branding['app_version'] }}</p>
            </footer>
        </div>
    </div>

    <script>
        function updateClock() {
            const clock = document.getElementById('user-clock-time');
            if (clock) { const now = new Date(); clock.textContent = now.toLocaleTimeString('en-US', { hour12: false }); }
        }
        setInterval(updateClock, 1000); updateClock();
    </script>
</x-layouts.app>
