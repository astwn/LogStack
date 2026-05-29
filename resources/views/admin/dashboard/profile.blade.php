<div class="space-y-6"
     x-data="{
        stats: {
            today_count: 0,
            week_count: 0,
            total_count: 0,
            top_app: '-',
            top_app_count: 0,
            last_login: '-',
            session_ip: '-',
            session_agent: '-',
            session_time: '-',
        },
        loading: true,
        fetchStats() {
            this.loading = true;
            fetch('/api/profile/stats')
                .then(res => res.json())
                .then(data => { this.stats = data; this.loading = false; })
                .catch(() => { this.loading = false; });
        },
        parseAgent(ua) {
            if (!ua) return '-';
            if (ua.includes('Chrome')) return 'Google Chrome';
            if (ua.includes('Firefox')) return 'Mozilla Firefox';
            if (ua.includes('Safari')) return 'Apple Safari';
            if (ua.includes('Edge')) return 'Microsoft Edge';
            if (ua.includes('curl')) return 'cURL Client';
            if (ua.includes('Node')) return 'Node.js Client';
            return ua.substring(0, 40) + '...';
        }
     }"
     x-init="fetchStats()">

    {{-- HEADER --}}
    <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">👤 Profil Identitas SSO</h1>
            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Manajemen kredensial utama dan informasi keamanan akun terpusat.</p>
        </div>
        <button @click="fetchStats()" class="w-8 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-gray-400 rounded-lg transition-colors shadow-sm" title="Refresh">
            <i class="fas fa-sync-alt text-xs" :class="loading ? 'animate-spin' : ''"></i>
        </button>
    </div>

    {{-- SECTION 1: IDENTITAS UTAMA --}}
    <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row gap-6 items-start">
        <div class="w-20 h-20 rounded-2xl bg-blue-600/10 dark:bg-blue-600/20 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-blue-600 text-3xl font-black shrink-0 shadow-inner">
            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
        </div>
        <div class="flex-1 w-full space-y-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white">{{ Auth::user()->name }}</h2>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    @php $isAdminPortal = $isAdminView ?? false; @endphp
                    <span class="{{ $isAdminPortal ? 'bg-purple-100 dark:bg-purple-950/40 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-900/30' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-300 dark:border-gray-700/60' }} border text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                        {{ $isAdminPortal ? 'Administrator Privileges' : 'User Account' }}
                    </span>
                    <span class="bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Akun Aktif
                    </span>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-slate-200 dark:border-slate-800/60">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-1">Alamat Email (SSO)</p>
                    <p class="text-xs font-mono text-slate-900 dark:text-white">{{ Auth::user()->email }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-1">Username (UID)</p>
                    <p class="text-xs font-mono text-slate-900 dark:text-white">{{ explode('@', Auth::user()->email)[0] }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-1">Otentikasi Server</p>
                    <p class="text-xs text-slate-900 dark:text-white font-medium flex items-center gap-1">
                        <i class="fas fa-shield-alt text-blue-500"></i> Keycloak & FreeIPA
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-1">Timezone</p>
                    <p class="text-xs text-slate-900 dark:text-white font-medium">Asia/Jakarta (WIB)</p>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 2: STATISTIK AKTIVITAS --}}
    <div>
        <h3 class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fas fa-chart-bar text-blue-500"></i> Statistik Aktivitas
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-2">Aktivitas Hari Ini</p>
                <div class="flex items-end gap-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white" x-text="loading ? '—' : stats.today_count"></span>
                    <span class="text-xs text-slate-400 mb-1">aksi</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 mt-2">
                    <i class="fas fa-calendar-day text-sm"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-2">Minggu Ini</p>
                <div class="flex items-end gap-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white" x-text="loading ? '—' : stats.week_count"></span>
                    <span class="text-xs text-slate-400 mb-1">aksi</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500 mt-2">
                    <i class="fas fa-calendar-week text-sm"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-2">Total Aktivitas</p>
                <div class="flex items-end gap-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white" x-text="loading ? '—' : stats.total_count"></span>
                    <span class="text-xs text-slate-400 mb-1">aksi</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500 mt-2">
                    <i class="fas fa-history text-sm"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-2">App Favorit</p>
                <div class="flex items-end gap-2">
                    <span class="text-lg font-black text-slate-900 dark:text-white capitalize" x-text="loading ? '—' : stats.top_app"></span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1" x-text="loading ? '' : stats.top_app_count + ' kali dibuka'"></p>
                <div class="w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-500 mt-2">
                    <i class="fas fa-star text-sm"></i>
                </div>
            </div>
        </div>
        {{-- Last Login --}}
        <div class="mt-4 bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-xl p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500 shrink-0">
                <i class="fas fa-sign-in-alt text-xs"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Login Terakhir</p>
                <p class="text-xs font-mono text-slate-900 dark:text-white mt-0.5" x-text="loading ? 'Memuat...' : stats.last_login"></p>
            </div>
        </div>
    </div>

    {{-- SECTION 3: LAYANAN TEROTORISASI --}}
    <div>
        <h3 class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fas fa-shield-alt text-blue-500"></i> Layanan Terotorisasi (Single Sign-On)
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex items-center justify-between gap-4 transition-transform hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 text-lg border border-blue-100 dark:border-blue-800">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">Nextcloud Drive</p>
                        <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5"><i class="fas fa-check"></i> Akses Diizinkan</p>
                    </div>
                </div>
                <a href="{{ route('open.nextcloud') }}" target="_blank" class="bg-blue-50 dark:bg-blue-950 hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-600 dark:text-white border border-blue-200 dark:border-blue-800 font-bold text-[10px] px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap">Buka ➔</a>
            </div>
            <div class="bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex items-center justify-between gap-4 transition-transform hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 text-lg border border-purple-100 dark:border-purple-800">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">Odoo ERP System</p>
                        <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5"><i class="fas fa-check"></i> Akses Diizinkan</p>
                    </div>
                </div>
                <a href="{{ route('open.odoo') }}" target="_blank" class="bg-blue-50 dark:bg-blue-950 hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-600 dark:text-white border border-blue-200 dark:border-blue-800 font-bold text-[10px] px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap">Buka ➔</a>
            </div>
            <div class="bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex items-center justify-between gap-4 transition-transform hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 text-lg border border-orange-100 dark:border-orange-800">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">SOGo Webmail</p>
                        <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5"><i class="fas fa-check"></i> Akses Diizinkan</p>
                    </div>
                </div>
                <a href="{{ route('open.sogo') }}" target="_blank" class="bg-orange-50 dark:bg-orange-950 hover:bg-orange-100 dark:hover:bg-orange-900 text-orange-600 dark:text-white border border-orange-200 dark:border-orange-900/40 font-bold text-[10px] px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap">Buka ➔</a>
            </div>
        </div>
    </div>

    {{-- SECTION 4: SESI AKTIF --}}
    <div>
        <h3 class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fas fa-lock text-emerald-500"></i> Sesi Aktif Sekarang
        </h3>
        <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 shrink-0">
                        <i class="fas fa-network-wired text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-0.5">IP Address</p>
                        <p class="text-xs font-mono text-slate-900 dark:text-white" x-text="loading ? 'Memuat...' : stats.session_ip"></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500 shrink-0">
                        <i class="fas fa-globe text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-0.5">Browser / Client</p>
                        <p class="text-xs text-slate-900 dark:text-white" x-text="loading ? 'Memuat...' : parseAgent(stats.session_agent)"></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500 shrink-0">
                        <i class="fas fa-clock text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-0.5">Aktivitas Terakhir</p>
                        <p class="text-xs font-mono text-slate-900 dark:text-white" x-text="loading ? 'Memuat...' : stats.session_time"></p>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">Sesi terautentikasi via Keycloak SSO — Aktif</p>
            </div>
        </div>
    </div>

    {{-- SECTION 5: GANTI PASSWORD --}}
    <div x-data="{
        showModal: false,
        loading: false,
        current_password: '',
        new_password: '',
        confirm_password: '',
        message: '',
        messageType: '',
        get passwordMatch() { return this.new_password === this.confirm_password; },
        get passwordNotSame() { return this.new_password !== this.current_password; },
        get passwordStrong() { return this.new_password.length >= 8; },
        get passwordHasCaps() { return /[A-Z]/.test(this.new_password); },
        get passwordHasNumber() { return /[0-9]/.test(this.new_password); },
        get passwordHasSpecial() { return /[^A-Za-z0-9]/.test(this.new_password); },
        get passwordValid() { return this.passwordStrong && this.passwordHasCaps && this.passwordHasNumber && this.passwordHasSpecial && this.passwordNotSame; },
        closeModal() {
            this.showModal = false;
            this.current_password = '';
            this.new_password = '';
            this.confirm_password = '';
            this.message = '';
        },
        async submitChange() {
            if (!this.passwordMatch || !this.passwordValid) return;
            this.loading = true;
            this.message = '';
            try {
                const res = await fetch('/api/profile/change-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        current_password: this.current_password,
                        new_password: this.new_password,
                        confirm_password: this.confirm_password,
                    })
                });
                const data = await res.json();
                this.messageType = data.success ? 'success' : 'error';
                this.message = data.message;
                if (data.success) {
                    setTimeout(() => this.closeModal(), 1500);
                }
            } catch(e) {
                this.messageType = 'error';
                this.message = 'Terjadi kesalahan. Coba lagi.';
            } finally {
                this.loading = false;
            }
        }
    }">
        <h3 class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fas fa-key text-orange-500"></i> Keamanan Akun
        </h3>
        <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-500 shrink-0">
                        <i class="fas fa-lock text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">Password Akun FreeIPA</p>
                        <p class="text-[10px] text-slate-400 dark:text-gray-500 mt-0.5">Ubah password login SSO Anda</p>
                    </div>
                </div>
                <button @click="showModal = true"
                    class="bg-orange-50 dark:bg-orange-950 hover:bg-orange-100 dark:hover:bg-orange-900 text-orange-600 dark:text-white border border-orange-200 dark:border-orange-900/40 font-bold text-xs px-4 py-2 rounded-xl transition-colors w-full sm:w-auto">
                    Ganti Password
                </button>
            </div>
        </div>

        {{-- MODAL GANTI PASSWORD --}}
        <div x-show="showModal"
             class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
             x-transition x-cloak
             @click.away="closeModal()">
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-md p-6 rounded-2xl shadow-2xl relative">

                {{-- Header Modal --}}
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-key text-orange-500"></i> Ganti Password Akun
                    </h3>
                    <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm">✕</button>
                </div>

                {{-- Notifikasi --}}
                <div x-show="message" x-transition x-cloak class="mb-4 p-3 rounded-xl text-xs font-bold flex items-center gap-2"
                    :class="messageType === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30' : 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/30'">
                    <i class="fas" :class="messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
                    <span x-text="message"></span>
                </div>

                {{-- Form --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Password Saat Ini</label>
                        <input type="password" x-model="current_password" placeholder="••••••••"
                            class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Password Baru</label>
                        <input type="password" x-model="new_password" placeholder="••••••••"
                            :class="new_password && !passwordStrong ? 'border-red-400 focus:border-red-500' : 'border-slate-200 dark:border-slate-700 focus:border-blue-500'"
                            class="w-full bg-white dark:bg-[#0b0e14] rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none transition-colors border">
                        {{-- Password strength indicators --}}
                        <div x-show="new_password" x-cloak class="mt-2 space-y-1">
                            <div class="flex items-center gap-1.5" :class="passwordStrong ? 'text-emerald-500' : 'text-red-400'">
                                <i class="fas text-[9px]" :class="passwordStrong ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                <span class="text-[10px] font-medium">Minimal 8 karakter</span>
                            </div>
                            <div class="flex items-center gap-1.5" :class="passwordHasCaps ? 'text-emerald-500' : 'text-red-400'">
                                <i class="fas text-[9px]" :class="passwordHasCaps ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                <span class="text-[10px] font-medium">Minimal 1 huruf kapital (A-Z)</span>
                            </div>
                            <div class="flex items-center gap-1.5" :class="passwordHasNumber ? 'text-emerald-500' : 'text-red-400'">
                                <i class="fas text-[9px]" :class="passwordHasNumber ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                <span class="text-[10px] font-medium">Minimal 1 angka (0-9)</span>
                            </div>
                            <div class="flex items-center gap-1.5" :class="passwordHasSpecial ? 'text-emerald-500' : 'text-red-400'">
                                <i class="fas text-[9px]" :class="passwordHasSpecial ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                <span class="text-[10px] font-medium">Minimal 1 karakter spesial (!@#$...)</span>
                            </div>
                            <div class="flex items-center gap-1.5" :class="passwordNotSame ? 'text-emerald-500' : 'text-red-400'">
                                <i class="fas text-[9px]" :class="passwordNotSame ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                <span class="text-[10px] font-medium">Tidak sama dengan password lama</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" x-model="confirm_password" placeholder="••••••••"
                            :class="confirm_password && !passwordMatch ? 'border-red-400 focus:border-red-500' : 'border-slate-200 dark:border-slate-700 focus:border-blue-500'"
                            class="w-full bg-white dark:bg-[#0b0e14] rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none transition-colors border">
                        <p x-show="confirm_password && !passwordMatch" x-cloak class="text-[10px] text-red-500 font-bold mt-1 flex items-center gap-1"><i class="fas fa-exclamation-triangle"></i> Password tidak cocok</p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-4 mt-4">
                    <button @click="closeModal()"
                        class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button @click="submitChange()"
                        :disabled="loading || !current_password || !passwordMatch || !passwordValid"
                        :class="loading || !current_password || !passwordMatch || !passwordValid ? 'opacity-40 cursor-not-allowed bg-slate-400 text-slate-600' : 'bg-orange-600 hover:bg-orange-700 text-white shadow-md shadow-orange-600/10'"
                        class="font-bold text-xs px-5 py-2 rounded-xl transition-all flex items-center gap-2">
                        <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-key'"></i>
                        <span x-text="loading ? 'Memproses...' : 'Simpan Password'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>