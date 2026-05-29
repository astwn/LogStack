<div class="space-y-6"
     x-data="{
        ncFiles: [],
        ncLoading: true,
        ncSearchQuery: '',
        ncShowUploadModal: false,
        ncShowDeleteModal: false,
        ncFileToDelete: '',
        ncSelectedFile: null,
        ncUploadProgress: 0,
        ncIsUploading: false,
        ncToast: { show: false, type: 'success', message: '' },
        ncCurrentPage: 1,
        ncItemsPerPage: 10,
        ncCurrentPath: '/',
        ncBreadcrumbs: [{ name: 'Root', path: '/' }],

        showToast(type, message) {
            this.ncToast = { show: true, type, message };
            setTimeout(() => { this.ncToast.show = false; }, 4000);
        },

        get filteredNcFiles() {
            let files = this.ncSearchQuery === ''
                ? this.ncFiles
                : this.ncFiles.filter(file => file.name.toLowerCase().includes(this.ncSearchQuery.toLowerCase()));
            // Sort: folder dulu, lalu file, keduanya alphabetical
            return files.slice().sort((a, b) => {
                if (a.is_dir && !b.is_dir) return -1;
                if (!a.is_dir && b.is_dir) return 1;
                return a.name.localeCompare(b.name);
            });
        },
        get paginatedNcFiles() {
            const start = (this.ncCurrentPage - 1) * this.ncItemsPerPage;
            return this.filteredNcFiles.slice(start, start + this.ncItemsPerPage);
        },
        get ncTotalPages() {
            return Math.ceil(this.filteredNcFiles.length / this.ncItemsPerPage) || 1;
        },

        fetchNcFiles() {
            this.ncLoading = true;
            this.ncCurrentPage = 1;
            const params = new URLSearchParams({ path: this.ncCurrentPath });
            fetch('/api/nextcloud/files?' + params.toString())
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        this.showToast('error', data.error);
                        this.ncFiles = [];
                    } else {
                        this.ncFiles = data;
                    }
                    this.ncLoading = false;
                })
                .catch(err => {
                    console.error('Gagal memuat file Nextcloud:', err);
                    this.ncLoading = false;
                    this.showToast('error', 'Gagal memuat file dari Nextcloud.');
                });
        },

        openFolder(folderName) {
            const newPath = this.ncCurrentPath === '/'
                ? '/' + folderName
                : this.ncCurrentPath + '/' + folderName;
            this.ncCurrentPath = newPath;
            this.ncSearchQuery = '';
            this.ncBreadcrumbs.push({ name: folderName, path: newPath });
            this.fetchNcFiles();
        },

        navigateTo(path) {
            this.ncCurrentPath = path;
            this.ncSearchQuery = '';
            const idx = this.ncBreadcrumbs.findIndex(b => b.path === path);
            if (idx !== -1) {
                this.ncBreadcrumbs = this.ncBreadcrumbs.slice(0, idx + 1);
            }
            this.fetchNcFiles();
        },

        handleFileUpload(event) {
            this.ncSelectedFile = event.target.files[0];
        },

        uploadFile() {
            if (!this.ncSelectedFile) return;

            this.ncIsUploading = true;
            this.ncUploadProgress = 0;

            const formData = new FormData();
            formData.append('file', this.ncSelectedFile);
            formData.append('path', this.ncCurrentPath);

            const progressInterval = setInterval(() => {
                if (this.ncUploadProgress < 90) {
                    this.ncUploadProgress += 10;
                }
            }, 200);

            fetch('/api/nextcloud/upload', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                clearInterval(progressInterval);
                this.ncUploadProgress = 100;
                setTimeout(() => {
                    if(data.success) {
                        this.fetchNcFiles();
                        this.ncShowUploadModal = false;
                        this.ncSelectedFile = null;
                        document.getElementById('ncFileInput').value = '';
                        this.showToast('success', 'File berhasil diunggah ke Nextcloud.');
                    } else {
                        this.showToast('error', 'Gagal mengunggah file: ' + (data.message || 'Unknown error'));
                    }
                    this.ncIsUploading = false;
                    this.ncUploadProgress = 0;
                }, 500);
            })
            .catch(err => {
                clearInterval(progressInterval);
                this.showToast('error', 'Terjadi kesalahan saat mengunggah.');
                this.ncIsUploading = false;
                this.ncUploadProgress = 0;
            });
        },

        confirmDelete(fileName) {
            this.ncFileToDelete = fileName;
            this.ncShowDeleteModal = true;
        },

        executeDelete() {
            if(!this.ncFileToDelete) return;

            fetch('/api/nextcloud/delete', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ file: this.ncFileToDelete, path: this.ncCurrentPath })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    this.fetchNcFiles();
                    this.ncShowDeleteModal = false;
                    this.showToast('success', 'File ' + this.ncFileToDelete + ' berhasil dihapus.');
                    this.ncFileToDelete = '';
                } else {
                    this.showToast('error', 'Gagal menghapus file.');
                }
            })
            .catch(err => {
                this.showToast('error', 'Terjadi kesalahan saat menghapus file.');
            });
        },

        formatDate(dateString) {
            if(!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
     }"
     x-init="fetchNcFiles(); $watch('ncSearchQuery', () => ncCurrentPage = 1)">

    {{-- Toast Notifikasi --}}
    <div x-show="ncToast.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 z-[9999] max-w-sm w-full"
         x-cloak>
        <div class="flex items-start p-4 rounded-2xl shadow-lg backdrop-blur-sm border gap-3"
             :class="ncToast.type === 'success'
                ? 'bg-emerald-50 dark:bg-emerald-900/40 border-emerald-200 dark:border-emerald-800'
                : 'bg-rose-50 dark:bg-rose-900/40 border-rose-200 dark:border-rose-800'">
            <i class="fas mt-0.5 text-lg flex-shrink-0"
               :class="ncToast.type === 'success'
                ? 'fa-check-circle text-emerald-500'
                : 'fa-exclamation-circle text-rose-500'"></i>
            <div class="flex-1">
                <p class="text-xs font-bold uppercase tracking-wide mb-0.5"
                   :class="ncToast.type === 'success' ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300'"
                   x-text="ncToast.type === 'success' ? 'Berhasil' : 'Gagal'"></p>
                <p class="text-xs leading-relaxed"
                   :class="ncToast.type === 'success' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                   x-text="ncToast.message"></p>
            </div>
            <button @click="ncToast.show = false"
                    class="flex-shrink-0 transition-colors"
                    :class="ncToast.type === 'success' ? 'text-emerald-400 hover:text-emerald-600' : 'text-rose-400 hover:text-rose-600'">✕</button>
        </div>
    </div>
    {{-- Statistik Kuota Storage --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Card: Storage Terpakai --}}
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-slate-800 p-5 rounded-2xl flex items-center gap-4 shadow-sm transition-transform hover:-translate-y-1">
            <div class="w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-500 shadow-inner">
                <i class="fas fa-hdd text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">STORAGE TERPAKAI</p>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $nextcloudQuota['used_gb'] ?? '0' }} GB</h3>
            </div>
        </div>

        {{-- Card: Sisa Ruang Kosong --}}
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-slate-800 p-5 rounded-2xl flex items-center gap-4 shadow-sm transition-transform hover:-translate-y-1">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 shadow-inner">
                <i class="fas fa-server text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">SISA RUANG KOSONG</p>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $nextcloudQuota['free_gb'] ?? '0' }} GB</h3>
            </div>
        </div>

        {{-- Card: Total Batas Kuota --}}
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-slate-800 p-5 rounded-2xl flex items-center gap-4 shadow-sm transition-transform hover:-translate-y-1">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 shadow-inner">
                <i class="fas fa-cloud text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">TOTAL BATAS KUOTA</p>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $nextcloudQuota['display_total'] ?? '0 GB' }}</h3>
            </div>
        </div>
    </div>

    {{-- Progress Bar Kapasitas --}}
    <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="flex justify-between items-center mb-3 text-xs font-bold text-slate-600 dark:text-gray-400">
            <span class="flex items-center gap-2"><i class="fas fa-chart-pie text-blue-500"></i> Persentase Pemakaian Storage</span>
            <span class="font-mono text-blue-600 dark:text-blue-400">{{ $nextcloudQuota['relative'] ?? 0 }}%</span>
        </div>

        <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-3 overflow-hidden shadow-inner relative">
            <div class="bg-blue-600 h-3 rounded-full transition-all duration-1000 ease-out"
                 style="width: {{ min($nextcloudQuota['relative'] ?? 0, 100) }}%"></div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-800/60 flex items-center justify-between text-[11px] text-slate-400 font-medium">
            <span class="flex items-center gap-1"><i class="fas fa-info-circle text-blue-500"></i> Data sinkronisasi real-time via OCS API.</span>
            <span class="font-mono bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 px-2 py-0.5 rounded text-blue-500 shadow-sm">
                @ {{ Auth::user()->username ?? explode('@', Auth::user()->email)[0] }}
            </span>
        </div>
    </div>

    {{-- MANAJER FILE NEXTCLOUD --}}
    <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-folder-tree text-emerald-500"></i> File Explorer
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1">Kelola semua file di direktori Nextcloud Anda.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">

{{-- Pencarian File --}}
<div class="relative w-full sm:w-56 group">
    
    {{-- Icon --}}
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <i class="fas fa-search text-[10px] text-slate-400"></i>
    </div>

    {{-- Input --}}
    <input
        type="text"
        x-model="ncSearchQuery"
        placeholder="Cari file..."
        class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl pl-9 pr-3 py-2 text-xs focus:outline-none focus:border-blue-500 transition-colors shadow-sm font-mono"
    >
</div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button @click="fetchNcFiles()" class="p-1.5 bg-slate-100 dark:bg-[#111827] hover:bg-slate-200 dark:hover:bg-gray-800 border border-transparent dark:border-slate-800 text-slate-600 dark:text-gray-400 rounded-lg text-xs font-bold transition-all shadow-sm flex-shrink-0" title="Refresh">
                        <i class="fas fa-sync-alt" :class="ncLoading ? 'animate-spin' : ''"></i>
                    </button>
                    <button @click="ncShowUploadModal = true" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/10 font-bold text-xs px-3 py-1.5 rounded-lg transition-all flex items-center justify-center gap-1.5 whitespace-nowrap">
                        <i class="fas fa-upload text-[10px]"></i> <span>Upload File</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Breadcrumb Navigation --}}
        <div class="flex items-center gap-1.5 text-xs mb-4 flex-wrap bg-slate-50 dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5">
            <i class="fas fa-folder-tree text-emerald-500 text-[10px]"></i>
            <template x-for="(crumb, index) in ncBreadcrumbs" :key="crumb.path">
                <div class="flex items-center gap-1.5">
                    <span x-show="index > 0" class="text-slate-300 dark:text-slate-600">/</span>
                    <button
                        @click="navigateTo(crumb.path)"
                        :class="index === ncBreadcrumbs.length - 1
                            ? 'text-slate-900 dark:text-white font-bold cursor-default'
                            : 'text-blue-600 dark:text-blue-400 hover:underline font-medium'"
                        class="transition-colors"
                        x-text="crumb.name">
                    </button>
                </div>
            </template>
        </div>

        {{-- Loading State --}}
        <div x-show="ncLoading" class="flex flex-col items-center justify-center py-8">
            <div class="w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mb-2"></div>
            <p class="text-[10px] text-slate-400 font-medium">Memuat struktur direktori...</p>
        </div>

        {{-- Empty State --}}
        <div x-show="!ncLoading && ncFiles.length === 0" class="flex flex-col items-center justify-center py-10 text-center bg-white dark:bg-[#111827] border border-dashed border-slate-200 dark:border-slate-800 rounded-xl shadow-sm" x-cloak>
            <i class="fas fa-box-open text-3xl text-slate-300 dark:text-slate-600 mb-3"></i>
            <h3 class="text-xs font-bold text-slate-700 dark:text-white">Direktori Kosong</h3>
            <p class="text-[10px] text-slate-500 dark:text-gray-400 mt-1">Belum ada file yang diunggah ke cloud.</p>
        </div>

        {{-- Tabel File --}}
        <div x-show="!ncLoading && filteredNcFiles.length > 0" class="w-full bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden" x-cloak>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-[#0d131f] border-b border-slate-200 dark:border-slate-800">
                        <tr class="text-[10px] text-slate-500 dark:text-gray-400 uppercase tracking-wider">
                            <th class="px-4 py-3 font-bold">Nama File / Folder</th>
                            <th class="px-4 py-3 font-bold w-24">Ukuran</th>
                            <th class="px-4 py-3 font-bold w-40">Dimodifikasi</th>
                            <th class="px-4 py-3 font-bold text-center w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        <template x-for="file in paginatedNcFiles" :key="file.name">
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group"
                                :class="file.is_dir ? 'cursor-pointer' : ''">
                                <td class="px-4 py-2.5" @click="file.is_dir ? openFolder(file.name) : null">
                                    <div class="flex items-center gap-3">
                                        {{-- Icon berdasarkan tipe file/folder --}}
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-xs"
                                             :class="{
                                                'bg-yellow-500/10 text-yellow-500': file.is_dir,
                                                'bg-blue-500/10 text-blue-500': !file.is_dir && ['doc','docx','odt'].includes(file.name.split('.').pop().toLowerCase()),
                                                'bg-emerald-500/10 text-emerald-500': !file.is_dir && ['xls','xlsx','ods','csv'].includes(file.name.split('.').pop().toLowerCase()),
                                                'bg-orange-500/10 text-orange-500': !file.is_dir && ['ppt','pptx','odp'].includes(file.name.split('.').pop().toLowerCase()),
                                                'bg-rose-500/10 text-rose-500': !file.is_dir && ['pdf'].includes(file.name.split('.').pop().toLowerCase()),
                                                'bg-purple-500/10 text-purple-500': !file.is_dir && ['mp4','mkv','avi','mov','webm'].includes(file.name.split('.').pop().toLowerCase()),
                                                'bg-sky-500/10 text-sky-500': !file.is_dir && ['jpg','jpeg','png','gif','webp','svg'].includes(file.name.split('.').pop().toLowerCase()),
                                                'bg-slate-500/10 text-slate-400 dark:text-slate-500': !file.is_dir && !['doc','docx','odt','xls','xlsx','ods','csv','ppt','pptx','odp','pdf','mp4','mkv','avi','mov','webm','jpg','jpeg','png','gif','webp','svg'].includes(file.name.split('.').pop().toLowerCase()),
                                             }">
                                            <i class="fas"
                                               :class="{
                                                'fa-folder': file.is_dir,
                                                'fa-file-word': !file.is_dir && ['doc','docx','odt'].includes(file.name.split('.').pop().toLowerCase()),
                                                'fa-file-excel': !file.is_dir && ['xls','xlsx','ods','csv'].includes(file.name.split('.').pop().toLowerCase()),
                                                'fa-file-powerpoint': !file.is_dir && ['ppt','pptx','odp'].includes(file.name.split('.').pop().toLowerCase()),
                                                'fa-file-pdf': !file.is_dir && ['pdf'].includes(file.name.split('.').pop().toLowerCase()),
                                                'fa-file-video': !file.is_dir && ['mp4','mkv','avi','mov','webm'].includes(file.name.split('.').pop().toLowerCase()),
                                                'fa-file-image': !file.is_dir && ['jpg','jpeg','png','gif','webp','svg'].includes(file.name.split('.').pop().toLowerCase()),
                                                'fa-file-alt': !file.is_dir && !['doc','docx','odt','xls','xlsx','ods','csv','ppt','pptx','odp','pdf','mp4','mkv','avi','mov','webm','jpg','jpeg','png','gif','webp','svg'].includes(file.name.split('.').pop().toLowerCase()),
                                               }"></i>
                                        </div>
                                        <div class="truncate max-w-[200px] sm:max-w-sm">
                                            <p class="text-xs font-semibold truncate"
                                               :class="file.is_dir ? 'text-blue-600 dark:text-blue-400' : 'text-slate-800 dark:text-gray-200'"
                                               x-text="file.name"></p>
                                            <p class="text-[10px] text-slate-400 dark:text-gray-500 mt-0.5"
                                               x-text="file.is_dir ? 'Folder' : file.name.split('.').pop().toUpperCase() + ' File'"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-[11px] text-slate-500 dark:text-gray-400 whitespace-nowrap" x-text="file.is_dir ? '--' : file.size"></td>
                                <td class="px-4 py-2.5 text-[10px] text-slate-500 dark:text-gray-400 whitespace-nowrap" x-text="formatDate(file.updated_at)"></td>
                                <td class="px-4 py-2.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        {{-- Tombol buka folder --}}
                                        <template x-if="file.is_dir">
                                            <button @click="openFolder(file.name)"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors text-blue-500 dark:text-blue-400 hover:bg-blue-500/10"
                                                    title="Buka Folder">
                                                <i class="fas fa-folder-open text-[11px]"></i>
                                            </button>
                                        </template>

                                        {{-- Tombol preview (gambar & PDF) --}}
                                        <template x-if="!file.is_dir && ['pdf','jpg','jpeg','png','gif','webp','svg'].includes(file.name.split('.').pop().toLowerCase())">
                                            <a :href="'/api/nextcloud/preview?file=' + encodeURIComponent(file.name) + '&path=' + encodeURIComponent(ncCurrentPath)"
                                               target="_blank"
                                               class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors text-emerald-500 dark:text-emerald-400 hover:bg-emerald-500/10"
                                               title="Preview">
                                                <i class="fas fa-eye text-[11px]"></i>
                                            </a>
                                        </template>

                                        {{-- Tombol download (semua file) --}}
                                        <template x-if="!file.is_dir">
                                            <a :href="'/api/nextcloud/download?file=' + encodeURIComponent(file.name) + '&path=' + encodeURIComponent(ncCurrentPath)"
                                               class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors text-blue-500 dark:text-blue-400 hover:bg-blue-500/10"
                                               title="Download">
                                                <i class="fas fa-download text-[11px]"></i>
                                            </a>
                                        </template>

                                        {{-- Tombol hapus file --}}
                                        <template x-if="!file.is_dir">
                                            <button @click.prevent="confirmDelete(file.name)"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors text-rose-500 dark:text-rose-400 hover:bg-rose-500/10"
                                                    title="Hapus File">
                                                <i class="fas fa-trash-alt text-[11px]"></i>
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] font-semibold text-slate-500 dark:text-gray-400 px-4 py-3 border-t border-slate-200 dark:border-slate-800">
                <div>
                    Menampilkan <span x-text="Math.min((ncCurrentPage - 1) * ncItemsPerPage + 1, filteredNcFiles.length)" class="text-slate-900 dark:text-white font-mono"></span>
                    - <span x-text="Math.min(ncCurrentPage * ncItemsPerPage, filteredNcFiles.length)" class="text-slate-900 dark:text-white font-mono"></span>
                    dari <span x-text="filteredNcFiles.length" class="text-blue-600 font-mono font-bold"></span> file
                </div>
                <div class="flex items-center gap-1.5">
                    <button @click="ncCurrentPage > 1 ? ncCurrentPage-- : null" :disabled="ncCurrentPage === 1"
                            :class="ncCurrentPage === 1 ? 'opacity-40 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : 'bg-white dark:bg-[#0b0e14] hover:border-slate-300'"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm transition-colors">
                        <i class="fas fa-chevron-left mr-1"></i> Prev
                    </button>
                    <span class="px-3 font-mono font-bold">Page <span x-text="ncCurrentPage"></span> / <span x-text="ncTotalPages"></span></span>
                    <button @click="ncCurrentPage < ncTotalPages ? ncCurrentPage++ : null" :disabled="ncCurrentPage === ncTotalPages"
                            :class="ncCurrentPage === ncTotalPages ? 'opacity-40 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : 'bg-white dark:bg-[#0b0e14] hover:border-slate-300'"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm transition-colors">
                        Next <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL UPLOAD --}}
    <div x-show="ncShowUploadModal" class="fixed inset-0 z-[150] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" x-transition x-cloak>
        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-sm p-6 rounded-2xl shadow-2xl relative" @click.away="!ncIsUploading ? ncShowUploadModal = false : null">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-cloud-upload-alt text-emerald-500"></i> Upload ke Nextcloud
                </h3>
                <button x-show="!ncIsUploading" @click="ncShowUploadModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm">✕</button>
            </div>

            <div class="space-y-4">
                <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-4 text-center hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors relative cursor-pointer" onclick="document.getElementById('ncFileInput').click()">
                    <input type="file" id="ncFileInput" class="hidden" @change="handleFileUpload(event)" :disabled="ncIsUploading">
                    
                    <div x-show="!ncSelectedFile">
                        <i class="fas fa-file-upload text-2xl text-slate-400 mb-2"></i>
                        <p class="text-xs font-bold text-slate-700 dark:text-gray-300">Pilih file untuk diunggah</p>
                        <p class="text-[10px] text-slate-500 mt-1">Maksimal ukuran tergantung limit server PHP</p>
                    </div>

                    <div x-show="ncSelectedFile" x-cloak>
                        <i class="fas fa-file-check text-2xl text-emerald-500 mb-2"></i>
                        <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 truncate px-2" x-text="ncSelectedFile?.name"></p>
                        <p class="text-[10px] text-slate-500 mt-1" x-text="(ncSelectedFile?.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div x-show="ncIsUploading" class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden" x-cloak>
                    <div class="bg-emerald-500 h-2 rounded-full transition-all duration-300" :style="`width: ${ncUploadProgress}%`"></div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-4 mt-6">
                <button type="button" @click="ncShowUploadModal = false" :disabled="ncIsUploading" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-lg transition-colors disabled:opacity-50">Batal</button>
                <button type="button" @click="uploadFile()" :disabled="!ncSelectedFile || ncIsUploading" class="bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/10 font-bold text-xs px-4 py-2 rounded-lg transition-all flex items-center gap-1.5 disabled:opacity-50">
                    <span x-show="!ncIsUploading">Upload</span>
                    <span x-show="ncIsUploading" x-cloak><i class="fas fa-spinner fa-spin"></i> Mengunggah...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DELETE --}}
    <div x-show="ncShowDeleteModal" class="fixed inset-0 z-[150] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" x-transition x-cloak>
        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-xs p-5 rounded-2xl shadow-2xl text-center relative" @click.away="ncShowDeleteModal = false">
            <h3 class="text-base font-black text-slate-900 dark:text-white mb-2">Hapus File?</h3>
            <p class="text-[11px] text-slate-500 dark:text-gray-400 mb-5 leading-relaxed">
                Hapus <span class="font-bold text-rose-500" x-text="ncFileToDelete"></span> secara permanen dari Nextcloud?
            </p>
            <div class="flex justify-center gap-2">
                <button type="button" @click="ncShowDeleteModal = false" class="w-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-gray-300 font-bold text-xs px-3 py-2 rounded-lg transition-colors">Batal</button>
                <button type="button" @click="executeDelete()" class="w-full bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:border-rose-800/50 font-bold text-xs px-3 py-2 rounded-lg transition-all">Ya, Hapus</button>
            </div>
        </div>
    </div>

</div>
