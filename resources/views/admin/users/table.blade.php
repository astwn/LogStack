<div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 rounded-2xl p-4 md:p-6"
     x-data="{
        showDeleteConfirm: false,
        userToDelete: '',
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
     }">
     
    {{-- Header: Dibuat Flex-Col pas Mobile, Flex-Row pas Desktop --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-slate-200 dark:border-slate-800">
        <span class="text-xs font-bold text-slate-700 dark:text-gray-400 flex items-center gap-2">
            <i class="fas fa-users text-blue-500"></i> Live Directory Server User List (FreeIPA API)
        </span>
        <button type="button" @click="showAddModal = true" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-2.5 sm:py-2 rounded-xl shadow-md transition-all flex items-center justify-center gap-1">
            <i class="fas fa-user-plus text-[10px]"></i> Add User via FreeIPA API
        </button>
    </div>

    {{-- Container Tabel: Diberikan overflow-x-auto + custom scrollbar tipis agar tidak merusak layout mobile --}}
    <div class="overflow-x-auto block w-full chunk-scrollbar">
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
                
                @forelse($freeIpaUsers ?? [] as $user)
                    <tr class="hover:bg-slate-100/40 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="py-3.5 font-mono text-slate-900 dark:text-white font-bold">
                            {{ is_array($user) ? ($user['username'] ?? '') : ($user->username ?? '') }}
                        </td>
                        <td class="py-3.5 whitespace-nowrap">
                            {{ is_array($user) ? ($user['first_name'] ?? '') : ($user->first_name ?? '') }}
                            {{ is_array($user) ? ($user['last_name'] ?? '') : ($user->last_name ?? '') }}
                        </td>
                        <td class="py-3.5 font-mono text-slate-600 dark:text-gray-400 whitespace-nowrap">
                            {{ is_array($user) ? ($user['email'] ?? '-') : ($user->email ?? '-') }}
                        </td>
                        <td class="py-3.5 whitespace-nowrap">
                            @php
                                $userGroups = is_array($user) ? ($user['groups'] ?? []) : ($user->groups ?? []);
                                if (is_string($userGroups)) {
                                    $userGroups = json_decode($userGroups, true) ?: [];
                                }

                                $hasAdminGroup = in_array('dash_admin', $userGroups) ||
                                                 in_array('admin', $userGroups) ||
                                                 in_array('admins', $userGroups);
                            @endphp

                            @if($hasAdminGroup)
                                <span class="bg-purple-100 dark:bg-purple-950/40 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-900/30 text-[9px] font-bold px-1.5 py-0.5 rounded">DASH_ADMIN</span>
                            @else
                                <span class="bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-900/30 text-[9px] font-bold px-1.5 py-0.5 rounded">DASH_USER</span>
                            @endif
                        </td>
                        <td class="py-3.5 whitespace-nowrap">
                            @php
                                $currentStatus = is_array($user) ? ($user['status'] ?? 'Active') : ($user->status ?? 'Active');
                                $accountIsDisabled = ($currentStatus === 'Locked' || $currentStatus === 'locked');
                            @endphp

                            @if($accountIsDisabled)
                                <span class="bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/30 text-[9px] px-2 py-0.5 rounded-full font-bold flex items-center gap-1 w-max">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> Locked
                                </span>
                            @else
                                <span class="bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30 text-[9px] px-2 py-0.5 rounded-full font-bold flex items-center gap-1 w-max">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 text-center flex items-center justify-center gap-1.5">
                            @php
                                $uName = is_array($user) ? ($user['username'] ?? '') : ($user->username ?? '') ;
                                $fName = is_array($user) ? ($user['first_name'] ?? '') : ($user->first_name ?? '');
                                $lName = is_array($user) ? ($user['last_name'] ?? '') : ($user->last_name ?? '');
                                $uEmail = is_array($user) ? ($user['email'] ?? '') : ($user->email ?? '');
                            @endphp

                            @if($uName === 'admin' || $uName === 'arif')
                                <span class="text-slate-400 dark:text-gray-600 text-[11px] font-medium italic px-4 py-1">Protected</span>
                            @else
                                <button type="button"
                                        @click="editUser.username = '{{ $uName }}';
                                                editUser.first_name = '{{ $fName }}';
                                                editUser.last_name = '{{ $lName }}';
                                                editUser.email = '{{ $uEmail }}';
                                                editUser.group = '{{ $hasAdminGroup ? 'dash_admin' : 'dash_user' }}';
                                                showEditModal = true;"
                                        class="text-blue-600 hover:text-blue-900 bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/30 w-8 h-8 flex items-center justify-center rounded-xl font-bold text-[11px] transition-colors shadow-sm">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button type="button"
                                        @click="submitToggleStatus('{{ $uName }}', {{ $accountIsDisabled ? 'true' : 'false' }})"
                                        class="text-orange-600 bg-orange-50 dark:bg-orange-950/40 border border-orange-100 dark:border-orange-900/30 w-8 h-8 flex items-center justify-center rounded-xl font-bold text-[11px] transition-colors shadow-sm">
                                    <i class="fas {{ $accountIsDisabled ? 'fa-unlock' : 'fa-lock' }}"></i>
                                </button>

                                <button type="button"
                                        @click="triggerDelete('{{ $uName }}')"
                                        class="text-rose-600 hover:text-rose-900 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-900/30 w-8 h-8 flex items-center justify-center rounded-xl font-bold text-[11px] transition-colors shadow-sm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400 dark:text-gray-500 font-medium">Tidak ada data pengguna yang terdaftar di direktori server FreeIPA.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 🔥 MODAL KONFIRMASI HAPUS: Dibuat max-w-sm dan padding responsif p-5 biar pas di layar HP --}}
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
                <button type="button"
                        @click="showDeleteConfirm = false"
                        class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 px-4 py-2.5 rounded-xl transition-colors flex-1">
                    Batal
                </button>
                <button type="button"
                        @click="confirmDelete()"
                        class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 rounded-xl transition-all shadow-md shadow-rose-600/10 flex-1">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    {{-- INTERFACES FORM SILUMAN --}}
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
    /* Styling scrollbar tipis estetik khusus perangkat mobile saat swipe tabel */
    .chunk-scrollbar::-webkit-scrollbar { height: 4px; }
    .chunk-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .chunk-scrollbar::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.2); border-radius: 10px; }
    .dark .chunk-scrollbar::-webkit-scrollbar-thumb { background: rgba(75, 85, 99, 0.4); }
</style>
