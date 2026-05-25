<div x-data="{ 
        showEditModal: false, 
        username: '', 
        firstName: '', 
        lastName: '', 
        email: '',
        currentGroup: ''
     }"
     @setup-edit-modal.window="
        showEditModal = true;
        username = $event.detail.username;
        email = $event.detail.email;
        currentGroup = $event.detail.group;
        let names = $event.detail.fullname.split(' ');
        firstName = names[0] || '';
        lastName = names.slice(1).join(' ') || '';
     "
     x-show="showEditModal" 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
     x-cloak>
    
    <div class="bg-[#111827] border border-gray-800 w-full max-w-lg rounded-2xl shadow-2xl p-6" @click.away="showEditModal = false">
        <div class="flex justify-between items-center mb-5 border-b border-gray-800 pb-3">
            <h3 class="text-lg font-bold text-white flex items-center gap-2"><span>📝</span> Edit User & Role</h3>
            <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-400 text-sm font-bold">✕</button>
        </div>

        <form :action="'/admin/users/' + username" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-gray-500 font-semibold mb-1 uppercase tracking-wider">Username (Locked)</label>
                <input type="text" x-model="username" disabled class="w-full bg-[#0b0f19]/60 border border-gray-800 rounded-lg px-3 py-2 text-gray-500 font-mono">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-400 font-semibold mb-1 uppercase tracking-wider">First Name</label>
                    <input type="text" name="first_name" required x-model="firstName" class="w-full bg-[#0b0f19] border border-gray-800 rounded-lg px-3 py-2 text-white focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-gray-400 font-semibold mb-1 uppercase tracking-wider">Last Name</label>
                    <input type="text" name="last_name" required x-model="lastName" class="w-full bg-[#0b0f19] border border-gray-800 rounded-lg px-3 py-2 text-white focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-gray-400 font-semibold mb-1 uppercase tracking-wider">Email Address</label>
                <input type="email" name="email" required x-model="email" class="w-full bg-[#0b0f19] border border-gray-800 rounded-lg px-3 py-2 text-white focus:border-blue-500">
            </div>

            <div>
                <label class="block text-gray-400 font-semibold mb-1 uppercase tracking-wider">Role / Otorisasi Grup</label>
                <select name="group" x-model="currentGroup" required class="w-full bg-[#0b0f19] border border-gray-800 rounded-lg px-3 py-2 text-gray-300 focus:border-blue-500 font-medium">
                    <option value="dash_user">User Biasa (dash_user)</option>
                    <option value="dash_admin">Admin Portal (dash_admin)</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-800 mt-6">
                <button type="button" @click="showEditModal = false" class="bg-gray-800 text-gray-300 px-4 py-2 rounded-lg font-semibold">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-semibold shadow-md shadow-blue-600/20">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
