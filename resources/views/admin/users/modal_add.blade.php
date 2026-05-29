<div x-show="showAddModal"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
     x-transition x-cloak>

    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-lg rounded-2xl shadow-2xl p-6" @click.away="showAddModal = false">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
            <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-user-plus text-blue-500"></i> Daftarkan User Baru (FreeIPA)
            </h3>
            <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm">✕</button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Username (UID)</label>
                <input type="text" name="username" required placeholder="contoh: awan"
                       class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors font-mono">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Depan</label>
                    <input type="text" name="first_name" required placeholder="Nama Depan"
                           class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Belakang</label>
                    <input type="text" name="last_name" required placeholder="Nama Belakang"
                           class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alamat Email Jaringan</label>
                <input type="email" name="email" required placeholder="awan@logstack.web.id"
                       class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Grup Otorisasi / Role</label>
                <select name="group" required
                        class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                    <option value="dash_user">User Biasa (dash_user)</option>
                    <option value="dash_admin">Administrator (dash_admin)</option>
                </select>
                <span class="text-[10px] text-slate-400 block mt-1">Menentukan tingkat hak akses dashboard saat login ke portal utama.</span>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Password Akun Default</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-4 mt-2">
                <button type="button" @click="showAddModal = false"
                        class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-5 py-2 rounded-xl transition-all shadow-md shadow-blue-600/10 flex items-center gap-1.5">
                    <i class="fas fa-user-plus text-[10px]"></i> Daftarkan User
                </button>
            </div>
        </form>
    </div>
</div>
