<x-layouts.app title="Welcome" loaderText="LogStack">
@php
    $brand = \App\Services\BrandingService::get();
@endphp

    <nav class="fixed top-0 w-full px-6 md:px-10 h-20 flex items-center justify-between z-50 bg-white/60 dark:bg-[#0b0e14]/60 backdrop-blur-lg border-b border-slate-100 dark:border-slate-800">
        <a href="/" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-all flex-shrink-0"
                 style="background-color: {{ $brand['primary_color'] }}">
                <i class="fas {{ $brand['app_logo_icon'] }} text-sm"></i>
            </div>
            <span class="text-lg font-bold tracking-tighter uppercase italic text-slate-900 dark:text-white">{{ $brand['app_name'] }}<span style="color: {{ $brand['primary_color'] }}">{{ substr($brand['app_full_name'], strlen($brand['app_name'])) }}</span></span>
        </a>
        <div class="flex items-center gap-3">
            <button @click="isDark = !isDark" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-yellow-400 border border-slate-100 dark:border-slate-700 hover:scale-105 transition-all">
                <i class="fas fa-sun text-sm" x-show="!isDark" x-cloak></i>
                <i class="fas fa-moon text-sm" x-show="isDark" x-cloak></i>
            </button>
            <button onclick="document.getElementById('requestModal').classList.remove('hidden')"
                class="px-4 py-2.5 text-[11px] font-bold rounded-lg transition-all border uppercase tracking-wider text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer">
                Request Access
            </button>
            <a href="{{ route('login.sso') }}" class="px-5 py-2.5 text-white text-[11px] font-bold rounded-lg transition-all shadow-lg uppercase tracking-wider"
               style="background-color: {{ $brand['primary_color'] }}">
                Masuk SSO
            </a>
        </div>
    </nav>

    <div class="flex-grow flex items-center justify-center pt-20">
        <main class="px-6 max-w-5xl w-full text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight mb-4 text-slate-900 dark:text-white">
                Empowering <span class="slogan-gradient italic">Autonomy.</span> <br class="hidden md:block"> Unified.
            </h1>
            <p class="text-slate-500 dark:text-gray-400 text-sm md:text-base mb-8 md:mb-12 max-w-xl mx-auto font-medium">
                {{ $brand['app_tagline'] }}
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-left mb-12">
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-blue-500 transition-all cursor-default">
                    <i class="fas fa-fingerprint text-blue-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight text-slate-900 dark:text-white">Identity Hub</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Autentikasi tersentralisasi dengan protokol Keycloak & FreeIPA.</p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-emerald-500 transition-all cursor-default">
                    <i class="fas fa-database text-emerald-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight text-slate-900 dark:text-white">Data Sovereign</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Data tetap milik Anda sepenuhnya di infrastruktur mandiri.</p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-purple-500 transition-all cursor-default">
                    <i class="fas fa-rocket text-purple-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight text-slate-900 dark:text-white">Rapid Ecosystem</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Ekosistem aplikasi bisnis yang siap digunakan dalam hitungan menit.</p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800 hover:border-orange-500 transition-all cursor-default">
                    <i class="fas fa-network-wired text-orange-600 mb-4 text-lg block"></i>
                    <h3 class="font-bold text-sm mb-1 uppercase tracking-tight text-slate-900 dark:text-white">Node Synergy</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-relaxed">Sinkronisasi mulus antar VM Cloud, ERP, dan Mail Server.</p>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-bold text-slate-400 dark:text-gray-600 uppercase tracking-[0.3em] mb-6 italic">Integrated Infrastructure</p>
                <div class="flex flex-wrap justify-center gap-6 items-center grayscale opacity-40 hover:opacity-100 transition-all duration-500">
                    <div class="flex items-center gap-2">
                        <div class="app-icon-mini" style="background: linear-gradient(135deg, #1D3B6B, #2563EB)">✉</div>
                        <span class="text-[10px] font-bold text-slate-900 dark:text-white">SOGo</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="app-icon-mini" style="background: linear-gradient(135deg, #0D3B2E, #059669)">N</div>
                        <span class="text-[10px] font-bold text-slate-900 dark:text-white">Nextcloud</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="app-icon-mini" style="background: linear-gradient(135deg, #3B2900, #D97706)">O</div>
                        <span class="text-[10px] font-bold text-slate-900 dark:text-white">Odoo</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="app-icon-mini" style="background: linear-gradient(135deg, #2D1B69, #7C3AED)">🔑</div>
                        <span class="text-[10px] font-bold text-slate-900 dark:text-white">Keycloak</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="app-icon-mini" style="background: linear-gradient(135deg, #1a1a2e, #16213e)">🛡</div>
                        <span class="text-[10px] font-bold text-slate-900 dark:text-white">FreeIPA</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <footer class="w-full py-6 text-center border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-black/20">
        <p class="text-[10px] font-bold text-slate-400 dark:text-gray-600 uppercase tracking-[0.4em]">
            &copy; 2026 {{ strtoupper($brand['footer_text']) }}
        </p>
    </footer>

    <script>
        localStorage.removeItem('activeTab');
        localStorage.removeItem('userActiveTab');
    </script>

    {{-- MODAL REQUEST ACCESS --}}
    <div id="requestModal" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); padding:1rem; backdrop-filter:blur(4px);">
        <div style="background:var(--modal-bg, #111827); border:1px solid rgba(255,255,255,0.1); width:100%; max-width:440px; padding:1.5rem; border-radius:1rem; box-shadow:0 25px 60px rgba(0,0,0,0.6);" class="bg-white dark:bg-[#111827]">
            <div id="successState" style="display:none;" class="text-center py-4">
                <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-950/40 rounded-full flex items-center justify-center text-emerald-500 text-2xl mx-auto mb-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="text-sm font-black text-slate-900 dark:text-white mb-2">Request Terkirim!</h3>
                <p class="text-xs text-slate-500 dark:text-gray-400 mb-4">Permintaan akses Anda telah dikirim. Admin akan meninjau dan menghubungi Anda via email.</p>
                <button onclick="closeReqModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-6 py-2.5 rounded-xl transition-colors">Tutup</button>
            </div>
            <div id="formState">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-user-plus" style="color:{{ $brand['primary_color'] }}"></i> Request Access
                    </h3>
                    <button onclick="closeReqModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm">✕</button>
                </div>
                <div id="reqError" style="display:none;" class="mb-4 p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/30 rounded-xl text-xs text-red-600 dark:text-red-400 font-bold flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i><span id="reqErrorText"></span>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
                        <input type="text" id="req_name" placeholder="John Doe" class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Username *</label>
                        <input type="text" id="req_username" placeholder="johndoe" class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors font-mono">
                        <p class="text-[10px] text-slate-400 mt-1">Hanya huruf dan angka, min 3 karakter</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Email Pribadi *</label>
                        <input type="email" id="req_email" placeholder="john@gmail.com" class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                        <p class="text-[10px] text-slate-400 mt-1">Credential akan dikirim ke email ini</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Departemen</label>
                        <select id="req_department" class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">-- Pilih Departemen --</option>
                            <option value="IT / Technology">IT / Technology</option>
                            <option value="Finance / Accounting">Finance / Accounting</option>
                            <option value="Human Resources">Human Resources</option>
                            <option value="Operations">Operations</option>
                            <option value="Marketing / Sales">Marketing / Sales</option>
                            <option value="Management">Management</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alasan (opsional)</label>
                        <textarea id="req_reason" placeholder="Jelaskan kebutuhan akses Anda..." rows="2" class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors resize-none"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 pt-4 mt-4">
                    <button onclick="closeReqModal()" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl transition-colors">Batal</button>
                    <button id="reqSubmitBtn" onclick="submitReq()" class="text-white font-bold text-xs px-5 py-2 rounded-xl transition-all flex items-center gap-2" style="background-color:{{ $brand['primary_color'] }}">
                        <i class="fas fa-paper-plane"></i> Kirim Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function closeReqModal() {
            var modal = document.getElementById('requestModal');
            modal.style.display = 'none';
            document.getElementById('successState').style.display = 'none';
            document.getElementById('formState').style.display = 'block';
            document.getElementById('reqError').style.display = 'none';
            ['req_name','req_username','req_email','req_reason'].forEach(function(id){ document.getElementById(id).value = ''; });
            document.getElementById('req_department').value = '';
        }
        document.getElementById('requestModal').addEventListener('click', function(e){ if(e.target===this) closeReqModal(); });
        // Open modal
        document.querySelectorAll('[onclick*="requestModal"]').forEach(function(btn){
            btn.onclick = function(){ 
                var modal = document.getElementById('requestModal');
                modal.style.display = 'flex';
            };
        });
        function submitReq() {
            var name = document.getElementById('req_name').value.trim();
            var username = document.getElementById('req_username').value.trim();
            var email = document.getElementById('req_email').value.trim();
            var dept = document.getElementById('req_department').value;
            var reason = document.getElementById('req_reason').value.trim();
            if (!name || !username || !email) {
                document.getElementById('reqErrorText').textContent = 'Nama, username, dan email wajib diisi.';
                document.getElementById('reqError').style.display = 'flex';
                return;
            }
            var btn = document.getElementById('reqSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            fetch('/access-request', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},
                body: JSON.stringify({name:name,username:username,email:email,department:dept,reason:reason})
            })
            .then(function(r){return r.json();})
            .then(function(d){
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Request';
                if (d.success) {
                    document.getElementById('formState').style.display = 'none';
                    document.getElementById('successState').style.display = 'block';
                } else {
                    document.getElementById('reqErrorText').textContent = d.message || 'Terjadi kesalahan.';
                    document.getElementById('reqError').style.display = 'flex';
                }
            })
            .catch(function(){
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Request';
                document.getElementById('reqErrorText').textContent = 'Terjadi kesalahan. Coba lagi.';
                document.getElementById('reqError').style.display = 'flex';
            });
        }
        document.getElementById('requestModal').addEventListener('click', function(e){ if(e.target===this) closeReqModal(); });
    </script>

</x-layouts.app>
