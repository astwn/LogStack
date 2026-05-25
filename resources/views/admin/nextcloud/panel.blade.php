<div class="space-y-6">
    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">☁️ Nextcloud Storage Drive</h1>
            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Manajemen alokasi storage, kuota user, dan kapasitas data cloud.</p>
        </div>
        <a href="https://drive.logstack.web.id" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition-all shadow-md shadow-blue-600/10 flex items-center gap-2">
            <i class="fas fa-external-link-alt"></i> Buka Nextcloud Drive ➔
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500">
                <i class="fas fa-hdd text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Storage Terpakai</p>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $nextcloudQuota['used_gb'] }} GB</h3>
            </div>
        </div>

        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                <i class="fas fa-server text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Sisa Ruang Kosong</p>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $nextcloudQuota['free_gb'] }} GB</h3>
            </div>
        </div>

        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500">
                <i class="fas fa-cloud-upload-alt text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Total Batas Kuota Anda</p>
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
            <div class="bg-blue-600 h-3 rounded-full transition-all duration-1000"
                 style="width: {{ $nextcloudQuota['relative'] }}%">
            </div>
        </div>

        <div class="mt-4 p-3 rounded-xl bg-blue-50/40 dark:bg-blue-950/20 border border-blue-100/40 dark:border-blue-900/30 text-[11px] text-slate-500 dark:text-gray-400 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-500"></i>
            <span>Data kuota di atas disinkronisasikan secara real-time langsung menggunakan <strong>Nextcloud Provisioning OCS API</strong>.</span>
        </div>
    </div>
</div>
