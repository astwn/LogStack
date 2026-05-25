<div x-show="currentTab === 'main'" x-transition>
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Main Dashboard</h1>
        <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Status operasional terpusat LogStack Apps Network.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl">
            <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-2">User Session</p>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>Sesi Aktif: {{ Auth::user()->email }}
            </div>
        </div>
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl">
            <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-2">Waktu Sistem Server</p>
            <div class="text-lg font-black text-slate-900 dark:text-white" id="live-clock">--:--:--</div>
        </div>
    </div>

    <h3 class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-4">Status Integrasi Aplikasi</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center justify-between">
            <div><h4 class="font-bold text-sm text-slate-900 dark:text-white">Nextcloud Storage</h4><p class="text-[11px] text-gray-400 mt-0.5">drive.logstack.web.id</p></div>
            @if(($appsStatus['nextcloud'] ?? 'OFFLINE') === 'ONLINE')
                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">ONLINE</span>
            @else
                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">OFFLINE</span>
            @endif
        </div>
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center justify-between">
            <div><h4 class="font-bold text-sm text-slate-900 dark:text-white">Odoo ERP System</h4><p class="text-[11px] text-gray-400 mt-0.5">erp.logstack.web.id</p></div>
            @if(($appsStatus['odoo'] ?? 'OFFLINE') === 'ONLINE')
                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">ONLINE</span>
            @else
                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">OFFLINE</span>
            @endif
        </div>
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center justify-between">
            <div><h4 class="font-bold text-sm text-slate-900 dark:text-white">SOGO Mail</h4><p class="text-[11px] text-gray-400 mt-0.5">mbox.logstack.web.id</p></div>
            @if(($appsStatus['sogo'] ?? 'OFFLINE') === 'ONLINE')
                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">ONLINE</span>
            @else
                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/30 text-[10px] px-2.5 py-1 rounded-full font-bold">OFFLINE</span>
            @endif
        </div>
    </div>
</div>
