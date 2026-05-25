<div x-show="currentTab === 'admin_status'" x-transition x-cloak class="space-y-6">
    <div class="mb-6 flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">🛠️ Admin Management Dashboard</h1>
            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Pusat kontrol infrastruktur, pemetaan jaringan lokal, dan status gateway.</p>
        </div>
        <a href="/log-viewer" target="_blank" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-xs text-slate-700 dark:text-gray-300 font-bold px-4 py-2 rounded-xl border border-slate-200 dark:border-gray-700 transition-colors flex items-center gap-2 shadow-sm">
            <i class="fas fa-terminal"></i> Open Local LogViewer
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div><h4 class="text-sm font-bold text-slate-900 dark:text-white">Grafana Monitoring Stack</h4><p class="text-xs text-slate-400 mt-1">monit.logstack.web.id</p></div>
                @if(($appsStatus['grafana'] ?? 'OFFLINE') === 'ONLINE')
                    <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 text-[9px] px-2 py-0.5 rounded-full font-bold">ONLINE</span>
                @else
                    <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 text-[9px] px-2 py-0.5 rounded-full font-bold">OFFLINE</span>
                @endif
            </div>
            <a href="https://monit.logstack.web.id" target="_blank" class="w-full text-center bg-orange-600 text-white text-xs font-bold py-2 rounded-lg mt-4 block tracking-wide">Launch Grafana ➔</a>
        </div>
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div><h4 class="text-sm font-bold text-slate-900 dark:text-white">FreeIPA Directory Portal</h4><p class="text-xs text-slate-400 mt-1">ipa.logstack.web.id</p></div>
                @if(($appsStatus['freeipa'] ?? 'OFFLINE') === 'ONLINE')
                    <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 text-[9px] px-2 py-0.5 rounded-full font-bold">ONLINE</span>
                @else
                    <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 text-[9px] px-2 py-0.5 rounded-full font-bold">OFFLINE</span>
                @endif
            </div>
            <a href="https://ipa.logstack.web.id" target="_blank" class="w-full text-center bg-red-700 text-white text-xs font-bold py-2 rounded-lg mt-4 block tracking-wide">Launch FreeIPA ➔</a>
        </div>
        <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-5 rounded-2xl flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div><h4 class="text-sm font-bold text-slate-900 dark:text-white">Nginx Reverse Proxy</h4><p class="text-xs text-slate-400 mt-1">gw.logstack.web.id</p></div>
                @if(($appsStatus['nginx'] ?? 'OFFLINE') === 'ONLINE')
                    <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 text-[9px] px-2 py-0.5 rounded-full font-bold">ONLINE</span>
                @else
                    <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 text-[9px] px-2 py-0.5 rounded-full font-bold">OFFLINE</span>
                @endif
            </div>
            <button type="button" class="w-full text-center bg-blue-600 text-white text-xs font-bold py-2 rounded-lg mt-4 block tracking-wide opacity-50 cursor-default">Reverse Proxy Status</button>
        </div>
    </div>

    <div class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 rounded-2xl p-6">
        <h3 class="font-bold text-slate-900 dark:text-white text-sm mb-4">🌐 Network Domain Routing & IP Inventory</h3>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-gray-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="pb-3 font-semibold">Internal IP</th>
                        <th class="pb-3 font-semibold">Subdomain Mapping</th>
                        <th class="pb-3 font-semibold">Node Name</th>
                        <th class="pb-3 font-semibold">Role Access</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-800/50 text-slate-700 dark:text-gray-300">
                    <tr><td class="py-3 font-mono text-emerald-600 dark:text-emerald-400">172.18.4.101</td><td class="py-3 font-bold">gw.logstack.web.id / sso.logstack.web.id</td><td>gw / sso</td><td><span class="bg-red-50 dark:bg-red-950/80 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/40 px-2 py-0.5 rounded text-[9px] font-bold">ADMIN ONLY</span></td></tr>
                    <tr><td class="py-3 font-mono text-emerald-600 dark:text-emerald-400">172.18.4.102</td><td class="py-3 font-bold">app.logstack.web.id</td><td>app (Portal)</td><td><span class="bg-blue-50 dark:bg-blue-950/80 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/40 px-2 py-0.5 rounded text-[9px] font-bold">ALL USERS</span></td></tr>
                    <tr><td class="py-3 font-mono text-emerald-600 dark:text-emerald-400">172.18.4.103</td><td class="py-3 font-bold">ipa.logstack.web.id</td><td>ipa (IdP Directory)</td><td><span class="bg-red-50 dark:bg-red-950/80 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/40 px-2 py-0.5 rounded text-[9px] font-bold">ADMIN ONLY</span></td></tr>
                    <tr><td class="py-3 font-mono text-emerald-600 dark:text-emerald-400">172.18.4.105</td><td class="py-3 font-bold">drive.logstack.web.id</td><td>drive (Nextcloud)</td><td><span class="bg-blue-50 dark:bg-blue-950/80 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/40 px-2 py-0.5 rounded text-[9px] font-bold">ALL USERS</span></td></tr>
                    <tr><td class="py-3 font-mono text-emerald-600 dark:text-emerald-400">172.18.4.106</td><td class="py-3 font-bold">erp.logstack.web.id</td><td>erp (Odoo Suite)</td><td><span class="bg-blue-50 dark:bg-blue-950/80 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/40 px-2 py-0.5 rounded text-[9px] font-bold">ALL USERS</span></td></tr>
                    <tr><td class="py-3 font-mono text-emerald-600 dark:text-emerald-400">172.18.4.107</td><td class="py-3 font-bold">mbox.logstack.web.id</td><td>mbox (Sogo Mail)</td><td><span class="bg-blue-50 dark:bg-blue-950/80 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/40 px-2 py-0.5 rounded text-[9px] font-bold">ALL USERS</span></td></tr>
                    <tr><td class="py-3 font-mono text-emerald-600 dark:text-emerald-400">172.18.4.108</td><td class="py-3 font-bold">monit.logstack.web.id</td><td>monit (Grafana Stack)</td><td><span class="bg-red-50 dark:bg-red-950/80 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/40 px-2 py-0.5 rounded text-[9px] font-bold">ADMIN ONLY</span></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
