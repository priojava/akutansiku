@extends('layouts.app')

@section('title', 'Daftar Aset Tetap')

@section('content')
<div class="space-y-6" x-data="{
    search: '{{ request('search', '') }}',
    selectedTypeFilter: '{{ request('asset_type_id', '') }}',
    selectedStatusFilter: '{{ request('status', '') }}',
    previewPhoto: null,
    previewTitle: '',

    // Modal Proses Depresiasi Bulanan
    deprModalOpen: false,
    deprPeriod: '{{ $currentPeriod }}',
    deprLoading: false,
    deprPreviewData: null,
    deprHistoryOpen: false,

    fetchDeprPreview() {
        this.deprLoading = true;
        fetch('{{ route('assets.depreciation.preview') }}?period=' + this.deprPeriod)
            .then(res => res.json())
            .then(data => {
                this.deprLoading = false;
                if (data.success) {
                    this.deprPreviewData = data;
                }
            })
            .catch(err => {
                this.deprLoading = false;
                console.error(err);
            });
    },

    openDeprModal() {
        this.deprModalOpen = true;
        this.fetchDeprPreview();
    },

    formatRupiah(val) {
        return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
    }
}">

    <!-- Page Header & Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Daftar Aset (Aktiva Tetap)</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola perolehan aset tetap, pencatatan nilai buku, dan eksekusi jurnal penyusutan bulanan otomatis</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Tombol Kelola Tipe Aset -->
            <a href="{{ route('master.asset_types') }}" class="open-in-tab bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer" data-tab-title="Master Tipe Aset" data-tab-icon="fa-solid fa-tags">
                <i class="fa-solid fa-tags text-indigo-600 text-xs"></i>
                <span>Tipe Aset</span>
            </a>

            <!-- Tombol Riwayat Depresiasi -->
            <button type="button" @click="deprHistoryOpen = true" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-clock-rotate-left text-blue-600 text-xs"></i>
                <span>Riwayat Depresiasi</span>
            </button>

            <!-- Tombol Proses Depresiasi Bulanan -->
            <button type="button" @click="openDeprModal()" class="bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-md shadow-amber-500/20 transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-bolt text-xs animate-pulse"></i>
                <span>Proses Depresiasi Bulanan</span>
            </button>

            <!-- Tombol Tambah Aset -->
            <a href="{{ route('assets.create') }}" class="open-in-tab bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-md shadow-blue-500/20 transition flex items-center space-x-1.5" data-tab-title="Tambah Aset" data-tab-icon="fa-solid fa-plus">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Aset</span>
            </a>

            <!-- Export Excel -->
            <a href="{{ route('assets.export') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-2xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-file-excel text-xs"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-semibold text-emerald-800 flex items-center space-x-2.5 shadow-2xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('info'))
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl text-xs font-semibold text-blue-800 flex items-center space-x-2.5 shadow-2xs">
            <i class="fa-solid fa-circle-info text-blue-600 text-sm"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-semibold text-rose-800 flex items-center space-x-2.5 shadow-2xs">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Peringatan Periode Penyusutan Tertunda (Jika Ada Bulan yang Lupa Diposting) -->
    @if(!empty($pendingPeriods))
        <div class="p-4.5 bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-amber-50/80 border border-amber-300 rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm animate-in fade-in duration-200">
            <div class="flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20 text-lg mt-0.5">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h4 class="font-extrabold text-sm text-slate-800">
                            Terdapat {{ count($pendingPeriods) }} Periode Penyusutan Tertunda (Belum Diposting)
                        </h4>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                            Total: Rp {{ number_format(array_sum(array_column($pendingPeriods, 'total_amount')), 0, ',', '.') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Sistem mendeteksi ada bulan yang belum dicatat jurnal depresiasinya:
                        @foreach($pendingPeriods as $p)
                            <span class="font-bold text-slate-800">{{ $p['period_label'] ?: $p['period'] }} (Rp {{ number_format($p['total_amount'], 0, ',', '.') }})</span>{{ !$loop->last ? ', ' : '.' }}
                        @endforeach
                        Anda dapat memposting semuanya sekaligus secara otomatis dengan tanggal akhir bulan masing-masing.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('assets.depreciation.run_bulk') }}" class="shrink-0" onsubmit="return confirm('Posting seluruh jurnal penyusutan untuk {{ count($pendingPeriods) }} periode tertunda sekarang?')">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 hover:from-amber-700 hover:to-orange-700 text-white text-xs font-extrabold rounded-xl shadow-md shadow-amber-600/20 transition flex items-center justify-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-bolt text-xs"></i>
                    <span>⚡ Posting Semua Periode Tertunggak</span>
                </button>
            </form>
        </div>
    @endif

    <!-- Summary Statistics Cards (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Nilai Perolehan -->
        <div class="bg-white p-4.5 rounded-2xl border border-blue-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Nilai Perolehan</p>
                <h3 class="text-lg font-black text-blue-900 mt-1">Rp {{ number_format($totalAcquisitionCost, 0, ',', '.') }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $assets->count() }} unit aset terdaftar</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-building-columns"></i>
            </div>
        </div>

        <!-- Card 2: Akumulasi Penyusutan -->
        <div class="bg-white p-4.5 rounded-2xl border border-rose-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Akumulasi Penyusutan</p>
                <h3 class="text-lg font-black text-rose-600 mt-1">Rp {{ number_format($totalAccumulatedDepreciation, 0, ',', '.') }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Total depresiasi terakumulasi</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-chart-line-down"></i>
            </div>
        </div>

        <!-- Card 3: Nilai Buku Bersih -->
        <div class="bg-white p-4.5 rounded-2xl border border-emerald-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Nilai Buku</p>
                <h3 class="text-lg font-black text-emerald-700 mt-1">Rp {{ number_format($totalBookValue, 0, ',', '.') }}</h3>
                <p class="text-[10px] text-emerald-600 font-semibold mt-0.5">Perolehan - Akumulasi</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>

        <!-- Card 4: Estimasi Beban / Bulan -->
        <div class="bg-white p-4.5 rounded-2xl border border-amber-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Beban Depresiasi/Bulan</p>
                <h3 class="text-lg font-black text-amber-600 mt-1">Rp {{ number_format($totalMonthlyDepreciation, 0, ',', '.') }}</h3>
                <p class="text-[10px] text-amber-600 font-semibold mt-0.5">Rutin tiap akhir bulan</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>
    </div>

    <!-- Table Card with Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
        
        <!-- Filter Toolbar -->
        <form method="GET" action="{{ route('assets.index') }}" class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Filter Tipe Aset -->
                <select name="asset_type_id" onchange="this.form.submit()"
                        class="px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Semua Tipe Aset</option>
                    @foreach($assetTypes as $type)
                        <option value="{{ $type->id }}" {{ request('asset_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ $type->code ?: 'Tipe' }})
                        </option>
                    @endforeach
                </select>

                <!-- Filter Status Depresiasi -->
                <select name="status" onchange="this.form.submit()"
                        class="px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif Disusutkan</option>
                    <option value="stopped" {{ request('status') == 'stopped' ? 'selected' : '' }}>Dihentikan Sementara</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai Disusutkan</option>
                </select>

                @if(request('asset_type_id') || request('status') || request('search'))
                    <a href="{{ route('assets.index') }}" class="text-xs text-rose-600 hover:text-rose-800 font-bold px-2 py-1 flex items-center space-x-1">
                        <i class="fa-solid fa-xmark"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </div>

            <!-- Search Box -->
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, akun..."
                       class="w-64 pl-8 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-slate-400 text-xs"></i>
            </div>
        </form>

        <!-- Horizontal Scrollable Table matching Accurate Standard -->
        <div class="overflow-x-auto sidebar-scroll">
            <table class="w-full text-left text-xs text-slate-700 whitespace-nowrap">
                <thead class="bg-slate-100/80 border-b border-slate-200 text-slate-600 uppercase text-[10px] tracking-wider font-bold">
                    <tr>
                        <th class="px-4 py-3.5 w-12 text-center">NO</th>
                        <th class="px-4 py-3.5">KODE</th>
                        <th class="px-4 py-3.5">TIPE ASET</th>
                        <th class="px-4 py-3.5">NAMA ASET</th>
                        <th class="px-4 py-3.5 text-center">TGL PEROLEHAN</th>
                        <th class="px-4 py-3.5 text-center">TGL PAKAI</th>
                        <th class="px-4 py-3.5 text-right font-bold text-slate-900">BIAYA PEROLEHAN</th>
                        <th class="px-4 py-3.5 text-right font-bold text-amber-700 bg-amber-50/30">DEPRESIASI / BLN</th>
                        <th class="px-4 py-3.5 text-right font-bold text-rose-700 bg-rose-50/20">AKUMULASI PENYUSUTAN</th>
                        <th class="px-4 py-3.5 text-right font-bold text-emerald-700 bg-emerald-50/40">NILAI BUKU SAAT INI</th>
                        <th class="px-4 py-3.5">AKUN BEBAN PENYUSUTAN</th>
                        <th class="px-4 py-3.5">AKUN AKUMULASI</th>
                        <th class="px-4 py-3.5 text-center">MASA MANFAAT</th>
                        <th class="px-4 py-3.5 text-center">STATUS</th>
                        <th class="px-4 py-3.5 text-center w-24">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assets as $index => $asset)
                        <tr class="hover:bg-slate-50/80 transition"
                            x-show="!search || '{{ strtolower($asset->code . ' ' . $asset->name . ' ' . ($asset->assetType?->name ?? '') . ' ' . ($asset->expenseAccount?->name ?? '')) }}'.includes(search.toLowerCase())">
                            <td class="px-4 py-3 text-center text-slate-400 font-semibold">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200 text-[11px]">
                                    {{ $asset->code }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($asset->assetType)
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        🏷️ {{ $asset->assetType->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Umum</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-800">
                                <div class="flex items-center space-x-2.5">
                                    @if($asset->photo_path)
                                        <button type="button" 
                                                @click="previewPhoto = '{{ asset('storage/' . $asset->photo_path) }}'; previewTitle = '{{ addslashes($asset->name) }}'" 
                                                title="Klik untuk melihat foto fisik"
                                                class="shrink-0 group relative focus:outline-none cursor-pointer">
                                            <img src="{{ asset('storage/' . $asset->photo_path) }}" 
                                                 alt="{{ $asset->name }}" 
                                                 class="w-7 h-7 rounded-lg object-cover border border-slate-200 shadow-2xs group-hover:ring-2 group-hover:ring-blue-500 transition"
                                                 onerror="this.onerror=null; this.style.display='none';">
                                        </button>
                                    @endif
                                    <div>
                                        <span class="truncate max-w-[200px] block" title="{{ $asset->name }}">{{ $asset->name }}</span>
                                        @if($asset->description)
                                            <span class="text-[10px] font-normal text-slate-400 block max-w-xs truncate">{{ $asset->description }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">
                                {{ $asset->acquisition_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-center text-slate-700 font-semibold">
                                {{ $asset->usage_date ? $asset->usage_date->format('d/m/Y') : $asset->acquisition_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right font-black text-slate-900">
                                Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-amber-700 bg-amber-50/30">
                                @if($asset->is_depreciated && $asset->monthly_depreciation > 0)
                                    Rp {{ number_format($asset->monthly_depreciation, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-400 font-normal">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-rose-600 bg-rose-50/20">
                                Rp {{ number_format($asset->accumulated_depreciation_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-black text-emerald-700 bg-emerald-50/40">
                                Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                @if($asset->expenseAccount)
                                    <div class="font-medium text-rose-700">{{ $asset->expenseAccount->name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">({{ $asset->expenseAccount->code }})</div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                @if($asset->accumulatedAccount)
                                    <div class="font-medium text-indigo-700">{{ $asset->accumulatedAccount->name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">({{ $asset->accumulatedAccount->code }})</div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-semibold">
                                @if($asset->useful_life_months)
                                    @php
                                        $thn = floor($asset->useful_life_months / 12);
                                        $bln = $asset->useful_life_months % 12;
                                    @endphp
                                    {{ $thn > 0 ? $thn . ' Thn ' : '' }}{{ $bln > 0 ? $bln . ' Bln' : ($thn == 0 ? '-' : '') }}
                                @elseif($asset->useful_life_years)
                                    {{ $asset->useful_life_years }} Tahun
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(!$asset->is_depreciated)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        Non-Depresiasi
                                    </span>
                                @elseif($asset->depreciation_status === 'completed')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                        Habis Masa Manfaat
                                    </span>
                                @elseif($asset->depreciation_status === 'stopped')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Dihentikan
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif Disusutkan
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    @if($asset->is_depreciated && $asset->depreciation_status !== 'completed')
                                        <form action="{{ route('assets.toggle_depreciation', $asset->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-xs text-amber-600 hover:bg-amber-50 rounded-lg transition" title="{{ $asset->depreciation_status === 'active' ? 'Hentikan Penyusutan' : 'Aktifkan Kembali' }}">
                                                <i class="fa-solid {{ $asset->depreciation_status === 'active' ? 'fa-pause' : 'fa-play' }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Hapus aset {{ $asset->name }} beserta seluruh jurnal perolehan dan penyusutannya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-xs text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus Aset">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-4 py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>
                                <h4 class="font-bold text-slate-700 text-sm">Belum Ada Aset Tetap</h4>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Mulai dengan mencatat aktiva tetap seperti kendaraan operasional, peralatan, atau bangunan kantor.</p>
                                <a href="{{ route('assets.create') }}" class="open-in-tab inline-flex items-center space-x-1.5 mt-3 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-blue-500/20" data-tab-title="Tambah Aset" data-tab-icon="fa-solid fa-plus">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Tambah Aset Pertama</span>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- MODAL 1: PROSES DEPRESIASI BULANAN (PERIOD-END JOURNAL RUN)        -->
    <!-- =================================================================== -->
    <div x-show="deprModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 animate-in fade-in duration-200">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-2xl w-full overflow-hidden flex flex-col max-h-[90vh]" @click.away="deprModalOpen = false">
            <!-- Header Modal -->
            <div class="p-4 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">Proses Jurnal Penyusutan Akhir Bulan</h3>
                        <p class="text-[11px] text-amber-100">Eksekusi perhitungan beban depresiasi dan catat jurnal otomatis</p>
                    </div>
                </div>
                <button type="button" @click="deprModalOpen = false" class="text-white/80 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-5 space-y-4 overflow-y-auto flex-1">
                <!-- Selector Periode Bulan -->
                <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800">Pilih Periode Bulan Penyusutan</label>
                        <p class="text-[11px] text-slate-500">Jurnal akan dibuat pada tanggal terakhir bulan yang dipilih</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="month" x-model="deprPeriod" @change="fetchDeprPreview()"
                               class="px-3 py-2 bg-white border border-amber-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-amber-500 focus:outline-none shadow-2xs">
                        <button type="button" @click="fetchDeprPreview()" class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-2xs">
                            <i class="fa-solid fa-rotate-right" :class="deprLoading ? 'animate-spin' : ''"></i>
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="deprLoading" class="py-8 text-center space-y-2">
                    <div class="w-8 h-8 border-3 border-amber-200 border-t-amber-600 rounded-full animate-spin mx-auto"></div>
                    <p class="text-xs font-semibold text-slate-500">Menghitung aset yang berhak disusutkan...</p>
                </div>

                <!-- Preview Data Table -->
                <div x-show="!deprLoading && deprPreviewData">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700">
                            Daftar Aset yang Akan Disusutkan (<span x-text="deprPreviewData?.count || 0"></span> unit)
                        </span>
                        <span class="text-xs font-black text-amber-700" x-text="'Total Beban: ' + formatRupiah(deprPreviewData?.total_amount)"></span>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-hidden max-h-64 overflow-y-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-100 text-slate-600 uppercase text-[10px] font-bold sticky top-0">
                                <tr>
                                    <th class="px-3 py-2">ASET</th>
                                    <th class="px-3 py-2">AKUN BEBAN / AKUMULASI</th>
                                    <th class="px-3 py-2 text-right">NILAI BUKU SBELUM</th>
                                    <th class="px-3 py-2 text-right">PENYUSUTAN (DEBET)</th>
                                    <th class="px-3 py-2 text-center">STATUS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="item in deprPreviewData?.items" :key="item.id">
                                    <tr :class="item.is_already_processed ? 'bg-slate-50/70 text-slate-400' : 'hover:bg-amber-50/30'">
                                        <td class="px-3 py-2 font-bold text-slate-800">
                                            <div x-text="item.name"></div>
                                            <div class="text-[10px] font-mono text-slate-400" x-text="item.code"></div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="text-[11px] text-rose-700 font-medium" x-text="'D: ' + item.expense_account"></div>
                                            <div class="text-[11px] text-indigo-700 font-medium" x-text="'K: ' + item.accumulated_account"></div>
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium" x-text="formatRupiah(item.book_value_before)"></td>
                                        <td class="px-3 py-2 text-right font-bold text-amber-700" x-text="formatRupiah(item.monthly_amount)"></td>
                                        <td class="px-3 py-2 text-center">
                                            <template x-if="item.is_already_processed">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Sudah Diposting
                                                </span>
                                            </template>
                                            <template x-if="!item.is_already_processed && item.has_valid_accounts">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    Siap Dijurnal
                                                </span>
                                            </template>
                                            <template x-if="!item.is_already_processed && !item.has_valid_accounts">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Akun Kosong
                                                </span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Informasi Jurnal Otomatis yang Dihasilkan -->
                    <div class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-600 leading-relaxed">
                        <div class="font-bold text-slate-800 flex items-center mb-1">
                            <i class="fa-solid fa-circle-info text-blue-600 mr-1.5"></i>
                            <span>Konstruksi Jurnal Otomatis yang Akan Dibuat:</span>
                        </div>
                        <ul class="list-disc pl-5 space-y-0.5 text-slate-600">
                            <li><strong>(Debit)</strong> Akun Beban Penyusutan (misal: <em>6-60502 Depresiasi - Kendaraan</em>)</li>
                            <li><strong>(Kredit)</strong> Akun Akumulasi Penyusutan (misal: <em>1-10753 Akumulasi Penyusutan - Kendaraan</em>)</li>
                            <li>Tanggal jurnal otomatis diset ke tanggal akhir bulan periode (<strong x-text="deprPeriod"></strong>).</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/75 flex items-center justify-between shrink-0">
                <button type="button" @click="deprModalOpen = false" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-100 transition">
                    Tutup
                </button>

                <form method="POST" action="{{ route('assets.depreciation.run') }}">
                    @csrf
                    <input type="hidden" name="period" :value="deprPeriod">
                    <button type="submit" 
                            :disabled="deprLoading || !deprPreviewData || deprPreviewData?.total_amount <= 0"
                            class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-md shadow-amber-500/20 transition flex items-center space-x-1.5 cursor-pointer">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Posting Jurnal Penyusutan Sekarang</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- MODAL 2: RIWAYAT JURNAL PENYUSUTAN BULANAN                          -->
    <!-- =================================================================== -->
    <div x-show="deprHistoryOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 animate-in fade-in duration-200">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-3xl w-full overflow-hidden flex flex-col max-h-[90vh]" @click.away="deprHistoryOpen = false">
            <div class="p-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-clock-rotate-left text-base"></i>
                    <h3 class="font-bold text-sm">Riwayat Jurnal Penyusutan Bulanan</h3>
                </div>
                <button type="button" @click="deprHistoryOpen = false" class="text-white/80 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-5 overflow-y-auto flex-1">
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-100 text-slate-600 uppercase text-[10px] font-bold">
                            <tr>
                                <th class="px-3.5 py-2.5">PERIODE</th>
                                <th class="px-3.5 py-2.5">ASET</th>
                                <th class="px-3.5 py-2.5 text-center">TGL JURNAL</th>
                                <th class="px-3.5 py-2.5 text-right">NOMINAL PENYUSUTAN</th>
                                <th class="px-3.5 py-2.5 text-right">NILAI BUKU AKHIR</th>
                                <th class="px-3.5 py-2.5 text-center w-20">BATALKAN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentLogs as $log)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-3.5 py-3">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $log->period }}
                                        </span>
                                    </td>
                                    <td class="px-3.5 py-3 font-bold text-slate-800">
                                        {{ $log->asset?->name ?? 'Aset Dihapus' }}
                                        <span class="text-[10px] font-mono font-normal text-slate-400 block">({{ $log->asset?->code ?? '-' }})</span>
                                    </td>
                                    <td class="px-3.5 py-3 text-center text-slate-600 font-medium">
                                        {{ $log->depreciation_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-3.5 py-3 text-right font-black text-rose-600">
                                        Rp {{ number_format($log->depreciation_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3.5 py-3 text-right font-bold text-emerald-700">
                                        Rp {{ number_format($log->book_value_after, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3.5 py-3 text-center">
                                        <form action="{{ route('assets.depreciation.rollback', $log->id) }}" method="POST" onsubmit="return confirm('Batalkan jurnal penyusutan periode {{ $log->period }} untuk {{ $log->asset?->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Batalkan Jurnal Depresiasi Ini">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                        Belum ada riwayat jurnal depresiasi yang pernah dieksekusi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end">
                <button type="button" @click="deprHistoryOpen = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Lightbox Preview Foto Fisik -->
    <div x-show="previewPhoto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/75 backdrop-blur-xs p-4 animate-in fade-in duration-200">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden" @click.away="previewPhoto = null">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-xs text-slate-800" x-text="previewTitle"></h3>
                <button type="button" @click="previewPhoto = null" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
            <div class="p-4 bg-slate-50 flex items-center justify-center max-h-[70vh] overflow-hidden">
                <img :src="previewPhoto" alt="Foto Fisik Aset" class="max-h-[60vh] max-w-full rounded-xl object-contain shadow-md">
            </div>
        </div>
    </div>

</div>
@endsection
