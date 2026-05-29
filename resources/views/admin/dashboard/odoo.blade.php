<div
     x-data="{
        data: { summary: { tasks: 0, activities: 0, meetings: 0, leads: 0 }, tasks: [], activities: [], meetings: [], todos: [], leads: [] },
        loading: true,
        activeTab: 'tasks',
        fetchData() {
            this.loading = true;
            fetch('/api/odoo/dashboard')
                .then(res => res.json())
                .then(d => { this.data = d; this.loading = false; })
                .catch(() => { this.loading = false; });
        },
        formatDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        },
        formatDateTime(d) {
            if (!d) return '-';
            return new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        }
     }"
     x-init="fetchData()">

    {{-- Loading --}}
    <div x-show="loading" x-cloak class="flex items-center justify-center py-16">
        <div class="w-8 h-8 border-4 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading" x-cloak>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div @click="activeTab = 'tasks'" class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl cursor-pointer hover:border-blue-400 dark:hover:border-blue-600 transition-all" :class="activeTab === 'tasks' ? 'border-blue-400 dark:border-blue-600 ring-1 ring-blue-400/30' : ''">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500"><i class="fas fa-tasks text-xs"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Tasks</p>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white" x-text="data.summary.tasks"></div>
                <p class="text-[10px] text-slate-400 mt-0.5">Tugas aktif</p>
            </div>
            <div @click="activeTab = 'activities'" class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl cursor-pointer hover:border-orange-400 dark:hover:border-orange-600 transition-all" :class="activeTab === 'activities' ? 'border-orange-400 dark:border-orange-600 ring-1 ring-orange-400/30' : ''">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-7 h-7 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-500"><i class="fas fa-bell text-xs"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Aktivitas</p>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white" x-text="data.summary.activities"></div>
                <p class="text-[10px] text-slate-400 mt-0.5">Pending</p>
            </div>
            <div @click="activeTab = 'meetings'" class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl cursor-pointer hover:border-emerald-400 dark:hover:border-emerald-600 transition-all" :class="activeTab === 'meetings' ? 'border-emerald-400 dark:border-emerald-600 ring-1 ring-emerald-400/30' : ''">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500"><i class="fas fa-calendar text-xs"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Meeting</p>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white" x-text="data.summary.meetings"></div>
                <p class="text-[10px] text-slate-400 mt-0.5">Hari ini</p>
            </div>
            <div @click="activeTab = 'leads'" class="bg-slate-50 dark:bg-[#111827] border border-slate-100 dark:border-gray-800 p-4 rounded-2xl cursor-pointer hover:border-purple-400 dark:hover:border-purple-600 transition-all" :class="activeTab === 'leads' ? 'border-purple-400 dark:border-purple-600 ring-1 ring-purple-400/30' : ''">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-7 h-7 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500"><i class="fas fa-funnel-dollar text-xs"></i></div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">CRM Leads</p>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white" x-text="data.summary.leads"></div>
                <p class="text-[10px] text-slate-400 mt-0.5">Aktif</p>
            </div>
        </div>

        {{-- Detail Panel --}}
        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden mt-6">

            {{-- Tasks --}}
            <div x-show="activeTab === 'tasks'" x-cloak>
                <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="fas fa-tasks text-blue-500"></i> My Tasks</h3>
                    <a href="{{ ($serviceUrls['odoo'] ?? 'https://erp.logstack.web.id') . '/odoo/project' }}" target="_blank" class="text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua &rarr;</a>
                </div>
                <template x-if="data.tasks.length === 0">
                    <div class="px-5 py-10 text-center text-slate-400 text-xs"><i class="fas fa-check-circle text-2xl mb-2 block opacity-30"></i>Tidak ada task aktif.</div>
                </template>
                <template x-for="task in data.tasks" :key="task.id">
                    <div class="flex items-center gap-2 sm:gap-4 px-3 sm:px-5 py-3 border-b border-slate-100 dark:border-slate-800/60 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <div class="w-2 h-2 rounded-full flex-shrink-0" :class="task.priority === '1' ? 'bg-red-500' : 'bg-slate-300 dark:bg-slate-600'"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="task.name"></p>
                            <p class="text-[10px] text-slate-400 mt-0.5" x-text="task.project_id ? task.project_id[1] : 'No Project'"></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400" x-text="task.stage_id ? task.stage_id[1] : '-'"></span>
                            <p class="text-[10px] text-slate-400 mt-1" x-text="formatDate(task.date_deadline)"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Activities --}}
            <div x-show="activeTab === 'activities'" x-cloak>
                <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="fas fa-bell text-orange-500"></i> My Activities</h3>
                    <a href="{{ ($serviceUrls['odoo'] ?? 'https://erp.logstack.web.id') . '/odoo' }}" target="_blank" class="text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua &rarr;</a>
                </div>
                <template x-if="data.activities.length === 0">
                    <div class="px-5 py-10 text-center text-slate-400 text-xs"><i class="fas fa-bell-slash text-2xl mb-2 block opacity-30"></i>Tidak ada aktivitas pending.</div>
                </template>
                <template x-for="act in data.activities" :key="act.id">
                    <div class="flex items-center gap-2 sm:gap-4 px-3 sm:px-5 py-3 border-b border-slate-100 dark:border-slate-800/60 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <div class="w-7 h-7 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-500 flex-shrink-0"><i class="fas fa-clock text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="act.summary || act.activity_type_id[1]"></p>
                            <p class="text-[10px] text-slate-400 mt-0.5" x-text="act.res_name || '-'"></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-[10px] font-bold text-orange-600 dark:text-orange-400" x-text="formatDate(act.date_deadline)"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Meetings --}}
            <div x-show="activeTab === 'meetings'" x-cloak>
                <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="fas fa-calendar text-emerald-500"></i> Upcoming Meetings</h3>
                    <a href="{{ ($serviceUrls['odoo'] ?? 'https://erp.logstack.web.id') . '/odoo/calendar' }}" target="_blank" class="text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua &rarr;</a>
                </div>
                <template x-if="data.meetings.length === 0">
                    <div class="px-5 py-10 text-center text-slate-400 text-xs"><i class="fas fa-calendar-times text-2xl mb-2 block opacity-30"></i>Tidak ada meeting minggu ini.</div>
                </template>
                <template x-for="meet in data.meetings" :key="meet.id">
                    <div class="flex items-center gap-2 sm:gap-4 px-3 sm:px-5 py-3 border-b border-slate-100 dark:border-slate-800/60 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500 flex-shrink-0"><i class="fas fa-video text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="meet.name"></p>
                            <p class="text-[10px] text-slate-400 mt-0.5" x-text="meet.location || 'No location'"></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400" x-text="formatDateTime(meet.start)"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- CRM Leads --}}
            <div x-show="activeTab === 'leads'" x-cloak>
                <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="fas fa-funnel-dollar text-purple-500"></i> My CRM Leads</h3>
                    <a href="{{ ($serviceUrls['odoo'] ?? 'https://erp.logstack.web.id') . '/odoo/crm' }}" target="_blank" class="text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua &rarr;</a>
                </div>
                <template x-if="data.leads.length === 0">
                    <div class="px-5 py-10 text-center text-slate-400 text-xs"><i class="fas fa-inbox text-2xl mb-2 block opacity-30"></i>Tidak ada leads aktif.</div>
                </template>
                <template x-for="lead in data.leads" :key="lead.id">
                    <div class="flex items-center gap-2 sm:gap-4 px-3 sm:px-5 py-3 border-b border-slate-100 dark:border-slate-800/60 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <div class="w-2 h-2 rounded-full flex-shrink-0" :class="lead.priority === '1' ? 'bg-red-500' : lead.priority === '2' ? 'bg-orange-500' : 'bg-slate-300'"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="lead.name"></p>
                            <p class="text-[10px] text-slate-400 mt-0.5" x-text="lead.stage_id ? lead.stage_id[1] : '-'"></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-[10px] font-bold text-purple-600 dark:text-purple-400" x-text="lead.expected_revenue ? 'Rp ' + Number(lead.expected_revenue).toLocaleString('id-ID') : '-'"></p>
                            <p class="text-[10px] text-slate-400 mt-0.5" x-text="formatDate(lead.date_deadline)"></p>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </div>
</div>
