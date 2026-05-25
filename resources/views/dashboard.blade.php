<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | LogStack Central</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { darkMode: 'class' }
        function toggleTheme() {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
        if (localStorage.getItem('theme') === 'light') document.documentElement.classList.remove('dark');

        function hideLoader() {
            const loader = document.getElementById('preloader');
            if (loader) {
                loader.classList.add('fade-out');
                setTimeout(() => { loader.style.display = 'none'; }, 600);
            }
        }
        window.addEventListener('load', hideLoader);
        setTimeout(hideLoader, 1200);
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s ease; }
        #preloader { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: #0b0e14; display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 9999; transition: opacity 0.6s ease, visibility 0.6s ease; }
        .light #preloader { background-color: #ffffff; }
        .fade-out { opacity: 0; visibility: hidden; }
        .loader-ring { width: 40px; height: 40px; border: 3px solid rgba(37, 99, 235, 0.1); border-radius: 50%; border-top-color: #2563eb; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white dark:bg-[#0b0e14] text-slate-900 dark:text-white min-h-screen transition-colors duration-300 flex flex-col"
      x-data="{
        currentTab: localStorage.getItem('activeTab') || 'main',
        showAddModal: false,
        showEditModal: false,
        editUser: { username: '', first_name: '', last_name: '', email: '', group: 'dash_user' },
        adminExpand: true,
        isMappingLoading: false,
        switchTab(tabName) {
            this.isMappingLoading = true;
            setTimeout(() => {
                this.currentTab = tabName;
                localStorage.setItem('activeTab', tabName);
                this.isMappingLoading = false;
            }, 150);
        }
      }">

    <div id="preloader">
        <div class="loader-ring mb-4"></div>
        <div class="text-[10px] font-black text-blue-600 uppercase tracking-[0.4em] animate-pulse">LogStack Admin</div>
    </div>

    <div x-show="isMappingLoading" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/80 dark:bg-[#0b0e14]/80 backdrop-blur-md" x-cloak>
        <div class="loader-ring mb-2"></div>
        <span class="text-[9px] font-mono tracking-widest text-slate-500 dark:text-gray-400 uppercase animate-pulse">Syncing Services...</span>
    </div>

    <div class="flex h-screen overflow-hidden">

        <aside class="w-64 h-screen bg-slate-50 dark:bg-[#111827] border-r border-slate-100 dark:border-slate-800 flex flex-col justify-between flex-shrink-0 overflow-y-auto">
            <div class="p-6">
                <a href="/" class="flex items-center gap-3 mb-8 group">
                    <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                        <i class="fas fa-layer-group text-sm"></i>
                    </div>
                    <span class="text-lg font-bold tracking-tighter uppercase italic text-slate-900 dark:text-white">LOG<span class="text-blue-600">STACK</span></span>
                </a>

                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider px-3 mb-2">Main Menu</p>
                    <button @click="switchTab('main')" :class="currentTab === 'main' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors">
                        <i class="fas fa-home w-4 text-left"></i><span>Main Dashboard</span>
                    </button>
                    <button @click="switchTab('nextcloud')" :class="currentTab === 'nextcloud' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors">
                        <i class="fas fa-cloud w-4 text-left"></i><span>Nextcloud Storage</span>
                    </button>
                    <button @click="switchTab('odoo')" :class="currentTab === 'odoo' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors">
                        <i class="fas fa-briefcase w-4 text-left"></i><span>Odoo ERP System</span>
                    </button>
                    <button @click="switchTab('sogo')" :class="currentTab === 'sogo' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors">
                        <i class="fas fa-envelope w-4 text-left"></i><span>Sogo Mail</span>
                    </button>
                    <button @click="switchTab('profile')" :class="currentTab === 'profile' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/10' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors">
                        <i class="fas fa-user w-4 text-left"></i><span>Profile Akun</span>
                    </button>
                </div>

                <div class="mt-6">
                    <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider px-3 mb-2">Management</p>
                    <button type="button"
                            @click="adminExpand = !adminExpand; switchTab('admin_status')"
                            :class="['admin_status', 'admin_freeipa', 'admin_sso', 'admin_monit', 'admin_nextcloud_monitor'].includes(currentTab) ? 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-white border-red-100 dark:border-red-900/50' : 'text-slate-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-800/60 border-transparent'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition-colors border">
                        <span class="flex items-center space-x-3"><i class="fas fa-tools w-4 text-left"></i><span>Admin Dashboard</span></span>
                        <span class="text-[10px] transition-transform duration-200" :class="adminExpand ? 'rotate-180' : ''">▼</span>
                    </button>

                    <div x-show="adminExpand" x-transition class="mt-1 ml-4 pl-2 border-l border-slate-200 dark:border-slate-800 space-y-1">
                        <button type="button" @click="switchTab('admin_nextcloud_monitor')" :class="currentTab === 'admin_nextcloud_monitor' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-100 dark:bg-gray-800/40' : 'text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white'" class="w-full text-left px-3 py-2 rounded-md text-xs transition-colors flex items-center gap-2"><i class="fas fa-cloud text-[10px]"></i> Nextcloud Monitor</button>
                        <button type="button" @click="switchTab('admin_freeipa')" :class="currentTab === 'admin_freeipa' ? 'text-red-600 dark:text-red-400 font-bold bg-slate-100 dark:bg-gray-800/40' : 'text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white'" class="w-full text-left px-3 py-2 rounded-md text-xs transition-colors flex items-center gap-2"><i class="fas fa-key text-[10px]"></i> FreeIPA Directory</button>
                        <button type="button" @click="switchTab('admin_sso')" :class="currentTab === 'admin_sso' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-100 dark:bg-gray-800/40' : 'text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white'" class="w-full text-left px-3 py-2 rounded-md text-xs transition-colors flex items-center gap-2"><i class="fas fa-shield-alt text-[10px]"></i> Keycloak SSO</button>
                        <button type="button" @click="switchTab('admin_monit')" :class="currentTab === 'admin_monit' ? 'text-orange-600 dark:text-orange-400 font-bold bg-slate-100 dark:bg-gray-800/40' : 'text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white'" class="w-full text-left px-3 py-2 rounded-md text-xs transition-colors flex items-center gap-2"><i class="fas fa-chart-bar text-[10px]"></i> Monit Grafana</button>
                        <a href="/log-viewer" target="_blank" class="w-full text-left px-3 py-2 rounded-md text-xs text-slate-500 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800/40 transition-colors flex items-center gap-2 border border-dashed border-slate-200 dark:border-gray-800/60 mt-2"><i class="fas fa-cog text-[10px]"></i> Open LogViewer</a>
                    </div>
                </div>
            </div>

            <div class="p-5 border-t border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-[#0d131f]">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-600/10 dark:bg-blue-600/20 border border-blue-200 dark:border-blue-800 flex items-center justify-center text-blue-600">
                        <i class="fas fa-user-shield text-xs"></i>
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                        <span class="text-[9px] bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/40 px-1.5 py-0.2 rounded font-bold uppercase tracking-wide">Admin Role</span>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-screen overflow-hidden">

            <header class="h-16 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-[#111827]/60 backdrop-blur-md flex items-center justify-between px-8 z-40 flex-shrink-0 transition-colors duration-200">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest">
                    <span>LogStack Central Enterprise</span>
                </div>

                <div class="flex items-center gap-4">
                    <button onclick="toggleTheme()" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 text-slate-500 dark:text-yellow-400 border border-slate-200 dark:border-slate-700 hover:scale-105 transition-all shadow-sm">
                        <i class="fas fa-sun block dark:hidden text-xs"></i>
                        <i class="fas fa-moon hidden dark:block text-xs"></i>
                    </button>

                    <a href="{{ route('logout') }}" class="h-9 px-4 flex items-center justify-center bg-red-50 dark:bg-red-950 hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/40 text-xs font-bold rounded-xl transition-all shadow-sm uppercase tracking-wider">
                        <i class="fas fa-sign-out-alt mr-2"></i> Keluar Sesi
                    </a>
                </div>
            </header>

            <main class="flex-1 p-8 overflow-y-auto bg-white dark:bg-[#0b0e14]">

                @include('admin.dashboard.main')

                <div x-show="currentTab === 'nextcloud'" x-transition x-cloak>
                    @include('admin.nextcloud.panel')
                </div>

                <div x-show="currentTab === 'admin_nextcloud_monitor'" x-transition x-cloak>
                    @include('admin.nextcloud.monitor')
                </div>

                <div x-show="currentTab === 'odoo'" x-transition x-cloak>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">💼 Odoo ERP System</h1>
                    <a href="https://erp.logstack.web.id" target="_blank" class="mt-4 inline-block bg-blue-600 text-white font-bold text-xs px-5 py-2.5 rounded-lg">Open Odoo ➔</a>
                </div>

                <div x-show="currentTab === 'sogo'" x-transition x-cloak>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">📧 SOGO Mail Services</h1>
                    <a href="https://mbox.logstack.web.id/?oidc=1" target="_blank" class="mt-4 inline-block bg-blue-600 text-white font-bold text-xs px-5 py-2.5 rounded-lg">Open SOGO ➔</a>
                </div>

                <div x-show="currentTab === 'profile'" x-transition x-cloak>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">👤 Profile Akun SSO</h1>
                    <p class="mt-2 text-purple-600 dark:text-purple-400 font-bold text-xs">Administrator Privileges Active</p>
                </div>

                @include('admin.dashboard.admin_status')

                {{-- 🚀 SAFE INDUK: Memanggil file include FreeIPA bersih tanpa lemparan variabel mati --}}
                @include('admin.dashboard.admin_freeipa')

                <div x-show="currentTab === 'admin_sso'" x-transition x-cloak class="space-y-6">
                    <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">🛡️ Keycloak Identity Provider</h1>
                            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Gerbang otorisasi protokol OAuth2 dan jembatan verifikasi token.</p>
                        </div>
                        <a href="https://sso.logstack.web.id" target="_blank" class="bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 font-bold text-xs px-4 py-2 rounded-xl transition-colors">Launch Console ➔</a>
                    </div>
                </div>

                <div x-show="currentTab === 'admin_monit'" x-transition x-cloak class="space-y-6">
                    <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">📊 Grafana Metrics Monitoring</h1>
                            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Visualisasi data performa hardware server dan throughput I/O jaringan.</p>
                        </div>
                        <a href="https://monit.logstack.web.id" target="_blank" class="bg-orange-600 text-white text-xs font-bold py-2 rounded-lg mt-4 block tracking-wide">Launch Dashboard ➔</a>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <div x-show="showAddModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
         x-transition x-cloak
         x-data="{ password: '', password_confirmation: '' }"
         @click.away="showAddModal = false; password = ''; password_confirmation = '';">

        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-lg p-6 rounded-2xl shadow-2xl relative">

            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-user-plus text-blue-500"></i> Daftarkan User Baru (FreeIPA LDAP)
                </h3>
                <button @click="showAddModal = false; password = ''; password_confirmation = '';" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm">✕</button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" @submit="showAddModal = false">
                @csrf
                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">Username (UID)</label>
                        <input type="text" name="username" required placeholder="contoh: tuying" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500 font-mono">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">Nama Depan</label>
                            <input type="text" name="first_name" required placeholder="Nama Depan" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">Nama Belakang</label>
                            <input type="text" name="last_name" required placeholder="Nama Belakang" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">Alamat Email Jaringan</label>
                        <input type="email" name="email" required placeholder="tuying@logstack.web.id" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500 font-mono">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">Grup Otorisasi / Role</label>
                        <select name="group" required class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500">
                            <option value="dash_user">User Biasa (dash_user)</option>
                            <option value="dash_admin">Administrator (dash_admin)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">Password Akun Default</label>
                        <input type="password" name="password" x-model="password" required placeholder="••••••••" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" x-model="password_confirmation" required placeholder="••••••••"
                               :class="password_confirmation && password !== password_confirmation ? 'border-rose-500 focus:border-rose-500 bg-rose-500/5' : 'border-slate-200 dark:border-slate-700 focus:border-blue-500 bg-slate-50 dark:bg-slate-800/50'"
                               class="w-full text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none transition-colors">

                        <div x-show="password_confirmation && password !== password_confirmation" x-transition class="text-[11px] font-bold text-rose-500 mt-1.5 flex items-center gap-1">
                            <i class="fas fa-exclamation-triangle"></i> Password tidak cocok! Harap periksa kembali inputan Anda.
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-4 mt-6">
                    <button type="button" @click="showAddModal = false; password = ''; password_confirmation = '';" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            :disabled="password !== password_confirmation"
                            :class="password !== password_confirmation ? 'opacity-40 cursor-not-allowed bg-slate-400 text-slate-600 shadow-none' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/10'"
                            class="font-bold text-xs px-5 py-2 rounded-xl transition-all flex items-center gap-1">
                        Daftarkan User ➔
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('live-clock').textContent = now.toLocaleTimeString('en-US', { hour12: false });
        }
        setInterval(updateClock, 1000); updateClock();
    </script>
</body>
</html>
