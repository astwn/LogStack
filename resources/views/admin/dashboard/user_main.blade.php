    {{-- GREETING HEADER --}}
    <div class="mb-8">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white" id="greeting-text">Selamat Datang</h1>
                <p class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-1">Akses seluruh ekosistem layanan cloud terintegrasi Anda.</p>
            </div>
        </div>
    </div>

    {{-- INFO + JAM ROW --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8"
         x-data="{ stats: {}, loading: true }"
         x-init="fetch('/api/profile/stats').then(r => r.json()).then(d => { stats = d; loading = false; }).catch(() => { loading = false; })">

        {{-- Session Info Card --}}
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl">
            <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-4">Informasi Sesi</p>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-500 dark:text-gray-400">
                        <i class="fas fa-user text-[10px] w-3"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">User</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $user->username ?? explode('@', $user->email ?? Auth::user()->email)[0] }}</p>
                        <p class="text-[10px] text-slate-400 dark:text-gray-500">{{ $user->email ?? Auth::user()->email }}</p>
                    </div>
                </div>
                <div class="h-px bg-slate-200 dark:bg-slate-800"></div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-500 dark:text-gray-400">
                        <i class="fas fa-user-shield text-[10px] w-3"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Role</span>
                    </div>
                    <span class="text-[10px] bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-gray-700/60 px-2 py-0.5 rounded font-bold uppercase tracking-wide">User</span>
                </div>
                <div class="h-px bg-slate-200 dark:bg-slate-800"></div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-500 dark:text-gray-400">
                        <i class="fas fa-network-wired text-[10px] w-3"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">IP Sesi</span>
                    </div>
                    <div x-show="loading" x-cloak class="w-3 h-3 border-2 border-slate-400 border-t-transparent rounded-full animate-spin"></div>
                    <span x-show="!loading" x-cloak class="text-xs font-bold text-slate-900 dark:text-white font-mono" x-text="stats.session_ip || '-'"></span>
                </div>
                <div class="h-px bg-slate-200 dark:bg-slate-800"></div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-500 dark:text-gray-400">
                        <i class="fas fa-sign-in-alt text-[10px] w-3"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Last Login</span>
                    </div>
                    <div x-show="loading" x-cloak class="w-3 h-3 border-2 border-slate-400 border-t-transparent rounded-full animate-spin"></div>
                    <span x-show="!loading" x-cloak class="text-[10px] font-bold text-slate-900 dark:text-white text-right" x-text="stats.last_login || '-'"></span>
                </div>
                <div class="h-px bg-slate-200 dark:bg-slate-800"></div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-500 dark:text-gray-400">
                        <i class="fas fa-circle text-[10px] w-3 text-emerald-500"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Status</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">Online</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Jam Live --}}
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex flex-col justify-between">
            <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-4">Waktu Sistem</p>
            <div class="flex-1 flex flex-col justify-start pt-2">
                <div class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-wider font-mono" id="user-clock-time">--:--:--</div>
                <div class="text-sm font-bold text-slate-600 dark:text-gray-300 mt-3" id="user-clock-date">Memuat...</div>
                <div class="text-xs font-bold text-blue-600 dark:text-blue-400 mt-1" id="user-clock-tz">--</div>
            </div>
        </div>
    </div>

    {{-- STATUS INTEGRASI --}}
    <h3 class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-4">Status Layanan Aplikasi</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500"><i class="fas fa-cloud text-xs"></i></div>
                <div><h4 class="font-bold text-xs text-slate-900 dark:text-white">Nextcloud Drive</h4><p class="text-[10px] text-gray-400 mt-0.5">drive.logstack.web.id</p></div>
            </div>
            @if(($appsStatus['nextcloud'] ?? 'OFFLINE') === 'ONLINE')
                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">ONLINE</span>
            @else
                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">OFFLINE</span>
            @endif
        </div>
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-500"><i class="fas fa-briefcase text-xs"></i></div>
                <div><h4 class="font-bold text-xs text-slate-900 dark:text-white">Odoo ERP Suite</h4><p class="text-[10px] text-gray-400 mt-0.5">erp.logstack.web.id</p></div>
            </div>
            @if(($appsStatus['odoo'] ?? 'OFFLINE') === 'ONLINE')
                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">ONLINE</span>
            @else
                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">OFFLINE</span>
            @endif
        </div>
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500"><i class="fas fa-envelope text-xs"></i></div>
                <div><h4 class="font-bold text-xs text-slate-900 dark:text-white">SOGO Mailbox</h4><p class="text-[10px] text-gray-400 mt-0.5">mbox.logstack.web.id</p></div>
            </div>
            @if(($appsStatus['sogo'] ?? 'OFFLINE') === 'ONLINE')
                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">ONLINE</span>
            @else
                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">OFFLINE</span>
            @endif
        </div>
    </div>

    {{-- RECENT ACTIVITY --}}
    <div class="mt-2"
         x-data="{
            recentLogs: [],
            recentLoading: true,
            fetchRecent() {
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
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-history text-blue-500"></i> Recent Activity
            </h3>
            <button @click="fetchRecent()" class="w-7 h-7 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-gray-400 rounded-lg transition-colors shadow-sm">
                <i class="fas fa-sync-alt text-[10px]" :class="recentLoading ? 'animate-spin' : ''"></i>
            </button>
        </div>
        <div x-show="recentLoading" class="flex items-center justify-center py-6">
            <div class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        </div>
        <div x-show="!recentLoading" class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm" x-cloak>
            <template x-for="log in recentLogs" :key="log.id">
                <div class="flex items-center gap-2 sm:gap-4 px-3 sm:px-5 py-3 border-b border-slate-100 dark:border-slate-800/60 last:border-0 hover:bg-white dark:hover:bg-slate-800/30 transition-colors">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs flex-shrink-0" :class="appColor(log.app)">
                        <i class="fas" :class="actionIcon(log.action)"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900 dark:text-white font-mono" x-text="log.username || '-'"></span>
                            <span class="text-[10px] font-bold" :class="actionColor(log.action)" x-text="formatAction(log.action)"></span>
                        </div>
                        <p class="text-[10px] text-slate-400 dark:text-gray-500 truncate mt-0.5" x-text="log.description || '-'"></p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-[10px] font-mono text-slate-400 dark:text-gray-500" x-text="log.time"></p>
                        <p class="text-[9px] text-slate-300 dark:text-gray-600" x-text="log.date"></p>
                    </div>
                </div>
            </template>
            <div x-show="recentLogs.length === 0" class="px-5 py-8 text-center text-slate-400 text-xs">
                Belum ada aktivitas.
            </div>
        </div>
    </div>

    <script>
        function updateUserClock() {
            const clockTime = document.getElementById('user-clock-time');
            const clockDate = document.getElementById('user-clock-date');
            const clockTz   = document.getElementById('user-clock-tz');
            if (clockTime) {
                const now = new Date();
                clockTime.textContent = now.toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
                if (clockDate) clockDate.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                if (clockTz) {
                    const offset = -now.getTimezoneOffset() / 60;
                    clockTz.textContent = offset >= 9 ? 'WIT (UTC+9)' : offset >= 8 ? 'WITA (UTC+8)' : 'WIB (UTC+7)';
                }
            }
        }
        updateUserClock();
        setInterval(updateUserClock, 1000);

        function updateGreeting() {
            const greetEl   = document.getElementById('greeting-text');
            const firstName = '{{ addslashes(explode(" ", ($user->name ?? Auth::user()->name))[0]) }}';
            const hour = new Date().getHours();
            let greet = 'Selamat Datang';
            if (hour >= 5  && hour < 11) greet = 'Selamat Pagi';
            else if (hour >= 11 && hour < 15) greet = 'Selamat Siang';
            else if (hour >= 15 && hour < 18) greet = 'Selamat Sore';
            else greet = 'Selamat Malam';
            if (greetEl) greetEl.textContent = greet + ', ' + firstName;
        }
        updateGreeting();
        setInterval(updateGreeting, 60000);
    </script>
