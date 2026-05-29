<div class="space-y-6" x-data="sogoMailWidget()" x-init="loadStats()">



    {{-- ERROR STATE --}}
    <div x-show="error" x-cloak
        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center gap-3 text-sm text-red-600 dark:text-red-400">
        <i class="fas fa-exclamation-circle text-lg"></i>
        <span x-text="error"></span>
    </div>
    {{-- STAT CARDS --}}
    <div x-show="!error" x-cloak class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Inbox Total --}}
        <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Inbox</span>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 text-sm">
                    <i class="fas fa-inbox"></i>
                </div>
            </div>
            <div class="flex items-end gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white" x-text="loading ? '—' : stats.inbox_total"></span>
                <span class="text-xs text-slate-400 mb-1">email</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full"
                    :class="stats.inbox_unread > 0 ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'">
                    <i class="fas fa-circle text-[6px]"></i>
                    <span x-text="stats.inbox_unread + ' belum dibaca'"></span>
                </span>
            </div>
        </div>
        {{-- Junk --}}
        <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Junk</span>
                <div class="w-8 h-8 rounded-lg bg-yellow-500/10 flex items-center justify-center text-yellow-500 text-sm">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
            <div class="flex items-end gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white" x-text="loading ? '—' : stats.junk_total"></span>
                <span class="text-xs text-slate-400 mb-1">email</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full"
                    :class="stats.junk_unread > 0 ? 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/40 dark:text-yellow-400' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'">
                    <i class="fas fa-circle text-[6px]"></i>
                    <span x-text="stats.junk_unread + ' belum dibaca'"></span>
                </span>
            </div>
        </div>
        {{-- Trash --}}
        <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Trash</span>
                <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center text-red-500 text-sm">
                    <i class="fas fa-trash-alt"></i>
                </div>
            </div>
            <div class="flex items-end gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white" x-text="loading ? '—' : stats.trash_total"></span>
                <span class="text-xs text-slate-400 mb-1">email</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <i class="fas fa-circle text-[6px]"></i>
                    <span>dihapus</span>
                </span>
            </div>
        </div>
        {{-- Sent --}}
        <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Terkirim</span>
                <div class="w-8 h-8 rounded-lg bg-green-500/10 flex items-center justify-center text-green-500 text-sm">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
            <div class="flex items-end gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white" x-text="loading ? '—' : stats.sent_total"></span>
                <span class="text-xs text-slate-400 mb-1">email</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <i class="fas fa-circle text-[6px]"></i>
                    <span>terkirim</span>
                </span>
            </div>
        </div>
    </div>
    {{-- TABEL EMAIL TERBARU --}}
    <div x-show="!error" x-cloak class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-clock text-blue-500"></i> Email Terbaru (Inbox)
            </h3>
            <div class="flex items-center gap-2">
                <button @click="loadStats()" :disabled="loading"
                    class="w-7 h-7 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-gray-400 rounded-lg transition-colors shadow-sm disabled:opacity-50" title="Refresh">
                    <i class="fas fa-sync-alt text-[10px]" :class="loading ? 'animate-spin' : ''"></i>
                </button>
                <a href="{{ route('open.sogo') }}" target="_blank"
                    class="text-xs text-blue-500 hover:text-blue-600 font-semibold flex items-center gap-1">
                    Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
        {{-- Loading skeleton --}}
        <div x-show="loading" x-cloak class="divide-y divide-slate-100 dark:divide-slate-800">
            <template x-for="i in 5">
                <div class="px-3 sm:px-6 py-3 flex items-center gap-2 sm:gap-4 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors animate-pulse">
                    <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-3/4"></div>
                        <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded w-1/2"></div>
                    </div>
                    <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded w-24"></div>
                </div>
            </template>
        </div>
        {{-- Data rows --}}
        <div x-show="!loading" class="divide-y divide-slate-100 dark:divide-slate-800">
            <template x-if="stats.recent_emails && stats.recent_emails.length === 0">
                <div class="px-6 py-10 text-center text-sm text-slate-400">
                    <i class="fas fa-inbox text-3xl mb-3 block opacity-30"></i>
                    Inbox kosong
                </div>
            </template>
            <template x-for="(mail, index) in stats.recent_emails" :key="index">
                <a href="{{ route('open.sogo') }}"
                   target="_blank"
                   class="px-3 sm:px-6 py-3 flex items-center gap-2 sm:gap-4 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer no-underline block">
                    {{-- Avatar --}}
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                        :class="['bg-blue-500','bg-purple-500','bg-green-500','bg-orange-500','bg-pink-500'][index % 5]"
                        x-text="mail.from ? mail.from.charAt(0).toUpperCase() : '?'">
                    </div>
                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate"
                            :class="!mail.seen ? 'font-black' : ''"
                            x-text="mail.subject"></p>
                        <p class="text-xs text-slate-400 truncate mt-0.5" x-text="mail.from"></p>
                    </div>
                    {{-- Date + unread badge --}}
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        <span class="text-xs text-slate-400" x-text="mail.date"></span>
                        <span x-show="!mail.seen"
                            class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                    </div>
                </a>
            </template>
        </div>
    </div>
</div>
<script>
function sogoMailWidget() {
    return {
        loading: true,
        error: null,
        stats: {
            inbox_total: 0,
            inbox_unread: 0,
            junk_total: 0,
            junk_unread: 0,
            trash_total: 0,
            sent_total: 0,
            recent_emails: [],
        },
        async loadStats() {
            this.loading = true;
            this.error = null;
            try {
                const res = await fetch('{{ route("api.mail.stats") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (data.error) {
                    this.error = data.error;
                } else {
                    this.stats = data;
                }
            } catch (e) {
                this.error = 'Gagal memuat statistik email. Coba refresh halaman.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
