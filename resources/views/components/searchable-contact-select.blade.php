@props([
    'name' => 'contact_id',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Tanpa Kontak...',
    'required' => false,
    'bgColor' => 'bg-slate-50',
])

@php
    $formattedOptions = collect($options)->map(function($c) {
        if (is_array($c)) {
            return [
                'id' => (string)($c['id'] ?? ''),
                'name' => (string)($c['name'] ?? ''),
                'type' => (string)($c['type'] ?? 'contact'),
                'phone' => (string)($c['phone'] ?? ''),
                'email' => (string)($c['email'] ?? ''),
            ];
        }
        return [
            'id' => (string)($c->id ?? ''),
            'name' => (string)($c->name ?? ''),
            'type' => (string)($c->type ?? 'contact'),
            'phone' => (string)($c->phone ?? ''),
            'email' => (string)($c->email ?? ''),
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
            return this.options.filter(c => 
                (c.name && c.name.toLowerCase().includes(s)) || 
                (c.type && c.type.toLowerCase().includes(s)) || 
                (c.phone && c.phone.toLowerCase().includes(s)) ||
                (c.email && c.email.toLowerCase().includes(s))
            );
        },
        getSelected() {
            return this.options.find(c => String(c.id) === String(this.selected));
        },
        getTypeLabel(type) {
            let t = (type || '').toLowerCase();
            if (t === 'customer' || t === 'pelanggan') return 'Pelanggan';
            if (t === 'vendor' || t === 'supplier') return 'Vendor / Supplier';
            if (t === 'employee' || t === 'karyawan') return 'Karyawan';
            return type.charAt(0).toUpperCase() + type.slice(1);
        },
        getTypeBadgeClass(type) {
            let t = (type || '').toLowerCase();
            if (t === 'customer' || t === 'pelanggan') return 'bg-blue-50 text-blue-700 border-blue-200';
            if (t === 'vendor' || t === 'supplier') return 'bg-purple-50 text-purple-700 border-purple-200';
            if (t === 'employee' || t === 'karyawan') return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            return 'bg-slate-100 text-slate-700 border-slate-200';
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
                <div class="flex items-center space-x-2 truncate">
                    <span class="font-bold text-slate-800" x-text="getSelected().name"></span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded border font-semibold"
                          :class="getTypeBadgeClass(getSelected().type)"
                          x-text="getTypeLabel(getSelected().type)"></span>
                    <span x-show="getSelected().phone" class="text-[10px] text-slate-400 font-mono" x-text="'(' + getSelected().phone + ')'"></span>
                </div>
            </template>
            <template x-if="!getSelected()">
                <span class="text-slate-400">{{ $placeholder }}</span>
            </template>
        </div>

        <div class="flex items-center space-x-1.5 text-slate-400 flex-shrink-0">
            <i class="fa-solid fa-user-tag text-[11px] text-slate-400"></i>
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
                    placeholder="Cari nama kontak, tipe (pelanggan/vendor), atau telepon..."
                    @keydown.escape="open = false"
                    class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Options List -->
        <div class="max-h-56 overflow-y-auto divide-y divide-slate-50">
            @if(!$required)
                <div @click="selected = ''; open = false; search = ''"
                    class="p-2.5 text-xs hover:bg-slate-50 cursor-pointer text-slate-400 italic flex items-center justify-between">
                    <span>-- Tanpa Kontak (Bukan Transaksi Rekanan) --</span>
                    <i x-show="!selected" class="fa-solid fa-check text-slate-400 text-xs"></i>
                </div>
            @endif

            <template x-for="c in filteredOptions" :key="c.id">
                <div @click="selected = c.id; open = false; search = ''"
                    class="p-2.5 text-xs hover:bg-blue-50 cursor-pointer flex items-center justify-between transition"
                    :class="String(selected) === String(c.id) ? 'bg-blue-50/70 font-semibold' : ''">
                    <div class="truncate pr-2">
                        <div class="text-slate-800 font-medium truncate" x-text="c.name"></div>
                        <div class="text-[10px] text-slate-500 mt-0.5 flex items-center space-x-1.5">
                            <span class="px-1.5 py-0.2 rounded border font-semibold text-[9px]"
                                  :class="getTypeBadgeClass(c.type)"
                                  x-text="getTypeLabel(c.type)"></span>
                            <span x-show="c.phone" class="font-mono text-slate-400" x-text="c.phone"></span>
                            <span x-show="c.email" class="text-slate-400 truncate" x-text="'• ' + c.email"></span>
                        </div>
                    </div>
                    <i x-show="String(selected) === String(c.id)" class="fa-solid fa-check text-blue-600 text-xs flex-shrink-0"></i>
                </div>
            </template>

            <div x-show="options.length > 0 && filteredOptions.length === 0" class="p-4 text-center text-xs text-slate-400">
                <i class="fa-solid fa-circle-exclamation mr-1 text-slate-300"></i> Tidak ada kontak yang cocok dengan kata kunci.
            </div>

            <div x-show="options.length === 0" class="p-4 text-center text-xs text-slate-400">
                <i class="fa-solid fa-user-plus text-slate-300 text-base mb-1 block"></i>
                <div class="font-medium text-slate-600">Belum ada data kontak di perusahaan ini.</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Kontak tersimpan per perusahaan (multi-tenant).</div>
                <div class="mt-2.5">
                    <a href="{{ route('master.contacts') }}" target="_blank" 
                       class="inline-flex items-center text-[11px] font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100/80 px-2.5 py-1 rounded-md border border-blue-200 transition">
                        <i class="fa-solid fa-plus mr-1 text-[9px]"></i> + Buat Pelanggan / Vendor
                    </a>
                </div>
            </div>
        </div>

        <!-- Dropdown Footer -->
        <div class="p-2 border-t border-slate-100 bg-slate-50/80 flex items-center justify-between text-[11px]">
            <span class="text-slate-400">Total: <strong class="text-slate-600" x-text="options.length"></strong> kontak</span>
            <a href="{{ route('master.contacts') }}" target="_blank" class="font-semibold text-blue-600 hover:underline flex items-center">
                <i class="fa-solid fa-arrow-up-right-from-square text-[9px] mr-1"></i> Kelola Kontak
            </a>
        </div>
    </div>
</div>
