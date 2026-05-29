{{-- MODAL EDIT USER
     State dikelola oleh parent x-data di dashboard.blade.php:
     - showEditModal, editUser, editPassword, editConfirmPassword
     - Event listener: @open-edit-modal.window sudah ada di parent
--}}
<div x-show="showEditModal"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
     x-transition x-cloak>

    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-lg rounded-2xl shadow-2xl p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
            <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-user-edit text-blue-500"></i> Edit User & Role
            </h3>
            <button @click="closeEditModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm">✕</button>
        </div>

        <form id="form-edit-user" action="/admin/users/placeholder" method="POST" class="space-y-4 text-xs"
              @submit.prevent="
                if (editPassword && editPassword !== editConfirmPassword) return;
                $el.action = '/admin/users/' + editUser.username;
                HTMLFormElement.prototype.submit.call($el);
              ">
            @csrf
            @method('PUT')
            <input type="hidden" name="_edit_username" :value="editUser.username">

            {{-- Username (readonly) --}}
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                    Username (Kunci Permanen)
                </label>
                <input type="text" x-model="editUser.username" disabled
                       class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-400 dark:text-gray-500 font-mono cursor-not-allowed">
                <p class="text-[10px] text-slate-400 dark:text-gray-600 mt-1 flex items-center gap-1">
                    <i class="fas fa-lock text-[9px]"></i> UID tidak dapat diubah setelah dibuat
                </p>
            </div>

            {{-- Nama Depan & Belakang --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Depan</label>
                    <input type="text" name="first_name" required x-model="editUser.first_name"
                           class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Belakang</label>
                    <input type="text" name="last_name" required x-model="editUser.last_name"
                           class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alamat Email Jaringan</label>
                <input type="email" name="email" required x-model="editUser.email"
                       class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
            </div>

            {{-- Role --}}
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Grup Otorisasi / Role</label>
                <select name="group" x-model="editUser.group" required
                        class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors text-xs">
                    <option value="dash_user">User Biasa (dash_user)</option>
                    <option value="dash_admin">Administrator (dash_admin)</option>
                </select>
            </div>

            {{-- Reset Password (Opsional) --}}
            <div class="border border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-4 space-y-3 bg-slate-50/50 dark:bg-slate-800/20">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fas fa-key text-orange-400"></i> Reset Password (Opsional)
                </p>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Password Baru</label>
                    <input type="password" name="new_password" x-model="editPassword"
                           placeholder="Kosongkan jika tidak ingin reset"
                           class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-orange-400 transition-colors">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" x-model="editConfirmPassword"
                           placeholder="Ulangi password baru"
                           :class="editConfirmPassword && !editPasswordMatch ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 dark:border-slate-700 focus:border-orange-400'"
                           class="w-full bg-white dark:bg-[#0b0e14] rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none transition-colors border">
                    <p x-show="editConfirmPassword && !editPasswordMatch"
                       class="text-[10px] text-rose-500 font-bold mt-1 flex items-center gap-1">
                        <i class="fas fa-exclamation-triangle"></i> Password tidak cocok
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-4 mt-2">
                <button type="button" @click="closeEditModal()"
                        class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit"
                        :disabled="editPassword.length > 0 && !editPasswordMatch"
                        :class="editPassword.length > 0 && !editPasswordMatch ? 'opacity-40 cursor-not-allowed bg-slate-400 text-slate-600' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/10'"
                        class="font-bold text-xs px-5 py-2 rounded-xl transition-all flex items-center gap-1.5">
                    <i class="fas fa-save text-[10px]"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
