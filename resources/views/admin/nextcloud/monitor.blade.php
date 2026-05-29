<div class="space-y-6" x-data="{
    activeUser: '',
    activeName: '',
    showQuotaModal: false,
    searchQuery: '',
    currentPage: 1,
    itemsPerPage: 10,
    userList: [
        @foreach($ncUserStorageList as $u)
        {
            username: '{{ $u['username'] }}',
            fullname: '{{ addslashes($u['fullname']) }}',
            used: '{{ $u['used'] }}',
            free: '{{ $u['free'] }}',
            total: '{{ $u['total'] }}'
        },
        @endforeach
    ],
    get filteredList() {
        if (!this.searchQuery) return this.userList;
        const q = this.searchQuery.toLowerCase();
        return this.userList.filter(u => u.username.toLowerCase().includes(q) || u.fullname.toLowerCase().includes(q));
    },
    get paginatedList() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        return this.filteredList.slice(start, start + this.itemsPerPage);
    },
    get totalPages() {
        return Math.ceil(this.filteredList.length / this.itemsPerPage) || 1;
    }
}"
x-init="$watch('searchQuery', () => currentPage = 1)">

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
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4 border-b border-slate-200 dark:border-slate-800 pb-3">
            <span class="text-xs font-bold text-slate-700 dark:text-gray-400 flex items-center gap-2">
                <i class="fas fa-users-cog text-blue-500"></i> Alokasi Manajemen Kuota Storage per Akun User
            </span>
            <div class="relative w-full sm:w-56">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="fas fa-search text-[10px]"></i>
                </span>
                <input type="text" x-model="searchQuery" placeholder="Cari user..."
                       class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl pl-9 pr-3 py-2 text-xs focus:outline-none focus:border-blue-500 transition-colors shadow-sm font-mono">
            </div>
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
                    <template x-for="u in paginatedList" :key="u.username">
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 font-semibold">
                                <p class="text-slate-900 dark:text-white font-bold" x-text="u.fullname"></p>
                                <span class="text-[10px] text-slate-400 font-mono font-medium" x-text="'@ ' + u.username"></span>
                            </td>
                            <td class="py-3.5 text-orange-600 dark:text-orange-400 font-bold" x-text="u.used"></td>
                            <td class="py-3.5 text-emerald-600 dark:text-emerald-400 font-medium" x-text="u.free"></td>
                            <td class="py-3.5">
                                <span class="bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 font-mono font-bold rounded px-2 py-0.5" x-text="u.total"></span>
                            </td>
                            <td class="py-3.5 text-center">
                                <button type="button"
                                        @click="activeUser = u.username; activeName = u.fullname; showQuotaModal = true"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] px-3 py-1.5 rounded-lg transition-colors shadow-sm flex items-center gap-1 mx-auto">
                                    <i class="fas fa-edit text-[10px]"></i> Rubah Kuota
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredList.length === 0" x-cloak>
                        <td colspan="5" class="py-8 text-center text-slate-400 dark:text-gray-500 font-medium">
                            <i class="fas fa-search text-xl mb-2 opacity-50 block"></i>
                            Tidak ada data user yang ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div x-show="filteredList.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] font-semibold text-slate-500 dark:text-gray-400 pt-3 border-t border-slate-200 dark:border-slate-800 mt-3" x-cloak>
            <div>
                Menampilkan <span x-text="Math.min((currentPage - 1) * itemsPerPage + 1, filteredList.length)" class="text-slate-900 dark:text-white font-mono"></span>
                - <span x-text="Math.min(currentPage * itemsPerPage, filteredList.length)" class="text-slate-900 dark:text-white font-mono"></span>
                dari <span x-text="filteredList.length" class="text-blue-600 font-mono font-bold"></span> user
            </div>
            <div class="flex items-center gap-1.5">
                <button @click="currentPage > 1 ? currentPage-- : null" :disabled="currentPage === 1"
                        :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : 'bg-white dark:bg-[#0b0e14] hover:border-slate-300 dark:hover:border-slate-600'"
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm transition-colors">
                    <i class="fas fa-chevron-left mr-1"></i> Prev
                </button>
                <span class="px-3 font-mono font-bold">Page <span x-text="currentPage"></span> / <span x-text="totalPages"></span></span>
                <button @click="currentPage < totalPages ? currentPage++ : null" :disabled="currentPage === totalPages"
                        :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : 'bg-white dark:bg-[#0b0e14] hover:border-slate-300 dark:hover:border-slate-600'"
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm transition-colors">
                    Next <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- 🔥 POP-UP MODAL ALPINJS (FIX DARK MODE STYLED) --}}
    <div x-show="showQuotaModal" 
         class="fixed inset-0 z-[110] flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
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
