@php
    use App\Services\BrandingService;
    $branding = BrandingService::get();
@endphp

<div class="space-y-6" x-data="{ showResetModal: false }">

    {{-- MODAL KONFIRMASI RESET --}}
    <div x-show="showResetModal"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
         x-transition x-cloak>
        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-sm p-6 rounded-2xl shadow-2xl">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-950/40 flex items-center justify-center text-red-500 flex-shrink-0">
                    <i class="fas fa-undo"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">Reset Branding?</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-0.5">Semua perubahan akan dikembalikan ke default.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button @click="showResetModal = false"
                    class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl transition-colors">
                    Batal
                </button>
                <form action="{{ route('admin.branding.reset') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-undo"></i> Ya, Reset
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">🎨 Branding Settings</h1>
            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Kustomisasi tampilan dan identitas aplikasi secara terpusat.</p>
        </div>
        <button @click="showResetModal = true"
            class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-600 dark:text-gray-400 border border-slate-200 dark:border-slate-700 font-bold text-xs px-4 py-2 rounded-xl transition-colors flex items-center gap-2 self-start sm:self-auto">
            <i class="fas fa-undo"></i> Reset ke Default
        </button>
    </div>

    {{-- PREVIEW --}}
    <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
            <i class="fas fa-eye text-blue-500"></i> Preview Branding Saat Ini
        </p>
        <div class="flex items-center gap-4 p-4 bg-white dark:bg-[#0b0e14] rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-lg flex-shrink-0"
                 style="background-color: {{ $branding['primary_color'] }}">
                <i class="fas {{ $branding['app_logo_icon'] }} text-sm"></i>
            </div>
            <div>
                <p class="text-lg font-bold tracking-tighter uppercase italic text-slate-900 dark:text-white">
                    {{ $branding['app_name'] }}<span style="color: {{ $branding['primary_color'] }}">{{ substr($branding['app_full_name'], strlen($branding['app_name'])) }}</span>
                </p>
                <p class="text-[10px] text-slate-400 dark:text-gray-500">{{ $branding['app_tagline'] }}</p>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-3 flex-wrap">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-md border border-slate-200 dark:border-slate-700" style="background-color: {{ $branding['primary_color'] }}"></div>
                <span class="text-[10px] font-mono text-slate-500 dark:text-gray-400">Primary: {{ $branding['primary_color'] }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-md border border-slate-200 dark:border-slate-700" style="background-color: {{ $branding['accent_color'] }}"></div>
                <span class="text-[10px] font-mono text-slate-500 dark:text-gray-400">Accent: {{ $branding['accent_color'] }}</span>
            </div>
            <span class="text-[10px] font-mono text-slate-400 dark:text-gray-600">Version: {{ $branding['app_version'] }}</span>
        </div>
    </div>

    {{-- FORM --}}
    <form action="{{ route('admin.branding.save') }}" method="POST">
        @csrf
        <div class="space-y-6">

            {{-- App Identity --}}
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-tag text-blue-500"></i> Identitas Aplikasi
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Pendek <span class="text-red-400">*</span></label>
                        <input type="text" name="app_name" value="{{ $branding['app_name'] }}" required maxlength="50"
                               placeholder="contoh: LogStack"
                               class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                        <p class="text-[10px] text-slate-400 mt-1">Dipakai di logo dan title browser</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                        <input type="text" name="app_full_name" value="{{ $branding['app_full_name'] }}" required maxlength="100"
                               placeholder="contoh: LogStack Central"
                               class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                        <p class="text-[10px] text-slate-400 mt-1">Dipakai di header dan halaman utama</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tagline</label>
                        <input type="text" name="app_tagline" value="{{ $branding['app_tagline'] }}" maxlength="255"
                               placeholder="contoh: Pusat kendali infrastruktur digital terintegrasi"
                               class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Teks Loader</label>
                        <input type="text" name="loader_text" value="{{ $branding['loader_text'] }}" maxlength="50"
                               placeholder="contoh: LogStack"
                               class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                        <p class="text-[10px] text-slate-400 mt-1">Teks yang muncul saat loading screen</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Versi Aplikasi</label>
                        <input type="text" name="app_version" value="{{ $branding['app_version'] }}" maxlength="20"
                               placeholder="contoh: v1.0.0"
                               class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Teks Footer</label>
                        <input type="text" name="footer_text" value="{{ $branding['footer_text'] }}" maxlength="150"
                               placeholder="contoh: LogStack Central • Project Sovereign"
                               class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                </div>
            </div>

            {{-- Logo & Colors --}}
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-palette text-purple-500"></i> Logo & Warna
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Icon Logo (FontAwesome) <span class="text-red-400">*</span></label>
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white flex-shrink-0"
                                 style="background-color: {{ $branding['primary_color'] }}"
                                 id="logo-preview">
                                <i class="fas {{ $branding['app_logo_icon'] }} text-sm" id="logo-icon-preview"></i>
                            </div>
                            <input type="text" name="app_logo_icon" value="{{ $branding['app_logo_icon'] }}" required
                                   placeholder="fa-layer-group"
                                   id="logo-icon-input"
                                   class="flex-1 bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors font-mono">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Cari icon di <a href="https://fontawesome.com/icons" target="_blank" class="text-blue-500 hover:underline">fontawesome.com</a></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Warna Primary <span class="text-red-400">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" value="{{ $branding['primary_color'] }}"
                                   class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer flex-shrink-0 p-0.5 bg-white dark:bg-slate-800">
                            <input type="text" id="primary-color-text" value="{{ $branding['primary_color'] }}"
                                   class="flex-1 bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors font-mono"
                                   readonly>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Warna utama tombol dan aksen</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Warna Accent <span class="text-red-400">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="accent_color" value="{{ $branding['accent_color'] }}"
                                   class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer flex-shrink-0 p-0.5 bg-white dark:bg-slate-800">
                            <input type="text" id="accent-color-text" value="{{ $branding['accent_color'] }}"
                                   class="flex-1 bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors font-mono"
                                   readonly>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Warna badge admin dan highlight</p>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3">
                <button type="button" @click="switchTab('main')"
                    class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-600 dark:text-gray-400 font-bold text-xs px-5 py-2.5 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-6 py-2.5 rounded-xl transition-colors shadow-md shadow-blue-600/20 flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Branding
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Live preview icon
    document.getElementById('logo-icon-input').addEventListener('input', function() {
        const icon = document.getElementById('logo-icon-preview');
        icon.className = 'fas ' + this.value + ' text-sm';
    });

    // Sync color picker with text input
    document.querySelector('input[name="primary_color"]').addEventListener('input', function() {
        document.getElementById('primary-color-text').value = this.value;
        document.getElementById('logo-preview').style.backgroundColor = this.value;
    });

    document.querySelector('input[name="accent_color"]').addEventListener('input', function() {
        document.getElementById('accent-color-text').value = this.value;
    });
</script>
