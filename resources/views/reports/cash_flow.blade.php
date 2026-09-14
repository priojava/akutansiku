@extends('layouts.app')

@section('title', 'Laporan Arus Kas (Cash Flow)')

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
    $netCash = $report['perubahan_kas'];
    $isNetPos = $netCash > 0;
    $isNetNeg = $netCash < 0;
    $netCashAbs = abs($netCash);

    $totalKasMasuk = $report['aktivitas_operasional']['penerimaan_pelanggan'];
    $totalKasKeluar = $report['aktivitas_operasional']['pembayaran_pemasok'];
@endphp

<div class="space-y-4">

    <!-- Print Header (Hanya tampil saat Print/PDF) -->
    <div class="hidden print-header">
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-lg font-bold text-slate-900 uppercase tracking-wide">{{ $company->name ?? 'PERUSAHAAN' }}</h1>
                <h2 class="text-sm font-semibold text-blue-800">LAPORAN ARUS KAS (STATEMENT OF CASH FLOWS)</h2>
                <p class="text-[11px] text-slate-500">Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</p>
            </div>
            <div class="text-right text-[10px] text-slate-400">
                <p>Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y HH:mm') }}</p>
            </div>
        </div>
    </div>

    <!-- Header Filter & Action (Kompak) -->
    <div class="no-print bg-white rounded-xl border border-slate-200 p-3 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('reports.cash_flow') }}" class="flex flex-wrap items-center gap-2">
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
            <button type="button" onclick="exportCashFlowToExcel()" class="px-3 py-1 bg-white border border-slate-300 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-slate-700 text-xs font-semibold rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
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
        <!-- Kas Masuk Operasional -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Arus Kas Masuk</p>
                <h3 class="text-sm sm:text-base font-bold font-mono text-emerald-700 mt-0.5">
                    Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xs shrink-0">
                <i class="fa-solid fa-arrow-down-left"></i>
            </div>
        </div>

        <!-- Kas Keluar Operasional -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Arus Kas Keluar</p>
                <h3 class="text-sm sm:text-base font-bold font-mono text-rose-700 mt-0.5">
                    Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 text-xs shrink-0">
                <i class="fa-solid fa-arrow-up-right"></i>
            </div>
        </div>

        <!-- Perubahan Kas Bersih -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Arus Kas Bersih</p>
                <h3 class="text-sm sm:text-base font-bold font-mono mt-0.5 flex items-center gap-1 {{ $isNetPos ? 'text-emerald-600' : ($isNetNeg ? 'text-rose-600' : 'text-slate-800') }}">
                    @if($isNetPos)
                        <i class="fa-solid fa-arrow-trend-up text-[11px] text-emerald-600"></i> +Rp {{ number_format($netCashAbs, 0, ',', '.') }}
                    @elseif($isNetNeg)
                        <i class="fa-solid fa-arrow-trend-down text-[11px] text-rose-600"></i> (Rp {{ number_format($netCashAbs, 0, ',', '.') }})
                    @else
                        Rp 0
                    @endif
                </h3>
            </div>
            <div class="w-8 h-8 rounded-lg {{ $isNetPos ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : ($isNetNeg ? 'bg-rose-50 border-rose-100 text-rose-600' : 'bg-slate-100 border-slate-200 text-slate-600') }} border flex items-center justify-center text-xs shrink-0">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
        </div>

        <!-- Posisi Kas Akhir -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kas & Bank Akhir</p>
                <h3 class="text-sm sm:text-base font-bold font-mono text-blue-800 mt-0.5">
                    Rp {{ number_format($report['posisi_kas_akhir'], 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs shrink-0">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>
    </div>

    <!-- Cash Flow Table Card (Kompak, Rapi, Pita Warna Elegan) -->
    <div class="card-table bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table id="cashFlowTable" class="w-full text-left text-xs text-slate-800">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-2.5 px-4">DESKRIPSI ARUS KAS</th>
                        <th class="py-2.5 px-4 text-right w-56">JUMLAH (IDR)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    
                    <!-- 1. AKTIVITAS OPERASIONAL (Pita Ramping) -->
                    <tr class="section-header-row">
                        <td colspan="2" class="p-0 border-t border-slate-200">
                            <div class="bg-gradient-to-r from-blue-700 to-indigo-700 px-4 py-1.5 text-white flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-gears text-[11px] opacity-90"></i>
                                    <span class="text-xs font-bold tracking-wide uppercase">
                                        AKTIVITAS OPERASIONAL (OPERATING ACTIVITIES)
                                    </span>
                                </div>
                                <span class="text-[10px] font-mono font-medium bg-black/20 text-white px-2 py-0.5 rounded">
                                    Arus Kas Usaha
                                </span>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="py-1.5 px-4 pl-8 text-slate-800 font-medium text-xs">Penerimaan kas dari pelanggan</td>
                        <td class="py-1.5 px-4 text-right font-mono font-semibold text-xs text-slate-900">
                            {{ $report['aktivitas_operasional']['penerimaan_pelanggan'] > 0 ? 'Rp ' . number_format($report['aktivitas_operasional']['penerimaan_pelanggan'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="py-1.5 px-4 pl-8 text-slate-800 font-medium text-xs">Aset lancar lainnya</td>
                        <td class="py-1.5 px-4 text-right font-mono font-semibold text-xs text-slate-400">-</td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="py-1.5 px-4 pl-8 text-slate-800 font-medium text-xs">Pembayaran kas ke pemasok</td>
                        <td class="py-1.5 px-4 text-right font-mono font-semibold text-xs text-rose-700">
                            @php $pembayaran = $report['aktivitas_operasional']['pembayaran_pemasok']; @endphp
                            {{ $pembayaran > 0 ? '(Rp ' . number_format($pembayaran, 0, ',', '.') . ')' : '-' }}
                        </td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="py-1.5 px-4 pl-8 text-slate-800 font-medium text-xs">Liabilitas jangka pendek lainnya</td>
                        <td class="py-1.5 px-4 text-right font-mono font-semibold text-xs text-slate-400">-</td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="py-1.5 px-4 pl-8 text-slate-800 font-medium text-xs">Pendapatan lainnya</td>
                        <td class="py-1.5 px-4 text-right font-mono font-semibold text-xs text-slate-400">-</td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="py-1.5 px-4 pl-8 text-slate-800 font-medium text-xs">Pengeluaran operasional</td>
                        <td class="py-1.5 px-4 text-right font-mono font-semibold text-xs text-slate-400">-</td>
                    </tr>
                    <!-- Subtotal Operasional -->
                    <tr class="bg-blue-50/70 border-t border-b border-slate-200 font-bold text-xs">
                        <td class="py-1.5 px-4 text-[11px] text-slate-700 uppercase">
                            Arus Kas Bersih dari Aktivitas Operasional
                        </td>
                        <td class="py-1.5 px-4 text-right font-mono font-bold text-xs text-slate-900">
                            @php $isOpNeg = $report['aktivitas_operasional']['total'] < 0; @endphp
                            <span class="{{ $isOpNeg ? 'text-rose-700' : 'text-slate-900' }}">
                                {{ $isOpNeg ? '(Rp ' . number_format(abs($report['aktivitas_operasional']['total']), 0, ',', '.') . ')' : 'Rp ' . number_format($report['aktivitas_operasional']['total'], 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>

                    <!-- 2. AKTIVITAS INVESTASI (Pita Ramping) -->
                    <tr class="section-header-row">
                        <td colspan="2" class="p-0 border-t border-slate-200">
                            <div class="bg-gradient-to-r from-indigo-700 to-blue-800 px-4 py-1.5 text-white flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-building-columns text-[11px] opacity-90"></i>
                                    <span class="text-xs font-bold tracking-wide uppercase">
                                        AKTIVITAS INVESTASI (INVESTING ACTIVITIES)
                                    </span>
                                </div>
                                <span class="text-[10px] font-mono font-medium bg-black/20 text-white px-2 py-0.5 rounded">
                                    Aset Tetap & Investasi
                                </span>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="py-1.5 px-4 pl-8 text-slate-800 font-medium text-xs">Perolehan / penjualan aset tetap</td>
                        <td class="py-1.5 px-4 text-right font-mono font-semibold text-xs text-slate-400">-</td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="py-1.5 px-4 pl-8 text-slate-800 font-medium text-xs">Aktivitas investasi lainnya</td>
                        <td class="py-1.5 px-4 text-right font-mono font-semibold text-xs text-slate-400">-</td>
                    </tr>
                    <!-- Subtotal Investasi -->
                    <tr class="bg-indigo-50/70 border-t border-b border-slate-200 font-bold text-xs">
                        <td class="py-1.5 px-4 text-[11px] text-slate-700 uppercase">
                            Arus Kas Bersih dari Aktivitas Investasi
                        </td>
                        <td class="py-1.5 px-4 text-right font-mono font-bold text-xs text-slate-800">Rp 0</td>
                    </tr>

                    <!-- 3. AKTIVITAS PENDANAAN / KEUANGAN (Pita Ramping) -->
                    <tr class="section-header-row">
                        <td colspan="2" class="p-0 border-t border-slate-200">
                            <div class="bg-gradient-to-r from-purple-700 to-violet-800 px-4 py-1.5 text-white flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-hand-holding-dollar text-[11px] opacity-90"></i>
                                    <span class="text-xs font-bold tracking-wide uppercase">
                                        AKTIVITAS PENDANAAN / KEUANGAN (FINANCING ACTIVITIES)
                                    </span>
                                </div>
                                <span class="text-[10px] font-mono font-medium bg-black/20 text-white px-2 py-0.5 rounded">
                                    Modal & Pinjaman
                                </span>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="py-1.5 px-4 pl-8 text-slate-800 font-medium text-xs">Pembayaran / penerimaan pinjaman bank</td>
                        <td class="py-1.5 px-4 text-right font-mono font-semibold text-xs text-slate-400">-</td>
                    </tr>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="py-1.5 px-4 pl-8 text-slate-800 font-medium text-xs">Setoran / penarikan ekuitas & modal</td>
                        <td class="py-1.5 px-4 text-right font-mono font-semibold text-xs text-slate-400">-</td>
                    </tr>
                    <!-- Subtotal Pendanaan -->
                    <tr class="bg-purple-50/70 border-t border-b border-slate-200 font-bold text-xs">
                        <td class="py-1.5 px-4 text-[11px] text-slate-700 uppercase">
                            Arus Kas Bersih dari Aktivitas Pendanaan
                        </td>
                        <td class="py-1.5 px-4 text-right font-mono font-bold text-xs text-slate-800">Rp 0</td>
                    </tr>

                    <!-- PERUBAHAN KAS BERSIH (Badge Berwarna Seimbang Mirip Buku Besar) -->
                    <tr class="bg-slate-50/90 border-t-2 border-slate-300">
                        <td class="py-2 px-4 align-middle">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md {{ $isNetPos ? 'bg-emerald-50 border border-emerald-300 text-emerald-900' : ($isNetNeg ? 'bg-rose-50 border border-rose-300 text-rose-900' : 'bg-slate-100 border border-slate-200 text-slate-800') }}">
                                <span class="text-[10px] uppercase font-bold text-slate-500">Kenaikan / (Penurunan) Kas Bersih:</span>
                                <span class="text-xs font-bold font-mono flex items-center gap-1 {{ $isNetPos ? 'text-emerald-700' : ($isNetNeg ? 'text-rose-700' : 'text-slate-800') }}">
                                    @if($isNetPos)
                                        <i class="fa-solid fa-arrow-trend-up text-[10px] text-emerald-600"></i> +Rp {{ number_format($netCashAbs, 0, ',', '.') }}
                                    @elseif($isNetNeg)
                                        <i class="fa-solid fa-arrow-trend-down text-[10px] text-rose-600"></i> (Rp {{ number_format($netCashAbs, 0, ',', '.') }})
                                    @else
                                        Rp 0
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td class="py-2 px-4 text-right align-middle font-mono font-bold text-xs {{ $isNetPos ? 'text-emerald-700' : ($isNetNeg ? 'text-rose-700' : 'text-slate-900') }}">
                            {{ $isNetNeg ? '(Rp ' . number_format($netCashAbs, 0, ',', '.') . ')' : 'Rp ' . number_format($netCashAbs, 0, ',', '.') }}
                        </td>
                    </tr>

                    <!-- SALDO KAS AWAL -->
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-1.5 px-4 text-slate-700 font-medium text-xs">
                            Saldo Kas & Bank Awal Periode
                        </td>
                        <td class="py-1.5 px-4 text-right font-mono font-semibold text-xs text-slate-800">
                            Rp {{ number_format($report['posisi_kas_awal'], 0, ',', '.') }}
                        </td>
                    </tr>

                    <!-- SALDO KAS AKHIR (Grand Total Dark Ribbon dengan Double Underline) -->
                    <tr class="bg-slate-900 text-white border-t-2 border-blue-500">
                        <td class="py-2.5 px-4 align-middle">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-white">
                                    SALDO KAS & BANK AKHIR PERIODE
                                </span>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-blue-500/30 text-blue-300 border border-blue-400/30">
                                    <i class="fa-solid fa-vault text-[9px]"></i>
                                    Kas Likuid
                                </span>
                            </div>
                        </td>
                        <td class="py-2.5 px-4 text-right align-middle font-mono font-bold text-xs sm:text-sm text-white border-b-2 border-double border-white">
                            @php $isAkhirNeg = $report['posisi_kas_akhir'] < 0; @endphp
                            {{ $isAkhirNeg ? '(Rp ' . number_format(abs($report['posisi_kas_akhir']), 0, ',', '.') . ')' : 'Rp ' . number_format($report['posisi_kas_akhir'], 0, ',', '.') }}
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- SCRIPT EKSPOR EXCEL NATIVE BERSIH UNTUK ARUS KAS -->
<script>
function exportCashFlowToExcel() {
    var companyName = @json($company->name ?? 'Perusahaan');
    var periodStr = @json(\Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y'));
    var todayStr = @json(date('Y-m-d'));

    var excelXml = '<' + 'xml><' + 'x:ExcelWorkbook><' + 'x:ExcelWorksheets><' + 'x:ExcelWorksheet>';
    excelXml += '<' + 'x:Name>Arus Kas<' + '/x:Name>';
    excelXml += '<' + 'x:WorksheetOptions><' + 'x:DisplayGridlines/><' + '/x:WorksheetOptions><' + '/x:ExcelWorksheet><' + '/x:ExcelWorksheets><' + '/x:ExcelWorkbook><' + '/xml>';

    var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    html += '<head><meta charset="utf-8"><!--[if gte mso 9]>' + excelXml + '<![endif]--></head><body>';

    var table = document.getElementById('cashFlowTable');
    if (!table) {
        alert('Tabel tidak ditemukan.');
        return;
    }

    html += '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse; font-family:Arial, sans-serif; font-size:10pt;">';
    html += '<tr><th colspan="2" style="font-size:13pt; font-weight:bold; text-align:left; background-color:#1e3a8a; color:#ffffff; padding:8px;">' + companyName + ' - LAPORAN ARUS KAS (CASH FLOW)</th></tr>';
    html += '<tr><th colspan="2" style="text-align:left; background-color:#f1f5f9; color:#475569; padding:6px;">Periode: ' + periodStr + '</th></tr>';
    html += '<tr><td colspan="2" style="height:10px;"></td></tr>';

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
            var isTotal = row.innerText.indexOf('SALDO KAS & BANK AKHIR') !== -1;
            var isSubtotal = row.innerText.indexOf('Arus Kas Bersih') !== -1 || row.innerText.indexOf('Kenaikan') !== -1;

            if (isSection) {
                var sectionTitle = tds[0].innerText.trim().replace(/\s+/g, ' ');
                html += '<tr style="background-color:#2563eb; color:#ffffff; font-weight:bold;">';
                html += '<td colspan="2" style="padding:6px; font-size:10.5pt; color:#ffffff; background-color:#2563eb;">' + sectionTitle + '</td>';
                html += '</tr>';
            } else if (isTotal) {
                var descVal = tds[0] ? tds[0].innerText.trim().replace(/\s+/g, ' ') : '';
                var amountVal = tds[1] ? tds[1].innerText.trim().replace(/\s+/g, ' ') : '';
                html += '<tr style="background-color:#0f172a; color:#ffffff; font-weight:bold; font-size:10.5pt;">';
                html += '<td style="padding:8px; color:#ffffff; background-color:#0f172a;">' + descVal + '</td>';
                html += '<td style="text-align:right; padding:8px; color:#ffffff; background-color:#0f172a;">' + amountVal + '</td>';
                html += '</tr>';
            } else if (isSubtotal) {
                var subDesc = tds[0] ? tds[0].innerText.trim().replace(/\s+/g, ' ') : '';
                var subAmt = tds[1] ? tds[1].innerText.trim() : '';
                html += '<tr style="background-color:#e2e8f0; font-weight:bold;">';
                html += '<td style="padding:5px; color:#1e293b;">' + subDesc + '</td>';
                html += '<td style="text-align:right; padding:5px; color:#1e293b;">' + subAmt + '</td>';
                html += '</tr>';
            } else {
                var desc = tds[0] ? tds[0].innerText.trim().replace(/\s+/g, ' ') : '';
                var amt = tds[1] ? tds[1].innerText.trim() : '';

                html += '<tr>';
                html += '<td style="padding:5px; border:1px solid #e2e8f0;">' + desc + '</td>';
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
    a.download = 'Arus_Kas_' + todayStr + '.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
@endsection
