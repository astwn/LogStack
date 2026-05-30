<div class="space-y-6" x-data="sogoMailWidget()" x-init="loadStats(); loadQuota()">

    {{-- MODAL BACA EMAIL --}}
    <div x-show="modalOpen" x-cloak
        class="fixed inset-0 z-[120] flex items-center justify-center bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="modalOpen = false">
        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[60vh]">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="font-black text-sm text-slate-900 dark:text-white truncate" x-text="activeEmail.subject || 'Memuat...'"></h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="activeEmail.from"></p>
                    <p class="text-[11px] text-slate-400 mt-0.5" x-text="activeEmail.date"></p>
                </div>
                <button @click="modalOpen = false"
                    class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors flex-shrink-0">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            {{-- Modal Body --}}
            <div class="px-6 py-4 overflow-y-auto flex-1">
                {{-- Loading --}}
                <div x-show="modalLoading" class="space-y-3 animate-pulse">
                    <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-full"></div>
                    <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-5/6"></div>
                    <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-4/6"></div>
                    <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded w-full mt-4"></div>
                    <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded w-3/4"></div>
                </div>
                {{-- Error --}}
                <div x-show="!modalLoading && activeEmail.error"
                    class="text-sm text-red-500 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <span x-text="activeEmail.error"></span>
                </div>
                {{-- Body email --}}
                <div x-show="!modalLoading && !activeEmail.error"
                    class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap font-mono text-xs"
                    x-html="activeEmail.body">
                </div>
            </div>
            {{-- Modal Footer --}}
            <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2 flex-shrink-0">
                <button @click="replyEmail()"
                    class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors flex items-center gap-1.5">
                    <i class="fas fa-reply text-[10px]"></i> Balas
                </button>
                <a href="{{ route('open.sogo') }}" target="_blank"
                    class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition-colors flex items-center gap-1.5">
                    <i class="fas fa-external-link-alt text-[10px]"></i> Buka di SOGo
                </a>
                <button @click="modalOpen = false"
                    class="px-4 py-2 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>



    {{-- MODAL COMPOSE EMAIL --}}
    <div x-show="composeOpen" x-cloak
        class="fixed inset-0 z-[130] flex items-center justify-center bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="composeOpen = false">
        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[70vh]">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-pen text-blue-500"></i>
                    <span x-text="composeMode === 'reply' ? 'Balas Email' : 'Tulis Email Baru'"></span>
                </h3>
                <button @click="composeOpen = false"
                    class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            {{-- Form --}}
            <div class="px-6 py-4 overflow-y-auto flex-1 space-y-3">
                <div>
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide block mb-1">Kepada</label>
                    <input type="email" x-model="compose.to" placeholder="email@domain.com"
                        class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide block mb-1">Subjek</label>
                    <input type="text" x-model="compose.subject" placeholder="Subjek email..."
                        class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide block mb-1">Pesan</label>
                    <textarea x-model="compose.body" rows="6" placeholder="Tulis pesan di sini..."
                        class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div x-show="compose.error" class="text-xs text-red-500 flex items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i>
                    <span x-text="compose.error"></span>
                </div>
                <div x-show="compose.success" class="text-xs text-emerald-500 flex items-center gap-1">
                    <i class="fas fa-check-circle"></i>
                    <span x-text="compose.success"></span>
                </div>
            </div>
            {{-- Footer --}}
            <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2 flex-shrink-0">
                <button @click="composeOpen = false"
                    class="px-4 py-2 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </button>
                <button @click="sendEmail()" :disabled="composeSending"
                    class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition-colors flex items-center gap-1.5 disabled:opacity-50">
                    <i class="fas fa-paper-plane text-[10px]" :class="composeSending ? 'animate-pulse' : ''"></i>
                    <span x-text="composeSending ? 'Mengirim...' : 'Kirim'"></span>
                </button>
            </div>
        </div>
    </div>

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
    {{-- MAIL QUOTA BAR --}}
    <div x-show="!error" x-cloak class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-database text-purple-500"></i> Kuota Mailbox
            </h3>
            <span class="text-xs font-mono text-slate-400 dark:text-slate-500"
                x-text="quotaLoading ? '...' : (quota.used_human + ' / ' + quota.limit_human)">
            </span>
        </div>
        {{-- Loading skeleton --}}
        <div x-show="quotaLoading" class="animate-pulse space-y-2">
            <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full w-full"></div>
            <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded w-1/3"></div>
        </div>
        {{-- Quota bar --}}
        <div x-show="!quotaLoading">
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden">
                <div class="h-3 rounded-full transition-all duration-700"
                    :class="{
                        'bg-emerald-500': quota.color === 'emerald',
                        'bg-yellow-500':  quota.color === 'yellow',
                        'bg-red-500':     quota.color === 'red'
                    }"
                    :style="'width: ' + quota.percentage + '%'">
                </div>
            </div>
            <div class="flex items-center justify-between mt-2">
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                    :class="{
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400': quota.color === 'emerald',
                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400':   quota.color === 'yellow',
                        'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400':               quota.color === 'red'
                    }"
                    x-text="quota.percentage + '% terpakai'">
                </span>
                <span class="text-[11px] text-slate-400"
                    x-text="quota.color === 'red' ? 'Kuota hampir penuh!' : (quota.color === 'yellow' ? 'Kuota mulai penuh' : 'Kuota aman')">
                </span>
            </div>
            {{-- Error quota --}}
            <div x-show="quota.error" class="mt-2 text-xs text-red-400" x-text="quota.error"></div>
        </div>
    </div>

    {{-- TABEL EMAIL TERBARU --}}
    <div x-show="!error" x-cloak class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2 shrink-0">
                <i class="fas fa-clock text-blue-500"></i> Email Terbaru
            </h3>
            <div class="flex items-center gap-2">
                {{-- Search --}}
                <div class="relative w-40 sm:w-56">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-[10px] text-slate-400"></i>
                    </div>
                    <input type="text" x-model="searchQuery" @input.debounce.500ms="doSearch()"
                        placeholder="Cari email..."
                        class="w-full bg-white dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl pl-9 pr-7 py-2 text-xs focus:outline-none focus:border-blue-500 transition-colors shadow-sm font-mono">
                    <button x-show="searchQuery" @click="searchQuery = ''; searchResults = []; searching = false"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </div>
                {{-- Tulis Email --}}
                <button @click="openCompose()"
                    class="bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/10 font-bold text-xs px-4 py-2 rounded-xl transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-pen"></i> Tulis
                </button>
                {{-- Refresh --}}
                <button @click="loadStats()" :disabled="loading"
                    class="w-8 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-gray-400 rounded-xl transition-colors shadow-sm disabled:opacity-50" title="Refresh">
                    <i class="fas fa-sync-alt text-xs" :class="loading ? 'animate-spin' : ''"></i>
                </button>
                {{-- Lihat Semua --}}
                <a href="{{ route('open.sogo') }}" target="_blank"
                    class="text-xs text-blue-500 hover:text-blue-600 font-semibold flex items-center gap-1 whitespace-nowrap">
                    Semua <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
        {{-- Loading skeleton --}}
        <div x-show="loading || searching" x-cloak class="divide-y divide-slate-100 dark:divide-slate-800">
            <template x-for="i in 5">
                <div class="px-3 sm:px-6 py-3 flex items-center gap-2 sm:gap-4 animate-pulse">
                    <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-3/4"></div>
                        <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded w-1/2"></div>
                    </div>
                    <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded w-24"></div>
                </div>
            </template>
        </div>
        {{-- Search results --}}
        <div x-show="!loading && !searching && searchQuery" class="divide-y divide-slate-100 dark:divide-slate-800">
            <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/50 text-[11px] text-slate-400 font-semibold">
                Hasil pencarian: <span x-text="searchResults.length"></span> email ditemukan
            </div>
            <template x-if="searchResults.length === 0">
                <div class="px-6 py-8 text-center text-sm text-slate-400">
                    <i class="fas fa-search text-2xl mb-2 block opacity-30"></i>
                    Tidak ada email yang cocok
                </div>
            </template>
            <template x-for="(mail, index) in searchResults" :key="index">
                <div @click="openEmail(mail.uid)"
                    class="px-3 sm:px-6 py-3 flex items-center gap-2 sm:gap-4 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                        :class="['bg-blue-500','bg-purple-500','bg-green-500','bg-orange-500','bg-pink-500'][index % 5]"
                        x-text="mail.from ? mail.from.charAt(0).toUpperCase() : '?'">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate" :class="!mail.seen ? 'font-black' : ''" x-text="mail.subject"></p>
                        <p class="text-xs text-slate-400 truncate mt-0.5" x-text="mail.from"></p>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        <span class="text-xs text-slate-400" x-text="mail.date"></span>
                        <span x-show="!mail.seen" class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                    </div>
                </div>
            </template>
        </div>
        {{-- Data rows (normal) --}}
        <div x-show="!loading && !searching && !searchQuery" class="divide-y divide-slate-100 dark:divide-slate-800">
            <template x-if="stats.recent_emails && stats.recent_emails.length === 0">
                <div class="px-6 py-10 text-center text-sm text-slate-400">
                    <i class="fas fa-inbox text-3xl mb-3 block opacity-30"></i>
                    Inbox kosong
                </div>
            </template>
            <template x-for="(mail, index) in stats.recent_emails" :key="index">
                <div @click="openEmail(mail.uid)"
                   class="px-3 sm:px-6 py-3 flex items-center gap-2 sm:gap-4 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                        :class="['bg-blue-500','bg-purple-500','bg-green-500','bg-orange-500','bg-pink-500'][index % 5]"
                        x-text="mail.from ? mail.from.charAt(0).toUpperCase() : '?'">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate"
                            :class="!mail.seen ? 'font-black' : ''"
                            x-text="mail.subject"></p>
                        <p class="text-xs text-slate-400 truncate mt-0.5" x-text="mail.from"></p>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        <span class="text-xs text-slate-400" x-text="mail.date"></span>
                        <span x-show="!mail.seen" class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                    </div>
                </div>
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
        quotaLoading: true,
        quota: {
            used_bytes: 0,
            limit_bytes: 0,
            used_mb: 0,
            limit_gb: 5,
            percentage: 0,
            used_human: '0 MB',
            limit_human: '5 GB',
            color: 'emerald',
            error: null,
        },
        // modal baca
        modalOpen: false,
        modalLoading: false,
        activeEmail: { uid: null, subject: '', from: '', date: '', body: '', error: null },
        // compose
        composeOpen: false,
        composeMode: 'new',
        composeSending: false,
        compose: { to: '', subject: '', body: '', error: null, success: null },
        // search
        searchQuery: '',
        searchResults: [],
        searching: false,

        async loadStats() {
            this.loading = true;
            this.error = null;
            try {
                const res = await fetch('{{ route("api.mail.stats") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (data.error) this.error = data.error;
                else this.stats = data;
            } catch (e) {
                this.error = 'Gagal memuat statistik email. Coba refresh halaman.';
            } finally {
                this.loading = false;
            }
        },

        async loadQuota() {
            this.quotaLoading = true;
            try {
                const res = await fetch('{{ route("api.mail.quota") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                this.quota = data;
            } catch (e) {
                this.quota.error = 'Gagal memuat data kuota mailbox.';
            } finally {
                this.quotaLoading = false;
            }
        },

        async openEmail(uid) {
            if (!uid) return;
            this.modalOpen = true;
            this.modalLoading = true;
            this.activeEmail = { uid, subject: '', from: '', date: '', body: '', error: null };
            try {
                const url = '{{ route("api.mail.message", ":uid") }}'.replace(':uid', uid);
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                this.activeEmail = data;
            } catch (e) {
                this.activeEmail.error = 'Gagal memuat isi email.';
            } finally {
                this.modalLoading = false;
            }
        },

        openCompose() {
            this.composeMode = 'new';
            this.compose = { to: '', subject: '', body: '', error: null, success: null };
            this.composeOpen = true;
        },

        replyEmail() {
            // Ambil email pengirim dari field from
            const fromRaw = this.activeEmail.from || '';
            const emailMatch = fromRaw.match(/<([^>]+)>/);
            const replyTo = emailMatch ? emailMatch[1] : fromRaw;
            const subject = this.activeEmail.subject || '';

            this.composeMode = 'reply';
            this.compose = {
                to: replyTo,
                subject: subject.startsWith('Re:') ? subject : 'Re: ' + subject,
                body: '\n\n--- Pesan Asli ---\nDari: ' + fromRaw + '\nTanggal: ' + this.activeEmail.date + '\n\n',
                error: null,
                success: null,
            };
            this.modalOpen = false;
            this.composeOpen = true;
        },

        async sendEmail() {
            if (!this.compose.to || !this.compose.subject || !this.compose.body) {
                this.compose.error = 'Semua field wajib diisi.';
                return;
            }
            this.composeSending = true;
            this.compose.error = null;
            this.compose.success = null;
            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch('{{ route("api.mail.compose") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({
                        to: this.compose.to,
                        subject: this.compose.subject,
                        body: this.compose.body,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    this.compose.success = 'Email berhasil dikirim!';
                    setTimeout(() => { this.composeOpen = false; this.loadStats(); }, 1500);
                } else {
                    this.compose.error = data.message || 'Gagal mengirim email.';
                }
            } catch (e) {
                this.compose.error = 'Terjadi kesalahan. Coba lagi.';
            } finally {
                this.composeSending = false;
            }
        },

        async doSearch() {
            const q = this.searchQuery.trim();
            if (q.length < 2) { this.searchResults = []; return; }
            this.searching = true;
            try {
                const res = await fetch('{{ route("api.mail.search") }}?q=' + encodeURIComponent(q), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                this.searchResults = await res.json();
            } catch (e) {
                this.searchResults = [];
            } finally {
                this.searching = false;
            }
        },
    }
}
</script>
