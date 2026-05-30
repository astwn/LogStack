@php
    use App\Models\AccessRequest;
    $requests = AccessRequest::latest()->get();
    $pendingCount = $requests->where('status', 'pending')->count();
    $approvedCount = $requests->where('status', 'approved')->count();
    $rejectedCount = $requests->where('status', 'rejected')->count();
@endphp

<div class="space-y-6" x-data="{ showRejectModal: false, rejectId: null, rejectName: '', rejectReason: '' }">

    {{-- MODAL REJECT --}}
    <div x-show="showRejectModal"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
         x-transition x-cloak>
        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 w-full max-w-md p-6 rounded-2xl shadow-2xl">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-950/40 flex items-center justify-center text-red-500 flex-shrink-0">
                    <i class="fas fa-times"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">Tolak Request</h3>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-0.5">Request dari <span class="font-bold" x-text="rejectName"></span></p>
                </div>
            </div>
            <form :action="'/admin/access-requests/' + rejectId + '/reject'" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alasan Penolakan <span class="text-red-400">*</span></label>
                    <textarea name="reject_reason" x-model="rejectReason" required rows="3"
                        placeholder="Jelaskan alasan penolakan..."
                        class="w-full bg-slate-50 dark:bg-[#0b0e14] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-red-500 transition-colors resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showRejectModal = false"
                        class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold text-xs px-4 py-2 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-times"></i> Tolak Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">🔔 Access Requests</h1>
            <p class="text-xs text-slate-500 dark:text-gray-400 font-medium">Kelola permintaan akses dari pengguna baru.</p>
        </div>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-yellow-50 dark:bg-yellow-950/20 border border-yellow-200 dark:border-yellow-800/30 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-yellow-500/10 flex items-center justify-center text-yellow-500 flex-shrink-0">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-yellow-600 dark:text-yellow-400 uppercase tracking-wider">Pending</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $pendingCount }}</p>
            </div>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/30 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 flex-shrink-0">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Approved</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $approvedCount }}</p>
            </div>
        </div>
        <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800/30 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center text-red-500 flex-shrink-0">
                <i class="fas fa-times"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-red-600 dark:text-red-400 uppercase tracking-wider">Rejected</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $rejectedCount }}</p>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        @if($requests->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-12 h-12 bg-slate-100 dark:bg-gray-800/60 text-slate-400 rounded-full flex items-center justify-center text-xl mb-3">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700 dark:text-white">Belum ada request</h3>
                <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1">Permintaan akses dari pengguna baru akan muncul di sini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-[#0d131f] border-b border-slate-200 dark:border-slate-800 text-[10px] text-slate-500 dark:text-gray-400 uppercase tracking-widest">
                            <th class="px-5 py-4 font-bold">Nama</th>
                            <th class="px-5 py-4 font-bold">Username</th>
                            <th class="px-5 py-4 font-bold">Email</th>
                            <th class="px-5 py-4 font-bold">Departemen</th>
                            <th class="px-5 py-4 font-bold">Status</th>
                            <th class="px-5 py-4 font-bold">Waktu</th>
                            <th class="px-5 py-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @foreach($requests as $req)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-5 py-4">
                                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $req->name }}</p>
                                @if($req->reason)
                                    <p class="text-[10px] text-slate-400 mt-0.5 truncate max-w-[150px]" title="{{ $req->reason }}">{{ $req->reason }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs font-mono font-bold text-slate-700 dark:text-gray-300">{{ $req->username }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs text-slate-600 dark:text-gray-400">{{ $req->email }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs text-slate-600 dark:text-gray-400">{{ $req->department ?: '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if($req->status === 'pending')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-lg bg-yellow-50 dark:bg-yellow-950/40 text-yellow-600 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800/30">
                                        <i class="fas fa-clock text-[8px]"></i> Pending
                                    </span>
                                @elseif($req->status === 'approved')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/30">
                                        <i class="fas fa-check text-[8px]"></i> Approved
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/30">
                                        <i class="fas fa-times text-[8px]"></i> Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-[10px] text-slate-500 dark:text-gray-400">{{ $req->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</p>
                                @if($req->processed_by)
                                    <p class="text-[9px] text-slate-400 mt-0.5">by {{ $req->processed_by }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($req->isPending())
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="{{ route('admin.access-requests.approve', $req->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 border border-transparent dark:border-emerald-800/30">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <button @click="showRejectModal = true; rejectId = {{ $req->id }}; rejectName = '{{ addslashes($req->name) }}'; rejectReason = ''"
                                            class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 border border-transparent dark:border-red-800/30">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    </div>
                                @elseif($req->isRejected() && $req->reject_reason)
                                    <p class="text-[10px] text-red-500 dark:text-red-400 max-w-[150px] truncate" title="{{ $req->reject_reason }}">
                                        {{ $req->reject_reason }}
                                    </p>
                                @else
                                    <span class="text-[10px] text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
