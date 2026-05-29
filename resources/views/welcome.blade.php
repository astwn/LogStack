<x-layouts.app title="Welcome" loaderText="LogStack">
@php
    $brand = \App\Services\BrandingService::get();
@endphp

    <nav class="fixed top-0 w-full px-6 md:px-10 h-20 flex items-center justify-between z-50 bg-white/60 dark:bg-[#0b0e14]/60 backdrop-blur-lg border-b border-slate-100 dark:border-slate-800">
        <a href="/" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-all flex-shrink-0"
                 style="background-color: {{ $brand['primary_color'] }}">
                <i class="fas {{ $brand['app_logo_icon'] }} text-sm"></i>
            </div>
            <span class="text-lg font-bold tracking-tighter uppercase italic text-slate-900 dark:text-white">{{ $brand['app_name'] }}<span style="color: {{ $brand['primary_color'] }}">{{ substr($brand['app_full_name'], strlen($brand['app_name'])) }}</span></span>
        </a>
        <div class="flex items-center gap-3">
            <button @click="isDark = !isDark" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-yellow-400 border border-slate-100 dark:border-slate-700 hover:scale-105 transition-all">
                <i class="fas fa-sun text-sm" x-show="!isDark" x-cloak></i>
                <i class="fas fa-moon text-sm" x-show="isDark" x-cloak></i>
            </button>
            <a href="{{ route('login.sso') }}" class="px-5 py-2.5 text-white text-[11px] font-bold rounded-lg transition-all shadow-lg uppercase tracking-wider"
               style="background-color: {{ $brand['primary_color'] }}">
                Masuk SSO
            </a>
        </div>
    </nav>

    <div class="flex-grow flex items-center justify-center pt-20">
        <main class="px-6 max-w-5xl w-full text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight mb-4 text-slate-900 dark:text-white">
                Empowering <span class="slogan-gradient italic">Autonomy.</span> <br class="hidden md:block"> Unified.
            </h1>
            <p class="text-slate-500 dark:text-gray-400 text-sm md:text-base mb-8 md:mb-12 max-w-xl mx-auto font-medium">
                {{ $brand['app_tagline'] }}
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-left mb-12">
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-blue-500 transition-all cursor-default">
                    <i class="fas fa-fingerprint text-blue-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight text-slate-900 dark:text-white">Identity Hub</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Autentikasi tersentralisasi dengan protokol Keycloak & FreeIPA.</p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-emerald-500 transition-all cursor-default">
                    <i class="fas fa-database text-emerald-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight text-slate-900 dark:text-white">Data Sovereign</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Data tetap milik Anda sepenuhnya di infrastruktur mandiri.</p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-purple-500 transition-all cursor-default">
                    <i class="fas fa-rocket text-purple-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight text-slate-900 dark:text-white">Rapid Ecosystem</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Ekosistem aplikasi bisnis yang siap digunakan dalam hitungan menit.</p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-orange-500 transition-all cursor-default">
                    <i class="fas fa-network-wired text-orange-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight text-slate-900 dark:text-white">Node Synergy</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Sinkronisasi mulus antar VM Cloud, ERP, dan Mail Server.</p>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-600 uppercase tracking-[0.3em] mb-6 italic">Integrated Infrastructure</p>
                <div class="flex flex-wrap justify-center gap-6 items-center grayscale opacity-40 hover:opacity-100 transition-all duration-500">
                    <div class="flex items-center gap-2">
                        <div class="app-icon-mini" style="background: linear-gradient(135deg, #1D3B6B, #2563EB)">✉</div>
                        <span class="text-[10px] font-bold text-slate-900 dark:text-white">SOGo</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="app-icon-mini" style="background: linear-gradient(135deg, #0D3B2E, #059669)">N</div>
                        <span class="text-[10px] font-bold text-slate-900 dark:text-white">Nextcloud</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="app-icon-mini" style="background: linear-gradient(135deg, #3B2900, #D97706)">O</div>
                        <span class="text-[10px] font-bold text-slate-900 dark:text-white">Odoo</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="app-icon-mini" style="background: linear-gradient(135deg, #2D1B69, #7C3AED)">🔑</div>
                        <span class="text-[10px] font-bold text-slate-900 dark:text-white">Keycloak</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="app-icon-mini" style="background: linear-gradient(135deg, #1a1a2e, #16213e)">🛡</div>
                        <span class="text-[10px] font-bold text-slate-900 dark:text-white">FreeIPA</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <footer class="w-full py-6 text-center border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-black/20">
        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-600 uppercase tracking-[0.4em]">
            &copy; 2026 {{ strtoupper($brand['footer_text']) }}
        </p>
    </footer>

    <script>
        localStorage.removeItem('activeTab');
        localStorage.removeItem('userActiveTab');
    </script>

</x-layouts.app>
