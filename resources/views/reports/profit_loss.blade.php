@extends('layouts.app')

@php
    $currentDimensionList = match($groupBy ?? 'department') {
        'project' => $projects,
        'tag' => $tags,
        'month', 'period' => collect(),
        default => $departments,
    };
    $selectedIds = $selectedIds ?? [];
    $repHeaderTitle = match($groupBy ?? 'department') {
        'project' => 'Profit & Loss by Project',
        'tag' => 'Profit & Loss by Tag',
        'month', 'period' => 'Profit & Loss (Multi Period)',
        default => 'Profit & Loss by Department'
    };
@endphp

@section('title', $viewMode === 'by_project' ? $repHeaderTitle : 'Laporan Laba Rugi')

@section('content')
<div class="space-y-6" x-data="{ showCodes: false, activeTab: '{{ $viewMode ?? 'standard' }}' }">

    <!-- Top Action & Filter Toolbar (Hidden on Print) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-xs space-y-4 print:hidden">
        
        <!-- View Mode Switcher Tabs -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center space-x-1.5 bg-slate-100 p-1 rounded-xl">
                <a href="{{ route('reports.profit_loss', array_merge(request()->except(['view_mode', 'page']), ['view_mode' => 'standard'])) }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1.5 {{ $viewMode !== 'by_project' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-file-lines text-xs"></i>
                    <span>Format Standar</span>
                </a>
                <a href="{{ route('reports.profit_loss', array_merge(request()->except(['view_mode', 'page']), ['view_mode' => 'by_project'])) }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1.5 {{ $viewMode === 'by_project' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-table-columns text-xs"></i>
                    <span>Komparatif</span>
                </a>
            </div>

            <!-- Right Action Buttons -->
            <div class="flex items-center space-x-2">
                <!-- Toggle Kode Akun -->
                <button type="button" @click="showCodes = !showCodes" 
                        :class="showCodes ? 'bg-blue-50 border-blue-300 text-blue-700 font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'" 
                        class="px-3 py-2 border text-xs font-semibold rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-barcode text-xs"></i>
                    <span x-text="showCodes ? 'Sembunyikan Kode' : 'Tampilkan Kode'"></span>
                </button>

                <!-- Export Excel -->
                <button type="button" onclick="exportToExcel()" class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-slate-700 text-xs font-bold rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                    <i class="fa-regular fa-file-excel text-emerald-600 text-sm"></i>
                    <span>Excel</span>
                </button>

                <!-- Cetak PDF / Print -->
                <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 text-slate-700 text-xs font-bold rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-print text-slate-500 text-xs"></i>
                    <span>Cetak / PDF</span>
                </button>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('reports.profit_loss') }}" class="flex flex-wrap items-center gap-2.5">
            <input type="hidden" name="view_mode" value="{{ $viewMode }}">

            <!-- Date Range Inputs -->
            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-700 shadow-2xs">
                <i class="fa-regular fa-calendar-days text-slate-400 mr-2 text-xs"></i>
                <span class="font-medium mr-1.5 text-slate-500">Periode:</span>
                <input type="date" name="start_date" id="start_date_input" value="{{ $startDate }}" class="bg-transparent focus:outline-none font-semibold text-slate-800 cursor-pointer">
                <span class="mx-2 text-slate-400 font-bold">s/d</span>
                <input type="date" name="end_date" id="end_date_input" value="{{ $endDate }}" class="bg-transparent focus:outline-none font-semibold text-slate-800 cursor-pointer">
            </div>

            @if($viewMode === 'by_project')
                <!-- Pengelompokan Kolom Dimensi -->
                <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs">
                    <span class="text-slate-500 mr-1.5 whitespace-nowrap">Dimensi:</span>
                    <select name="group_by" class="bg-transparent font-bold text-slate-800 focus:outline-none cursor-pointer" onchange="this.form.submit()">
                        <option value="department" {{ ($groupBy ?? 'department') === 'department' ? 'selected' : '' }}>🏢 Per Departemen</option>
                        @if(count($projects) > 0)
                            <option value="project" {{ ($groupBy ?? '') === 'project' ? 'selected' : '' }}>📁 Per Master Proyek</option>
                        @endif
                        @if(count($tags) > 0)
                            <option value="tag" {{ ($groupBy ?? '') === 'tag' ? 'selected' : '' }}>🏷️ Per Tag / Label</option>
                        @endif
                        <option value="month" {{ in_array(($groupBy ?? ''), ['month', 'period']) ? 'selected' : '' }}>📅 Multi-Periode (Bulanan)</option>
                    </select>
                </div>

                @if(!in_array($groupBy, ['month', 'period']) && count($currentDimensionList) > 0)
                    <!-- Multi-Select Checkbox Dropdown -->
                    <div class="relative" x-data="{
                        dropdownOpen: false,
                        searchQuery: '',
                        selected: {{ json_encode(array_map('intval', $selectedIds)) }},
                        items: {{ json_encode($currentDimensionList->map(fn($item) => ['id' => $item->id, 'name' => $item->name, 'code' => $item->code ?? ''])) }},
                        selectAll() {
                            this.selected = this.items.map(i => i.id);
                        },
                        clearAll() {
                            this.selected = [];
                        },
                        get filteredItems() {
                            if (!this.searchQuery) return this.items;
                            return this.items.filter(i => i.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || (i.code && i.code.toLowerCase().includes(this.searchQuery.toLowerCase())));
                        }
                    }">
                        <!-- Dropdown Button Trigger -->
                        <button type="button" @click="dropdownOpen = !dropdownOpen" 
                                class="flex items-center justify-between gap-2 px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 shadow-2xs hover:bg-slate-50 cursor-pointer min-w-[220px]">
                            <div class="flex items-center gap-1.5 truncate">
                                <i class="fa-solid fa-list-check text-blue-600"></i>
                                <template x-if="selected.length === 0">
                                    <span class="text-slate-600">Semua {{ match($groupBy) { 'project' => 'Proyek', 'tag' => 'Tag', default => 'Departemen' } }} ({{ count($currentDimensionList) }})</span>
                                </template>
                                <template x-if="selected.length > 0">
                                    <span class="text-blue-700 font-bold" x-text="selected.length + ' {{ match($groupBy) { 'project' => 'Proyek', 'tag' => 'Tag', default => 'Departemen' } }} Dipilih'"></span>
                                </template>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="dropdownOpen ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- Dropdown Popover Menu -->
                        <div x-show="dropdownOpen" @click.outside="dropdownOpen = false" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute left-0 mt-1.5 w-72 bg-white rounded-2xl border border-slate-200 shadow-xl z-50 p-3 space-y-2.5" 
                             style="display: none;">
                            
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 text-xs">
                                <span class="font-bold text-slate-800">Pilih {{ match($groupBy) { 'project' => 'Proyek', 'tag' => 'Tag', default => 'Departemen' } }}</span>
                                <div class="flex items-center space-x-2 text-[11px]">
                                    <button type="button" @click="selectAll()" class="text-blue-600 hover:underline font-bold cursor-pointer">Semua</button>
                                    <span class="text-slate-300">|</span>
                                    <button type="button" @click="clearAll()" class="text-slate-500 hover:text-rose-600 font-semibold cursor-pointer">Reset</button>
                                </div>
                            </div>

                            <!-- Search inside dropdown -->
                            <div class="relative" x-show="items.length > 5">
                                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                                <input type="text" x-model="searchQuery" placeholder="Cari..." 
                                       class="w-full pl-7 pr-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:bg-white focus:border-blue-500">
                            </div>

                            <!-- List of Checkboxes -->
                            <div class="max-h-52 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                <template x-for="item in filteredItems" :key="item.id">
                                    <label class="flex items-center space-x-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer text-xs transition">
                                        <input type="checkbox" name="selected_ids[]" :value="item.id" x-model="selected"
                                               class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-800" x-text="item.name"></span>
                                            <span x-show="item.code" class="text-[10px] text-slate-400 font-mono" x-text="item.code"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>

                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] text-slate-500" x-text="selected.length + ' dipilih'"></span>
                                <button type="button" @click="dropdownOpen = false; $el.closest('form').submit()" 
                                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition cursor-pointer shadow-xs">
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <!-- Filter Tag / Proyek untuk Mode Standar -->
                <select name="tag_id" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:bg-white focus:outline-none">
                    <option value="">Semua Tag / Proyek</option>
                    @foreach($tags as $t)
                        <option value="{{ $t->id }}" {{ ($tagId ?? '') == $t->id ? 'selected' : '' }}>
                            🏷️ {{ $t->name }}
                        </option>
                    @endforeach
                </select>

                @if(count($projects) > 0)
                    <select name="project_id" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:bg-white focus:outline-none">
                        <option value="">Semua Proyek Master</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ ($projectId ?? '') == $p->id ? 'selected' : '' }}>
                                📁 {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                @if(count($departments) > 0)
                    <select name="department_id" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:bg-white focus:outline-none">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ ($departmentId ?? '') == $d->id ? 'selected' : '' }}>
                                🏢 {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            @endif

            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-filter text-[11px]"></i>
                <span>Tampilkan</span>
            </button>

            <!-- Quick Filter Shortcuts -->
            <div class="hidden xl:flex items-center space-x-1 pl-2 border-l border-slate-200 text-xs text-slate-600">
                <span class="text-[11px] text-slate-400 mr-1 font-medium">Pilihan Cepat:</span>
                <button type="button" @click="document.getElementById('start_date_input').value = '{{ \Carbon\Carbon::now()->startOfMonth()->toDateString() }}'; document.getElementById('end_date_input').value = '{{ \Carbon\Carbon::now()->endOfMonth()->toDateString() }}'; $el.closest('form').submit()" 
                        class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold rounded-lg transition cursor-pointer">Bulan Ini</button>
                <button type="button" @click="document.getElementById('start_date_input').value = '{{ \Carbon\Carbon::now()->subMonth()->startOfMonth()->toDateString() }}'; document.getElementById('end_date_input').value = '{{ \Carbon\Carbon::now()->subMonth()->endOfMonth()->toDateString() }}'; $el.closest('form').submit()" 
                        class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold rounded-lg transition cursor-pointer">Bulan Lalu</button>
                <button type="button" @click="document.getElementById('start_date_input').value = '{{ \Carbon\Carbon::now()->startOfYear()->toDateString() }}'; document.getElementById('end_date_input').value = '{{ \Carbon\Carbon::now()->endOfYear()->toDateString() }}'; $el.closest('form').submit()" 
                        class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold rounded-lg transition cursor-pointer">Tahun Ini</button>
            </div>
        </form>

    </div>

    @php
        $formatNum = function($val, $zeroText = '0') {
            if ($val === null || $val == 0) {
                return $zeroText;
            }
            if ($val < 0) {
                return '(' . number_format(abs($val), 0, ',', '.') . ')';
            }
            return number_format($val, 0, ',', '.');
        };

        $formatDecimal = function($val, $zeroText = '0,00') {
            if ($val === null || $val == 0) {
                return $zeroText;
            }
            if ($val < 0) {
                return '(' . number_format(abs($val), 2, ',', '.') . ')';
            }
            return number_format($val, 2, ',', '.');
        };
    @endphp

    @if($viewMode === 'by_project')
        <!-- ========================================================================================= -->
        <!-- VIEW MODE: KOMPARATIF MULTI-KOLOM (ACCURATE STYLE MATRIX)                                 -->
        <!-- ========================================================================================= -->
        @php
            $columns = $projectReport['columns'] ?? [];
            $colKeys = array_keys($columns);
            $totalCols = count($columns) + 2; // Description + All columns + Total
        @endphp

        <div class="max-w-7xl mx-auto bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-10 lg:p-12 print:p-0 print:border-none print:shadow-none overflow-x-auto" id="profit-loss-sheet">
            
            <!-- Formal Report Header -->
            <div class="text-center pb-6 border-b-2 border-slate-900">
                <h2 class="text-sm sm:text-base font-bold text-slate-800 uppercase tracking-widest">{{ $company->name }}</h2>
                <h1 class="text-xl sm:text-2xl font-black text-rose-950 uppercase tracking-tight mt-0.5">{{ $repHeaderTitle }}</h1>
                <p class="text-xs sm:text-sm text-slate-600 font-medium mt-1">
                    Untuk Periode {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
                </p>
                <p class="text-[11px] text-slate-400 italic mt-0.5">
                    (Dinyatakan dalam Rupiah Indonesia)
                </p>
            </div>

            <!-- Statement Table -->
            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-xs sm:text-sm text-slate-900 border-collapse min-w-[700px]" id="report-table-export">
                    <thead>
                        <tr class="border-b-2 border-slate-900 text-slate-900 bg-slate-50/50">
                            <th class="py-2.5 text-left font-bold text-blue-900 uppercase tracking-wider pl-3 w-1/3 min-w-[200px]">
                                Description
                            </th>
                            @foreach($columns as $colId => $col)
                                <th class="py-2.5 text-right font-bold text-blue-900 px-3 uppercase tracking-wider min-w-[120px]">
                                    {{ $col['name'] }}
                                </th>
                            @endforeach
                            <th class="py-2.5 text-right font-black text-slate-950 px-3 uppercase tracking-wider min-w-[130px] border-l border-slate-300">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody>

                        <!-- ================= 1. OPERATING REVENUE ================= -->
                        <tr>
                            <td class="font-black text-slate-950 pt-4 pb-1 uppercase tracking-wide" colspan="{{ $totalCols }}">
                                OPERATING REVENUE
                            </td>
                        </tr>
                        <tr>
                            <td class="font-bold text-slate-800 pt-1 pb-1 pl-4" colspan="{{ $totalCols }}">
                                Pendapatan
                            </td>
                        </tr>

                        @forelse($projectReport['operating_revenue']['accounts'] as $acc)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-1 pl-8 text-slate-800">
                                    <span x-show="showCodes" class="font-mono text-[11px] text-slate-500 mr-2" style="display: none;">{{ $acc['code'] }}</span>
                                    <span>{{ $acc['name'] }}</span>
                                </td>
                                @foreach($colKeys as $cId)
                                    <td class="py-1 text-right font-mono text-slate-900 px-3">
                                        {{ $formatDecimal($acc['amounts'][$cId] ?? 0) }}
                                    </td>
                                @endforeach
                                <td class="py-1 text-right font-mono font-semibold text-slate-950 px-3 border-l border-slate-200">
                                    {{ $formatDecimal($acc['amounts']['total'] ?? 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-1 pl-8 text-slate-400 italic">Penjualan</td>
                                @foreach($colKeys as $cId)
                                    <td class="py-1 text-right font-mono text-slate-400 px-3">0,00</td>
                                @endforeach
                                <td class="py-1 text-right font-mono text-slate-400 px-3 border-l border-slate-200">0,00</td>
                            </tr>
                        @endforelse

                        <!-- Total Operating Revenue -->
                        <tr class="border-t border-slate-800 font-bold">
                            <td class="pt-2 pb-1 pl-4 text-slate-950 uppercase tracking-wide">
                                Total OPERATING REVENUE
                            </td>
                            @foreach($colKeys as $cId)
                                <td class="pt-2 pb-1 text-right font-mono text-slate-950 px-3">
                                    {{ $formatDecimal($projectReport['operating_revenue']['total'][$cId] ?? 0) }}
                                </td>
                            @endforeach
                            <td class="pt-2 pb-1 text-right font-mono font-black text-slate-950 px-3 border-l border-slate-200">
                                {{ $formatDecimal($projectReport['operating_revenue']['total']['total'] ?? 0) }}
                            </td>
                        </tr>
                        <tr><td colspan="{{ $totalCols }}" class="h-4"></td></tr>


                        <!-- ================= 2. COST OF GOODS SOLD (HPP) ================= -->
                        <tr>
                            <td class="font-black text-slate-950 pt-2 pb-1 uppercase tracking-wide" colspan="{{ $totalCols }}">
                                Cost of Goods Sold
                            </td>
                        </tr>
                        @forelse($projectReport['cogs']['accounts'] as $acc)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-1 pl-6 text-slate-800">
                                    <span x-show="showCodes" class="font-mono text-[11px] text-slate-500 mr-2" style="display: none;">{{ $acc['code'] }}</span>
                                    <span>{{ $acc['name'] }}</span>
                                </td>
                                @foreach($colKeys as $cId)
                                    <td class="py-1 text-right font-mono text-slate-900 px-3">
                                        {{ $formatDecimal($acc['amounts'][$cId] ?? 0) }}
                                    </td>
                                @endforeach
                                <td class="py-1 text-right font-mono font-semibold text-slate-950 px-3 border-l border-slate-100">
                                    {{ $formatDecimal($acc['amounts']['total'] ?? 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-1 pl-6 text-slate-400 italic">COGS / HPP</td>
                                @foreach($colKeys as $cId)
                                    <td class="py-1 text-right font-mono text-slate-400 px-3">0,00</td>
                                @endforeach
                                <td class="py-1 text-right font-mono text-slate-400 px-3 border-l border-slate-100">0,00</td>
                            </tr>
                        @endforelse

                        <!-- Total Cost of Goods Sold -->
                        <tr class="border-t border-slate-800 font-bold">
                            <td class="pt-2 pb-1 pl-4 text-slate-950">
                                Total Cost of Goods Sold
                            </td>
                            @foreach($colKeys as $cId)
                                <td class="pt-2 pb-1 text-right font-mono text-slate-950 px-3">
                                    {{ $formatDecimal($projectReport['cogs']['total'][$cId] ?? 0) }}
                                </td>
                            @endforeach
                            <td class="pt-2 pb-1 text-right font-mono font-black text-slate-950 px-3 border-l border-slate-200">
                                {{ $formatDecimal($projectReport['cogs']['total']['total'] ?? 0) }}
                            </td>
                        </tr>
                        <tr><td colspan="{{ $totalCols }}" class="h-2"></td></tr>


                        <!-- ================= 3. GROSS PROFIT ================= -->
                        <tr class="border-t-2 border-b border-slate-900 bg-slate-50/50 font-black">
                            <td class="py-2 pl-2 text-slate-950 uppercase tracking-wide">
                                GROSS PROFIT
                            </td>
                            @foreach($colKeys as $cId)
                                @php $gpVal = $projectReport['gross_profit'][$cId] ?? 0; @endphp
                                <td class="py-2 text-right font-mono text-slate-950 px-3 {{ $gpVal < 0 ? 'text-rose-600' : '' }}">
                                    {{ $formatDecimal($gpVal) }}
                                </td>
                            @endforeach
                            @php $totalGp = $projectReport['gross_profit']['total'] ?? 0; @endphp
                            <td class="py-2 text-right font-mono font-black text-slate-950 px-3 border-l border-slate-200 {{ $totalGp < 0 ? 'text-rose-600' : '' }}">
                                {{ $formatDecimal($totalGp) }}
                            </td>
                        </tr>
                        <tr><td colspan="{{ $totalCols }}" class="h-4"></td></tr>


                        <!-- ================= 4. OPERATING EXPENSES ================= -->
                        <tr>
                            <td class="font-black text-slate-950 pt-2 pb-1 uppercase tracking-wide" colspan="{{ $totalCols }}">
                                Operating Expenses
                            </td>
                        </tr>
                        @forelse($projectReport['operating_expenses']['accounts'] as $acc)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-1 pl-6 text-slate-800">
                                    <span x-show="showCodes" class="font-mono text-[11px] text-slate-500 mr-2" style="display: none;">{{ $acc['code'] }}</span>
                                    <span>{{ $acc['name'] }}</span>
                                </td>
                                @foreach($colKeys as $cId)
                                    <td class="py-1 text-right font-mono text-slate-900 px-3">
                                        {{ $formatDecimal($acc['amounts'][$cId] ?? 0) }}
                                    </td>
                                @endforeach
                                <td class="py-1 text-right font-mono font-semibold text-slate-950 px-3 border-l border-slate-100">
                                    {{ $formatDecimal($acc['amounts']['total'] ?? 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-1 pl-6 text-slate-400 italic">Operating Expenses</td>
                                @foreach($colKeys as $cId)
                                    <td class="py-1 text-right font-mono text-slate-400 px-3">0,00</td>
                                @endforeach
                                <td class="py-1 text-right font-mono text-slate-400 px-3 border-l border-slate-100">0,00</td>
                            </tr>
                        @endforelse

                        <!-- Total Operating Expenses -->
                        <tr class="border-t border-slate-800 font-bold">
                            <td class="pt-2 pb-1 pl-4 text-slate-950">
                                Total Operating Expenses
                            </td>
                            @foreach($colKeys as $cId)
                                <td class="pt-2 pb-1 text-right font-mono text-slate-950 px-3">
                                    {{ $formatDecimal($projectReport['operating_expenses']['total'][$cId] ?? 0) }}
                                </td>
                            @endforeach
                            <td class="pt-2 pb-1 text-right font-mono font-black text-slate-950 px-3 border-l border-slate-200">
                                {{ $formatDecimal($projectReport['operating_expenses']['total']['total'] ?? 0) }}
                            </td>
                        </tr>
                        <tr><td colspan="{{ $totalCols }}" class="h-2"></td></tr>


                        <!-- ================= 5. INCOME FROM OPERATION ================= -->
                        <tr class="border-t-2 border-b border-slate-900 bg-slate-50/50 font-black">
                            <td class="py-2 pl-2 text-slate-950 uppercase tracking-wide">
                                INCOME FROM OPERATION
                            </td>
                            @foreach($colKeys as $cId)
                                @php $opVal = $projectReport['operating_income'][$cId] ?? 0; @endphp
                                <td class="py-2 text-right font-mono text-slate-950 px-3 {{ $opVal < 0 ? 'text-rose-600' : '' }}">
                                    {{ $formatDecimal($opVal) }}
                                </td>
                            @endforeach
                            @php $totalOp = $projectReport['operating_income']['total'] ?? 0; @endphp
                            <td class="py-2 text-right font-mono font-black text-slate-950 px-3 border-l border-slate-200 {{ $totalOp < 0 ? 'text-rose-600' : '' }}">
                                {{ $formatDecimal($totalOp) }}
                            </td>
                        </tr>
                        <tr><td colspan="{{ $totalCols }}" class="h-4"></td></tr>


                        <!-- ================= 6. OTHER INCOME AND EXPENSES ================= -->
                        <tr>
                            <td class="font-black text-slate-950 pt-2 pb-1 uppercase tracking-wide" colspan="{{ $totalCols }}">
                                Other Income and Expenses
                            </td>
                        </tr>

                        <!-- Other Income -->
                        <tr>
                            <td class="font-bold text-slate-800 pt-1 pb-0.5 pl-4" colspan="{{ $totalCols }}">
                                Other Income
                            </td>
                        </tr>
                        @forelse($projectReport['other_income']['accounts'] as $acc)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-0.5 pl-8 text-slate-700">
                                    <span x-show="showCodes" class="font-mono text-[11px] text-slate-500 mr-2" style="display: none;">{{ $acc['code'] }}</span>
                                    <span>{{ $acc['name'] }}</span>
                                </td>
                                @foreach($colKeys as $cId)
                                    <td class="py-0.5 text-right font-mono text-slate-800 px-3">
                                        {{ $formatDecimal($acc['amounts'][$cId] ?? 0) }}
                                    </td>
                                @endforeach
                                <td class="py-0.5 text-right font-mono text-slate-900 px-3 border-l border-slate-100">
                                    {{ $formatDecimal($acc['amounts']['total'] ?? 0) }}
                                </td>
                            </tr>
                        @empty
                        @endforelse
                        <tr class="font-semibold text-slate-800">
                            <td class="py-1 pl-4">
                                Total Other Income
                            </td>
                            @foreach($colKeys as $cId)
                                <td class="py-1 text-right font-mono text-slate-900 px-3">
                                    {{ $formatDecimal($projectReport['other_income']['total'][$cId] ?? 0) }}
                                </td>
                            @endforeach
                            <td class="py-1 text-right font-mono font-bold text-slate-950 px-3 border-l border-slate-100">
                                {{ $formatDecimal($projectReport['other_income']['total']['total'] ?? 0) }}
                            </td>
                        </tr>

                        <!-- Other Expenses -->
                        <tr>
                            <td class="font-bold text-slate-800 pt-2 pb-0.5 pl-4" colspan="{{ $totalCols }}">
                                Other Expenses
                            </td>
                        </tr>
                        @forelse($projectReport['other_expenses']['accounts'] as $acc)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-0.5 pl-8 text-slate-700">
                                    <span x-show="showCodes" class="font-mono text-[11px] text-slate-500 mr-2" style="display: none;">{{ $acc['code'] }}</span>
                                    <span>{{ $acc['name'] }}</span>
                                </td>
                                @foreach($colKeys as $cId)
                                    <td class="py-0.5 text-right font-mono text-slate-800 px-3">
                                        {{ $formatDecimal($acc['amounts'][$cId] ?? 0) }}
                                    </td>
                                @endforeach
                                <td class="py-0.5 text-right font-mono text-slate-900 px-3 border-l border-slate-100">
                                    {{ $formatDecimal($acc['amounts']['total'] ?? 0) }}
                                </td>
                            </tr>
                        @empty
                        @endforelse
                        <tr class="font-semibold text-slate-800">
                            <td class="py-1 pl-4">
                                Total Other Expenses
                            </td>
                            @foreach($colKeys as $cId)
                                <td class="py-1 text-right font-mono text-slate-900 px-3">
                                    {{ $formatDecimal($projectReport['other_expenses']['total'][$cId] ?? 0) }}
                                </td>
                            @endforeach
                            <td class="py-1 text-right font-mono font-bold text-slate-950 px-3 border-l border-slate-100">
                                {{ $formatDecimal($projectReport['other_expenses']['total']['total'] ?? 0) }}
                            </td>
                        </tr>

                        <!-- Total Other Income and Expenses -->
                        <tr class="border-t border-slate-800 font-bold">
                            <td class="pt-2 pb-1 pl-4 text-slate-950">
                                Total Other Income and Expenses
                            </td>
                            @foreach($colKeys as $cId)
                                @php $otherNetVal = $projectReport['other_net'][$cId] ?? 0; @endphp
                                <td class="pt-2 pb-1 text-right font-mono text-slate-950 px-3 {{ $otherNetVal < 0 ? 'text-rose-600' : '' }}">
                                    {{ $formatDecimal($otherNetVal) }}
                                </td>
                            @endforeach
                            @php $totalOtherNetVal = $projectReport['other_net']['total'] ?? 0; @endphp
                            <td class="pt-2 pb-1 text-right font-mono font-black text-slate-950 px-3 border-l border-slate-200 {{ $totalOtherNetVal < 0 ? 'text-rose-600' : '' }}">
                                {{ $formatDecimal($totalOtherNetVal) }}
                            </td>
                        </tr>
                        <tr><td colspan="{{ $totalCols }}" class="h-4"></td></tr>


                        <!-- ================= 7. NET PROFIT / LOSS (BEFORE TAX) ================= -->
                        <tr class="border-t-2 border-b border-slate-900 font-black">
                            <td class="py-2.5 pl-2 text-slate-950 uppercase tracking-wide">
                                NET PROFIT/LOSS (Before Tax)
                            </td>
                            @foreach($colKeys as $cId)
                                @php $npBt = $projectReport['net_profit_before_tax'][$cId] ?? 0; @endphp
                                <td class="py-2.5 text-right font-mono text-slate-950 px-3 {{ $npBt < 0 ? 'text-rose-600' : '' }}">
                                    {{ $formatDecimal($npBt) }}
                                </td>
                            @endforeach
                            @php $totalNpBt = $projectReport['net_profit_before_tax']['total'] ?? 0; @endphp
                            <td class="py-2.5 text-right font-mono font-black text-slate-950 px-3 border-l border-slate-200 {{ $totalNpBt < 0 ? 'text-rose-600' : '' }}">
                                {{ $formatDecimal($totalNpBt) }}
                            </td>
                        </tr>

                        <!-- Tax Expenses if any -->
                        @if(count($projectReport['tax_expenses']['accounts']) > 0)
                            @foreach($projectReport['tax_expenses']['accounts'] as $acc)
                                <tr>
                                    <td class="py-1 pl-6 text-slate-700 font-medium">
                                        {{ $acc['name'] }}
                                    </td>
                                    @foreach($colKeys as $cId)
                                        <td class="py-1 text-right font-mono text-slate-800 px-3">
                                            {{ $formatDecimal($acc['amounts'][$cId] ?? 0) }}
                                        </td>
                                    @endforeach
                                    <td class="py-1 text-right font-mono text-slate-900 px-3 border-l border-slate-100">
                                        {{ $formatDecimal($acc['amounts']['total'] ?? 0) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                        <!-- ================= 8. NET PROFIT / LOSS (AFTER TAX) ================= -->
                        <tr class="border-t-2 border-b-4 border-double border-slate-900 font-black bg-slate-50/80">
                            <td class="py-3 pl-2 text-slate-950 uppercase tracking-wider text-sm sm:text-base">
                                NET PROFIT/LOSS (After Tax)
                            </td>
                            @foreach($colKeys as $cId)
                                @php $npAt = $projectReport['net_profit_after_tax'][$cId] ?? 0; @endphp
                                <td class="py-3 text-right font-mono font-black text-sm sm:text-base px-3 {{ $npAt < 0 ? 'text-rose-600' : 'text-slate-950' }}">
                                    {{ $formatDecimal($npAt) }}
                                </td>
                            @endforeach
                            @php $totalNpAt = $projectReport['net_profit_after_tax']['total'] ?? 0; @endphp
                            <td class="py-3 text-right font-mono font-black text-sm sm:text-base px-3 border-l border-slate-300 {{ $totalNpAt < 0 ? 'text-rose-600' : 'text-slate-950' }}">
                                {{ $formatDecimal($totalNpAt) }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- Footer Signatures (For Official Report / Print) -->
            <div class="hidden print:grid grid-cols-2 gap-8 mt-14 pt-6 text-center text-xs text-slate-800 border-t border-slate-300">
                <div>
                    <p class="font-medium text-slate-600">Disiapkan Oleh,</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline text-slate-900">Bagian Keuangan / Akuntan</p>
                </div>
                <div>
                    <p class="font-medium text-slate-600">Disetujui Oleh,</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline text-slate-900">Direktur / Manajemen Proyek</p>
                </div>
            </div>

        </div>

    @else
        <!-- ========================================================================================= -->
        <!-- VIEW MODE: FORMAT STANDAR (SINGLE COLUMN STATEMENT)                                       -->
        <!-- ========================================================================================= -->
        @php
            $totalPendapatan = $report['total_pendapatan'] ?? 0;
            $totalHpp = $report['total_hpp'] ?? 0;
            $labaKotor = $report['laba_kotor'] ?? 0;
            $totalBebanOperasional = $report['total_beban_operasional'] ?? 0;
            $labaOperasional = $report['laba_bersih_operasional'] ?? 0;
            $totalPendapatanLainnya = $report['total_pendapatan_lainnya'] ?? 0;
            $totalBebanLainnya = $report['total_beban_lainnya'] ?? 0;
            $totalNonOperasionalNet = $report['total_non_operasional_net'] ?? ($totalPendapatanLainnya - $totalBebanLainnya);
            $labaSebelumPajak = $report['laba_sebelum_pajak'] ?? ($labaOperasional + $totalNonOperasionalNet);
            $totalPajak = $report['total_pajak'] ?? 0;
            $labaSetelahPajak = $report['laba_setelah_pajak'] ?? ($labaSebelumPajak - $totalPajak);
        @endphp

        <!-- MAIN STATEMENT SHEET (Formal Paper Accounting Layout) -->
        <div class="max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-10 lg:p-12 print:p-0 print:border-none print:shadow-none" id="profit-loss-sheet">
            
            <!-- Formal Report Header -->
            <div class="text-center pb-6 border-b-2 border-slate-900">
                <h2 class="text-sm sm:text-base font-bold text-slate-800 uppercase tracking-widest">{{ $company->name }}</h2>
                <h1 class="text-xl sm:text-2xl font-black text-slate-950 uppercase tracking-tight mt-0.5">LAPORAN LABA RUGI</h1>
                <p class="text-xs sm:text-sm text-slate-600 font-medium mt-1">
                    Untuk Periode yang Berakhir pada {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
                </p>
                <p class="text-[11px] text-slate-400 italic mt-0.5">
                    (Dinyatakan dalam Rupiah Indonesia)
                </p>
                @if(!empty($tagId) && ($activeTag = $tags->firstWhere('id', $tagId)))
                    <div class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-white shadow-2xs" style="background-color: {{ $activeTag->color ?? '#3b82f6' }}">
                        <i class="fa-solid fa-tag text-[10px]"></i>
                        <span>Tag / Proyek: {{ $activeTag->name }}</span>
                    </div>
                @endif
                @if(!empty($projectId) && ($activeProj = $projects->firstWhere('id', $projectId)))
                    <div class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-600 text-white shadow-2xs">
                        <i class="fa-solid fa-folder text-[10px]"></i>
                        <span>Proyek: {{ $activeProj->name }}</span>
                    </div>
                @endif
            </div>

            <!-- Statement Table -->
            <div class="mt-6">
                <table class="w-full text-xs sm:text-sm text-slate-900 border-collapse" id="report-table-export" style="table-layout: fixed; width: 100%;">
                    <colgroup>
                        <col style="width: 70%;">
                        <col style="width: 30%;">
                    </colgroup>
                    <tbody>

                        <!-- ================= 1. PENDAPATAN ================= -->
                        <tr>
                            <td class="font-black text-slate-950 pt-4 pb-1 uppercase tracking-wide">
                                PENDAPATAN
                            </td>
                            <td></td>
                        </tr>

                        @forelse($report['pendapatan_list'] as $acc)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-1 pl-6 text-slate-800">
                                    <span x-show="showCodes" class="font-mono text-[11px] text-slate-500 mr-2" style="display: none;">{{ $acc['code'] }}</span>
                                    <span>{{ $acc['name'] }}</span>
                                </td>
                                <td class="py-1 text-right font-mono text-slate-900 pr-3">
                                    {{ $formatNum($acc['total']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-1 pl-6 text-slate-400 italic">Penjualan</td>
                                <td class="py-1 text-right font-mono text-slate-400 pr-3">0</td>
                            </tr>
                        @endforelse

                        <!-- Subtotal Pendapatan -->
                        <tr>
                            <td class="font-bold text-slate-950 pt-2 pb-1">
                                Jumlah Pendapatan
                            </td>
                            <td class="font-bold text-right font-mono text-slate-950 pt-2 pb-1 pr-3 border-t border-slate-900">
                                {{ $formatNum($totalPendapatan) }}
                            </td>
                        </tr>
                        <tr><td colspan="2" class="h-4"></td></tr>


                        <!-- ================= 2. BEBAN POKOK PENJUALAN (HPP) ================= -->
                        <tr>
                            <td class="font-black text-slate-950 pt-3 pb-1 uppercase tracking-wide">
                                BEBAN POKOK PENJUALAN <span class="text-xs font-normal text-slate-500 lowercase">(Harga Pokok Penjualan)</span>
                            </td>
                            <td></td>
                        </tr>

                        @forelse($report['hpp_list'] as $acc)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-1 pl-6 text-slate-800">
                                    <span x-show="showCodes" class="font-mono text-[11px] text-slate-500 mr-2" style="display: none;">{{ $acc['code'] }}</span>
                                    <span>{{ $acc['name'] }}</span>
                                </td>
                                <td class="py-1 text-right font-mono text-slate-900 pr-3">
                                    {{ $formatNum($acc['total']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-1 pl-6 text-slate-400 italic">Beban Pokok Penjualan</td>
                                <td class="py-1 text-right font-mono text-slate-400 pr-3">0</td>
                            </tr>
                        @endforelse

                        <!-- Subtotal HPP -->
                        <tr>
                            <td class="font-bold text-slate-950 pt-2 pb-1">
                                Jumlah Beban Pokok Penjualan
                            </td>
                            <td class="font-bold text-right font-mono text-slate-950 pt-2 pb-1 pr-3 border-t border-slate-900">
                                {{ $formatNum($totalHpp) }}
                            </td>
                        </tr>
                        <tr><td colspan="2" class="h-2"></td></tr>


                        <!-- ================= 3. LABA KOTOR ================= -->
                        <tr class="border-t-2 border-b border-slate-900">
                            <td class="py-2.5 font-black text-slate-950 uppercase tracking-wide">
                                LABA KOTOR
                            </td>
                            <td class="py-2.5 text-right font-mono font-black text-slate-950 pr-3 {{ $labaKotor < 0 ? 'text-rose-600' : '' }}">
                                {{ $formatNum($labaKotor) }}
                            </td>
                        </tr>
                        <tr><td colspan="2" class="h-4"></td></tr>


                        <!-- ================= 4. BEBAN OPERASIONAL ================= -->
                        <tr>
                            <td class="font-black text-slate-950 pt-3 pb-1 uppercase tracking-wide">
                                BEBAN OPERASIONAL
                            </td>
                            <td></td>
                        </tr>

                        @forelse($report['beban_operasional_list'] as $acc)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-1 pl-6 text-slate-800">
                                    <span x-show="showCodes" class="font-mono text-[11px] text-slate-500 mr-2" style="display: none;">{{ $acc['code'] }}</span>
                                    <span>{{ $acc['name'] }}</span>
                                </td>
                                <td class="py-1 text-right font-mono text-slate-900 pr-3">
                                    {{ $formatNum($acc['total']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-1 pl-6 text-slate-400 italic">Beban Operasional</td>
                                <td class="py-1 text-right font-mono text-slate-400 pr-3">0</td>
                            </tr>
                        @endforelse

                        <!-- Subtotal Beban Operasional -->
                        <tr>
                            <td class="font-bold text-slate-950 pt-2 pb-1">
                                Jumlah Beban Operasional
                            </td>
                            <td class="font-bold text-right font-mono text-slate-950 pt-2 pb-1 pr-3 border-t border-slate-900">
                                {{ $formatNum($totalBebanOperasional) }}
                            </td>
                        </tr>
                        <tr><td colspan="2" class="h-2"></td></tr>


                        <!-- ================= 5. PENDAPATAN OPERASIONAL ================= -->
                        <tr class="border-t-2 border-b border-slate-900">
                            <td class="py-2.5 font-black text-slate-950 uppercase tracking-wide">
                                PENDAPATAN OPERASIONAL
                            </td>
                            <td class="py-2.5 text-right font-mono font-black text-slate-950 pr-3 {{ $labaOperasional < 0 ? 'text-rose-600' : '' }}">
                                {{ $formatNum($labaOperasional) }}
                            </td>
                        </tr>
                        <tr><td colspan="2" class="h-4"></td></tr>


                        <!-- ================= 6. PENDAPATAN DAN BEBAN NON OPERASIONAL ================= -->
                        <tr>
                            <td class="font-black text-slate-950 pt-3 pb-1 uppercase tracking-wide">
                                PENDAPATAN DAN BEBAN NON OPERASIONAL
                            </td>
                            <td></td>
                        </tr>

                        <!-- Pendapatan Non Operasional -->
                        <tr>
                            <td class="pl-4 font-bold text-slate-800 pt-1">
                                Pendapatan Non Operasional
                            </td>
                            <td></td>
                        </tr>
                        @forelse($report['pendapatan_lainnya_list'] as $acc)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-0.5 pl-8 text-slate-700">
                                    <span x-show="showCodes" class="font-mono text-[11px] text-slate-500 mr-2" style="display: none;">{{ $acc['code'] }}</span>
                                    <span>{{ $acc['name'] }}</span>
                                </td>
                                <td class="py-0.5 text-right font-mono text-slate-800 pr-3">
                                    {{ $formatNum($acc['total']) }}
                                </td>
                            </tr>
                        @empty
                        @endforelse
                        <tr>
                            <td class="pl-4 font-medium text-slate-700 pb-1">
                                Jumlah Pendapatan Non Operasional
                            </td>
                            <td class="text-right font-mono text-slate-900 pb-1 pr-3">
                                {{ $formatNum($totalPendapatanLainnya) }}
                            </td>
                        </tr>

                        <!-- Beban Non Operasional -->
                        <tr>
                            <td class="pl-4 font-bold text-slate-800 pt-2">
                                Beban Non Operasional
                            </td>
                            <td></td>
                        </tr>
                        @forelse($report['beban_lainnya_list'] as $acc)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-0.5 pl-8 text-slate-700">
                                    <span x-show="showCodes" class="font-mono text-[11px] text-slate-500 mr-2" style="display: none;">{{ $acc['code'] }}</span>
                                    <span>{{ $acc['name'] }}</span>
                                </td>
                                <td class="py-0.5 text-right font-mono text-slate-800 pr-3">
                                    {{ $formatNum($acc['total']) }}
                                </td>
                            </tr>
                        @empty
                        @endforelse
                        <tr>
                            <td class="pl-4 font-medium text-slate-700 pb-1">
                                Jumlah Beban Non Operasional
                            </td>
                            <td class="text-right font-mono text-slate-900 pb-1 pr-3">
                                {{ $formatNum($totalBebanLainnya) }}
                            </td>
                        </tr>

                        <!-- Subtotal Net Non-Operasional -->
                        <tr>
                            <td class="font-bold text-slate-950 pt-2 pb-1">
                                Jumlah Pendapatan dan Beban Non Operasional
                            </td>
                            <td class="font-bold text-right font-mono text-slate-950 pt-2 pb-1 pr-3 border-t border-slate-900 {{ $totalNonOperasionalNet < 0 ? 'text-rose-600' : '' }}">
                                {{ $formatNum($totalNonOperasionalNet) }}
                            </td>
                        </tr>
                        <tr><td colspan="2" class="h-6"></td></tr>

                    </tbody>
                </table>

                <!-- ================= 7. KOTAK HIGHLIGHT: LABA BERSIH (SEBELUM & SETELAH PAJAK) ================= -->
                <div class="mt-4 border-2 border-slate-900 bg-slate-50/60 rounded-xl p-4 sm:p-5 print:bg-transparent print:border-2 print:border-black">
                    <table class="w-full text-xs sm:text-sm text-slate-900 border-collapse" style="table-layout: fixed; width: 100%;">
                        <colgroup>
                            <col style="width: 70%;">
                            <col style="width: 30%;">
                        </colgroup>
                        <tbody>
                            <!-- Laba Bersih Sebelum Pajak -->
                            <tr>
                                <td class="py-1 font-black text-slate-950 uppercase tracking-wide">
                                    LABA BERSIH (Sebelum Pajak)
                                </td>
                                <td class="py-1 text-right font-mono font-black text-slate-950 pr-3 {{ $labaSebelumPajak < 0 ? 'text-rose-600' : '' }}">
                                    {{ $formatNum($labaSebelumPajak) }}
                                </td>
                            </tr>

                            <!-- Pajak Penghasilan -->
                            <tr>
                                <td class="py-1 pl-4 text-slate-700 font-medium">
                                    Pajak Penghasilan
                                </td>
                                <td class="py-1 text-right font-mono text-slate-800 pr-3">
                                    {{ $formatNum($totalPajak) }}
                                </td>
                            </tr>

                            <!-- Garis Pemisah & Laba Bersih Setelah Pajak (Double Underline) -->
                            <tr>
                                <td class="pt-2 font-black text-sm sm:text-base text-slate-950 uppercase tracking-wider">
                                    LABA BERSIH (Setelah Pajak)
                                </td>
                                <td class="pt-2 text-right pr-3 border-t border-slate-900">
                                    <span class="font-mono font-black text-sm sm:text-base border-b-4 border-double border-slate-950 pb-0.5 inline-block {{ $labaSetelahPajak < 0 ? 'text-rose-600 border-rose-600' : 'text-slate-950' }}">
                                        {{ $formatNum($labaSetelahPajak) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Footer Signatures (For Official Report / Print) -->
            <div class="hidden print:grid grid-cols-2 gap-8 mt-14 pt-6 text-center text-xs text-slate-800 border-t border-slate-300">
                <div>
                    <p class="font-medium text-slate-600">Disiapkan Oleh,</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline text-slate-900">Bagian Keuangan / Akuntan</p>
                </div>
                <div>
                    <p class="font-medium text-slate-600">Disetujui Oleh,</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline text-slate-900">Direktur / Pemilik Usaha</p>
                </div>
            </div>

        </div>
    @endif

</div>

<!-- CSS for Print / PDF Perfection -->
<style>
@media print {
    @page {
        size: {{ $viewMode === 'by_project' ? 'A4 landscape' : 'A4 portrait' }};
        margin: 12mm 15mm 12mm 15mm;
    }
    html, body {
        height: auto !important;
        overflow: visible !important;
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 9.5pt !important;
        line-height: 1.3 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    header, aside, nav, .print\:hidden {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
        overflow: visible !important;
    }
    #profit-loss-sheet {
        box-shadow: none !important;
        border: none !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        padding: 0 !important;
    }
    table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    td, th {
        padding-top: 2px !important;
        padding-bottom: 2px !important;
    }
}
</style>

<script>
function exportToExcel() {
    let mode = '{{ $viewMode }}';
    let group = '{{ $groupBy ?? 'department' }}';
    let prefix = mode === 'by_project' ? ('Profit_Loss_' + group + '_') : 'Laporan_Laba_Rugi_';
    let title = prefix + '{{ str_replace('-', '', $startDate) }}_{{ str_replace('-', '', $endDate) }}';
    let table = document.getElementById('report-table-export');
    if (!table) return;

    let compName = @json(strtoupper($company->name));
    let repTitle = mode === 'by_project' ? @json(strtoupper($repHeaderTitle ?? 'PROFIT & LOSS')) : 'LAPORAN LABA RUGI';
    let dateRange = @json(\Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y'));

    let html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">'
        + '<head><meta charset="utf-8">'
        + '<!--[if gte mso 9]><xml><' + 'x:ExcelWorkbook><' + 'x:ExcelWorksheets><' + 'x:ExcelWorksheet><' + 'x:Name>Laba Rugi</' + 'x:Name><' + 'x:WorksheetOptions><' + 'x:DisplayGridlines/></' + 'x:WorksheetOptions></' + 'x:ExcelWorksheet></' + 'x:ExcelWorksheets></' + 'x:ExcelWorkbook></xml><![endif]-->'
        + '<style>'
        + 'body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #000; }'
        + 'table { border-collapse: collapse; width: 100%; }'
        + 'th, td { padding: 4px 8px; vertical-align: middle; }'
        + '.text-center { text-align: center; }'
        + '.text-right { text-align: right; }'
        + '.font-bold { font-weight: bold; }'
        + '.header-title { font-size: 14pt; font-weight: bold; text-align: center; }'
        + '.header-sub { font-size: 10pt; text-align: center; color: #555; }'
        + '.border-top { border-top: 1px solid #000; }'
        + '.border-bottom { border-bottom: 1px solid #000; }'
        + '.double-bottom { border-bottom: 3px double #000; }'
        + '</style></head><body>'
        + '<table>'
        + '<tr><td colspan="15" class="header-title">' + compName + '</td></tr>'
        + '<tr><td colspan="15" class="header-title" style="font-size: 16pt;">' + repTitle + '</td></tr>'
        + '<tr><td colspan="15" class="header-sub">Periode: ' + dateRange + '</td></tr>'
        + '<tr><td colspan="15" class="header-sub" style="font-style: italic;">(Dinyatakan dalam Rupiah Indonesia)</td></tr>'
        + '<tr><td colspan="15" style="height: 15px;"></td></tr>'
        + '</table>'
        + table.outerHTML
        + '</body></html>';

    let blob = new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    let url = URL.createObjectURL(blob);
    let a = document.createElement('a');
    a.href = url;
    a.download = title + '.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
@endsection
