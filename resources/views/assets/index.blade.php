@extends('layouts.app')

@section('title', 'Daftar Aset')

@section('content')
<div class="space-y-6" x-data="{
    search: '',
    formatRupiah(val) {
        return 'Rp ' + Number(val).toLocaleString('id-ID');
    }
}">

    <!-- Page Header & Action Buttons -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Daftar Aset (Aktiva Tetap)</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola perolehan aset tetap, pencatatan nilai buku, dan jadwal penyusutan depresiasi</p>
        </div>
        <div class="flex items-center space-x-2.5">
            <a href="{{ route('assets.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Aset</span>
            </a>
            <a href="{{ route('assets.export') }}" class="bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-file-excel text-xs text-emerald-300"></i>
                <span>Export Excel</span>
            </a>
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-blue-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">Total Nilai Perolehan</p>
                <h3 class="text-base font-bold text-blue-900 mt-0.5">Rp {{ number_format($totalAcquisitionCost, 0, ',', '.') }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-building-columns"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-rose-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">Akumulasi Penyusutan</p>
                <h3 class="text-base font-bold text-rose-600 mt-0.5">Rp {{ number_format($totalAccumulatedDepreciation, 0, ',', '.') }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-chart-line-down"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-emerald-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500">Total Nilai Buku Bersih</p>
                <h3 class="text-base font-bold text-emerald-700 mt-0.5">Rp {{ number_format($totalBookValue, 0, ',', '.') }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>
    </div>

    <!-- Table Card with Search -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Search Toolbar -->
        <div class="p-4 border-b border-slate-100 flex items-center justify-end">
            <div class="relative">
                <input type="text" x-model="search" placeholder="Cari..."
                       class="w-64 pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-blue-500 text-xs"></i>
            </div>
        </div>

        <!-- Horizontal Scrollable Table matching mockup -->
        <div class="overflow-x-auto sidebar-scroll">
            <table class="w-full text-left text-xs text-slate-700 whitespace-nowrap">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold">
                    <tr>
                        <th class="px-4 py-3.5 w-12 text-center">NO</th>
                        <th class="px-4 py-3.5">KODE</th>
                        <th class="px-4 py-3.5">NAMA</th>
                        <th class="px-4 py-3.5">AKUN ASET TETAP</th>
                        <th class="px-4 py-3.5">DESKRIPSI</th>
                        <th class="px-4 py-3.5">TANGGAL AKUISISI</th>
                        <th class="px-4 py-3.5 text-right">BIAYA AKUISISI</th>
                        <th class="px-4 py-3.5 text-right font-bold text-blue-900">NILAI BUKU</th>
                        <th class="px-4 py-3.5">AKUN DIKREDITKAN</th>
                        <th class="px-4 py-3.5 text-center">ASET DEPRESIASI</th>
                        <th class="px-4 py-3.5">METODE</th>
                        <th class="px-4 py-3.5 text-center">MASA MANFAAT</th>
                        <th class="px-4 py-3.5">AKUN PENYUSUTAN</th>
                        <th class="px-4 py-3.5">AKUMULASI AKUN PENYUSUTAN</th>
                        <th class="px-4 py-3.5 text-right">AKUMULASI PENYUSUTAN</th>
                        <th class="px-4 py-3.5">BULAN AKHIR AKUMULASI PENYUSUTAN</th>
                        <th class="px-4 py-3.5 text-center">STATUS DEPRESIASI</th>
                        <th class="px-4 py-3.5 text-center">STOP DEPRESIASI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assets as $index => $asset)
                        <tr class="hover:bg-slate-50/80 transition"
                            x-show="!search || '{{ strtolower($asset->code . ' ' . $asset->name . ' ' . $asset->description . ' ' . ($asset->assetAccount?->name ?? '')) }}'.includes(search.toLowerCase())">
                            <td class="px-4 py-3 text-center text-slate-400 font-semibold">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-mono font-bold text-blue-600">{{ $asset->code }}</td>
                            <td class="px-4 py-3 font-bold text-slate-800 flex items-center space-x-2">
                                @if($asset->photo_path)
                                    <img src="{{ asset('storage/' . $asset->photo_path) }}" alt="{{ $asset->name }}" class="w-6 h-6 rounded object-cover border border-slate-200">
                                @endif
                                <span>{{ $asset->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $asset->assetAccount?->name }} <span class="text-[10px] text-slate-400">({{ $asset->assetAccount?->code }})</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 max-w-xs truncate">{{ $asset->description ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $asset->acquisition_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900">
                                Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-600 bg-emerald-50/40">
                                Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $asset->creditedAccount?->name }} <span class="text-[10px] text-slate-400">({{ $asset->creditedAccount?->code }})</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($asset->is_depreciated)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Ya</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Tidak</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                @if($asset->is_depreciated)
                                    {{ $asset->depreciation_method === 'declining_balance' ? 'Saldo Menurun' : 'Garis Lurus' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-medium">
                                {{ $asset->useful_life_years ? $asset->useful_life_years . ' Tahun' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $asset->expenseAccount?->name ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $asset->accumulatedAccount?->name ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-right text-rose-600 font-semibold">
                                Rp {{ number_format($asset->accumulated_depreciation_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $asset->depreciation_end_date ? $asset->depreciation_end_date->format('M Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($asset->depreciation_status === 'active')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                @elseif($asset->depreciation_status === 'stopped')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Berhenti</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Selesai</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    @if($asset->is_depreciated)
                                        <form method="POST" action="{{ route('assets.toggle_depreciation', $asset->id) }}">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 text-[11px] font-bold rounded-lg border transition {{ $asset->depreciation_status === 'active' ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                                                {{ $asset->depreciation_status === 'active' ? 'Stop' : 'Aktif' }}
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('assets.destroy', $asset->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset \'{{ $asset->name }}\' ({{ $asset->code }}) ini? Jurnal perolehannya juga akan dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus Aset">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="18" class="text-center py-12 text-slate-400">
                                No data available in table. Klik tombol <strong>+ Tambah Aset</strong> di atas untuk mendaftarkan aset tetap Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
