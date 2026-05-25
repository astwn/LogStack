<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Portal | LogStack Central</title>
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
        currentTab: localStorage.getItem('userActiveTab') || 'main',
        isMappingLoading: false,
        switchTab(tabName) {
            this.isMappingLoading = true;
            setTimeout(() => {
                this.currentTab = tabName;
                localStorage.setItem('userActiveTab', tabName);
                this.isMappingLoading = false;
            }, 150);
        }
      }">

    <div id="preloader">
        <div class="loader-ring mb-4"></div>
        <div class="text-[10px] font-black text-blue-600 uppercase tracking-[0.4em] animate-pulse">LogStack Central</div>
    </div>

    <div x-show="isMappingLoading" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/80 dark:bg-[#0b0e14]/80 backdrop-blur-md" x-cloak>
        <div class="loader-ring mb-2"></div>
        <span class="text-[9px] font-mono tracking-widest text-slate-500 dark:text-gray-400 uppercase animate-pulse">Syncing App Stack...</span>
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
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider px-3 mb-2">Layanan Cloud</p>
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
            </div>

            <div class="p-5 border-t border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-[#0d131f]">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-600/10 dark:bg-emerald-600/20 border border-emerald-200 dark:border-emerald-800/40 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-user text-xs"></i>
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $user->name }}</p>
                        <span class="text-[9px] bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-gray-700/60 px-1.5 py-0.2 rounded font-bold uppercase tracking-wide">User Account</span>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-screen overflow-hidden">

            <header class="h-16 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-[#111827]/60 backdrop-blur-md flex items-center justify-between px-8 z-40 flex-shrink-0 transition-colors duration-200">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest">
                    <span>LogStack Central Network</span>
                </div>

                <div class="flex items-center gap-4">
                    <button onclick="toggleTheme()" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 text-slate-500 dark:text-yellow-400 border border-slate-200 dark:border-slate-700 hover:scale-105 transition-all shadow-sm">
                        <i class="fas fa-sun block dark:hidden text-xs"></i>
                        <i class="fas fa-moon hidden dark:block text-xs"></i>
                    </button>

                    <a href="{{ route('logout') }}" class="h-9 px-4 flex items-center justify-center bg-red-50 dark:bg-red-950 hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/40 text-xs font-bold rounded-xl transition-all shadow-sm uppercase tracking-wider">
                        <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                    </a>
                </div>
            </header>

            <main class="flex-1 p-8 overflow-y-auto bg-white dark:bg-[#0b0e14]">

                <div x-show="currentTab === 'main'" x-transition>
                    <div class="mb-6">
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Selamat Datang di LogStack Portal</h1>
                        <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Akses seluruh ekosistem layanan cloud terintegrasi Anda.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl">
                            <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-2">Identitas Autentikasi</p>
                            <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>Terverifikasi: {{ $user->email }}
                            </div>
                        </div>
                        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl">
                            <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-2">Waktu Lokal Sistem</p>
                            <div class="text-lg font-black text-slate-900 dark:text-white" id="live-clock">--:--:--</div>
                        </div>
                    </div>

                    <h3 class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-4">Status Layanan Aplikasi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center justify-between">
                            <div><h4 class="font-bold text-sm text-slate-900 dark:text-white">Nextcloud Drive</h4><p class="text-[11px] text-gray-400 mt-0.5">drive.logstack.web.id</p></div>
                            @if(($appsStatus['nextcloud'] ?? 'OFFLINE') === 'ONLINE')
                                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">ONLINE</span>
                            @else
                                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">OFFLINE</span>
                            @endif
                        </div>
                        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center justify-between">
                            <div><h4 class="font-bold text-sm text-slate-900 dark:text-white">Odoo ERP Suite</h4><p class="text-[11px] text-gray-400 mt-0.5">erp.logstack.web.id</p></div>
                            @if(($appsStatus['odoo'] ?? 'OFFLINE') === 'ONLINE')
                                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">ONLINE</span>
                            @else
                                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">OFFLINE</span>
                            @endif
                        </div>
                        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center justify-between">
                            <div><h4 class="font-bold text-sm text-slate-900 dark:text-white">SOGO Mailbox</h4><p class="text-[11px] text-gray-400 mt-0.5">mbox.logstack.web.id</p></div>
                            @if(($appsStatus['sogo'] ?? 'OFFLINE') === 'ONLINE')
                                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">ONLINE</span>
                            @else
                                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">OFFLINE</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div x-show="currentTab === 'nextcloud'" x-transition x-cloak class="space-y-6">
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div>
                            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">☁️ Nextcloud Storage Drive</h1>
                            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-1">Manajemen alokasi storage, kuota user, dan kapasitas data cloud.</p>
                        </div>
                        <a href="https://drive.logstack.web.id" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md transition-all flex items-center gap-2">
                            <i class="fas fa-external-link-alt"></i> Buka Nextcloud Drive ➔
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-500">
                                <i class="fas fa-hdd text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">STORAGE TERPAKAI</p>
                                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $nextcloudQuota['used_gb'] }} GB</h3>
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500">
                                <i class="fas fa-server text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">SISA RUANG KOSONG</p>
                                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $nextcloudQuota['free_gb'] }} GB</h3>
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500">
                                <i class="fas fa-cloud text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">TOTAL BATAS KUOTA ANDA</p>
                                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $nextcloudQuota['display_total'] }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 rounded-2xl p-6">
                        <div class="flex justify-between items-center mb-3 text-xs font-bold text-slate-600 dark:text-gray-400">
                            <span class="flex items-center gap-2"><i class="fas fa-chart-pie text-blue-500"></i> Persentase Pemakaian Storage</span>
                            <span class="font-mono text-blue-600 dark:text-blue-400">{{ $nextcloudQuota['relative'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-3 overflow-hidden">
                            <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" style="width: {{ min($nextcloudQuota['relative'], 100) }}%"></div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-gray-800/50 flex items-center justify-between text-[11px] text-slate-400 font-medium">
                            <span class="flex items-center gap-1"><i class="fas fa-info-circle text-blue-500"></i> Data kuota di atas disinkronisasikan secara real-time langsung menggunakan Nextcloud Provisioning OCS API.</span>
                            <span class="font-mono bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 px-2 py-0.5 rounded text-blue-500">@ {{ $user->username ?: explode('@', $user->email)[0] }}</span>
                        </div>
                    </div>
                </div>

                <div x-show="currentTab === 'odoo'" x-transition x-cloak>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">💼 Odoo ERP Enterprise System</h1>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Sistem manajemen resource bisnis, logistik, dan operasional internal.</p>
                    <a href="https://erp.logstack.web.id" target="_blank" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i> Masuk Ke Odoo Suite ➔
                    </a>
                </div>

                <div x-show="currentTab === 'sogo'" x-transition x-cloak>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">📧 SOGO Webmail Services</h1>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Layanan surat elektronik terenkripsi terpusat pada domain logstack.web.id.</p>
                    <a href="https://mbox.logstack.web.id" target="_blank" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md transition-colors">
                        <i class="fas fa-envelope-open-text mr-2"></i> Buka Kotak Masuk SOGO ➔
                    </a>
                </div>

                <div x-show="currentTab === 'profile'" x-transition x-cloak>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">👤 Manajemen Profile Akun</h1>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Detail identifikasi akun Single-Sign-On (SSO) terpusat.</p>
                    
                    <div class="mt-6 max-w-xl bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 rounded-2xl p-6 text-xs space-y-4">
                        <div class="flex justify-between border-b border-slate-100 dark:border-gray-800 pb-3">
                            <span class="text-slate-400">Nama Lengkap</span>
                            <span class="font-bold text-slate-800 dark:text-white">{{ $user->name }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 dark:border-gray-800 pb-3">
                            <span class="text-slate-400">Alamat Email</span>
                            <span class="font-bold text-slate-800 dark:text-white font-mono">{{ $user->email }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 dark:border-gray-800 pb-3">
                            <span class="text-slate-400">Status Otoritas</span>
                            <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded font-bold uppercase tracking-wide text-[9px] border border-emerald-100 dark:border-emerald-900/30">User Terverifikasi</span>
                        </div>
                    </div>
                </div>

            </main>
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
