<div class="space-y-6"
     x-data="{
        files: [],
        loading: true,
        showCreateModal: false,
        showDeleteModal: false,
        fileToDelete: '',
        newFileName: '',
        fileType: 'docx',
        searchQuery: '',
        docToast: { show: false, type: 'success', message: '' },
        currentPage: 1,
        itemsPerPage: 10,

        showToast(type, message) {
            this.docToast = { show: true, type, message };
            setTimeout(() => { this.docToast.show = false; }, 4000);
        },

        get filteredFiles() {
            if (this.searchQuery === '') return this.files;
            return this.files.filter(file => file.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
        },
        get paginatedFiles() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredFiles.slice(start, start + this.itemsPerPage);
        },
        get totalPages() {
            return Math.ceil(this.filteredFiles.length / this.itemsPerPage) || 1;
        },

        fetchFiles() {
            this.loading = true;
            fetch('/api/documents/list')
                .then(res => res.json())
                .then(data => {
                    this.files = data;
                    this.loading = false;
                })
                .catch(err => {
                    console.error(err);
                    this.loading = false;
                    this.showToast('error', 'Gagal memuat daftar dokumen.');
                });
        },
        createDocument() {
            if(!this.newFileName) return;
            let baseName = this.newFileName.replace(/\.(docx|xlsx|pptx)$/i, '');
            let name = baseName + '.' + this.fileType;

            this.showCreateModal = false;
            window.open('/document/edit?file=' + encodeURIComponent(name), '_blank');
            setTimeout(() => { this.fetchFiles(); }, 2000);
            this.showToast('success', 'Dokumen ' + name + ' berhasil dibuat.');

            this.newFileName = '';
            this.fileType = 'docx';
        },
        confirmDeletePopup(fileName) {
            this.fileToDelete = fileName;
            this.showDeleteModal = true;
        },
        executeDelete() {
            if(!this.fileToDelete) return;

            fetch('/api/documents/delete', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ file: this.fileToDelete })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    this.fetchFiles();
                    this.showDeleteModal = false;
                    this.showToast('success', 'Dokumen ' + this.fileToDelete + ' berhasil dihapus.');
                    this.fileToDelete = '';
                } else {
                    this.showToast('error', 'Gagal menghapus dokumen.');
                }
            })
            .catch(err => {
                console.error(err);
                this.showToast('error', 'Terjadi kesalahan saat menghapus dokumen.');
            });
        }
     }"
     x-init="fetchFiles(); $watch('searchQuery', () => currentPage = 1)">

    {{-- Toast Notifikasi --}}
    <div x-show="docToast.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 z-[9999] max-w-sm w-full"
         x-cloak>
        <div class="flex items-start p-4 rounded-2xl shadow-lg backdrop-blur-sm border gap-3"
             :class="docToast.type === 'success'
                ? 'bg-emerald-50 dark:bg-emerald-900/40 border-emerald-200 dark:border-emerald-800'
                : 'bg-rose-50 dark:bg-rose-900/40 border-rose-200 dark:border-rose-800'">
            <i class="fas mt-0.5 text-lg flex-shrink-0"
               :class="docToast.type === 'success' ? 'fa-check-circle text-emerald-500' : 'fa-exclamation-circle text-rose-500'"></i>
            <div class="flex-1">
                <p class="text-xs font-bold uppercase tracking-wide mb-0.5"
                   :class="docToast.type === 'success' ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300'"
                   x-text="docToast.type === 'success' ? 'Berhasil' : 'Gagal'"></p>
                <p class="text-xs leading-relaxed"
                   :class="docToast.type === 'success' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                   x-text="docToast.message"></p>
            </div>
            <button @click="docToast.show = false"
                    class="flex-shrink-0 transition-colors"
                    :class="docToast.type === 'success' ? 'text-emerald-400 hover:text-emerald-600' : 'text-rose-400 hover:text-rose-600'">✕</button>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-600 text-white rounded-xl flex items-center justify-center text-sm shadow-md shadow-blue-600/20">
                    <i class="fas fa-folder-open"></i>
                </div>
                Workspace Documents
            </h1>
            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-2">
                Kelola dan edit dokumen Anda secara real-time langsung dari server Nextcloud Storage.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                <button @click="fetchFiles()" class="w-8 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-gray-400 rounded-xl transition-colors shadow-sm flex-shrink-0" title="Refresh">
                    <i class="fas fa-sync-alt text-xs" :class="loading ? 'animate-spin' : ''"></i>
                </button>

            <div class="relative w-full sm:w-56 lg:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-[10px] text-slate-400"></i>
                </div>
                <input type="text" x-model="searchQuery" placeholder="Cari dokumen..."
                       class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl pl-9 pr-3 py-2 text-xs focus:outline-none focus:border-blue-500 transition-colors shadow-sm font-mono">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button @click="showCreateModal = true" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/10 font-bold text-xs px-4 py-2 rounded-xl transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                    <i class="fas fa-plus"></i> <span>Buat Dokumen</span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="loading" class="flex flex-col items-center justify-center py-12 space-y-3">
        <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-xs text-slate-400 font-medium">Menghubungkan ke Nextcloud Storage...</p>
    </div>

    <div x-show="!loading && files.length === 0" class="flex flex-col items-center justify-center py-16 text-center bg-white dark:bg-[#111827] border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm mt-4" x-cloak>
        <div class="w-12 h-12 bg-slate-100 dark:bg-gray-800/60 text-slate-400 rounded-full flex items-center justify-center text-xl mb-3">
            <i class="fas fa-file-alt"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-700 dark:text-white">Belum ada dokumen</h3>
        <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1">Klik tombol 'Buat Dokumen' untuk membuat file pertama Anda.</p>
    </div>

    <div x-show="!loading && files.length > 0 && filteredFiles.length === 0" class="flex flex-col items-center justify-center py-16 text-center bg-white dark:bg-[#111827] border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm mt-4" x-cloak>
        <div class="w-12 h-12 bg-slate-100 dark:bg-gray-800/60 text-slate-400 rounded-full flex items-center justify-center text-xl mb-3">
            <i class="fas fa-search"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-700 dark:text-white">Dokumen tidak ditemukan</h3>
        <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1">Tidak ada file yang cocok dengan pencarian "<span x-text="searchQuery" class="font-bold text-slate-700 dark:text-slate-300"></span>".</p>
    </div>

    <div x-show="!loading && filteredFiles.length > 0" class="w-full bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl mt-4 shadow-sm overflow-hidden" x-cloak>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-[#0d131f] border-b border-slate-200 dark:border-slate-800 text-[11px] text-slate-500 dark:text-gray-400 uppercase tracking-widest">
                        <th class="px-5 py-4 font-bold">Nama File</th>
                        <th class="px-5 py-4 font-bold">Ukuran</th>
                        <th class="px-5 py-4 font-bold">Terakhir Diubah</th>
                        <th class="px-5 py-4 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    <template x-for="file in paginatedFiles" :key="file.name">
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm flex-shrink-0"
                                         :class="{
                                            'bg-blue-500/10 text-blue-500': file.ext === 'docx' || file.ext === 'doc' || file.ext === 'odt',
                                            'bg-emerald-500/10 text-emerald-500': file.ext === 'xlsx' || file.ext === 'xls' || file.ext === 'ods' || file.ext === 'csv',
                                            'bg-orange-500/10 text-orange-500': file.ext === 'pptx' || file.ext === 'ppt' || file.ext === 'odp',
                                            'bg-rose-500/10 text-rose-500': file.ext === 'pdf',
                                            'bg-purple-500/10 text-purple-500': ['mp4','mkv','avi','mov','webm'].includes(file.ext),
                                            'bg-sky-500/10 text-sky-500': ['jpg','jpeg','png','gif','webp','svg'].includes(file.ext),
                                            'bg-slate-500/10 text-slate-400 dark:text-slate-500': !['docx','doc','odt','xlsx','xls','ods','csv','pptx','ppt','odp','pdf','mp4','mkv','avi','mov','webm','jpg','jpeg','png','gif','webp','svg'].includes(file.ext),
                                         }">
                                        <i class="fas" :class="{
                                            'fa-file-word': file.ext === 'docx' || file.ext === 'doc' || file.ext === 'odt',
                                            'fa-file-excel': file.ext === 'xlsx' || file.ext === 'xls' || file.ext === 'ods' || file.ext === 'csv',
                                            'fa-file-powerpoint': file.ext === 'pptx' || file.ext === 'ppt' || file.ext === 'odp',
                                            'fa-file-pdf': file.ext === 'pdf',
                                            'fa-file-video': ['mp4','mkv','avi','mov','webm'].includes(file.ext),
                                            'fa-file-image': ['jpg','jpeg','png','gif','webp','svg'].includes(file.ext),
                                            'fa-file-alt': !['docx','doc','odt','xlsx','xls','ods','csv','pptx','ppt','odp','pdf','mp4','mkv','avi','mov','webm','jpg','jpeg','png','gif','webp','svg'].includes(file.ext),
                                        }"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="file.name"></h3>
                                        <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-0.5" x-text="file.ext.toUpperCase() + ' Document'"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs font-medium text-slate-600 dark:text-gray-300 whitespace-nowrap" x-text="file.size"></td>
                            <td class="px-5 py-4 text-xs font-medium text-slate-600 dark:text-gray-300 whitespace-nowrap">
                                <span class="flex items-center gap-1.5"><i class="far fa-clock text-slate-400 dark:text-gray-500"></i> <span x-text="file.updated_at"></span></span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a :href="'/document/edit?file=' + encodeURIComponent(file.name)" target="_blank"
                                       class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 border border-transparent dark:border-blue-800/30" title="Buka Dokumen">
                                        <i class="fas fa-external-link-alt"></i> Buka
                                    </a>
                                    <button @click.prevent="confirmDeletePopup(file.name)"
                                            class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 border border-transparent dark:border-red-800/30" title="Hapus Dokumen">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] font-semibold text-slate-500 dark:text-gray-400 px-5 py-3 border-t border-slate-200 dark:border-slate-800">
            <div>
                Menampilkan <span x-text="Math.min((currentPage - 1) * itemsPerPage + 1, filteredFiles.length)" class="text-slate-900 dark:text-white font-mono"></span>
                - <span x-text="Math.min(currentPage * itemsPerPage, filteredFiles.length)" class="text-slate-900 dark:text-white font-mono"></span>
                dari <span x-text="filteredFiles.length" class="text-blue-600 font-mono font-bold"></span> dokumen
            </div>
            <div class="flex items-center gap-1.5">
                <button @click="currentPage > 1 ? currentPage-- : null" :disabled="currentPage === 1"
                        :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : 'bg-white dark:bg-[#0b0e14] hover:border-slate-300'"
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm transition-colors">
                    <i class="fas fa-chevron-left mr-1"></i> Prev
                </button>
                <span class="px-3 font-mono font-bold">Page <span x-text="currentPage"></span> / <span x-text="totalPages"></span></span>
                <button @click="currentPage < totalPages ? currentPage++ : null" :disabled="currentPage === totalPages"
                        :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : 'bg-white dark:bg-[#0b0e14] hover:border-slate-300'"
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm transition-colors">
                    Next <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30 rounded-xl p-4 mt-8 flex items-start gap-4 shadow-sm">
        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="fas fa-cloud-upload-alt text-xs"></i>
        </div>
        <div>
            <h4 class="text-xs font-bold text-blue-700 dark:text-blue-400">Sinkronisasi Nextcloud Aktif</h4>
            <p class="text-[11px] text-blue-600/90 dark:text-blue-300/70 mt-1 leading-relaxed">
                Setiap dokumen disimpan dan di-autosave otomatis ke direktori <span class="font-mono bg-blue-100 dark:bg-blue-900/40 px-1.5 py-0.5 rounded text-blue-700 dark:text-blue-300">/Documents/{{ Auth::user()->username ?? 'user' }}</span>. Jika file belum ada, sistem otomatis membuat file kosong melalui callback OnlyOffice.
            </p>
        </div>
    </div>

    <div x-show="showCreateModal"
         class="fixed inset-0 z-[110] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
         x-transition x-cloak>
        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-lg p-6 rounded-2xl shadow-2xl relative"
             @click.away="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-file-word text-blue-500"></i> Buat Dokumen Baru
                </h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm">✕</button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-gray-300 mb-2">Nama File</label>
                    <input type="text" x-model="newFileName" @keydown.enter="createDocument()" placeholder="contoh: Catatan_Rapat"
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 dark:text-white mb-3">

                    <label class="block text-xs font-bold text-slate-700 dark:text-gray-300 mb-2">Tipe Dokumen</label>
                    <select x-model="fileType" class="w-full px-3 py-2 bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="docx">Word Document (.docx)</option>
                        <option value="xlsx">Excel Spreadsheet (.xlsx)</option>
                        <option value="pptx">PowerPoint Presentation (.pptx)</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-4 mt-6">
                <button type="button" @click="showCreateModal = false" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl transition-colors">Batal</button>
                <button type="button" @click="createDocument()" :disabled="!newFileName" :class="!newFileName ? 'opacity-40 cursor-not-allowed bg-slate-400 text-slate-600 shadow-none' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/10'" class="font-bold text-xs px-5 py-2 rounded-xl transition-all flex items-center gap-1">Buat & Buka ➔</button>
            </div>
        </div>
    </div>

    <div x-show="showDeleteModal"
         class="fixed inset-0 z-[120] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
         x-transition x-cloak>
        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-sm p-6 rounded-2xl shadow-2xl text-center relative"
             @click.away="showDeleteModal = false">

            <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Hapus Dokumen?</h3>
            <p class="text-xs text-slate-500 dark:text-gray-400 mb-6 leading-relaxed">
                Anda yakin ingin menghapus <span class="font-bold text-slate-800 dark:text-slate-200" x-text="fileToDelete"></span>? <br> File akan dihapus permanen.
            </p>

            <div class="flex justify-center gap-3">
                <button type="button" @click="showDeleteModal = false" class="w-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2.5 rounded-xl transition-colors">Batal</button>
                <button type="button" @click="executeDelete()" class="w-full bg-red-50 dark:bg-red-950 hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/40 font-bold text-xs px-4 py-2.5 rounded-xl transition-all">Ya, Hapus</button>
            </div>
        </div>
    </div>

</div>
