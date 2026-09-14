@extends('layouts.app')

@section('title', 'Laporan Beban Operasional')

@push('styles')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 12mm 10mm;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 8.5pt !important;
        }
        aside, nav, header, .no-print, button, form {
            display: none !important;
        }
        .main-content, main, .space-y-4 {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .print-header {
            display: block !important;
            margin-bottom: 12px !important;
            border-bottom: 2px solid #0f172a !important;
            padding-bottom: 6px !important;
        }
        .kpi-cards {
            display: none !important;
        }
        .card-table {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            page-break-inside: auto !important;
        }
        tr {
            page-break-inside: avoid !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $totalBeban = (float)($report['total_beban_operasional'] ?? 0);
    $bebanCount = count($report['beban_list'] ?? []);
    
    // Cari beban tertinggi
    $highestExpense = null;
    if ($bebanCount > 0) {
        $sortedList = $report['beban_list'];
        usort($sortedList, fn($a, $b) => $b['total'] <=> $a['total']);
        $highestExpense = $sortedList[0] ?? null;
    }
    
    $averageExpense = $bebanCount > 0 ? $totalBeban / $bebanCount : 0;
@endphp

<div class="space-y-4">

    <!-- Print Header (Hanya tampil saat Print/PDF) -->
    <div class="hidden print-header">
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-lg font-bold text-slate-900 uppercase tracking-wide">{{ $company->name ?? 'PERUSAHAAN' }}</h1>
                <h2 class="text-sm font-semibold text-rose-800">LAPORAN BEBAN OPERASIONAL (OPERATING EXPENSES)</h2>
                <p class="text-[11px] text-slate-500">Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</p>
            </div>
            <div class="text-right text-[10px] text-slate-400">
                <p>Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y HH:mm') }}</p>
            </div>
        </div>
    </div>

    <!-- Header Filter & Action (Kompak) -->
    <div class="no-print bg-white rounded-xl border border-slate-200 p-3 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('reports.operating_expenses') }}" class="flex flex-wrap items-center gap-2">
            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1 text-xs text-slate-700">
                <i class="fa-regular fa-calendar text-slate-400 mr-2 text-[11px]"></i>
                <input type="date" name="start_date" value="{{ $startDate }}" class="bg-transparent focus:outline-none font-medium text-xs">
                <span class="mx-1.5 text-slate-400">&rarr;</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="bg-transparent focus:outline-none font-medium text-xs">
            </div>

            <button type="submit" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-xs transition flex items-center space-x-1 cursor-pointer">
                <i class="fa-solid fa-filter text-[10px]"></i>
                <span>Tampilkan</span>
            </button>
        </form>

        <div class="flex items-center space-x-2">
            <button type="button" onclick="exportExpensesToExcel()" class="px-3 py-1 bg-white border border-slate-300 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-slate-700 text-xs font-semibold rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-regular fa-file-excel text-emerald-600 text-xs"></i>
                <span>Excel</span>
            </button>
            <button type="button" onclick="window.print()" class="px-3 py-1 bg-white border border-slate-300 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 text-slate-700 text-xs font-semibold rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-regular fa-file-pdf text-rose-600 text-xs"></i>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    <!-- KPI Summary Cards (Kompak & Elegan) -->
    <div class="kpi-cards grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Total Beban -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Beban Operasional</p>
                <h3 class="text-sm sm:text-base font-bold font-mono text-rose-700 mt-0.5">
                    Rp {{ number_format($totalBeban, 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 text-xs shrink-0">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
        </div>

        <!-- Jumlah Pos Akun -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pos Akun Pengeluaran</p>
                <h3 class="text-sm sm:text-base font-bold text-slate-800 mt-0.5">
                    {{ $bebanCount }} <span class="text-[10px] text-slate-400 font-normal">Pos Aktif</span>
                </h3>
            </div>
            <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 text-xs shrink-0">
                <i class="fa-solid fa-list-check"></i>
            </div>
        </div>

        <!-- Beban Terbesar -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Beban Terbesar</p>
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 mt-0.5 truncate" title="{{ $highestExpense['name'] ?? '-' }}">
                    {{ $highestExpense['name'] ?? '-' }}
                </h3>
                <span class="text-[10px] font-mono font-bold text-rose-600">
                    {{ $highestExpense ? 'Rp ' . number_format($highestExpense['total'], 0, ',', '.') : '-' }}
                </span>
            </div>
            <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 text-xs shrink-0">
                <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
        </div>

        <!-- Rata-rata per Pos -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rata-Rata per Pos</p>
                <h3 class="text-sm sm:text-base font-bold font-mono text-slate-800 mt-0.5">
                    Rp {{ number_format($averageExpense, 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs shrink-0">
                <i class="fa-solid fa-calculator"></i>
            </div>
        </div>
    </div>

    <!-- Table Card (Kompak, Rapi, Pita Warna Elegan) -->
    <div class="card-table bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table id="operatingExpensesTable" class="w-full text-left text-xs text-slate-800">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-2.5 px-4 w-36">KODE AKUN</th>
                        <th class="py-2.5 px-4">NAMA AKUN BEBAN</th>
                        <th class="py-2.5 px-4 text-center w-28">PORSI (%)</th>
                        <th class="py-2.5 px-4 text-right w-48">TOTAL BIAYA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    
                    <!-- PITA KATEGORI BEBAN (Ramping & Selaras) -->
                    <tr class="section-header-row">
                        <td colspan="4" class="p-0 border-t border-slate-200">
                            <div class="bg-gradient-to-r from-rose-700 to-red-700 px-4 py-1.5 text-white flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-chart-pie text-[11px] opacity-90"></i>
                                    <span class="text-xs font-bold tracking-wide uppercase">
                                        BEBAN OPERASIONAL & UMUM (OPERATING EXPENSES)
                                    </span>
                                </div>
                                <span class="text-[10px] font-mono font-medium bg-black/20 text-white px-2 py-0.5 rounded">
                                    {{ $bebanCount }} Rekening Beban
                                </span>
                            </div>
                        </td>
                    </tr>

                    @forelse($report['beban_list'] as $b)
                        @php
                            $percentage = $totalBeban > 0 ? ($b['total'] / $totalBeban) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-rose-50/30 transition">
                            <td class="py-2 px-4">
                                <span class="inline-block font-mono font-semibold text-[11px] text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                    {{ $b['code'] }}
                                </span>
                            </td>
                            <td class="py-2 px-4 font-medium text-slate-800 text-xs">
                                {{ $b['name'] }}
                            </td>
                            <td class="py-2 px-4 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-mono text-[10px] font-semibold border border-slate-200">
                                    {{ number_format($percentage, 1) }}%
                                </span>
                            </td>
                            <td class="py-2 px-4 text-right font-mono font-semibold text-xs text-rose-700">
                                Rp {{ number_format($b['total'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-slate-400 italic text-xs">
                                Tidak ada transaksi beban operasional pada periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse

                    <!-- GRAND TOTAL ROW (Sleek Dark Ribbon dengan Double Underline) -->
                    <tr class="bg-slate-900 text-white border-t-2 border-rose-500">
                        <td colspan="2" class="py-2.5 px-4 align-middle">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-white">
                                    TOTAL BEBAN OPERASIONAL
                                </span>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-rose-500/30 text-rose-300 border border-rose-400/30">
                                    <i class="fa-solid fa-receipt text-[9px]"></i>
                                    Pengeluaran Usaha
                                </span>
                            </div>
                        </td>
                        <td class="py-2.5 px-4 text-center align-middle font-mono font-bold text-xs text-slate-400">
                            100%
                        </td>
                        <td class="py-2.5 px-4 text-right align-middle font-mono font-bold text-xs sm:text-sm text-white border-b-2 border-double border-white">
                            Rp {{ number_format($totalBeban, 0, ',', '.') }}
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- SCRIPT EKSPOR EXCEL NATIVE BERSIH UNTUK BEBAN OPERASIONAL -->
<script>
function exportExpensesToExcel() {
    var companyName = @json($company->name ?? 'Perusahaan');
    var periodStr = @json(\Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y'));
    var todayStr = @json(date('Y-m-d'));

    var excelXml = '<' + 'xml><' + 'x:ExcelWorkbook><' + 'x:ExcelWorksheets><' + 'x:ExcelWorksheet>';
    excelXml += '<' + 'x:Name>Beban Operasional<' + '/x:Name>';
    excelXml += '<' + 'x:WorksheetOptions><' + 'x:DisplayGridlines/><' + '/x:WorksheetOptions><' + '/x:ExcelWorksheet><' + '/x:ExcelWorksheets><' + '/x:ExcelWorkbook><' + '/xml>';

    var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    html += '<head><meta charset="utf-8"><!--[if gte mso 9]>' + excelXml + '<![endif]--></head><body>';

    var table = document.getElementById('operatingExpensesTable');
    if (!table) {
        alert('Tabel tidak ditemukan.');
        return;
    }

    html += '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse; font-family:Arial, sans-serif; font-size:10pt;">';
    html += '<tr><th colspan="4" style="font-size:13pt; font-weight:bold; text-align:left; background-color:#1e3a8a; color:#ffffff; padding:8px;">' + companyName + ' - LAPORAN BEBAN OPERASIONAL</th></tr>';
    html += '<tr><th colspan="4" style="text-align:left; background-color:#f1f5f9; color:#475569; padding:6px;">Periode: ' + periodStr + '</th></tr>';
    html += '<tr><td colspan="4" style="height:10px;"></td></tr>';

    var rows = table.querySelectorAll('tr');
    rows.forEach(function(row) {
        var ths = row.querySelectorAll('th');
        var tds = row.querySelectorAll('td');

        if (ths.length > 0) {
            html += '<tr style="background-color:#f8fafc; font-weight:bold; text-align:center;">';
            ths.forEach(function(th) {
                html += '<th style="padding:6px; border:1px solid #cbd5e1;">' + th.innerText.trim() + '</th>';
            });
            html += '</tr>';
        } else if (tds.length > 0) {
            var isSection = row.classList.contains('section-header-row') || tds.length === 1;
            var isTotal = row.innerText.indexOf('TOTAL BEBAN OPERASIONAL') !== -1;

            if (isSection) {
                var sectionTitle = tds[0].innerText.trim().replace(/\s+/g, ' ');
                html += '<tr style="background-color:#be123c; color:#ffffff; font-weight:bold;">';
                html += '<td colspan="4" style="padding:6px; font-size:10.5pt; color:#ffffff; background-color:#be123c;">' + sectionTitle + '</td>';
                html += '</tr>';
            } else if (isTotal) {
                var totalVal = tds[2] ? tds[2].innerText.trim() : (tds[1] ? tds[1].innerText.trim() : '');
                html += '<tr style="background-color:#0f172a; color:#ffffff; font-weight:bold; font-size:10.5pt;">';
                html += '<td colspan="3" style="padding:8px; color:#ffffff; background-color:#0f172a;">TOTAL BEBAN OPERASIONAL</td>';
                html += '<td style="text-align:right; padding:8px; color:#ffffff; background-color:#0f172a;">' + totalVal + '</td>';
                html += '</tr>';
            } else {
                var code = tds[0] ? tds[0].innerText.trim() : '';
                var name = tds[1] ? tds[1].innerText.trim() : '';
                var pct = tds[2] ? tds[2].innerText.trim() : '';
                var amt = tds[3] ? tds[3].innerText.trim() : '';

                html += '<tr>';
                html += '<td style="padding:5px; border:1px solid #e2e8f0;">' + code + '</td>';
                html += '<td style="padding:5px; border:1px solid #e2e8f0;">' + name + '</td>';
                html += '<td style="text-align:center; padding:5px; border:1px solid #e2e8f0;">' + pct + '</td>';
                html += '<td style="text-align:right; padding:5px; border:1px solid #e2e8f0;">' + amt + '</td>';
                html += '</tr>';
            }
        }
    });

    html += '</table></body></html>';

    var blob = new Blob(['\ufeff', html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'Beban_Operasional_' + todayStr + '.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
@endsection
