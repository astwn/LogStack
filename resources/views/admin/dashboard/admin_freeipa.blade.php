<div x-show="currentTab === 'admin_freeipa'" x-transition x-cloak class="space-y-6">
    
    {{-- 🔥 REKAYASA NOTIFIKASI ALERTS: Auto-dismiss 4 Detik + Klik Silang Manis --}}
    @if(session('success_user'))
        <div x-data="{ showNotif: true }" 
             x-init="setTimeout(() => showNotif = false, 4000)" 
             x-show="showNotif" 
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="p-4 mb-4 text-xs font-bold text-emerald-800 bg-emerald-100 rounded-xl dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30 flex items-center justify-between gap-2 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500 text-sm"></i> 
                <span>{{ session('success_user') }}</span>
            </div>
            <button @click="showNotif = false" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 font-bold px-2">✕</button>
        </div>
    @endif

    @if(session('error_user'))
        <div x-data="{ showNotif: true }" 
             x-init="setTimeout(() => showNotif = false, 5000)" 
             x-show="showNotif" 
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="p-4 mb-4 text-xs font-bold text-rose-800 bg-rose-100 rounded-xl dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800/30 flex items-center justify-between gap-2 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-rose-500 text-sm"></i> 
                <span>{{ session('error_user') }}</span>
            </div>
            <button @click="showNotif = false" class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-300 font-bold px-2">✕</button>
        </div>
    @endif

    {{-- Header Content --}}
    <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">🔑 FreeIPA Directory Core</h1>
            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Manajemen database LDAP identitas user, otorisasi POSIX group.</p>
        </div>
        <a href="https://ipa.logstack.web.id" target="_blank" class="bg-red-50 dark:bg-red-950 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 font-bold text-xs px-4 py-2 rounded-xl transition-colors">Launch Portal ➔</a>
    </div>

    {{-- Load Tabel User Bawaan --}}
    @include('admin.users.table')

    {{-- MODAL POPUP: UPDATE / EDIT USER & ROLE --}}
    <div x-show="showEditModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" 
         x-transition x-cloak>
        <div @click.away="showEditModal = false" 
             class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-lg p-6 rounded-2xl shadow-2xl relative">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-user-edit text-blue-500"></i> Perbarui Data & Role User (LDAP)
                </h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm">✕</button>
            </div>

            <form :action="'/admin/users/' + editUser.username" method="POST" @submit="showEditModal = false">
                @csrf
                @method('PUT')
                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-400 dark:text-gray-500 mb-1.5 uppercase tracking-wider text-[10px]">Username (UID) - Kunci Permanen</label>
                        <input type="text" readonly :value="editUser.username" class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-gray-500 rounded-xl px-3 py-2.5 focus:outline-none font-mono cursor-not-allowed">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">Nama Depan</label>
                            <input type="text" name="first_name" required x-model="editUser.first_name" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">Nama Belakang</label>
                            <input type="text" name="last_name" required x-model="editUser.last_name" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">Alamat Email Jaringan</label>
                        <input type="email" name="email" required x-model="editUser.email" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500 font-mono">
                    </div>
                    
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">Ubah Grup Otorisasi / Role Akun</label>
                        <select name="group" required x-model="editUser.group" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500">
                            <option value="dash_user">User Biasa (dash_user)</option>
                            <option value="dash_admin">Administrator (dash_admin)</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-4 mt-6">
                    <button type="button" @click="showEditModal = false" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-5 py-2 rounded-xl transition-colors shadow-md shadow-blue-600/10">
                        Simpan Perubahan ➔
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
