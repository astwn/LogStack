@props(['label', 'name', 'type' => 'text', 'placeholder' => '', 'required' => false, 'model' => null])

<div class="w-full">
    @if($label)
        <label class="block font-bold text-slate-700 dark:text-gray-400 mb-1.5 uppercase tracking-wider text-[10px]">
            {{ $label }}
        </label>
    @endif
    
    <input type="{{ $type }}" 
           name="{{ $name }}" 
           {{ $required ? 'required' : '' }} 
           placeholder="{{ $placeholder }}"
           {{ $model ? 'x-model='.$model : '' }}
           {{ $attributes->merge(['class' => 'w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-blue-500 transition-colors font-mono text-xs']) }}>
</div>
