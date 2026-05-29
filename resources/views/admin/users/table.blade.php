<div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 rounded-2xl p-4 md:p-6"
     x-data="{
        // Modal State
        showDeleteConfirm: false,
        userToDelete: '',
        
        // Pagination & Search State
        searchQuery: '',
        currentPage: 1,
        itemsPerPage: 10,
        
        // Transformasi Data Blade ke Alpine JS
        usersList: [
            @foreach($freeIpaUsers ?? [] as $user)
                @php
                    $uName = is_array($user) ? ($user['username'] ?? '') : ($user->username ?? '');
                    $fName = is_array($user) ? ($user['first_name'] ?? '') : ($user->first_name ?? '');
                    $lName = is_array($user) ? ($user['last_name'] ?? '') : ($user->last_name ?? '');
                    $uEmail = is_array($user) ? ($user['email'] ?? '-') : ($user->email ?? '-');
                    
                    $userGroups = is_array($user) ? ($user['groups'] ?? []) : ($user->groups ?? []);
                    if (is_string($userGroups)) { $userGroups = json_decode($userGroups, true) ?: []; }
                    $hasAdminGroup = in_array('dash_admin', $userGroups) || in_array('admin', $userGroups) || in_array('admins', $userGroups);
                    
                    $currentStatus = is_array($user) ? ($user['status'] ?? 'Active') : ($user->status ?? 'Active');
                    $accountIsDisabled = ($currentStatus === 'Locked' || $currentStatus === 'locked');
                    
                    $isProtected = in_array($uName, ['admin', 'arif']);
                @endphp
                {
                    username: '{{ $uName }}',
                    fullName: '{{ addslashes(trim($fName . ' ' . $lName)) }}',
                    firstName: '{{ $fName }}',
                    lastName: '{{ $lName }}',
                    email: '{{ $uEmail }}',
                    isAdmin: {{ $hasAdminGroup ? 'true' : 'false' }},
                    isLocked: {{ $accountIsDisabled ? 'true' : 'false' }},
                    isProtected: {{ $isProtected ? 'true' : 'false' }}
                },
            @endforeach
        ],

        // Engine Pencarian
        get filteredUsers() {
            return this.usersList.filter(user => {
                const query = this.searchQuery.toLowerCase();
                return user.username.toLowerCase().includes(query) ||
                       user.fullName.toLowerCase().includes(query) ||
                       user.email.toLowerCase().includes(query);
            });
        },

        // Engine Pagination
        get paginatedUsers() {
            let start = (this.currentPage - 1) * this.itemsPerPage;
            let end = start + this.itemsPerPage;
            return this.filteredUsers.slice(start, end);
        },

        get totalPages() {
            return Math.ceil(this.filteredUsers.length / this.itemsPerPage) || 1;
        },

        // Action Handlers
        submitToggleStatus(username, isDisabled) {
            const form = document.getElementById('global-toggle-status-form');
            form.action = '/admin/users/' + username + '/toggle-status';
            document.getElementById('global-lock-value').value = isDisabled ? 0 : 1;
            form.submit();
        },
        triggerDelete(username) {
            this.userToDelete = username;
            this.showDeleteConfirm = true;
        },
        confirmDelete() {
            const form = document.getElementById('global-delete-form');
            form.action = '/admin/users/' + this.userToDelete;
            form.submit();
        }
     }"
     x-init="$watch('searchQuery', () => currentPage = 1)">

    {{-- Header Kontrol: Search Bar & Tambah User --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5 pb-4 border-b border-slate-200 dark:border-slate-800">
        
        {{-- Kiri: Judul Direktori --}}
        <div class="flex items-center gap-3 text-xs font-bold text-slate-700 dark:text-gray-400 whitespace-nowrap">
            <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 border border-blue-500/20 shadow-sm">
                <i class="fas fa-users"></i>
            </div>
            <span>FreeIPA Directory Server</span>
        </div>
        
        {{-- Kanan: Search & Add Button (Sejajar Rapi) --}}
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            
            {{-- Kotak Pencarian (Mungil di Kanan) --}}
            <div class="relative w-full sm:w-56 md:w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="fas fa-search text-[10px]"></i>
                </span>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Cari user..." 
                       class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl pl-9 pr-3 py-2 text-xs focus:outline-none focus:border-blue-500 transition-colors shadow-sm font-mono">
            </div>

            {{-- Tombol Refresh + Add User --}}
            <button type="button" onclick="window.location.reload()" class="w-8 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-gray-400 rounded-xl transition-colors shadow-sm shrink-0" title="Refresh">
                <i class="fas fa-sync-alt text-xs"></i>
            </button>
            <button type="button" @click="showAddModal = true" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 whitespace-nowrap border border-blue-600 shrink-0">
                <i class="fas fa-user-plus text-[10px]"></i> Add User
            </button>
            
        </div>
    </div>

    {{-- Container Tabel --}}
    <div class="overflow-x-auto block w-full chunk-scrollbar mb-4">
        <table class="w-full text-left text-xs border-collapse min-w-[600px] md:min-w-full">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 dark:text-gray-500 font-bold uppercase text-[10px] tracking-wider">
                    <th class="pb-3">USERNAME</th>
                    <th class="pb-3">NAMA LENGKAP</th>
                    <th class="pb-3">EMAIL</th>
                    <th class="pb-3">GRUP DIREKTORI</th>
                    <th class="pb-3">STATUS</th>
                    <th class="pb-3 text-center" width="20%">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 text-slate-700 dark:text-gray-300">
                
                {{-- Loop Tabel Memakai Alpine.js --}}
                <template x-for="user in paginatedUsers" :key="user.username">
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="py-3.5 font-mono text-blue-600 dark:text-blue-400 font-bold" x-text="user.username"></td>
                        <td class="py-3.5 whitespace-nowrap" x-text="user.fullName"></td>
                        <td class="py-3.5 font-mono text-slate-500 dark:text-gray-400 whitespace-nowrap" x-text="user.email"></td>
                        
                        <td class="py-3.5 whitespace-nowrap">
                            <span x-show="user.isAdmin" class="bg-purple-100 dark:bg-purple-950/40 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-900/30 text-[9px] font-bold px-1.5 py-0.5 rounded" x-cloak>ADMIN</span>
                            <span x-show="!user.isAdmin" class="bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-900/30 text-[9px] font-bold px-1.5 py-0.5 rounded" x-cloak>USER</span>
                        </td>
                        
                        <td class="py-3.5 whitespace-nowrap">
                            <span x-show="user.isLocked" class="bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/30 text-[9px] px-2 py-0.5 rounded-full font-bold flex items-center gap-1 w-max" x-cloak>
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> Locked
                            </span>
                            <span x-show="!user.isLocked" class="bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30 text-[9px] px-2 py-0.5 rounded-full font-bold flex items-center gap-1 w-max" x-cloak>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                            </span>
                        </td>
                        
                        <td class="py-3.5 text-center flex items-center justify-center gap-1.5">
                            
                            {{-- Jika User Protected --}}
                            <span x-show="user.isProtected" class="text-slate-400 dark:text-gray-600 text-[11px] font-medium italic px-4 py-1" x-cloak>Protected</span>
                            
			   {{-- Tombol Aksi Jika Bukan Protected --}}
    <div x-show="!user.isProtected" class="flex items-center gap-1.5" x-cloak>
        
        {{-- 1. Tombol Edit --}}
        <button type="button"
                @click="$dispatch('open-edit-modal', { username: user.username, first_name: user.firstName, last_name: user.lastName, email: user.email, group: user.isAdmin ? 'dash_admin' : 'dash_user' })"
                class="text-blue-600 hover:text-blue-900 bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/30 w-8 h-8 flex items-center justify-center rounded-xl font-bold text-[11px] transition-colors shadow-sm"
                title="Edit Data User">
            <i class="fas fa-edit"></i>
        </button>

        {{-- 2. Tombol Gembok (Logic Dibalik Sesuai Tindakan) --}}
        <button type="button"
                @click="submitToggleStatus(user.username, user.isLocked)"
                :class="!user.isLocked ? 'text-rose-600 hover:text-rose-900 bg-rose-50 dark:bg-rose-950/40 border-rose-100 dark:border-rose-900/30' : 'text-emerald-600 hover:text-emerald-900 bg-emerald-50 dark:bg-emerald-950/40 border-emerald-100 dark:border-emerald-900/30'"
                class="w-8 h-8 flex items-center justify-center border rounded-xl font-bold text-[11px] transition-colors shadow-sm"
                :title="!user.isLocked ? 'Kunci (Lock) User Ini' : 'Aktifkan (Unlock) User Ini'">
            <i class="fas transition-transform hover:scale-110" :class="!user.isLocked ? 'fa-lock' : 'fa-lock-open'"></i>
        </button>

        {{-- 3. Tombol Hapus --}}
        <button type="button"
                @click="triggerDelete(user.username)"
                class="text-rose-600 hover:text-rose-900 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-900/30 w-8 h-8 flex items-center justify-center rounded-xl font-bold text-[11px] transition-colors shadow-sm"
                title="Hapus User">
            <i class="fas fa-trash-alt transition-transform hover:scale-110"></i>
        </button>
    </div>

                        </td>
                    </tr>
                </template>
                
                {{-- Notifikasi Jika Tabel Kosong / Tidak Ditemukan --}}
                <tr x-show="filteredUsers.length === 0" x-cloak>
                    <td colspan="6" class="py-8 text-center text-slate-400 dark:text-gray-500 font-medium text-xs">
                        <i class="fas fa-search text-xl mb-2 opacity-50 block"></i>
                        Data pengguna tidak ditemukan.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Footer Pagination Control --}}
    <div x-show="filteredUsers.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] font-semibold text-slate-500 dark:text-gray-400 pt-3 border-t border-slate-200 dark:border-slate-800" x-cloak>
        <div>
            Menampilkan <span x-text="Math.min((currentPage - 1) * itemsPerPage + 1, filteredUsers.length)" class="text-slate-900 dark:text-white font-mono"></span> 
            - <span x-text="Math.min(currentPage * itemsPerPage, filteredUsers.length)" class="text-slate-900 dark:text-white font-mono"></span> 
            dari <span x-text="filteredUsers.length" class="text-blue-600 font-mono font-bold"></span> user
        </div>
        
        <div class="flex items-center gap-1.5">
            <button @click="currentPage > 1 ? currentPage-- : null" :disabled="currentPage === 1" :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : 'bg-white dark:bg-[#0b0e14] hover:border-slate-300 dark:hover:border-slate-600'" class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm transition-colors">
                <i class="fas fa-chevron-left mr-1"></i> Prev
            </button>
            <span class="px-3 font-mono font-bold">Page <span x-text="currentPage"></span> / <span x-text="totalPages"></span></span>
            <button @click="currentPage < totalPages ? currentPage++ : null" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : 'bg-white dark:bg-[#0b0e14] hover:border-slate-300 dark:hover:border-slate-600'" class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm transition-colors">
                Next <i class="fas fa-chevron-right ml-1"></i>
            </button>
        </div>
    </div>

    {{-- 🔥 MODAL KONFIRMASI HAPUS --}}
    <div x-show="showDeleteConfirm"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-md p-4"
         x-transition x-cloak>
        <div @click.away="showDeleteConfirm = false"
             class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-sm p-5 md:p-6 rounded-2xl shadow-2xl relative text-center">
            
            <div class="w-12 h-12 bg-rose-50 dark:bg-rose-950/50 border border-rose-100 dark:border-rose-900/30 rounded-2xl flex items-center justify-center text-rose-500 text-lg mx-auto mb-3 shadow-sm">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <h3 class="text-xs md:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider mb-2">Hapus Pengguna</h3>
            <p class="text-[11px] md:text-xs text-slate-500 dark:text-gray-400 leading-relaxed mb-5">
                Hapus pengguna <span class="font-mono font-black text-rose-500 bg-rose-500/5 px-1 py-0.5 rounded border border-rose-500/10" x-text="'@' + userToDelete"></span> secara permanen dari basis data LDAP? Tindakan ini merusak sinkronisasi sistem.
            </p>
            
            <div class="flex items-center gap-3 justify-center text-xs font-bold">
                <button type="button" @click="showDeleteConfirm = false" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 px-4 py-2.5 rounded-xl transition-colors flex-1">
                    Batal
                </button>
                <button type="button" @click="confirmDelete()" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 rounded-xl transition-all shadow-md shadow-rose-600/10 flex-1">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    {{-- INTERFACES FORM SILUMAN (Tetap sama, tidak ada fungsi backend yang dirusak) --}}
    <form id="global-toggle-status-form" method="POST" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="lock" id="global-lock-value">
    </form>

    <form id="global-delete-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

<style>
    /* Styling scrollbar tipis estetik */
    .chunk-scrollbar::-webkit-scrollbar { height: 4px; }
    .chunk-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .chunk-scrollbar::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.2); border-radius: 10px; }
    .dark .chunk-scrollbar::-webkit-scrollbar-thumb { background: rgba(75, 85, 99, 0.4); }
</style>
