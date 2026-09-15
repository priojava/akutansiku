@props([
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Pilih Akun...',
    'required' => false,
    'bgColor' => 'bg-slate-50',
])

@php
    $formattedOptions = collect($options)->map(function($acc) {
        if (is_array($acc)) {
            return [
                'id' => (string)($acc['id'] ?? ''),
                'code' => (string)($acc['code'] ?? ''),
                'name' => (string)($acc['name'] ?? ''),
                'category' => (string)($acc['category'] ?? ''),
            ];
        }
        return [
            'id' => (string)($acc->id ?? ''),
            'code' => (string)($acc->code ?? ''),
            'name' => (string)($acc->name ?? ''),
            'category' => (string)($acc->category ?? ''),
        ];
    })->values()->all();

    $initialSelected = (string)old($name, $selected ?? '');
@endphp

<div x-data="{
        open: false,
        search: '',
        selected: '{{ $initialSelected }}',
        options: {{ Js::from($formattedOptions) }},
        get filteredOptions() {
            if (!this.search) return this.options;
            let s = this.search.toLowerCase();
            return this.options.filter(a => 
                (a.name && a.name.toLowerCase().includes(s)) || 
                (a.code && a.code.toLowerCase().includes(s)) || 
                (a.category && a.category.toLowerCase().includes(s))
            );
        },
        getSelected() {
            return this.options.find(a => String(a.id) === String(this.selected));
        }
    }" 
    class="relative w-full text-left"
    @click.away="open = false">
    
    <input type="hidden" name="{{ $name }}" :value="selected" {{ $required ? 'required' : '' }}>

    <!-- Trigger Input / Display Box -->
    <div @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()); }"
        class="w-full min-h-[38px] px-3.5 py-2 {{ $bgColor }} border border-slate-300 rounded-lg text-xs cursor-pointer flex items-center justify-between hover:bg-slate-100/80 transition focus-within:ring-2 focus-within:ring-blue-500 shadow-2xs"
        :class="selected ? 'border-blue-400 bg-blue-50/20' : ''">
        
        <div class="flex items-center space-x-2 truncate pr-2">
            <template x-if="getSelected()">
                <div class="flex items-center space-x-1.5 truncate">
                    <span class="font-bold text-slate-800" x-text="getSelected().name"></span>
                    <span class="font-mono text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded font-semibold" x-text="getSelected().code"></span>
                    <span x-show="getSelected().category" class="text-[10px] text-slate-400" x-text="'[' + getSelected().category + ']'"></span>
                </div>
            </template>
            <template x-if="!getSelected()">
                <span class="text-slate-400">{{ $placeholder }}</span>
            </template>
        </div>

        <div class="flex items-center space-x-1.5 text-slate-400 flex-shrink-0">
            <i class="fa-solid fa-magnifying-glass text-[11px] text-slate-400"></i>
            <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180 text-blue-600' : ''"></i>
        </div>
    </div>

    <!-- Dropdown Menu with Search -->
    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-300 rounded-xl shadow-xl overflow-hidden">
        
        <!-- Search Input Header -->
        <div class="p-2 border-b border-slate-100 bg-slate-50">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                <input type="text" x-ref="searchInput" x-model="search"
                    placeholder="Ketik nama, kode, atau kategori akun..."
                    @keydown.escape="open = false"
                    class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Options List -->
        <div class="max-h-56 overflow-y-auto divide-y divide-slate-50">
            @if(!$required)
                <div @click="selected = ''; open = false; search = ''"
                    class="p-2.5 text-xs hover:bg-slate-50 cursor-pointer text-slate-400 italic flex items-center justify-between">
                    <span>-- Kosongkan Pilihan --</span>
                    <i x-show="!selected" class="fa-solid fa-check text-slate-400 text-xs"></i>
                </div>
            @endif

            <template x-for="acc in filteredOptions" :key="acc.id">
                <div @click="selected = acc.id; open = false; search = ''"
                    class="p-2.5 text-xs hover:bg-blue-50 cursor-pointer flex items-center justify-between transition"
                    :class="String(selected) === String(acc.id) ? 'bg-blue-50/70 font-semibold' : ''">
                    <div class="truncate pr-2">
                        <div class="text-slate-800 font-medium truncate" x-text="acc.name"></div>
                        <div class="text-[10px] text-slate-500 mt-0.5 flex items-center space-x-1.5">
                            <span class="font-mono bg-slate-100 border border-slate-200 px-1 py-0.5 rounded font-semibold text-slate-700" x-text="acc.code"></span>
                            <span x-show="acc.category" x-text="acc.category"></span>
                        </div>
                    </div>
                    <i x-show="String(selected) === String(acc.id)" class="fa-solid fa-check text-blue-600 text-xs flex-shrink-0"></i>
                </div>
            </template>

            <div x-show="filteredOptions.length === 0" class="p-4 text-center text-xs text-slate-400">
                <i class="fa-solid fa-circle-exclamation mr-1 text-slate-300"></i> Tidak ada akun yang cocok.
            </div>
        </div>
    </div>
</div>
