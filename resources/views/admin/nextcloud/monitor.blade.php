<div class="space-y-6" x-data="{ activeUser: '', activeName: '', showQuotaModal: false }">
    
    {{-- Notifikasi Sukses/Gagal --}}
    @if(session('success'))
        <div class="p-4 mb-4 text-xs font-bold text-emerald-800 bg-emerald-100 rounded-xl dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 mb-4 text-xs font-bold text-rose-800 bg-rose-100 rounded-xl dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200 dark:border-rose-900/30">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- Header Monitor --}}
    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">☁️ Nextcloud User Quota Center</h1>
            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-1">Audit status kapasitas partisi server OS beserta kontrol manajemen alokasi kuota storage user network.</p>
        </div>
        <button onclick="window.location.reload();" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 transition-all flex items-center gap-1">
            <i class="fas fa-sync-alt"></i> Refresh Data
        </button>
    </div>

    {{-- Widget Cards Storage Atas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-500">
                <i class="fas fa-hdd text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Storage Terpakai Server</p>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $ncStorage['used_gb'] ?? '0' }} GB</h3>
            </div>
        </div>

        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500">
                <i class="fas fa-server text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Sisa Ruang Kosong Server</p>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $ncStorage['free_gb'] ?? '0' }} GB</h3>
            </div>
        </div>

        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500">
                <i class="fas fa-cloud text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Total Kapasitas Hardisk</p>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $ncStorage['display_total'] ?? '0 GB' }}</h3>
            </div>
        </div>
    </div>

    {{-- Progress Bar Persentase OS --}}
    <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 rounded-2xl p-6">
        <div class="flex justify-between items-center mb-3 text-xs font-bold text-slate-600 dark:text-gray-400">
            <span class="flex items-center gap-2"><i class="fas fa-chart-pie text-blue-500"></i> Persentase Pemakaian Storage OS</span>
            <span class="font-mono text-blue-600 dark:text-blue-400">{{ $ncStorage['percentage'] ?? 0 }}%</span>
        </div>
        <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-3 overflow-hidden">
            <div class="bg-blue-600 h-3 rounded-full transition-all duration-1000"
                 style="width: {{ $ncStorage['percentage'] ?? 0 }}%">
            </div>
        </div>
    </div>

    {{-- TABEL DATA USER --}}
    <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
            <span class="text-xs font-bold text-slate-700 dark:text-gray-400 flex items-center gap-2">
                <i class="fas fa-users-cog text-blue-500"></i> Alokasi Manajemen Kuota Storage per Akun User
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 dark:text-gray-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="pb-3">Nama Lengkap (UID)</th>
                        <th class="pb-3">Used Space</th>
                        <th class="pb-3">Free Space</th>
                        <th class="pb-3">Total Quota</th>
                        <th class="pb-3 text-center" width="25%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 text-slate-700 dark:text-gray-300">
                    @forelse($ncUserStorageList as $userStore)
                        <tr class="hover:bg-slate-100/40 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="py-3.5 font-semibold">
                                <p class="text-slate-900 dark:text-white font-bold">{{ $userStore['fullname'] }}</p>
                                <span class="text-[10px] text-slate-400 font-mono font-medium">@ {{ $userStore['username'] }}</span>
                            </td>
                            <td class="py-3.5 text-orange-600 dark:text-orange-400 font-bold">{{ $userStore['used'] }}</td>
                            <td class="py-3.5 text-emerald-600 dark:text-emerald-400 font-medium">{{ $userStore['free'] }}</td>
                            <td class="py-3.5"><span class="bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 font-mono font-bold rounded px-2 py-0.5">{{ $userStore['total'] }}</span></td>
                            <td class="py-3.5 text-center">
                                {{-- Tombol Rubah Kuota Memicu Modal Pop-up --}}
                                <button type="button" 
                                        @click="activeUser = '{{ $userStore['username'] }}'; activeName = '{{ $userStore['fullname'] }}'; showQuotaModal = true"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] px-3 py-1.5 rounded-lg transition-colors shadow-sm flex items-center gap-1 mx-auto">
                                    <i class="fas fa-edit text-[10px]"></i> Rubah Kuota
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 dark:text-gray-500 font-medium">Belum ada data user storage Nextcloud yang berhasil disinkronisasikan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 🔥 POP-UP MODAL ALPINJS (FIX DARK MODE STYLED) --}}
    <div x-show="showQuotaModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
         x-transition
         x-cloak>
        
        <div @click.away="showQuotaModal = false" 
             class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-md p-6 rounded-2xl shadow-2xl relative">
            
            {{-- Header Modal --}}
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-cloud-download-alt text-blue-500"></i> Set Alokasi Kuota Baru
                </h3>
                <button @click="showQuotaModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm">✕</button>
            </div>

            {{-- Detail User Info --}}
            <div class="mb-5 bg-slate-50 dark:bg-[#0d131f] border border-slate-100 dark:border-slate-800/60 p-3 rounded-xl text-xs">
                <p class="text-slate-400 font-medium">Target User:</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5" x-text="activeName"></p>
                <p class="text-[10px] text-blue-500 font-mono mt-0.5">@ <span x-text="activeUser"></span></p>
            </div>

            {{-- Form Submit --}}
            <form action="{{ route('admin.nextcloud.update-quota') }}" method="POST" @submit="showQuotaModal = false">
                @csrf
                <input type="hidden" name="username" :value="activeUser">
                
                <label class="block text-[11px] font-bold uppercase text-slate-400 tracking-wider mb-2">Pilih Kapasitas Storage Baru</label>
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <label class="border border-slate-200 dark:border-slate-700 rounded-xl p-3 flex items-center gap-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <input type="radio" name="quota" value="5 GB" required class="text-blue-600">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">5 GB</span>
                    </label>
                    <label class="border border-slate-200 dark:border-slate-700 rounded-xl p-3 flex items-center gap-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <input type="radio" name="quota" value="10 GB" class="text-blue-600">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">10 GB</span>
                    </label>
                    <label class="border border-slate-200 dark:border-slate-700 rounded-xl p-3 flex items-center gap-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <input type="radio" name="quota" value="15 GB" class="text-blue-600">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">15 GB</span>
                    </label>
                    <label class="border border-slate-200 dark:border-slate-700 rounded-xl p-3 flex items-center gap-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <input type="radio" name="quota" value="20 GB" class="text-blue-600">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">20 GB</span>
                    </label>
                    <label class="col-span-2 border border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-3 flex items-center gap-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors justify-center">
                        <input type="radio" name="quota" value="default" class="text-blue-600">
                        <span class="text-xs font-bold text-slate-500 dark:text-gray-400">Kembalikan ke Default Server</span>
                    </label>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-3">
                    <button type="button" @click="showQuotaModal = false" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-5 py-2 rounded-xl transition-colors shadow-md shadow-blue-600/10">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
