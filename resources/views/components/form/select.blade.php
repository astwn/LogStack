@props(['label', 'name', 'options' => []])

<div class="w-full" x-data="{
    open: false,
    value: '{{ array_key_first($options) }}',
    text: '{{ $options[array_key_first($options)] }}',
    options: {{ json_encode($options) }},
    select(val, txt) {
        this.value = val;
        this.text = txt;
        this.open = false;
    }
}">
    @if($label)
        <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">
            {{ $label }}
        </label>
    @endif
    
    <input type="hidden" name="{{ $name }}" x-model="value">
    
    <div class="relative">
        <button type="button" @click="open = !open" @click.away="open = false"
                class="w-full flex items-center justify-between bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500 transition-colors text-xs text-left">
            <span x-text="text" class="font-mono"></span>
            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
        </button>
        
        <div x-show="open" x-transition x-cloak
             class="absolute z-50 w-full mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl overflow-hidden py-1">
            <template x-for="(label, val) in options" :key="val">
                <button type="button" @click="select(val, label)"
                        class="w-full text-left px-3 py-2 text-xs transition-colors hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-500/20 dark:hover:text-blue-400 font-mono"
                        :class="value === val ? 'bg-slate-50 dark:bg-slate-700/50 font-bold text-slate-900 dark:text-white' : 'text-slate-600 dark:text-gray-300 font-medium'">
                    <span x-text="label"></span>
                </button>
            </template>
        </div>
    </div>
</div>
