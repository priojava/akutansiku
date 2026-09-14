@extends('layouts.app')

@section('title', 'Laporan Buku Besar')

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
            font-size: 9pt !important;
        }
        aside, nav, header, .no-print, button, form {
            display: none !important;
        }
        .main-content, main, .space-y-6 {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .print-header {
            display: block !important;
            margin-bottom: 16px !important;
            border-bottom: 2px solid #0f172a !important;
            padding-bottom: 8px !important;
        }
        .card-ledger {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            margin-bottom: 20px !important;
            page-break-inside: avoid;
        }
        .col-action {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="{
    modalVoucherOpen: false,
    activeEntry: null,
    openVoucher(b64) {
        try {
            const jsonStr = decodeURIComponent(escape(window.atob(b64)));
            this.activeEntry = JSON.parse(jsonStr);
            this.modalVoucherOpen = true;
        } catch(e) {
            console.error('Gagal membuka bukti jurnal:', e);
        }
    },
    getTotalDebit() {
        if (!this.activeEntry || !this.activeEntry.journal_items) return 0;
        return this.activeEntry.journal_items.reduce((sum, i) => sum + (Number(i.debit) || 0), 0);
    },
    getTotalCredit() {
        if (!this.activeEntry || !this.activeEntry.journal_items) return 0;
        return this.activeEntry.journal_items.reduce((sum, i) => sum + (Number(i.credit) || 0), 0);
    }
}">

    <!-- Print Header (Hanya tampil saat Print/PDF) -->
    <div class="hidden print-header">
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-xl font-bold text-slate-900 uppercase tracking-wide">{{ $company->name ?? 'PERUSAHAAN' }}</h1>
                <h2 class="text-base font-semibold text-blue-800">LAPORAN BUKU BESAR (GENERAL LEDGER)</h2>
                <p class="text-xs text-slate-500">Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</p>
            </div>
            <div class="text-right text-[10px] text-slate-400">
                <p>Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y HH:mm') }}</p>
            </div>
        </div>
    </div>

    <!-- Header Filter & Action (Diabaikan saat Print) -->
    <div class="no-print bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('reports.general_ledger') }}" class="flex flex-wrap items-center gap-2">
            <select name="account_id" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:outline-none">
                <option value="">Semua Akun (Buku Besar Lengkap)</option>
                @foreach($allAccounts as $acc)
                    <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>
                        {{ $acc->code }} - {{ $acc->name }}
                    </option>
                @endforeach
            </select>

            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-700">
                <input type="date" name="start_date" value="{{ $startDate }}" class="bg-transparent focus:outline-none font-medium">
                <span class="mx-2 text-slate-400">&rarr;</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="bg-transparent focus:outline-none font-medium">
            </div>

            <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>
        </form>

        <div class="flex items-center space-x-2">
            <button type="button" onclick="exportLedgerToExcel()" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-regular fa-file-excel text-emerald-600 text-sm"></i>
                <span>Excel</span>
            </button>
            <button type="button" onclick="window.print()" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-regular fa-file-pdf text-rose-600 text-sm"></i>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    <!-- Grouped Ledger Tables -->
    <div id="ledgerContainer" class="space-y-4">
        @forelse($report['accounts'] as $accData)
            <div class="card-ledger bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                
                <!-- HEADER AKUN: Ramping & Pas Seperti Neraca Saldo -->
                <div class="bg-gradient-to-r from-blue-700 to-indigo-700 px-4 py-1.5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-book-bookmark text-[11px] opacity-90"></i>
                        <span class="text-xs font-bold tracking-wide uppercase">
                            {{ $accData['account']['name'] }}
                        </span>
                        <span class="text-[10px] font-mono font-medium bg-black/20 text-white px-2 py-0.5 rounded">
                            {{ $accData['account']['code'] }}
                        </span>
                    </div>

                    <!-- SALDO AWAL (Kanan Header - Ramping) -->
                    <div class="flex items-center gap-1.5 text-right">
                        <span class="text-[10px] font-medium text-blue-100 uppercase tracking-wider">Saldo Awal:</span>
                        <span class="text-xs font-bold font-mono text-white">
                            Rp {{ number_format($accData['initial_balance'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- TABEL MUTASI TRANSAKSI (Kompak) -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-800">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                                <th class="py-2 px-3 w-36">TANGGAL</th>
                                <th class="py-2 px-3 w-28">TRANSAKSI</th>
                                <th class="py-2 px-3">CATATAN / KETERANGAN</th>
                                <th class="py-2 px-3 text-right w-32">DEBIT</th>
                                <th class="py-2 px-3 text-right w-32">KREDIT</th>
                                <th class="py-2 px-3 text-right w-32">SALDO</th>
                                <th class="py-2 px-2 text-center w-10 col-action no-print">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($accData['entries'] as $entry)
                                <tr class="hover:bg-blue-50/30 transition">
                                    <td class="py-1.5 px-3 text-slate-500 font-mono text-[10.5px]">{{ $entry['date'] }}</td>
                                    <td class="py-1.5 px-3">
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-semibold border border-slate-200">
                                            {{ $entry['transaction_type'] }}
                                        </span>
                                    </td>
                                    <td class="py-1.5 px-3 text-slate-800 font-medium text-xs">{{ $entry['notes'] }}</td>
                                    <td class="py-1.5 px-3 text-right font-mono font-medium text-xs text-slate-800">
                                        {{ $entry['debit'] > 0 ? 'Rp ' . number_format($entry['debit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-1.5 px-3 text-right font-mono font-medium text-xs text-slate-800">
                                        {{ $entry['credit'] > 0 ? 'Rp ' . number_format($entry['credit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-1.5 px-3 text-right font-mono font-semibold text-xs">
                                        @php $isNeg = $entry['saldo'] < 0; @endphp
                                        <span class="{{ $isNeg ? 'text-rose-600' : 'text-slate-900' }}">
                                            {{ $isNeg ? '(Rp ' . number_format(abs($entry['saldo']), 0, ',', '.') . ')' : 'Rp ' . number_format($entry['saldo'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="py-1.5 px-2 text-center col-action no-print">
                                        <button type="button" 
                                                @click="openVoucher('{{ base64_encode(json_encode($entry)) }}')" 
                                                class="text-slate-400 hover:text-blue-600 p-1 rounded hover:bg-blue-50 transition cursor-pointer" 
                                                title="Lihat Bukti Jurnal">
                                            <i class="fa-regular fa-eye text-xs pointer-events-none"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-3 px-3 text-center text-slate-400 italic text-xs">
                                        Belum ada mutasi transaksi pada periode ini.
                                    </td>
                                </tr>
                            @endforelse

                            <!-- SUMMARY ROW: PERUBAHAN SALDO & TOTAL KOMPAK -->
                            @php 
                                $diff = $accData['ending_balance'] - $accData['initial_balance']; 
                                $isPos = $diff > 0;
                                $isNeg = $diff < 0;
                                $diffAbs = abs($diff);
                            @endphp
                            <tr class="bg-slate-50/90 border-t border-slate-200">
                                <td colspan="3" class="py-1.5 px-3 align-middle">
                                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md {{ $isPos ? 'bg-emerald-50 border border-emerald-300 text-emerald-900' : ($isNeg ? 'bg-rose-50 border border-rose-300 text-rose-900' : 'bg-slate-100 border border-slate-200 text-slate-800') }}">
                                        <span class="text-[10px] uppercase font-bold text-slate-500">Perubahan Saldo:</span>
                                        <span class="text-xs font-bold font-mono flex items-center gap-1 {{ $isPos ? 'text-emerald-700' : ($isNeg ? 'text-rose-700' : 'text-slate-800') }}">
                                            @if($isPos)
                                                <i class="fa-solid fa-arrow-trend-up text-[10px] text-emerald-600"></i> +Rp {{ number_format($diffAbs, 0, ',', '.') }}
                                            @elseif($isNeg)
                                                <i class="fa-solid fa-arrow-trend-down text-[10px] text-rose-600"></i> (Rp {{ number_format($diffAbs, 0, ',', '.') }})
                                            @else
                                                <i class="fa-solid fa-minus text-[10px] text-slate-400"></i> Rp 0
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="py-1.5 px-3 text-right align-middle">
                                    <div class="font-bold text-xs font-mono text-slate-800">
                                        Rp {{ number_format($accData['total_debit'], 0, ',', '.') }}
                                    </div>
                                    <span class="text-[9px] text-slate-400 font-normal">({{ count($accData['entries']) }} mutasi)</span>
                                </td>
                                <td class="py-1.5 px-3 text-right align-middle">
                                    <div class="font-bold text-xs font-mono text-slate-800">
                                        Rp {{ number_format($accData['total_credit'], 0, ',', '.') }}
                                    </div>
                                    <span class="text-[9px] text-slate-400 font-normal">({{ count($accData['entries']) }} mutasi)</span>
                                </td>
                                <td class="py-1.5 px-3 text-right align-middle">
                                    <div class="text-[9px] uppercase font-bold text-slate-400">Saldo Akhir</div>
                                    @php $isEndingNeg = $accData['ending_balance'] < 0; @endphp
                                    <div class="font-bold text-xs font-mono {{ $isEndingNeg ? 'text-rose-700' : 'text-blue-900' }}">
                                        {{ $isEndingNeg ? '(Rp ' . number_format(abs($accData['ending_balance']), 0, ',', '.') . ')' : 'Rp ' . number_format($accData['ending_balance'], 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="col-action no-print"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400 shadow-sm">
                <i class="fa-regular fa-folder-open text-3xl mb-3 block text-slate-300"></i>
                <p class="font-medium">Tidak ada transaksi buku besar untuk akun atau periode yang dipilih.</p>
            </div>
        @endforelse
    </div>

    <!-- MODAL DETAIL BUKTI JURNAL TRANSAKSI -->
    <div x-show="modalVoucherOpen" x-cloak class="no-print fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden border border-slate-200" @click.away="modalVoucherOpen = false">
            
            <template x-if="activeEntry">
                <div>
                    <!-- Modal Header -->
                    <div class="p-5 border-b border-slate-100 bg-slate-50/80 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">Bukti Jurnal Transaksi (Voucher)</h3>
                                <div class="flex items-center space-x-2 text-[11px] text-slate-500 mt-0.5">
                                    <span class="font-mono font-bold text-blue-600" x-text="activeEntry.entry_number"></span>
                                    <span>&bull;</span>
                                    <span class="font-semibold text-slate-600" x-text="activeEntry.reference_number"></span>
                                </div>
                            </div>
                        </div>
                        <button type="button" @click="modalVoucherOpen = false" class="text-slate-400 hover:text-slate-600 text-sm p-1 rounded-lg hover:bg-slate-100">
                            <i class="fa-solid fa-xmark text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body Info -->
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-400 text-[10px] uppercase font-bold block">Tanggal</span>
                                <span class="font-semibold text-slate-800" x-text="activeEntry.date"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] uppercase font-bold block">Jenis</span>
                                <span class="font-bold text-blue-600" x-text="activeEntry.transaction_type"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] uppercase font-bold block">Diinput Oleh</span>
                                <span class="font-semibold text-slate-800" x-text="activeEntry.creator"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] uppercase font-bold block">Status</span>
                                <span class="text-emerald-600 font-bold text-[11px] flex items-center mt-0.5">
                                    <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Terjurnal Seimbang
                                </span>
                            </div>
                        </div>

                        <div>
                            <span class="text-slate-500 text-xs font-semibold block mb-1">Catatan / Keterangan:</span>
                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 font-medium" x-text="activeEntry.notes"></div>
                        </div>

                        <!-- Double Entry Items Table -->
                        <div>
                            <span class="text-slate-700 text-xs font-bold block mb-2">Ayat Jurnal Berpasangan (Double Entry):</span>
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <table class="w-full text-left text-xs text-slate-700">
                                    <thead class="bg-blue-50/60 border-b border-blue-100 text-blue-950 font-bold text-[10px] uppercase">
                                        <tr>
                                            <th class="px-4 py-2.5">Kode & Nama Akun</th>
                                            <th class="px-4 py-2.5 text-right w-36">Debit</th>
                                            <th class="px-4 py-2.5 text-right w-36">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(item, idx) in (activeEntry.journal_items || [])" :key="idx">
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-4 py-2.5" :class="item.credit > 0 ? 'pl-8' : ''">
                                                    <div class="flex items-start space-x-2">
                                                        <template x-if="item.credit > 0">
                                                            <span class="text-slate-400 text-xs font-mono select-none mt-0.5">&rdsh;</span>
                                                        </template>
                                                        <span class="inline-flex items-center justify-center font-mono font-bold text-[11px] px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200/80 shrink-0" x-text="item.account_code"></span>
                                                        <div class="min-w-0 flex-1">
                                                            <span class="font-bold text-slate-900 text-xs" x-text="item.account_name"></span>
                                                            <template x-if="item.memo && item.memo !== activeEntry.notes">
                                                                <span class="block text-[11px] text-slate-400 font-normal italic mt-0.5" x-text="item.memo"></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-900" x-text="item.debit > 0 ? 'Rp ' + Number(item.debit).toLocaleString('id-ID') : '-'"></td>
                                                <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-900" x-text="item.credit > 0 ? 'Rp ' + Number(item.credit).toLocaleString('id-ID') : '-'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="bg-slate-50 font-bold text-slate-900 border-t border-slate-200 text-xs">
                                        <tr>
                                            <td class="px-4 py-2 text-slate-600">Total Seimbang</td>
                                            <td class="px-4 py-2 text-right text-emerald-700" x-text="'Rp ' + Number(getTotalDebit()).toLocaleString('id-ID')"></td>
                                            <td class="px-4 py-2 text-right text-emerald-700" x-text="'Rp ' + Number(getTotalCredit()).toLocaleString('id-ID')"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                        <button type="button" onclick="window.print()" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition flex items-center space-x-1.5">
                            <i class="fa-solid fa-print text-xs text-slate-500"></i>
                            <span>Cetak Bukti Jurnal</span>
                        </button>
                        <button type="button" @click="modalVoucherOpen = false" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition">
                            Tutup
                        </button>
                    </div>

                </div>
            </template>

        </div>
    </div>

</div>

<!-- SCRIPT EKSPOR EXCEL NATIVE BERSIH -->
<script>
function exportLedgerToExcel() {
    var companyName = @json($company->name ?? 'Perusahaan');
    var periodStr = @json(\Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y'));
    var todayStr = @json(date('Y-m-d'));

    var excelXml = '<' + 'xml><' + 'x:ExcelWorkbook><' + 'x:ExcelWorksheets><' + 'x:ExcelWorksheet>';
    excelXml += '<' + 'x:Name>Buku Besar<' + '/x:Name>';
    excelXml += '<' + 'x:WorksheetOptions><' + 'x:DisplayGridlines/><' + '/x:WorksheetOptions><' + '/x:ExcelWorksheet><' + '/x:ExcelWorksheets><' + '/x:ExcelWorkbook><' + '/xml>';

    var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    html += '<head><meta charset="utf-8"><!--[if gte mso 9]>' + excelXml + '<![endif]--></head><body>';
    
    html += '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse; font-family:Arial, sans-serif; font-size:10pt;">';
    html += '<tr><th colspan="6" style="font-size:14pt; font-weight:bold; text-align:left; background-color:#1e3a8a; color:#ffffff; padding:10px;">' + companyName + ' - LAPORAN BUKU BESAR</th></tr>';
    html += '<tr><th colspan="6" style="text-align:left; background-color:#f1f5f9; color:#475569;">Periode: ' + periodStr + '</th></tr>';
    html += '<tr><td colspan="6" style="height:10px;"></td></tr>';

    var cards = document.querySelectorAll('.card-ledger');
    if (!cards || cards.length === 0) {
        alert('Tidak ada data buku besar untuk diekspor.');
        return;
    }

    cards.forEach(function(card) {
        var titleEl = card.querySelector('h3');
        var codeEl = card.querySelector('.font-mono');
        var initialBalEl = card.querySelectorAll('.font-mono')[1];

        var accName = titleEl ? titleEl.innerText.trim() : '';
        var accCode = codeEl ? codeEl.innerText.trim() : '';
        var initBal = initialBalEl ? initialBalEl.innerText.trim() : 'Rp 0';

        html += '<tr style="background-color:#2563eb; color:#ffffff; font-weight:bold;">';
        html += '<td colspan="4" style="font-size:11pt; padding:8px; color:#ffffff;">' + accName + ' (' + accCode + ')</td>';
        html += '<td colspan="2" style="font-size:10pt; text-align:right; padding:8px; color:#ffffff;">Saldo Awal: ' + initBal + '</td>';
        html += '</tr>';

        html += '<tr style="background-color:#f8fafc; font-weight:bold; text-align:center;">';
        html += '<th style="width:140px; padding:6px;">Tanggal</th>';
        html += '<th style="width:120px; padding:6px;">Transaksi</th>';
        html += '<th style="width:300px; padding:6px;">Catatan / Keterangan</th>';
        html += '<th style="width:140px; text-align:right; padding:6px;">Debit</th>';
        html += '<th style="width:140px; text-align:right; padding:6px;">Kredit</th>';
        html += '<th style="width:150px; text-align:right; padding:6px;">Saldo</th>';
        html += '</tr>';

        var rows = card.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            if (cells.length >= 6) {
                // If it's summary row
                if (cells.length === 5 || cells[0].hasAttribute('colspan')) {
                    var diffText = cells[0].innerText.trim().replace(/\s+/g, ' ');
                    var debitTotal = cells[1].innerText.trim().replace(/\s+/g, ' ');
                    var creditTotal = cells[2].innerText.trim().replace(/\s+/g, ' ');
                    var endingBal = cells[3].innerText.trim().replace(/\s+/g, ' ');

                    html += '<tr style="background-color:#e2e8f0; font-weight:bold;">';
                    html += '<td colspan="3" style="padding:8px; color:#0f172a;">' + diffText + '</td>';
                    html += '<td style="text-align:right; padding:8px;">' + debitTotal + '</td>';
                    html += '<td style="text-align:right; padding:8px;">' + creditTotal + '</td>';
                    html += '<td style="text-align:right; padding:8px; color:#1e3a8a;">' + endingBal + '</td>';
                    html += '</tr>';
                } else {
                    var date = cells[0].innerText.trim();
                    var type = cells[1].innerText.trim();
                    var notes = cells[2].innerText.trim();
                    var debit = cells[3].innerText.trim();
                    var credit = cells[4].innerText.trim();
                    var balance = cells[5].innerText.trim();

                    html += '<tr>';
                    html += '<td style="padding:6px;">' + date + '</td>';
                    html += '<td style="padding:6px;">' + type + '</td>';
                    html += '<td style="padding:6px;">' + notes + '</td>';
                    html += '<td style="text-align:right; padding:6px;">' + debit + '</td>';
                    html += '<td style="text-align:right; padding:6px;">' + credit + '</td>';
                    html += '<td style="text-align:right; font-weight:bold; padding:6px;">' + balance + '</td>';
                    html += '</tr>';
                }
            }
        });

        html += '<tr><td colspan="6" style="height:14px; border:none;"></td></tr>';
    });

    html += '</table></body></html>';

    var blob = new Blob(['\ufeff', html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'Buku_Besar_' + todayStr + '.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
@endsection
