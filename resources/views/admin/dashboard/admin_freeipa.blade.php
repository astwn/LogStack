<div class="space-y-6">

    {{-- Header Content --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">🔑 FreeIPA Directory Core</h1>
            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Manajemen database LDAP identitas user, otorisasi POSIX group.</p>
        </div>
        <a href="{{ $serviceUrls['freeipa'] ?? 'https://ipa.logstack.web.id' }}" target="_blank" class="bg-red-50 dark:bg-red-950 hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-white border border-red-200 dark:border-red-800 font-bold text-xs px-4 py-2 rounded-xl transition-colors self-start sm:self-auto">Launch Portal ➔</a>
    </div>

    {{-- Load Tabel User --}}
    @include('admin.users.table')

</div>
