<div x-show="showAddModal" 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     x-cloak>
    
    <div class="bg-[#111827] border border-gray-800 w-full max-w-lg rounded-2xl shadow-2xl p-6" @click.away="showAddModal = false">
        <div class="flex justify-between items-center mb-5 border-b border-gray-800 pb-3">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <span>👤</span> Add New User & Assign Group
            </h3>
            <button @click="showAddModal = false" class="text-gray-500 hover:text-gray-400 text-sm font-bold">✕</button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            
            <div>
                <label class="block text-gray-400 font-semibold mb-1 uppercase tracking-wider">System Username (UID)</label>
                <input type="text" name="username" required placeholder="contoh: awan" class="w-full bg-[#0b0f19] border border-gray-800 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-blue-500 font-mono">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-400 font-semibold mb-1 uppercase tracking-wider">First Name</label>
                    <input type="text" name="first_name" required placeholder="Awan" class="w-full bg-[#0b0f19] border border-gray-800 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-gray-400 font-semibold mb-1 uppercase tracking-wider">Last Name</label>
                    <input type="text" name="last_name" required placeholder="Digital" class="w-full bg-[#0b0f19] border border-gray-800 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-gray-400 font-semibold mb-1 uppercase tracking-wider">Email Address</label>
                <input type="email" name="email" required placeholder="awan@logstack.web.id" class="w-full bg-[#0b0f19] border border-gray-800 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-gray-400 font-semibold mb-1 uppercase tracking-wider">Grup Otorisasi Sistem</label>
                <select name="group" required class="w-full bg-[#0b0f19] border border-gray-800 rounded-lg px-3 py-2 text-gray-300 focus:outline-none focus:border-blue-500 font-medium">
                    <option value="dash_user">User Biasa / Akses Terbatas (Grup: dash_user)</option> <option value="dash_admin">Admin / Infrastruktur (Grup: dash_admin)</option>
                </select>
                <span class="text-[10px] text-gray-500 block mt-1">Menentukan tingkat hak akses dashboard saat login ke portal utama.</span>
            </div>

            <div>
                <label class="block text-gray-400 font-semibold mb-1 uppercase tracking-wider">Temporary Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full bg-[#0b0f19] border border-gray-800 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-blue-500">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-800 mt-6">
                <button type="button" @click="showAddModal = false" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2 rounded-lg font-semibold transition-colors">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2 rounded-lg font-semibold transition-all shadow-md shadow-blue-600/20">🚀 Submit to FreeIPA API</button>
            </div>
        </form>
    </div>
</div>
