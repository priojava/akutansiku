@extends('layouts.app')

@section('title', 'Laporan Neraca Keuangan')

@section('content')
<div class="space-y-6" x-data="{ 
    viewMode: 'skontro', 
    showCodes: false,
    exportToExcel() {
        let table = document.getElementById('balance-sheet-table');
        let html = table.outerHTML;
        let blob = new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel' });
        let url = URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = 'Laporan_Neraca_Keuangan_{{ str_replace('-', '', $asOfDate) }}.xls';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
}">

    <!-- Filter & Action Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex flex-wrap items-center justify-between gap-4 print:hidden">
        <form method="GET" action="{{ route('reports.balance_sheet') }}" class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-700">
                <i class="fa-regular fa-calendar-days text-slate-400 mr-2"></i>
                <span class="font-medium mr-2 text-slate-600">Per Tanggal:</span>
                <input type="date" name="as_of_date" id="as_of_date_input" value="{{ $asOfDate }}" class="bg-transparent focus:outline-none font-semibold text-slate-800">
            </div>

            <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                <span>Tampilkan</span>
            </button>

            <!-- Quick Shortcuts -->
            <div class="hidden lg:flex items-center space-x-1 pl-2 border-l border-slate-200 text-xs text-slate-600">
                <span class="text-[11px] text-slate-400 mr-1">Pilihan Cepat:</span>
                <button type="button" @click="document.getElementById('as_of_date_input').value = '{{ \Carbon\Carbon::now()->toDateString() }}'; $el.closest('form').submit()" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] rounded transition">Hari Ini</button>
                <button type="button" @click="document.getElementById('as_of_date_input').value = '{{ \Carbon\Carbon::now()->endOfMonth()->toDateString() }}'; $el.closest('form').submit()" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] rounded transition">Akhir Bulan Ini</button>
                <button type="button" @click="document.getElementById('as_of_date_input').value = '{{ \Carbon\Carbon::now()->subMonth()->endOfMonth()->toDateString() }}'; $el.closest('form').submit()" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] rounded transition">Akhir Bulan Lalu</button>
                <button type="button" @click="document.getElementById('as_of_date_input').value = '{{ \Carbon\Carbon::now()->endOfYear()->toDateString() }}'; $el.closest('form').submit()" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] rounded transition">Akhir Tahun</button>
            </div>
        </form>

        <div class="flex items-center flex-wrap gap-2">
            <!-- Toggle Kode Akun -->
            <button type="button" @click="showCodes = !showCodes" :class="showCodes ? 'bg-indigo-50 border-indigo-300 text-indigo-700 font-bold' : 'bg-white border-slate-300 text-slate-600'" class="px-3 py-1.5 border text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5" title="Tampilkan/Sembunyikan Nomor Kode Akun">
                <i class="fa-solid fa-barcode text-xs"></i>
                <span x-text="showCodes ? 'Sembunyikan Kode' : 'Tampilkan Kode'"></span>
            </button>

            <!-- Toggle Format Skontro vs Stafel -->
            <div class="inline-flex rounded-lg border border-slate-300 bg-slate-100 p-0.5">
                <button type="button" @click="viewMode = 'skontro'" :class="viewMode === 'skontro' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 text-xs rounded-md transition flex items-center space-x-1">
                    <i class="fa-solid fa-table-columns text-[11px]"></i>
                    <span>Skontro (2 Kolom)</span>
                </button>
                <button type="button" @click="viewMode = 'stafel'" :class="viewMode === 'stafel' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 text-xs rounded-md transition flex items-center space-x-1">
                    <i class="fa-solid fa-bars text-[11px]"></i>
                    <span>Stafel (Vertikal)</span>
                </button>
            </div>

            <!-- Export Buttons -->
            <button @click="exportToExcel()" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-regular fa-file-excel text-emerald-600"></i>
                <span>Excel</span>
            </button>
            <button onclick="window.print()" class="px-3 py-1.5 bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-800 text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print text-blue-600"></i>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 print:hidden">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Jumlah Aset</p>
                    <h3 class="text-lg font-bold text-blue-700 mt-1">Rp {{ number_format($report['aset']['total'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                    <i class="fa-solid fa-vault text-base"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex justify-between">
                <span>Lancar: Rp {{ number_format($report['aset']['lancar']['total'], 0, ',', '.') }}</span>
                <span>Tidak Lancar: Rp {{ number_format($report['aset']['tidak_lancar']['total'], 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Jumlah Kewajiban</p>
                    <h3 class="text-lg font-bold text-amber-700 mt-1">Rp {{ number_format($report['kewajiban']['total'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                    <i class="fa-solid fa-file-invoice text-base"></i>
                </div>
            </div>
            <p class="mt-2 text-[11px] text-slate-500">Utang Usaha & Kewajiban Berjalan</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Jumlah Ekuitas</p>
                    <h3 class="text-lg font-bold text-purple-700 mt-1">Rp {{ number_format($report['ekuitas']['total'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
                    <i class="fa-solid fa-coins text-base"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex justify-between">
                <span>Modal: Rp {{ number_format($report['ekuitas']['modal']['total'], 0, ',', '.') }}</span>
                <span>Laba Berjalan: Rp {{ number_format($report['ekuitas']['laba_berjalan'], 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border {{ $report['is_balanced'] ? 'border-emerald-200 bg-emerald-50/20' : 'border-rose-200 bg-rose-50/20' }} p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Status Neraca</p>
                    @if($report['is_balanced'])
                        <h3 class="text-lg font-bold text-emerald-600 mt-1 flex items-center space-x-1.5">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            <span>SEIMBANG</span>
                        </h3>
                    @else
                        <h3 class="text-lg font-bold text-rose-600 mt-1 flex items-center space-x-1.5">
                            <i class="fa-solid fa-circle-xmark text-rose-500"></i>
                            <span>SELISIH</span>
                        </h3>
                    @endif
                </div>
                <div class="w-10 h-10 rounded-lg {{ $report['is_balanced'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} flex items-center justify-center">
                    <i class="fa-solid fa-scale-balanced text-base"></i>
                </div>
            </div>
            <p class="mt-2 text-[11px] {{ $report['is_balanced'] ? 'text-emerald-700 font-medium' : 'text-rose-700 font-bold' }}">
                {{ $report['is_balanced'] ? 'Aset = Kewajiban + Ekuitas' : 'Selisih Rp ' . number_format(abs($report['diff']), 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- REPORT PRESENTATION CONTAINER (Printable & Exportable) -->
    <div id="balance-sheet-table" class="bg-white rounded-xl border-2 border-slate-900 shadow-sm p-4 sm:p-6 text-slate-900 font-sans print:border-2 print:border-black print:p-2 print:shadow-none">

        <!-- Report Header Formil (Sesuai Standar Akuntansi & Gambar) -->
        <div class="text-center pb-4 border-b-2 border-slate-900">
            <h4 class="text-xs sm:text-sm font-semibold uppercase tracking-wider text-slate-600">{{ $company->name }}</h4>
            <h1 class="text-base sm:text-xl font-black uppercase tracking-widest text-slate-900 mt-0.5">LAPORAN NERACA KEUANGAN</h1>
            <p class="text-xs sm:text-sm font-medium text-slate-700 italic mt-0.5">Per Tanggal {{ $report['as_of_date_formatted'] }}</p>
        </div>

        <!-- 1. BENTUK SKONTRO (2 KOLOM BERDAMPINGAN KIRI-KANAN SESUAI GAMBAR) -->
        <div x-show="viewMode === 'skontro'" class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x-2 divide-slate-900 pt-0 print:grid-cols-2 print:divide-x-2 print:divide-y-0">

            <!-- ================= KOLOM KIRI: ASET ================= -->
            <div class="flex flex-col justify-between pt-2 pb-2 md:pr-4">
                <div>
                    <!-- Header Kolom Aset -->
                    <div class="text-center font-black tracking-widest text-sm py-1.5 border-b-2 border-slate-900 bg-slate-100/80 uppercase">
                        A S E T
                    </div>

                    <!-- Bagian: ASET LANCAR -->
                    <div class="mt-3">
                        <div class="font-bold text-xs uppercase tracking-wide text-slate-900 px-1 py-1">
                            ASET LANCAR
                        </div>
                        <div class="space-y-1 mt-0.5">
                            @forelse($report['aset']['lancar']['items'] as $item)
                                <div class="flex justify-between items-center text-xs py-0.5 px-3 hover:bg-slate-50">
                                    <span class="text-slate-800">
                                        <span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-1.5">{{ $item['code'] }}</span>{{ $item['name'] }}
                                    </span>
                                    <span class="font-mono text-slate-900 text-right">
                                        {{ number_format($item['amount'], 2, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-xs text-slate-400 italic px-3 py-1">Tidak ada aset lancar</div>
                            @endforelse
                        </div>

                        <!-- Subtotal Aset Lancar -->
                        <div class="mt-1 pt-1.5 border-t border-slate-900 flex justify-between items-center font-bold text-xs px-2 py-1">
                            <span>Jumlah Aset Lancar</span>
                            <span class="font-mono underline text-slate-900 text-right">
                                {{ number_format($report['aset']['lancar']['total'], 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Bagian: ASET TIDAK LANCAR -->
                    <div class="mt-5">
                        <div class="font-bold text-xs uppercase tracking-wide text-slate-900 px-1 py-1">
                            ASET TIDAK LANCAR
                        </div>
                        <div class="space-y-1 mt-0.5">
                            <!-- Harta Tetap -->
                            @foreach($report['aset']['tidak_lancar']['tetap'] as $item)
                                <div class="flex justify-between items-center text-xs py-0.5 px-3 hover:bg-slate-50">
                                    <span class="text-slate-800">
                                        <span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-1.5">{{ $item['code'] }}</span>{{ $item['name'] }}
                                    </span>
                                    <span class="font-mono text-slate-900 text-right">
                                        {{ number_format($item['amount'], 2, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach

                            <!-- Akumulasi Penyusutan (Contra-Asset dalam kurung) -->
                            @foreach($report['aset']['tidak_lancar']['akumulasi'] as $item)
                                <div class="flex justify-between items-center text-xs py-0.5 px-3 hover:bg-slate-50 text-slate-700">
                                    <span>
                                        <span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-1.5">{{ $item['code'] }}</span>{{ $item['name'] }}
                                    </span>
                                    <span class="font-mono text-right">
                                        ({{ number_format($item['amount'], 2, ',', '.') }})
                                    </span>
                                </div>
                            @endforeach

                            <!-- Harta Lainnya -->
                            @foreach($report['aset']['tidak_lancar']['lainnya'] as $item)
                                <div class="flex justify-between items-center text-xs py-0.5 px-3 hover:bg-slate-50">
                                    <span class="text-slate-800">
                                        <span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-1.5">{{ $item['code'] }}</span>{{ $item['name'] }}
                                    </span>
                                    <span class="font-mono text-slate-900 text-right">
                                        {{ number_format($item['amount'], 2, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach

                            @if(count($report['aset']['tidak_lancar']['tetap']) == 0 && count($report['aset']['tidak_lancar']['akumulasi']) == 0 && count($report['aset']['tidak_lancar']['lainnya']) == 0)
                                <div class="text-xs text-slate-400 italic px-3 py-1">Tidak ada aset tidak lancar</div>
                            @endif
                        </div>

                        <!-- Subtotal Aset Tidak Lancar -->
                        <div class="mt-1 pt-1.5 border-t border-slate-900 flex justify-between items-center font-bold text-xs px-2 py-1">
                            <span>Jumlah Aset Tidak Lancar</span>
                            <span class="font-mono underline text-slate-900 text-right">
                                {{ number_format($report['aset']['tidak_lancar']['total'], 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- GRAND TOTAL ASET (Double Underline) -->
                <div class="mt-8 pt-3 border-t-2 border-slate-900">
                    <div class="flex justify-between items-center font-black text-xs sm:text-sm uppercase tracking-wider px-2 py-1.5 bg-slate-50/80 border-b-4 border-double border-slate-900">
                        <span>JUMLAH ASET</span>
                        <span class="font-mono text-right">
                            {{ number_format($report['aset']['total'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- ================= KOLOM KANAN: KEWAJIBAN DAN EKUITAS ================= -->
            <div class="flex flex-col justify-between pt-2 pb-2 md:pl-4 mt-4 md:mt-0 print:mt-0 print:pl-4">
                <div>
                    <!-- Header Kolom Kewajiban & Ekuitas -->
                    <div class="text-center font-black tracking-widest text-sm py-1.5 border-b-2 border-slate-900 bg-slate-100/80 uppercase">
                        KEWAJIBAN DAN EKUITAS
                    </div>

                    <!-- Bagian: KEWAJIBAN JANGKA PENDEK -->
                    <div class="mt-3">
                        <div class="font-bold text-xs uppercase tracking-wide text-slate-900 px-1 py-1">
                            KEWAJIBAN JANGKA PENDEK
                        </div>
                        <div class="space-y-1 mt-0.5">
                            @forelse($report['kewajiban']['jangka_pendek']['items'] as $item)
                                <div class="flex justify-between items-center text-xs py-0.5 px-3 hover:bg-slate-50">
                                    <span class="text-slate-800">
                                        <span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-1.5">{{ $item['code'] }}</span>{{ $item['name'] }}
                                    </span>
                                    <span class="font-mono text-slate-900 text-right">
                                        {{ number_format($item['amount'], 2, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-xs text-slate-400 italic px-3 py-1">Tidak ada utang jangka pendek</div>
                            @endforelse
                        </div>

                        <!-- Subtotal Kewajiban Jangka Pendek -->
                        <div class="mt-1 pt-1.5 border-t border-slate-900 flex justify-between items-center font-bold text-xs px-2 py-1">
                            <span>Jumlah Kewajiban Jangka Pendek</span>
                            <span class="font-mono underline text-slate-900 text-right">
                                {{ number_format($report['kewajiban']['jangka_pendek']['total'], 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Bagian: KEWAJIBAN JANGKA PANJANG (Jika ada) -->
                    @if(count($report['kewajiban']['jangka_panjang']['items']) > 0)
                    <div class="mt-4">
                        <div class="font-bold text-xs uppercase tracking-wide text-slate-900 px-1 py-1">
                            KEWAJIBAN JANGKA PANJANG
                        </div>
                        <div class="space-y-1 mt-0.5">
                            @foreach($report['kewajiban']['jangka_panjang']['items'] as $item)
                                <div class="flex justify-between items-center text-xs py-0.5 px-3 hover:bg-slate-50">
                                    <span class="text-slate-800">
                                        <span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-1.5">{{ $item['code'] }}</span>{{ $item['name'] }}
                                    </span>
                                    <span class="font-mono text-slate-900 text-right">
                                        {{ number_format($item['amount'], 2, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-1 pt-1.5 border-t border-slate-900 flex justify-between items-center font-bold text-xs px-2 py-1">
                            <span>Jumlah Kewajiban Jangka Panjang</span>
                            <span class="font-mono underline text-slate-900 text-right">
                                {{ number_format($report['kewajiban']['jangka_panjang']['total'], 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @endif

                    <!-- Bagian: EKUITAS -->
                    <div class="mt-5">
                        <div class="font-bold text-xs uppercase tracking-wide text-slate-900 px-1 py-1">
                            EKUITAS
                        </div>
                        <div class="space-y-1 mt-0.5">
                            <!-- Modal Disetor / Modal Saham -->
                            @foreach($report['ekuitas']['modal']['items'] as $item)
                                <div class="flex justify-between items-center text-xs py-0.5 px-3 hover:bg-slate-50">
                                    <span class="text-slate-800">
                                        <span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-1.5">{{ $item['code'] }}</span>{{ $item['name'] }}
                                    </span>
                                    <span class="font-mono text-slate-900 text-right">
                                        {{ number_format($item['amount'], 2, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach

                            <!-- Laba Ditahan -->
                            @foreach($report['ekuitas']['laba_ditahan']['items'] as $item)
                                <div class="flex justify-between items-center text-xs py-0.5 px-3 hover:bg-slate-50">
                                    <span class="text-slate-800">
                                        <span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-1.5">{{ $item['code'] }}</span>{{ $item['name'] }}
                                    </span>
                                    <span class="font-mono text-slate-900 text-right">
                                        {{ number_format($item['amount'], 2, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach

                            <!-- Laba (Rugi) Periode Berjalan -->
                            <div class="flex justify-between items-center text-xs py-0.5 px-3 hover:bg-slate-50">
                                <span class="text-slate-800 font-medium">Laba (Rugi) Periode Berjalan</span>
                                <span class="font-mono text-slate-900 text-right">
                                    @if($report['ekuitas']['laba_berjalan'] < 0)
                                        ({{ number_format(abs($report['ekuitas']['laba_berjalan']), 2, ',', '.') }})
                                    @else
                                        {{ number_format($report['ekuitas']['laba_berjalan'], 2, ',', '.') }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Subtotal Ekuitas -->
                        <div class="mt-1 pt-1.5 border-t border-slate-900 flex justify-between items-center font-bold text-xs px-2 py-1">
                            <span>Jumlah Ekuitas</span>
                            <span class="font-mono underline text-slate-900 text-right">
                                {{ number_format($report['ekuitas']['total'], 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- GRAND TOTAL KEWAJIBAN DAN EKUITAS (Double Underline, Sejajar Aset) -->
                <div class="mt-8 pt-3 border-t-2 border-slate-900">
                    <div class="flex justify-between items-center font-black text-xs sm:text-sm uppercase tracking-wider px-2 py-1.5 bg-slate-50/80 border-b-4 border-double border-slate-900">
                        <span>JUMLAH KEWAJIBAN DAN EKUITAS</span>
                        <span class="font-mono text-right">
                            {{ number_format($report['total_kewajiban_dan_ekuitas'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- 2. BENTUK STAFEL (1 KOLOM VERTIKAL) -->
        <div x-show="viewMode === 'stafel'" class="space-y-6 pt-3" style="display: none;">
            
            <!-- ASET -->
            <div class="border border-slate-300 rounded-lg p-3">
                <h3 class="font-black text-sm uppercase tracking-wider text-slate-900 pb-2 border-b border-slate-200">1. ASET</h3>
                
                <!-- Aset Lancar -->
                <div class="mt-3">
                    <p class="font-bold text-xs uppercase text-slate-700">Aset Lancar</p>
                    <table class="w-full text-xs mt-1">
                        @foreach($report['aset']['lancar']['items'] as $item)
                        <tr class="border-b border-slate-100">
                            <td class="py-1"><span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-2">{{ $item['code'] }}</span>{{ $item['name'] }}</td>
                            <td class="py-1 text-right font-mono">{{ number_format($item['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="font-bold bg-slate-50">
                            <td class="py-1.5">Jumlah Aset Lancar</td>
                            <td class="py-1.5 text-right font-mono">{{ number_format($report['aset']['lancar']['total'], 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Aset Tidak Lancar -->
                <div class="mt-4">
                    <p class="font-bold text-xs uppercase text-slate-700">Aset Tidak Lancar</p>
                    <table class="w-full text-xs mt-1">
                        @foreach($report['aset']['tidak_lancar']['tetap'] as $item)
                        <tr class="border-b border-slate-100">
                            <td class="py-1"><span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-2">{{ $item['code'] }}</span>{{ $item['name'] }}</td>
                            <td class="py-1 text-right font-mono">{{ number_format($item['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        @foreach($report['aset']['tidak_lancar']['akumulasi'] as $item)
                        <tr class="border-b border-slate-100 text-slate-600">
                            <td class="py-1"><span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-2">{{ $item['code'] }}</span>{{ $item['name'] }}</td>
                            <td class="py-1 text-right font-mono">({{ number_format($item['amount'], 2, ',', '.') }})</td>
                        </tr>
                        @endforeach
                        @foreach($report['aset']['tidak_lancar']['lainnya'] as $item)
                        <tr class="border-b border-slate-100">
                            <td class="py-1"><span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-2">{{ $item['code'] }}</span>{{ $item['name'] }}</td>
                            <td class="py-1 text-right font-mono">{{ number_format($item['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="font-bold bg-slate-50">
                            <td class="py-1.5">Jumlah Aset Tidak Lancar</td>
                            <td class="py-1.5 text-right font-mono">{{ number_format($report['aset']['tidak_lancar']['total'], 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="flex justify-between items-center font-bold text-sm bg-blue-50/70 p-2.5 rounded-md mt-4 border border-blue-200">
                    <span>TOTAL ASET</span>
                    <span class="font-mono text-blue-800">Rp {{ number_format($report['aset']['total'], 2, ',', '.') }}</span>
                </div>
            </div>

            <!-- KEWAJIBAN -->
            <div class="border border-slate-300 rounded-lg p-3">
                <h3 class="font-black text-sm uppercase tracking-wider text-slate-900 pb-2 border-b border-slate-200">2. KEWAJIBAN</h3>
                <div class="mt-3">
                    <p class="font-bold text-xs uppercase text-slate-700">Kewajiban Jangka Pendek</p>
                    <table class="w-full text-xs mt-1">
                        @foreach($report['kewajiban']['jangka_pendek']['items'] as $item)
                        <tr class="border-b border-slate-100">
                            <td class="py-1"><span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-2">{{ $item['code'] }}</span>{{ $item['name'] }}</td>
                            <td class="py-1 text-right font-mono">{{ number_format($item['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="font-bold bg-slate-50">
                            <td class="py-1.5">Jumlah Kewajiban Jangka Pendek</td>
                            <td class="py-1.5 text-right font-mono">{{ number_format($report['kewajiban']['jangka_pendek']['total'], 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                @if(count($report['kewajiban']['jangka_panjang']['items']) > 0)
                <div class="mt-4">
                    <p class="font-bold text-xs uppercase text-slate-700">Kewajiban Jangka Panjang</p>
                    <table class="w-full text-xs mt-1">
                        @foreach($report['kewajiban']['jangka_panjang']['items'] as $item)
                        <tr class="border-b border-slate-100">
                            <td class="py-1"><span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-2">{{ $item['code'] }}</span>{{ $item['name'] }}</td>
                            <td class="py-1 text-right font-mono">{{ number_format($item['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endif

                <div class="flex justify-between items-center font-bold text-sm bg-amber-50/70 p-2.5 rounded-md mt-4 border border-amber-200">
                    <span>TOTAL KEWAJIBAN</span>
                    <span class="font-mono text-amber-800">Rp {{ number_format($report['kewajiban']['total'], 2, ',', '.') }}</span>
                </div>
            </div>

            <!-- EKUITAS -->
            <div class="border border-slate-300 rounded-lg p-3">
                <h3 class="font-black text-sm uppercase tracking-wider text-slate-900 pb-2 border-b border-slate-200">3. EKUITAS</h3>
                <table class="w-full text-xs mt-2">
                    @foreach($report['ekuitas']['modal']['items'] as $item)
                    <tr class="border-b border-slate-100">
                        <td class="py-1"><span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-2">{{ $item['code'] }}</span>{{ $item['name'] }}</td>
                        <td class="py-1 text-right font-mono">{{ number_format($item['amount'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    @foreach($report['ekuitas']['laba_ditahan']['items'] as $item)
                    <tr class="border-b border-slate-100">
                        <td class="py-1"><span x-show="showCodes" class="text-slate-400 font-mono text-[10px] mr-2">{{ $item['code'] }}</span>{{ $item['name'] }}</td>
                        <td class="py-1 text-right font-mono">{{ number_format($item['amount'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-b border-slate-100 font-medium">
                        <td class="py-1">Laba (Rugi) Periode Berjalan</td>
                        <td class="py-1 text-right font-mono">
                            {{ $report['ekuitas']['laba_berjalan'] < 0 ? '(' . number_format(abs($report['ekuitas']['laba_berjalan']), 2, ',', '.') . ')' : number_format($report['ekuitas']['laba_berjalan'], 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="font-bold bg-slate-50">
                        <td class="py-1.5">Jumlah Ekuitas</td>
                        <td class="py-1.5 text-right font-mono">{{ number_format($report['ekuitas']['total'], 2, ',', '.') }}</td>
                    </tr>
                </table>

                <div class="flex justify-between items-center font-bold text-sm bg-purple-50/70 p-2.5 rounded-md mt-4 border border-purple-200">
                    <span>TOTAL EKUITAS</span>
                    <span class="font-mono text-purple-800">Rp {{ number_format($report['ekuitas']['total'], 2, ',', '.') }}</span>
                </div>
            </div>

            <!-- GRAND TOTAL KEWAJIBAN & EKUITAS (Stafel) -->
            <div class="flex justify-between items-center font-black text-sm sm:text-base p-3 bg-slate-900 text-white rounded-lg">
                <span>TOTAL KEWAJIBAN & EKUITAS</span>
                <span class="font-mono">Rp {{ number_format($report['total_kewajiban_dan_ekuitas'], 2, ',', '.') }}</span>
            </div>
        </div>

    </div>

</div>

<!-- Print Stylesheet Khusus untuk Laporan Neraca Formal -->
<style>
@media print {
    body {
        background: white !important;
        font-family: 'Times New Roman', Times, serif, sans-serif !important;
        color: black !important;
    }
    aside, nav, header, button, .print\:hidden {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    #balance-sheet-table {
        border: 2px solid black !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 10px !important;
    }
    .divide-slate-900 {
        border-color: black !important;
    }
    .border-slate-900 {
        border-color: black !important;
    }
    .bg-slate-50, .bg-slate-100\/80 {
        background-color: transparent !important;
    }
}
</style>
@endsection
