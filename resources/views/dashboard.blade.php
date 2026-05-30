<x-layouts.app title="Admin Portal | {{ $branding['app_full_name'] }}" loaderText="{{ $branding['loader_text'] }} Admin">
    <div class="flex h-screen overflow-hidden flex-1"
         x-data="{
            currentTab: localStorage.getItem('activeTab') || 'main',
            sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
            mobileOpen: false,
            pendingRequests: 0,
            fetchPendingRequests() {
                fetch('/api/access-request/pending-count')
                    .then(r => r.json())
                    .then(d => { this.pendingRequests = d.count; })
                    .catch(() => {});
            },
            showAddModal: false,
            showEditModal: false,
            editUser: { username: '', first_name: '', last_name: '', email: '', group: 'dash_user' },
            editPassword: '',
            editConfirmPassword: '',
            get editPasswordMatch() { return this.editPassword === this.editConfirmPassword; },
            get editPasswordStrong() { return this.editPassword.length >= 8; },
            adminExpand: localStorage.getItem('adminExpand') !== 'false',
            switchTab(tabName) {
                this.currentTab = tabName;
                localStorage.setItem('activeTab', tabName);
                this.mobileOpen = false;
                this.$nextTick(() => {
                    const main = document.querySelector('main');
                    if (main) main.scrollTop = 0;
                });
            },
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
                localStorage.setItem('sidebarOpen', this.sidebarOpen);
                if (!this.sidebarOpen) this.adminExpand = false;
            },
            toggleAdmin() {
                this.adminExpand = !this.adminExpand;
                localStorage.setItem('adminExpand', this.adminExpand);
            },
            closeEditModal() {
                this.showEditModal = false;
                this.editPassword = '';
                this.editConfirmPassword = '';
            }
         }"
         @open-edit-modal.window="editUser.username = $event.detail.username; editUser.first_name = $event.detail.first_name; editUser.last_name = $event.detail.last_name; editUser.email = $event.detail.email; editUser.group = $event.detail.group; editPassword = ''; editConfirmPassword = ''; showEditModal = true;"
         x-init="fetchPendingRequests(); setInterval(() => fetchPendingRequests(), 60000)">

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
                            <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">Overview</span>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">Overview</div>
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
                            <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">File Manager</span>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">File Manager</div>
                    </div>
                    <div class="relative group/tip">
                        <button @click="switchTab('odoo')" :class="currentTab === 'odoo' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors" :class="sidebarOpen ? 'space-x-3' : 'justify-center'">
                            <i class="fas fa-briefcase w-4 flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">ERP System</span>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">ERP System</div>
                    </div>
                    <div class="relative group/tip">
                        <button @click="switchTab('sogo')" :class="currentTab === 'sogo' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors" :class="sidebarOpen ? 'space-x-3' : 'justify-center'">
                            <i class="fas fa-envelope w-4 flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">Webmail</span>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">Webmail</div>
                    </div>
                    <div class="relative group/tip">
                        <button @click="switchTab('profile')" :class="currentTab === 'profile' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors" :class="sidebarOpen ? 'space-x-3' : 'justify-center'">
                            <i class="fas fa-user w-4 flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">My Profile</span>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">My Profile</div>
                    </div>
                </div>

                {{-- Management Section --}}
                <div class="mt-8">
                    <div x-show="sidebarOpen" x-transition.opacity class="h-px bg-slate-200 dark:bg-slate-800 mb-3"></div>
                    <p x-show="sidebarOpen" x-transition.opacity class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider px-3 mb-2">Management</p>
                    <div class="relative group/tip">
                        <button type="button"
                                @click="sidebarOpen ? toggleAdmin() : (sidebarOpen = true, localStorage.setItem('sidebarOpen', true), adminExpand = true, localStorage.setItem('adminExpand', true))"
                                :class="['admin_status','admin_freeipa','admin_sso','admin_monit','admin_nextcloud_monitor','activity_log','branding','access_requests'].includes(currentTab) ? 'bg-slate-100 dark:bg-gray-800/60 text-slate-900 dark:text-white' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800/60'"
                                class="w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors gap-3"
                                :class="sidebarOpen ? 'justify-between' : 'justify-center'">
                            <span class="flex items-center" :class="sidebarOpen ? 'space-x-3' : ''">
                                <i class="fas fa-tools w-4 flex-shrink-0"></i>
                                <span x-show="sidebarOpen" x-transition.opacity class="whitespace-nowrap">Admin Dashboard</span>
                            </span>
                            <i x-show="sidebarOpen" class="fas fa-chevron-down text-[10px] transition-transform duration-200" :class="adminExpand ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="!sidebarOpen" class="absolute left-14 top-1/2 -translate-y-1/2 bg-slate-900 dark:bg-slate-700 text-white text-xs px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/tip:opacity-100 transition-opacity z-50 pointer-events-none shadow-lg">Management</div>
                    </div>
                    <div x-show="adminExpand && sidebarOpen" x-transition class="mt-1 pl-3">
                        <div class="border-l-2 border-slate-200 dark:border-slate-700 pl-3 space-y-0.5">
                            <button type="button" @click="switchTab('admin_status')" :class="currentTab === 'admin_status' ? 'text-blue-600 dark:text-blue-400 font-bold bg-blue-50 dark:bg-blue-950/30' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800/40'" class="w-full text-left px-3 py-2 rounded-lg text-xs transition-colors flex items-center gap-2.5"><i class="fas fa-tachometer-alt text-[10px] w-3"></i> Service Status</button>
                            <button type="button" @click="switchTab('admin_nextcloud_monitor')" :class="currentTab === 'admin_nextcloud_monitor' ? 'text-blue-600 dark:text-blue-400 font-bold bg-blue-50 dark:bg-blue-950/30' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800/40'" class="w-full text-left px-3 py-2 rounded-lg text-xs transition-colors flex items-center gap-2.5"><i class="fas fa-cloud text-[10px] w-3"></i> Storage Monitor</button>
                            <button type="button" @click="switchTab('admin_freeipa')" :class="currentTab === 'admin_freeipa' ? 'text-red-600 dark:text-red-400 font-bold bg-red-50 dark:bg-red-950/30' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800/40'" class="w-full text-left px-3 py-2 rounded-lg text-xs transition-colors flex items-center gap-2.5"><i class="fas fa-key text-[10px] w-3"></i> User Directory</button>
                            <button type="button" @click="switchTab('admin_sso')" :class="currentTab === 'admin_sso' ? 'text-purple-600 dark:text-purple-400 font-bold bg-purple-50 dark:bg-purple-950/30' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800/40'" class="w-full text-left px-3 py-2 rounded-lg text-xs transition-colors flex items-center gap-2.5"><i class="fas fa-shield-alt text-[10px] w-3"></i> SSO Sessions</button>
                            <button type="button" @click="switchTab('admin_monit')" :class="currentTab === 'admin_monit' ? 'text-orange-600 dark:text-orange-400 font-bold bg-orange-50 dark:bg-orange-950/30' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800/40'" class="w-full text-left px-3 py-2 rounded-lg text-xs transition-colors flex items-center gap-2.5"><i class="fas fa-chart-bar text-[10px] w-3"></i> Resource Monitoring</button>
                            <button type="button" @click="switchTab('activity_log')" :class="currentTab === 'activity_log' ? 'text-blue-600 dark:text-blue-400 font-bold bg-blue-50 dark:bg-blue-950/30' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800/40'" class="w-full text-left px-3 py-2 rounded-lg text-xs transition-colors flex items-center gap-2.5"><i class="fas fa-history text-[10px] w-3"></i> Activity Log</button>
                            <button type="button" @click="switchTab('access_requests')" :class="currentTab === 'access_requests' ? 'text-yellow-600 dark:text-yellow-400 font-bold bg-yellow-50 dark:bg-yellow-950/30' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800/40'" class="w-full text-left px-3 py-2 rounded-lg text-xs transition-colors flex items-center gap-2.5 relative">
                                <i class="fas fa-bell text-[10px] w-3"></i> Access Requests
                                <span x-show="pendingRequests > 0" x-text="pendingRequests"
                                    class="ml-auto bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full min-w-[16px] text-center" x-cloak></span>
                            </button>
                            <button type="button" @click="switchTab('branding')" :class="currentTab === 'branding' ? 'text-pink-600 dark:text-pink-400 font-bold bg-pink-50 dark:bg-pink-950/30' : 'text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800/40'" class="w-full text-left px-3 py-2 rounded-lg text-xs transition-colors flex items-center gap-2.5"><i class="fas fa-palette text-[10px] w-3"></i> Branding</button>
                            <a href="/log-viewer" target="_blank" class="w-full text-left px-3 py-2 rounded-lg text-xs text-slate-400 dark:text-gray-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800/40 transition-colors flex items-center gap-2.5 mt-1"><i class="fas fa-terminal text-[10px] w-3"></i> Open LogViewer</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTTOM: User Info --}}
            <div :class="sidebarOpen ? 'p-5' : 'p-3'" class="border-t border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-[#0d131f] transition-all duration-300">
                <div class="flex items-center" :class="sidebarOpen ? 'gap-3' : 'justify-center'">
                    <div class="w-8 h-8 rounded-full bg-blue-600/10 dark:bg-blue-600/20 border border-blue-200 dark:border-blue-800 flex items-center justify-center text-blue-600 flex-shrink-0">
                        <i class="fas fa-user-shield text-xs"></i>
                    </div>
                    <div x-show="sidebarOpen" x-transition.opacity class="truncate min-w-0">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                        <span class="text-[9px] bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/40 px-1.5 py-0.5 rounded font-bold uppercase tracking-wide">Admin Role</span>
                    </div>
                </div>
            </div>
        </aside>
        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col h-screen overflow-hidden min-w-0">
            <header class="h-16 border-b border-slate-100 dark:border-slate-800 bg-white/80 dark:bg-[#111827]/80 backdrop-blur-md flex items-center justify-between px-4 sm:px-8 z-20 flex-shrink-0 sticky top-0 transition-colors duration-200">
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
                        <i class="fas fa-sign-out-alt mr-2"></i> Keluar Sesi
                    </a>
                </div>
            </header>
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto bg-white dark:bg-[#0b0e14]">
                <div x-show="currentTab === 'main'" x-transition x-cloak class="space-y-6">@include('admin.dashboard.main')</div>
                <div x-show="currentTab === 'documents'" x-transition x-cloak class="space-y-6">@include('admin.dashboard.documents')</div>
                <div x-show="currentTab === 'nextcloud'" x-transition x-cloak class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div><h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Nextcloud Storage Drive</h1><p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Manajemen alokasi storage, sinkronisasi file, dan kuota data cloud.</p></div>
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
                <div x-show="currentTab === 'admin_nextcloud_monitor'" x-transition x-cloak class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div><h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Nextcloud User Quota Center</h1><p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Audit kapasitas storage dan manajemen kuota user network.</p></div>
                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <button onclick="window.location.reload();" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 transition-all flex items-center gap-1.5"><i class="fas fa-sync-alt"></i></button>
                            <a href="{{ route('open.nextcloud') }}" target="_blank" class="bg-blue-50 dark:bg-blue-950 hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-600 dark:text-white border border-blue-200 dark:border-blue-800 font-bold text-xs px-4 py-2 rounded-xl transition-colors flex items-center gap-2"><i class="fas fa-external-link-alt"></i> Launch Portal</a>
                        </div>
                    </div>
                    @include('admin.nextcloud.monitor')
                </div>
                <div x-show="currentTab === 'admin_status'" x-transition x-cloak>@include('admin.dashboard.admin_status')</div>
                <div x-show="currentTab === 'admin_freeipa'" x-transition x-cloak>@include('admin.dashboard.admin_freeipa')</div>
                <div x-show="currentTab === 'access_requests'" x-transition x-cloak class="space-y-6">@include('admin.dashboard.access_requests')</div>
                <div x-show="currentTab === 'branding'" x-transition x-cloak class="space-y-6">@include('admin.dashboard.branding')</div>
                <div x-show="currentTab === 'admin_sso'" x-transition x-cloak class="space-y-6"
                     x-data="{
                        stats: { user_count: 0, client_count: 0, total_sessions: 0, sessions: [], user_sessions: [] },
                        loading: true,
                        lastUpdate: null,
                        search: '', perPage: 10, page: 1,
                        get filtered() { return (this.stats.user_sessions || []).filter(s => s.username.toLowerCase().includes(this.search.toLowerCase()) || s.ip.includes(this.search) || s.apps.toLowerCase().includes(this.search.toLowerCase())); },
                        get paginated() { return this.filtered.slice((this.page-1)*this.perPage, this.page*this.perPage); },
                        get totalPages() { return Math.ceil(this.filtered.length / this.perPage); },
                        fetchStats() {
                            this.loading = true;
                            fetch('/api/keycloak/stats')
                                .then(r => r.json())
                                .then(d => { this.stats = d; this.loading = false; this.lastUpdate = new Date().toLocaleTimeString('id-ID'); })
                                .catch(() => { this.loading = false; });
                        }
                     }"
                     x-init="$watch('$el', () => {}); fetchStats()">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Keycloak Identity Provider</h1>
                            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Gerbang otorisasi protokol OAuth2 dan jembatan verifikasi token.</p>
                        </div>
                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <span x-show="lastUpdate" class="text-[10px] text-slate-400 dark:text-gray-500">Update: <span x-text="lastUpdate"></span></span>
                            <button type="button" @click="fetchStats()" class="w-8 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-gray-400 rounded-lg transition-colors">
                                <i class="fas fa-sync-alt text-xs" :class="loading ? 'animate-spin' : ''"></i>
                            </button>
                            <a href="{{ route('open.keycloak') }}" target="_blank" class="bg-purple-50 dark:bg-purple-950 hover:bg-purple-100 dark:hover:bg-purple-900 text-purple-600 dark:text-white border border-purple-200 dark:border-purple-800 font-bold text-xs px-4 py-2 rounded-xl transition-colors flex items-center gap-2"><i class="fas fa-external-link-alt"></i> Launch Portal</a>
                        </div>
                    </div>

                    {{-- Loading --}}
                    <div x-show="loading" x-cloak class="flex items-center justify-center py-16">
                        <div class="w-8 h-8 border-4 border-purple-500 border-t-transparent rounded-full animate-spin"></div>
                    </div>

                    <div x-show="!loading" x-cloak>
                        {{-- Summary Cards --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-500"><i class="fas fa-users text-sm"></i></div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Total Users</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Realm logstack</p>
                                    </div>
                                </div>
                                <span class="text-2xl font-black text-slate-900 dark:text-white" x-text="stats.user_count"></span>
                            </div>
                            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500"><i class="fas fa-plug text-sm"></i></div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Total Clients</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Aplikasi terdaftar</p>
                                    </div>
                                </div>
                                <span class="text-2xl font-black text-slate-900 dark:text-white" x-text="stats.client_count"></span>
                            </div>
                            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500"><i class="fas fa-user-check text-sm"></i></div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Active Sessions</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Semua aplikasi</p>
                                    </div>
                                </div>
                                <span class="text-2xl font-black text-slate-900 dark:text-white" x-text="stats.total_sessions"></span>
                            </div>
                        </div>

                        {{-- Sessions per Client --}}
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden mb-6">
                            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="fas fa-chart-bar text-purple-500"></i> Sessions per Application</h3>
                            </div>
                            <div class="p-5 space-y-4">
                                <template x-for="s in stats.sessions" :key="s.client">
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-circle text-[8px]" :class="s.count > 0 ? 'text-emerald-500' : 'text-slate-300 dark:text-slate-600'"></i>
                                                <span class="text-xs font-bold text-slate-900 dark:text-white" x-text="s.client"></span>
                                            </div>
                                            <span class="text-xs font-black text-slate-900 dark:text-white" x-text="s.count + ' sessions'"></span>
                                        </div>
                                        <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-700 bg-purple-500"
                                                 :style="'width:' + (stats.total_sessions > 0 ? (s.count / stats.total_sessions * 100) : 0) + '%'"></div>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="stats.sessions.length === 0" class="text-center text-slate-400 text-xs py-4">Tidak ada sesi aktif.</div>
                            </div>
                        </div>

                        {{-- User Sessions Table --}}
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2 flex-shrink-0"><i class="fas fa-user-clock text-purple-500"></i> Active User Sessions</h3>
                                <div class="flex items-center gap-2">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-search text-[10px]"></i></span>
                                        <input type="text" x-model="search" @input="page=1" placeholder="Cari username, IP..."
                                               class="bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl pl-9 pr-3 py-1.5 text-xs focus:outline-none focus:border-purple-500 w-48">
                                    </div>
                                    <select x-model.number="perPage" @change="page=1" class="bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-2 py-1.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-purple-500">
                                        <option value="5">5</option>
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                    </select>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 dark:bg-[#0d131f] border-b border-slate-200 dark:border-slate-800 text-[10px] text-slate-500 dark:text-gray-400 uppercase tracking-widest">
                                            <th class="px-5 py-3 font-bold">Username</th>
                                            <th class="px-5 py-3 font-bold">IP Address</th>
                                            <th class="px-5 py-3 font-bold">Apps</th>
                                            <th class="px-5 py-3 font-bold">Login</th>
                                            <th class="px-5 py-3 font-bold">Last Access</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                        <template x-for="s in paginated" :key="s.username + s.ip">
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                                <td class="px-5 py-3"><span class="text-xs font-bold text-slate-900 dark:text-white font-mono" x-text="s.username"></span></td>
                                                <td class="px-5 py-3"><span class="text-[10px] font-mono text-slate-400" x-text="s.ip"></span></td>
                                                <td class="px-5 py-3"><span class="text-[10px] text-slate-600 dark:text-gray-400" x-text="s.apps"></span></td>
                                                <td class="px-5 py-3"><span class="text-[10px] text-slate-500 dark:text-gray-400" x-text="s.start"></span></td>
                                                <td class="px-5 py-3"><span class="text-[10px] text-slate-500 dark:text-gray-400" x-text="s.last_access"></span></td>
                                            </tr>
                                        </template>
                                        <tr x-show="filtered.length === 0">
                                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-xs">Tidak ada sesi aktif.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <p class="text-[11px] text-slate-500">Total <span class="text-purple-600 font-bold" x-text="filtered.length"></span> sesi &mdash; Hal <span x-text="page"></span>/<span x-text="totalPages || 1"></span></p>
                                <div class="flex items-center gap-1">
                                    <button @click="page--" :disabled="page <= 1" :class="page <= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:border-purple-500'" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-gray-400 bg-white dark:bg-[#0b0e14] transition-colors">&larr; Prev</button>
                                    <button @click="page++" :disabled="page >= totalPages" :class="page >= totalPages ? 'opacity-40 cursor-not-allowed' : 'hover:border-purple-500'" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-gray-400 bg-white dark:bg-[#0b0e14] transition-colors">Next &rarr;</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="currentTab === 'admin_monit'" x-transition class="space-y-6"
                     x-data="{
                        metrics: [],
                        loading: true,
                        refreshing: false,
                        lastUpdate: null,
                        fetchMetrics(isRefresh = false) {
                            if (isRefresh) { this.refreshing = true; }
                            else { this.loading = true; }
                            fetch('/api/metrics')
                                .then(r => r.json())
                                .then(d => {
                                    this.metrics = d;
                                    this.loading = false;
                                    this.refreshing = false;
                                    this.lastUpdate = new Date().toLocaleTimeString('id-ID');
                                })
                                .catch(() => { this.loading = false; this.refreshing = false; });
                        },
                        getColor(val) {
                            if (val === null) return 'bg-slate-200 dark:bg-slate-700';
                            if (val >= 80) return 'bg-rose-500';
                            if (val >= 60) return 'bg-orange-500';
                            if (val >= 40) return 'bg-yellow-500';
                            return 'bg-emerald-500';
                        },
                        getTextColor(val) {
                            if (val === null) return 'text-slate-400';
                            if (val >= 80) return 'text-rose-600 dark:text-rose-400';
                            if (val >= 60) return 'text-orange-600 dark:text-orange-400';
                            if (val >= 40) return 'text-yellow-600 dark:text-yellow-400';
                            return 'text-emerald-600 dark:text-emerald-400';
                        },
                        fmt(val) { return val !== null ? val.toFixed(1) + '%' : '-'; }
                     }"
                     x-init="
                        fetchMetrics();
                        setInterval(() => { if (currentTab === 'admin_monit') fetchMetrics(true); }, 30000);
                     ">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Resource Monitoring</h1>
                            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Visualisasi data performa hardware server dan throughput I/O jaringan.</p>
                        </div>
                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <span x-show="lastUpdate" class="text-[10px] text-slate-400 dark:text-gray-500">Update: <span x-text="lastUpdate"></span></span>
                            <button type="button" @click="fetchMetrics(true)" class="w-8 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-gray-400 rounded-lg transition-colors">
                                <i class="fas fa-sync-alt text-xs" :class="refreshing ? 'animate-spin' : ''"></i>
                            </button>
                            <a href="{{ route('open.grafana') }}" target="_blank" class="bg-orange-50 dark:bg-orange-950 hover:bg-orange-100 dark:hover:bg-orange-900 text-orange-600 dark:text-white border border-orange-200 dark:border-orange-900/40 text-xs font-bold px-4 py-2 rounded-xl transition-colors flex items-center gap-2"><i class="fas fa-external-link-alt"></i> Launch Portal</a>
                        </div>
                    </div>

                    {{-- Loading first time only --}}
                    <div x-show="loading && metrics.length === 0" x-cloak class="flex items-center justify-center py-16">
                        <div class="w-8 h-8 border-4 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
                    </div>

                    <div x-show="metrics.length > 0" x-cloak>
                        {{-- Summary Cards --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-7 h-7 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500"><i class="fas fa-microchip text-xs"></i></div>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Avg CPU</p>
                                </div>
                                <div class="text-2xl font-black text-slate-900 dark:text-white" x-text="fmt(metrics.filter(m => m.cpu !== null).reduce((a,b) => a + b.cpu, 0) / metrics.filter(m => m.cpu !== null).length)"></div>
                                <div class="mt-2 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 bg-blue-500" :style="'width:' + (metrics.filter(m => m.cpu !== null).reduce((a,b) => a + b.cpu, 0) / metrics.filter(m => m.cpu !== null).length) + '%'"></div>
                                </div>
                            </div>
                            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-7 h-7 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500"><i class="fas fa-memory text-xs"></i></div>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Avg RAM</p>
                                </div>
                                <div class="text-2xl font-black text-slate-900 dark:text-white" x-text="fmt(metrics.filter(m => m.ram !== null).reduce((a,b) => a + b.ram, 0) / metrics.filter(m => m.ram !== null).length)"></div>
                                <div class="mt-2 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 bg-purple-500" :style="'width:' + (metrics.filter(m => m.ram !== null).reduce((a,b) => a + b.ram, 0) / metrics.filter(m => m.ram !== null).length) + '%'"></div>
                                </div>
                            </div>
                            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500"><i class="fas fa-hdd text-xs"></i></div>
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Avg Disk</p>
                                </div>
                                <div class="text-2xl font-black text-slate-900 dark:text-white" x-text="fmt(metrics.filter(m => m.disk !== null).reduce((a,b) => a + b.disk, 0) / metrics.filter(m => m.disk !== null).length)"></div>
                                <div class="mt-2 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 bg-emerald-500" :style="'width:' + (metrics.filter(m => m.disk !== null).reduce((a,b) => a + b.disk, 0) / metrics.filter(m => m.disk !== null).length) + '%'"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Loading overlay --}}
                        <div x-show="loading" x-cloak class="flex items-center justify-center py-8">
                            <div class="w-6 h-6 border-4 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
                        </div>

                        {{-- Server Table --}}
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="fas fa-server text-slate-400"></i> Server Nodes <span class="text-[10px] text-slate-400 font-normal">(klik baris untuk detail)</span></h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 dark:bg-[#0d131f] border-b border-slate-200 dark:border-slate-800 text-[10px] text-slate-500 dark:text-gray-400 uppercase tracking-widest">
                                            <th class="px-5 py-3 font-bold">Server</th>
                                            <th class="px-5 py-3 font-bold">IP</th>
                                            <th class="px-5 py-3 font-bold w-32">CPU</th>
                                            <th class="px-5 py-3 font-bold">RAM</th>
                                            <th class="px-5 py-3 font-bold">Disk</th>
                                            <th class="px-5 py-3 font-bold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="node in metrics" :key="node.ip">
                                            <tr class="border-b border-slate-100 dark:border-slate-800/60 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                                <td class="px-5 py-3">
                                                    <p class="text-xs font-bold text-slate-900 dark:text-white" x-text="node.name"></p>
                                                </td>
                                                <td class="px-5 py-3">
                                                    <span class="text-[10px] font-mono text-slate-400" x-text="node.ip"></span>
                                                </td>
                                                <td class="px-5 py-3">
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex-1 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                            <div class="h-full rounded-full transition-all duration-500" :class="getColor(node.cpu)" :style="'width:' + (node.cpu || 0) + '%'"></div>
                                                        </div>
                                                        <span class="text-[10px] font-bold w-10 text-right" :class="getTextColor(node.cpu)" x-text="fmt(node.cpu)"></span>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-3">
                                                    <p class="text-xs font-bold text-slate-900 dark:text-white" x-text="node.ram_used + ' / ' + node.ram_total"></p>
                                                    <div class="mt-1 h-1 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                        <div class="h-full rounded-full transition-all duration-500" :class="getColor(node.ram)" :style="'width:' + (node.ram || 0) + '%'"></div>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-3">
                                                    <p class="text-xs font-bold text-slate-900 dark:text-white" x-text="node.disk_used + ' / ' + node.disk_total"></p>
                                                    <div class="mt-1 h-1 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                        <div class="h-full rounded-full transition-all duration-500" :class="getColor(node.disk)" :style="'width:' + (node.disk || 0) + '%'"></div>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-3">
                                                    <span x-show="node.status === 'up'" class="flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> UP</span>
                                                    <span x-show="node.status === 'down'" class="flex items-center gap-1 text-[10px] font-bold text-rose-600 dark:text-rose-400"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> DOWN</span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="currentTab === 'activity_log'" x-transition x-cloak
                     x-data="{
                        logs: [], loading: true, currentPage: 1, lastPage: 1, total: 0,
                        filterUsername: '', filterApp: '', filterAction: '', filterDateFrom: '', filterDateTo: '',
                        fetchLogs(page = 1) {
                            this.loading = true; this.currentPage = page;
                            let params = new URLSearchParams({ page, username: this.filterUsername, app: this.filterApp, action: this.filterAction, date_from: this.filterDateFrom, date_to: this.filterDateTo });
                            fetch('/admin/activity-log/api?' + params.toString()).then(res => res.json()).then(data => { this.logs = data.data; this.currentPage = data.current_page; this.lastPage = data.last_page; this.total = data.total; this.loading = false; }).catch(() => { this.loading = false; });
                        },
                        resetFilter() { this.filterUsername = ''; this.filterApp = ''; this.filterAction = ''; this.filterDateFrom = ''; this.filterDateTo = ''; this.fetchLogs(1); },
                        appColor(app) { return window.logstackHelpers.appColor(app); },
                        actionColor(action) { return window.logstackHelpers.actionColor(action); },
                        actionIcon(action) { return window.logstackHelpers.actionIcon(action); },
                        formatAction(action) { return window.logstackHelpers.formatAction(action); }
                     }"
                     x-init="if(currentTab === 'activity_log') fetchLogs(1); $watch('currentTab', val => { if(val === 'activity_log') fetchLogs(1); })">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4 mb-6">
                        <div><h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">User Activity Log</h1><p class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-1">Rekam jejak aktivitas seluruh user di semua aplikasi.</p></div>
                        <div class="flex items-center gap-3 self-start sm:self-auto">
                            <div class="text-xs font-bold text-slate-400">Total: <span class="text-blue-600" x-text="total"></span> aktivitas</div>
                            <button @click="fetchLogs(1)" class="w-8 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-gray-400 rounded-lg transition-colors shadow-sm"><i class="fas fa-sync-alt text-xs" :class="loading ? 'animate-spin' : ''"></i></button>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 mb-6 shadow-sm">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div><label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Username</label><div class="relative"><span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-search text-[10px]"></i></span><input type="text" x-model="filterUsername" placeholder="Cari username..." class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl pl-9 pr-3 py-2 text-xs focus:outline-none focus:border-blue-500 transition-colors shadow-sm font-mono"></div></div>
                            <div><label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Aplikasi</label><select x-model="filterApp" class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500"><option value="">Semua App</option><option value="laravel">Laravel</option><option value="sogo">Sogo</option><option value="nextcloud">Nextcloud</option><option value="odoo">Odoo</option><option value="onlyoffice">OnlyOffice</option><option value="grafana">Grafana</option></select></div>
                            <div><label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Aksi</label><select x-model="filterAction" class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500"><option value="">Semua Aksi</option><option value="login">Login</option><option value="logout">Logout</option><option value="open_app">Open App</option><option value="open_document">Open Document</option><option value="create_document">Create Document</option><option value="delete_document">Delete Document</option></select></div>
                            <div><label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Dari Tanggal</label><input type="date" x-model="filterDateFrom" class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500"></div>
                            <div><label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Sampai Tanggal</label><input type="date" x-model="filterDateTo" class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500"></div>
                        </div>
                        <div class="flex items-center gap-3 mt-4">
                            <button @click="fetchLogs(1)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-5 py-2 rounded-xl flex items-center gap-2"><i class="fas fa-search"></i> Filter</button>
                            <button @click="resetFilter()" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-gray-400 font-bold text-xs px-5 py-2 rounded-xl flex items-center gap-2"><i class="fas fa-times"></i> Reset</button>
                        </div>
                    </div>
                    <div x-show="loading" x-cloak class="flex items-center justify-center py-12"><div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div></div>
                    <div x-show="!loading" class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden" x-cloak>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead><tr class="bg-slate-50 dark:bg-[#0d131f] border-b border-slate-200 dark:border-slate-800 text-[10px] text-slate-500 dark:text-gray-400 uppercase tracking-widest"><th class="px-5 py-4 font-bold">Waktu</th><th class="px-5 py-4 font-bold">Username</th><th class="px-5 py-4 font-bold">Aplikasi</th><th class="px-5 py-4 font-bold">Aksi</th><th class="px-5 py-4 font-bold">Deskripsi</th><th class="px-5 py-4 font-bold">IP Address</th></tr></thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                    <template x-for="log in logs" :key="log.id">
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                            <td class="px-5 py-3.5 whitespace-nowrap"><p class="text-xs font-mono text-slate-700 dark:text-gray-300" x-text="log.date"></p><p class="text-[10px] font-mono text-slate-400" x-text="log.time + ' WIB'"></p></td>
                                            <td class="px-5 py-3.5"><span class="font-mono font-bold text-xs text-blue-600 dark:text-blue-400" x-text="log.username || '-'"></span></td>
                                            <td class="px-5 py-3.5"><span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-lg" :class="appColor(log.app)" x-text="log.app.charAt(0).toUpperCase() + log.app.slice(1)"></span></td>
                                            <td class="px-5 py-3.5"><span class="text-[10px] font-bold px-2 py-1 rounded-lg" :class="actionColor(log.action)" x-text="formatAction(log.action)"></span></td>
                                            <td class="px-5 py-3.5"><p class="text-xs text-slate-600 dark:text-gray-400 max-w-xs truncate" x-text="log.description || '-'"></p></td>
                                            <td class="px-5 py-3.5"><span class="font-mono text-[10px] text-slate-500" x-text="log.ip_address || '-'"></span></td>
                                        </tr>
                                    </template>
                                    <tr x-show="logs.length === 0" x-cloak><td colspan="6" class="px-5 py-12 text-center text-slate-400"><i class="fas fa-history text-3xl mb-3 block opacity-30"></i><p class="text-xs font-medium">Belum ada aktivitas.</p></td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <p class="text-[11px] text-slate-500">Total <span class="text-blue-600 font-bold" x-text="total"></span> &mdash; Hal <span x-text="currentPage"></span>/<span x-text="lastPage"></span></p>
                            <div class="flex items-center gap-1">
                                <button @click="fetchLogs(currentPage - 1)" :disabled="currentPage <= 1" :class="currentPage <= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:border-blue-500'" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-gray-400 bg-white dark:bg-[#0b0e14] transition-colors">&larr; Prev</button>
                                <button @click="fetchLogs(currentPage + 1)" :disabled="currentPage >= lastPage" :class="currentPage >= lastPage ? 'opacity-40 cursor-not-allowed' : 'hover:border-blue-500'" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-gray-400 bg-white dark:bg-[#0b0e14] transition-colors">Next &rarr;</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            {{-- FOOTER --}}
            <footer class="flex-shrink-0 border-t border-slate-100 dark:border-slate-800 bg-white/80 dark:bg-[#111827]/80 px-4 sm:px-8 py-3 flex items-center justify-between gap-4">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-600 uppercase tracking-widest">&copy; 2026 {{ strtoupper($branding['footer_text']) }}</p>
                <p class="text-[10px] text-slate-300 dark:text-gray-700 font-mono hidden sm:block">{{ $branding['app_version'] }}</p>
            </footer>
        </div>

        {{-- MODAL ADD USER --}}
        <div x-show="showAddModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" x-transition x-cloak x-data="{ password: '', password_confirmation: '' }" @click.away="showAddModal = false; password = ''; password_confirmation = '';">
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-lg p-6 rounded-2xl shadow-2xl relative">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2"><i class="fas fa-user-plus text-blue-500"></i> Daftarkan User Baru (FreeIPA)</h3>
                    <button @click="showAddModal = false; password = ''; password_confirmation = '';" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm">&times;</button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST" @submit="showAddModal = false">
                    @csrf
                    <div class="space-y-4 text-xs">
                        <x-form.input label="Username (UID)" name="username" placeholder="contoh: tuying" required="true" />
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form.input label="Nama Depan" name="first_name" placeholder="Nama Depan" required="true" />
                            <x-form.input label="Nama Belakang" name="last_name" placeholder="Nama Belakang" required="true" />
                        </div>
                        <x-form.input type="email" label="Alamat Email Jaringan" name="email" placeholder="contoh: user@{{ $serviceUrls['mail_domain'] ?? 'logstack.web.id' }}" required="true" />
                        @php $roleOptions = ['dash_user' => 'User Biasa (dash_user)', 'dash_admin' => 'Administrator (dash_admin)']; @endphp
                        <x-form.select label="Grup Otorisasi / Role" name="group" :options="$roleOptions" />
                        <x-form.input type="password" label="Password Akun Default" name="password" model="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required="true" />
                        <div>
                            <x-form.input type="password" label="Konfirmasi Password" name="password_confirmation" model="password_confirmation" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required="true" ::class="password_confirmation && password !== password_confirmation ? 'border-rose-500 focus:border-rose-500 bg-rose-500/5' : 'border-slate-200 dark:border-slate-700 focus:border-blue-500 bg-slate-50 dark:bg-slate-800/50'" />
                            <div x-show="password_confirmation && password !== password_confirmation" x-transition class="text-[11px] font-bold text-rose-500 mt-1.5 flex items-center gap-1"><i class="fas fa-exclamation-triangle"></i> Password tidak cocok!</div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-4 mt-6">
                        <button type="button" @click="showAddModal = false; password = ''; password_confirmation = '';" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl transition-colors">Batal</button>
                        <button type="submit" :disabled="password !== password_confirmation" :class="password !== password_confirmation ? 'opacity-40 cursor-not-allowed bg-slate-400 text-slate-600' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/10'" class="font-bold text-xs px-5 py-2 rounded-xl transition-all flex items-center gap-1">Daftarkan User</button>
                    </div>
                </form>
                        </div>
                    </div>

        {{-- MODAL EDIT USER --}}
        @include('admin.users.modal_edit')

    </div>
</x-layouts.app>
