@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="space-y-6" x-data="{ showCodes: false }">

    <!-- Top Action & Filter Toolbar (Hidden on Print) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-xs flex flex-wrap items-center justify-between gap-4 print:hidden">
        
        <!-- Date Range Filter Form -->
        <form method="GET" action="{{ route('reports.profit_loss') }}" class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-700 shadow-2xs">
                <i class="fa-regular fa-calendar-days text-slate-400 mr-2 text-xs"></i>
                <span class="font-medium mr-1.5 text-slate-500">Periode:</span>
                <input type="date" name="start_date" id="start_date_input" value="{{ $startDate }}" class="bg-transparent focus:outline-none font-semibold text-slate-800 cursor-pointer">
                <span class="mx-2 text-slate-400 font-bold">s/d</span>
                <input type="date" name="end_date" id="end_date_input" value="{{ $endDate }}" class="bg-transparent focus:outline-none font-semibold text-slate-800 cursor-pointer">
            </div>

            <!-- Filter Tag Proyek / Cabang -->
            <select name="tag_id" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:bg-white focus:outline-none">
                <option value="">Semua Tag / Proyek</option>
                @foreach($tags as $t)
                    <option value="{{ $t->id }}" {{ ($tagId ?? '') == $t->id ? 'selected' : '' }}>
                        🏷️ {{ $t->name }}
                    </option>
                @endforeach
            </select>

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

        <!-- Right Buttons: Toggle Code & Export/Print -->
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
        </div>

        <!-- Statement Table (Proper HTML table for perfect layout in PDF and Excel) -->
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

</div>

<!-- CSS for Print / PDF Perfection -->
<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 15mm 18mm 15mm 18mm;
    }
    html, body {
        height: auto !important;
        overflow: visible !important;
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 10pt !important;
        line-height: 1.35 !important;
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
        table-layout: fixed !important;
        border-collapse: collapse !important;
    }
    td, th {
        padding-top: 2.5px !important;
        padding-bottom: 2.5px !important;
    }
    .pr-cell {
        padding-right: 15px !important;
    }
}
</style>

<script>
function exportToExcel() {
    let title = 'Laporan_Laba_Rugi_{{ str_replace('-', '', $startDate) }}_{{ str_replace('-', '', $endDate) }}';
    let table = document.getElementById('report-table-export');
    if (!table) return;

    let compName = @json(strtoupper($company->name));
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
        + '<tr><td colspan="2" class="header-title">' + compName + '</td></tr>'
        + '<tr><td colspan="2" class="header-title" style="font-size: 16pt;">LAPORAN LABA RUGI</td></tr>'
        + '<tr><td colspan="2" class="header-sub">Periode: ' + dateRange + '</td></tr>'
        + '<tr><td colspan="2" class="header-sub" style="font-style: italic;">(Dinyatakan dalam Rupiah Indonesia)</td></tr>'
        + '<tr><td colspan="2" style="height: 15px;"></td></tr>'
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
