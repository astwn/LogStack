<div class="space-y-6">

    {{-- Header --}}
    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Admin Management Dashboard</h1>
            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Pusat kontrol infrastruktur, status layanan, dan akses cepat portal admin.</p>
        </div>

    </div>

    {{-- Infrastructure Health Cards --}}
    <div>
        <h3 class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fas fa-heartbeat text-rose-500"></i> Infrastructure Status
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

            {{-- Nextcloud --}}
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500">
                        <i class="fas fa-cloud text-sm"></i>
                    </div>
                    @if(($appsStatus['nextcloud'] ?? 'OFFLINE') === 'ONLINE')
                        <span class="flex items-center gap-1 text-[9px] font-bold text-emerald-600 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE</span>
                    @else
                        <span class="flex items-center gap-1 text-[9px] font-bold text-rose-600 dark:text-rose-400"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> OFFLINE</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">Nextcloud</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500 font-mono">{{ env('SERVICE_IP_NEXTCLOUD', '172.18.4.105') }}</p>
                </div>
            </div>

            {{-- Odoo --}}
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500">
                        <i class="fas fa-briefcase text-sm"></i>
                    </div>
                    @if(($appsStatus['odoo'] ?? 'OFFLINE') === 'ONLINE')
                        <span class="flex items-center gap-1 text-[9px] font-bold text-emerald-600 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE</span>
                    @else
                        <span class="flex items-center gap-1 text-[9px] font-bold text-rose-600 dark:text-rose-400"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> OFFLINE</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">Odoo ERP</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500 font-mono">{{ env('SERVICE_IP_ODOO', '172.18.4.106') }}</p>
                </div>
            </div>

            {{-- SOGo --}}
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                        <i class="fas fa-envelope text-sm"></i>
                    </div>
                    @if(($appsStatus['sogo'] ?? 'OFFLINE') === 'ONLINE')
                        <span class="flex items-center gap-1 text-[9px] font-bold text-emerald-600 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE</span>
                    @else
                        <span class="flex items-center gap-1 text-[9px] font-bold text-rose-600 dark:text-rose-400"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> OFFLINE</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">SOGo Mail</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500 font-mono">{{ env('SERVICE_IP_SOGO', '172.18.4.107') }}</p>
                </div>
            </div>

            {{-- FreeIPA --}}
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-red-500/10 flex items-center justify-center text-red-500">
                        <i class="fas fa-key text-sm"></i>
                    </div>
                    @if(($appsStatus['freeipa'] ?? 'OFFLINE') === 'ONLINE')
                        <span class="flex items-center gap-1 text-[9px] font-bold text-emerald-600 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE</span>
                    @else
                        <span class="flex items-center gap-1 text-[9px] font-bold text-rose-600 dark:text-rose-400"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> OFFLINE</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">FreeIPA</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500 font-mono">{{ env('SERVICE_IP_FREEIPA', '172.18.4.103') }}</p>
                </div>
            </div>

            {{-- Grafana --}}
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500">
                        <i class="fas fa-chart-bar text-sm"></i>
                    </div>
                    @if(($appsStatus['grafana'] ?? 'OFFLINE') === 'ONLINE')
                        <span class="flex items-center gap-1 text-[9px] font-bold text-emerald-600 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE</span>
                    @else
                        <span class="flex items-center gap-1 text-[9px] font-bold text-rose-600 dark:text-rose-400"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> OFFLINE</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">Grafana</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500 font-mono">{{ env('SERVICE_IP_GRAFANA', '172.18.4.108') }}</p>
                </div>
            </div>

            {{-- Nginx --}}
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-slate-500/10 flex items-center justify-center text-slate-500">
                        <i class="fas fa-server text-sm"></i>
                    </div>
                    @if(($appsStatus['nginx'] ?? 'OFFLINE') === 'ONLINE')
                        <span class="flex items-center gap-1 text-[9px] font-bold text-emerald-600 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE</span>
                    @else
                        <span class="flex items-center gap-1 text-[9px] font-bold text-rose-600 dark:text-rose-400"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> OFFLINE</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">Nginx Gateway</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500 font-mono">{{ env('SERVICE_IP_NGINX', '172.18.4.101') }}</p>
                </div>
            </div>

            {{-- App Portal --}}
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500">
                        <i class="fas fa-layer-group text-sm"></i>
                    </div>
                    <span class="flex items-center gap-1 text-[9px] font-bold text-emerald-600 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">App Portal</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500 font-mono">{{ env('SERVICE_IP_APP_PORTAL', '172.18.4.102') }}</p>
                </div>
            </div>

            {{-- Keycloak SSO --}}
            <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-500">
                        <i class="fas fa-shield-alt text-sm"></i>
                    </div>
                    @if(($appsStatus['nginx'] ?? 'OFFLINE') === 'ONLINE')
                        <span class="flex items-center gap-1 text-[9px] font-bold text-emerald-600 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE</span>
                    @else
                        <span class="flex items-center gap-1 text-[9px] font-bold text-rose-600 dark:text-rose-400"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> OFFLINE</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">Keycloak SSO</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500 font-mono">{{ env('SERVICE_IP_NGINX', '172.18.4.101') }}</p>
                </div>
            </div>

        </div>
    </div>

    {{-- Quick Access Admin Portals --}}
    <div>
        <h3 class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fas fa-external-link-alt text-blue-500"></i> Quick Access - Admin Portals
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

            {{-- Nextcloud --}}
            <a href="{{ ($serviceUrls['nextcloud'] ?? 'https://drive.logstack.web.id') . '/index.php/settings/admin' }}" target="_blank"
               class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-center gap-3 hover:border-blue-400 dark:hover:border-blue-600 transition-all group shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                    <i class="fas fa-cloud text-sm"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">Nextcloud</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500">File Storage</p>
                </div>
            </a>

            {{-- SOGo --}}
            <a href="{{ ($serviceUrls['sogo'] ?? 'https://mbox.logstack.web.id') . '/SOGo' }}" target="_blank"
               class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-center gap-3 hover:border-emerald-400 dark:hover:border-emerald-600 transition-all group shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
                    <i class="fas fa-envelope text-sm"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">SOGo</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500">Mail & Calendar</p>
                </div>
            </a>

            {{-- Odoo --}}
            <a href="{{ ($serviceUrls['odoo'] ?? 'https://erp.logstack.web.id') . '/odoo/settings' }}" target="_blank"
               class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-center gap-3 hover:border-orange-400 dark:hover:border-orange-600 transition-all group shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform">
                    <i class="fas fa-briefcase text-sm"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">Odoo ERP</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500">ERP System</p>
                </div>
            </a>

            {{-- Grafana --}}
            <a href="{{ ($serviceUrls['grafana'] ?? 'https://monit.logstack.web.id') . '/login' }}" target="_blank"
               class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-center gap-3 hover:border-orange-400 dark:hover:border-orange-600 transition-all group shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform">
                    <i class="fas fa-chart-bar text-sm"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">Grafana</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500">Monitoring</p>
                </div>
            </a>

            {{-- FreeIPA --}}
            <a href="{{ ($serviceUrls['freeipa'] ?? 'https://ipa.logstack.web.id') . '/ipa/ui/' }}" target="_blank"
               class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-center gap-3 hover:border-red-400 dark:hover:border-red-600 transition-all group shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-red-500/10 flex items-center justify-center text-red-500 group-hover:scale-110 transition-transform">
                    <i class="fas fa-key text-sm"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">FreeIPA</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500">Directory</p>
                </div>
            </a>

            {{-- Keycloak --}}
            <a href="{{ ($serviceUrls['keycloak'] ?? 'https://sso.logstack.web.id') . '/admin/master/console/' }}" target="_blank"
               class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-center gap-3 hover:border-purple-400 dark:hover:border-purple-600 transition-all group shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-500 group-hover:scale-110 transition-transform">
                    <i class="fas fa-shield-alt text-sm"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">Keycloak</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500">SSO Admin</p>
                </div>
            </a>

            {{-- LogViewer --}}
            <a href="/log-viewer" target="_blank"
               class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-center gap-3 hover:border-slate-400 dark:hover:border-slate-500 transition-all group shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-slate-500/10 flex items-center justify-center text-slate-500 group-hover:scale-110 transition-transform">
                    <i class="fas fa-terminal text-sm"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white">LogViewer</p>
                    <p class="text-[10px] text-slate-400 dark:text-gray-500">App Logs</p>
                </div>
            </a>

        </div>
    </div>

</div>
