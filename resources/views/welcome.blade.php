<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | LogStack Central</title>
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
        .app-icon-mini { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.75rem; font-weight: bold; }
        .slogan-gradient { background: linear-gradient(to right, #3b82f6, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-white dark:bg-[#0b0e14] text-slate-900 dark:text-white min-h-screen transition-colors duration-300 flex flex-col">

    <div id="preloader">
        <div class="loader-ring mb-4"></div>
        <div class="text-[10px] font-black text-blue-600 uppercase tracking-[0.4em] animate-pulse">LogStack</div>
    </div>

    <nav class="fixed top-0 w-full px-6 md:px-10 h-20 flex items-center justify-between z-50 bg-white/60 dark:bg-[#0b0e14]/60 backdrop-blur-lg border-b border-slate-100 dark:border-slate-800">
        <a href="/" class="flex items-center gap-3 group">
            <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-600/20 group-hover:scale-110 transition-all">
                <i class="fas fa-layer-group text-sm"></i>
            </div>
            <span class="text-lg font-bold tracking-tighter uppercase italic">LOG<span class="text-blue-600">STACK</span></span>
        </a>
        <div class="flex items-center gap-3">
            <button onclick="toggleTheme()" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-yellow-400 border border-slate-100 dark:border-slate-700 hover:scale-105 transition-all">
                <i class="fas fa-sun block dark:hidden text-sm"></i>
                <i class="fas fa-moon hidden dark:block text-sm"></i>
            </button>
            <a href="{{ route('login.sso') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold rounded-lg transition-all shadow-lg shadow-blue-600/20 uppercase tracking-wider">
                Masuk SSO
            </a>
        </div>
    </nav>

    <div class="flex-grow flex items-center justify-center pt-20">
        <main class="px-6 max-w-5xl w-full text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight mb-4">
                Empowering <span class="slogan-gradient italic">Autonomy.</span> <br class="hidden md:block"> Unified.
            </h1>
            <p class="text-slate-500 dark:text-gray-400 text-sm md:text-base mb-12 max-w-xl mx-auto font-medium">
                Pusat kendali infrastruktur digital terintegrasi untuk kedaulatan data dan efisiensi manajemen node korporasi.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-left mb-12">
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-blue-500 transition-all group cursor-default">
                    <i class="fas fa-fingerprint text-blue-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight">Identity Hub</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Autentikasi tersentralisasi dengan protokol Keycloak & FreeIPA.</p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-emerald-500 transition-all group cursor-default">
                    <i class="fas fa-database text-emerald-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight">Data Sovereign</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Data tetap milik Anda sepenuhnya di infrastruktur mandiri.</p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-purple-500 transition-all group cursor-default">
                    <i class="fas fa-rocket text-purple-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight">Rapid Ecosystem</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Ekosistem aplikasi bisnis yang siap digunakan dalam hitungan menit.</p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-orange-500 transition-all group cursor-default">
                    <i class="fas fa-network-wired text-orange-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight">Node Synergy</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Sinkronisasi mulus antar VM Cloud, ERP, dan Mail Server.</p>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-600 uppercase tracking-[0.3em] mb-6 italic">Integrated Infrastructure</p>
                <div class="flex flex-wrap justify-center gap-6 items-center grayscale opacity-40 hover:opacity-100 transition-all duration-500">
                    <div class="flex items-center gap-2"><div class="app-icon-mini" style="background: linear-gradient(135deg, #1D3B6B, #2563EB)">✉</div><span class="text-[10px] font-bold">SOGo</span></div>
                    <div class="flex items-center gap-2"><div class="app-icon-mini" style="background: linear-gradient(135deg, #0D3B2E, #059669)">N</div><span class="text-[10px] font-bold">Nextcloud</span></div>
                    <div class="flex items-center gap-2"><div class="app-icon-mini" style="background: linear-gradient(135deg, #3B2900, #D97706)">O</div><span class="text-[10px] font-bold">Odoo</span></div>
                    <div class="flex items-center gap-2"><div class="app-icon-mini" style="background: linear-gradient(135deg, #2D1B69, #7C3AED)">🔑</div><span class="text-[10px] font-bold">Keycloak</span></div>
                    <div class="flex items-center gap-2"><div class="app-icon-mini" style="background: linear-gradient(135deg, #1a1a2e, #16213e)">🛡</div><span class="text-[10px] font-bold">FreeIPA</span></div>
                </div>
            </div>
        </main>
    </div>

    <footer class="w-full py-6 text-center border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-black/20">
        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-600 uppercase tracking-[0.4em]">
            &copy; 2026 LOGSTACK CENTRAL &bull; PROJECT SOVEREIGN INDONESIA
        </p>
    </footer>
</body>
</html>
