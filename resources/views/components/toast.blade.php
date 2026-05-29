@if (session('success') || session('error') || session('success_user') || session('error_user') || $errors->any())
    <div x-data="{ show: true }" 
         x-init="setTimeout(() => show = false, 5000)" 
         x-show="show" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-10" 
         x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0 translate-x-10" 
         class="fixed bottom-6 right-4 sm:right-6 z-[9999] flex flex-col gap-3 max-w-sm w-[calc(100%-2rem)] sm:w-full"
         x-cloak>
        
        {{-- Pesan Sukses --}}
        @if (session('success') || session('success_user'))
            <div class="flex items-start p-4 bg-emerald-50 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl shadow-lg shadow-emerald-500/10 backdrop-blur-sm">
                <div class="flex-shrink-0 mt-0.5">
                    <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wide mb-1">Berhasil</p>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400/90 leading-relaxed">{{ session('success') ?? session('success_user') }}</p>
                </div>
                <button @click="show = false" class="ml-4 flex-shrink-0 text-emerald-400 hover:text-emerald-600 transition-colors">✕</button>
            </div>
        @endif

        {{-- Pesan Error / Validasi --}}
        @if (session('error') || session('error_user') || $errors->any())
            <div class="flex items-start p-4 bg-rose-50 dark:bg-rose-900/40 border border-rose-200 dark:border-rose-800 rounded-2xl shadow-lg shadow-rose-500/10 backdrop-blur-sm">
                <div class="flex-shrink-0 mt-0.5">
                    <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-xs font-bold text-rose-800 dark:text-rose-300 uppercase tracking-wide mb-1">Terjadi Kesalahan</p>
                    <p class="text-xs text-rose-600 dark:text-rose-400/90 leading-relaxed">
                        @if(session('error'))
                            {{ session('error') }}
                        @elseif(session('error_user'))
                            {{ session('error_user') }}
                        @else
                            {{ $errors->first() }}
                        @endif
                    </p>
                </div>
                <button @click="show = false" class="ml-4 flex-shrink-0 text-rose-400 hover:text-rose-600 transition-colors">✕</button>
            </div>
        @endif
    </div>
@endif
