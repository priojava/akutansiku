@extends('layouts.app')

@section('title', 'Laporan Jurnal Umum')

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
        .col-action {
            display: none !important;
        }
        tr {
            page-break-inside: avoid !important;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-4" x-data="{
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
        if (!this.activeEntry || !this.activeEntry.items) return 0;
        return this.activeEntry.items.reduce((sum, i) => sum + (Number(i.debit) || 0), 0);
    },
    getTotalCredit() {
        if (!this.activeEntry || !this.activeEntry.items) return 0;
        return this.activeEntry.items.reduce((sum, i) => sum + (Number(i.credit) || 0), 0);
    }
}">

    <!-- Print Header (Hanya tampil saat Print/PDF) -->
    <div class="hidden print-header">
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-lg font-bold text-slate-900 uppercase tracking-wide">{{ $company->name ?? 'PERUSAHAAN' }}</h1>
                <h2 class="text-sm font-semibold text-blue-800">LAPORAN JURNAL UMUM (GENERAL JOURNAL)</h2>
                <p class="text-[11px] text-slate-500">Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</p>
            </div>
            <div class="text-right text-[10px] text-slate-400">
                <p>Status: {{ $report['is_balanced'] ? 'SEIMBANG (BALANCED)' : 'TIDAK SEIMBANG' }}</p>
                <p>Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y HH:mm') }}</p>
            </div>
        </div>
    </div>

    <!-- Header Filter & Action (Kompak & Fitur Pencarian) -->
    <div class="no-print bg-white rounded-xl border border-slate-200 p-3 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('reports.journal') }}" class="flex flex-wrap items-center gap-2">
            <!-- Kotak Pencarian Jurnal -->
            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1 text-xs text-slate-700">
                <i class="fa-solid fa-magnifying-glass text-slate-400 mr-2 text-[11px]"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari No. Jurnal / Keterangan / Akun..." class="bg-transparent focus:outline-none font-medium text-xs w-48 sm:w-64">
                @if(!empty($search))
                    <a href="{{ route('reports.journal', ['start_date' => $startDate, 'end_date' => $endDate, 'type' => $type, 'account_id' => $accountId]) }}" class="text-slate-400 hover:text-slate-600 ml-1 text-xs font-bold" title="Hapus Pencarian">&times;</a>
                @endif
            </div>

            <!-- Filter Tanggal -->
            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1 text-xs text-slate-700">
                <i class="fa-regular fa-calendar text-slate-400 mr-2 text-[11px]"></i>
                <input type="date" name="start_date" value="{{ $startDate }}" class="bg-transparent focus:outline-none font-medium text-xs">
                <span class="mx-1.5 text-slate-400">&rarr;</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="bg-transparent focus:outline-none font-medium text-xs">
            </div>

            <!-- Filter Jenis Jurnal -->
            <select name="type" class="px-2.5 py-1 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium text-slate-700 focus:bg-white focus:outline-none">
                <option value="">Semua Jenis</option>
                <option value="income" {{ $type === 'income' ? 'selected' : '' }}>Pemasukan</option>
                <option value="expense" {{ $type === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                <option value="transfer" {{ $type === 'transfer' ? 'selected' : '' }}>Transfer Kas</option>
                <option value="journal" {{ in_array($type, ['journal', 'manual']) ? 'selected' : '' }}>Jurnal Umum</option>
                <option value="closing" {{ $type === 'closing' ? 'selected' : '' }}>Jurnal Penutup</option>
            </select>

            <!-- Filter Akun -->
            <select name="account_id" class="px-2.5 py-1 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium text-slate-700 focus:bg-white focus:outline-none max-w-44 truncate">
                <option value="">Semua Akun</option>
                @foreach($allAccounts as $acc)
                    <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>
                        {{ $acc->code }} - {{ $acc->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-xs transition flex items-center space-x-1 cursor-pointer">
                <i class="fa-solid fa-filter text-[10px]"></i>
                <span>Filter</span>
            </button>
        </form>

        <div class="flex items-center space-x-2">
            <button type="button" onclick="exportJournalToExcel()" class="px-3 py-1 bg-white border border-slate-300 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-slate-700 text-xs font-semibold rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-regular fa-file-excel text-emerald-600 text-xs"></i>
                <span>Excel</span>
            </button>
            <button type="button" onclick="window.print()" class="px-3 py-1 bg-white border border-slate-300 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 text-slate-700 text-xs font-semibold rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-regular fa-file-pdf text-rose-600 text-xs"></i>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards (Kompak & Elegan) -->
    <div class="kpi-cards grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Total Ayat Jurnal -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Transaksi</p>
                <h3 class="text-sm sm:text-base font-bold text-slate-800 mt-0.5">
                    {{ $report['total_entries'] }} <span class="text-[10px] text-slate-400 font-normal">Ayat Jurnal</span>
                </h3>
            </div>
            <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs shrink-0">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>

        <!-- Total Mutasi Debit -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Mutasi Debit</p>
                <h3 class="text-sm sm:text-base font-bold font-mono text-blue-700 mt-0.5">
                    Rp {{ number_format($report['total_debit'], 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs shrink-0">
                <i class="fa-solid fa-arrow-down-long"></i>
            </div>
        </div>

        <!-- Total Mutasi Kredit -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Mutasi Kredit</p>
                <h3 class="text-sm sm:text-base font-bold font-mono text-indigo-700 mt-0.5">
                    Rp {{ number_format($report['total_credit'], 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xs shrink-0">
                <i class="fa-solid fa-arrow-up-long"></i>
            </div>
        </div>

        <!-- Status Keseimbangan -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Keseimbangan</p>
                <h3 class="text-xs sm:text-sm font-bold mt-0.5 flex items-center gap-1 {{ $report['is_balanced'] ? 'text-emerald-600' : 'text-rose-600' }}">
                    <i class="fa-solid {{ $report['is_balanced'] ? 'fa-circle-check text-[11px]' : 'fa-triangle-exclamation text-[11px]' }}"></i>
                    {{ $report['is_balanced'] ? 'SEIMBANG' : 'TIDAK SEIMBANG' }}
                </h3>
            </div>
            <div class="w-8 h-8 rounded-lg {{ $report['is_balanced'] ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 'bg-rose-50 border-rose-100 text-rose-600' }} border flex items-center justify-center text-xs shrink-0">
                <i class="fa-solid {{ $report['is_balanced'] ? 'fa-scale-balanced' : 'fa-triangle-exclamation' }}"></i>
            </div>
        </div>
    </div>

    <!-- Journal Report Table Card -->
    <div class="card-table bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table id="journalTable" class="w-full text-left text-xs text-slate-800">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-2.5 px-3 w-36">TANGGAL & WAKTU</th>
                        <th class="py-2.5 px-3 w-40">NO. JURNAL / REF</th>
                        <th class="py-2.5 px-3">KETERANGAN & AKUN BERPASANGAN</th>
                        <th class="py-2.5 px-3 text-right w-36">DEBIT</th>
                        <th class="py-2.5 px-3 text-right w-36">KREDIT</th>
                        <th class="py-2.5 px-2 text-center w-12 col-action no-print">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($report['entries'] as $entry)
                        @php
                            $tLower = strtolower($entry['type']);
                            $badgeClass = match($tLower) {
                                'pemasukan', 'income' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                                'pengeluaran', 'expense' => 'bg-rose-50 text-rose-800 border-rose-300',
                                'transfer kas', 'transfer' => 'bg-blue-50 text-blue-800 border-blue-300',
                                'jurnal penutup', 'closing' => 'bg-purple-50 text-purple-800 border-purple-300',
                                default => 'bg-indigo-50 text-indigo-800 border-indigo-300',
                            };
                            $badgeIcon = match($tLower) {
                                'pemasukan', 'income' => 'fa-solid fa-arrow-down-left text-emerald-600',
                                'pengeluaran', 'expense' => 'fa-solid fa-arrow-up-right text-rose-600',
                                'transfer kas', 'transfer' => 'fa-solid fa-arrow-right-arrow-left text-blue-600',
                                'jurnal penutup', 'closing' => 'fa-solid fa-lock text-purple-600',
                                default => 'fa-solid fa-book-bookmark text-indigo-600',
                            };
                        @endphp

                        <!-- JOURNAL HEADER ROW: Berwarna, Ramping, Kontras & Menarik -->
                        <tr class="bg-gradient-to-r from-slate-100/90 via-blue-50/40 to-slate-100/90 border-t-2 border-slate-300 transition">
                            <!-- Tanggal & Waktu -->
                            <td class="py-2 px-3 align-top">
                                <div class="font-bold text-slate-800 text-xs">{{ $entry['date'] }}</div>
                                <div class="text-[10px] font-mono text-slate-500 font-medium mt-0.5 inline-flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-[9px] text-slate-400"></i>
                                    {{ $entry['time'] }}
                                </div>
                            </td>

                            <!-- No. Jurnal & Ref -->
                            <td class="py-2 px-3 align-top">
                                <span class="font-mono font-bold text-xs text-blue-700 hover:text-blue-800 bg-blue-50/90 px-2 py-0.5 rounded border border-blue-200/80 inline-block mb-0.5">
                                    {{ $entry['entry_number'] }}
                                </span>
                                <div class="text-[10px] font-mono text-slate-500 font-medium">Ref: {{ $entry['reference_number'] }}</div>
                            </td>

                            <!-- Badge Jenis & Deskripsi Transaksi -->
                            <td class="py-2 px-3 align-top">
                                <div class="flex items-center flex-wrap gap-1.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold border shadow-2xs {{ $badgeClass }}">
                                        <i class="{{ $badgeIcon }} text-[9px]"></i>
                                        {{ $entry['type'] }}
                                    </span>
                                    <span class="font-bold text-slate-900 text-xs">{{ $entry['description'] }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-1.5 flex-wrap">
                                    <span><i class="fa-regular fa-user text-[9px] mr-1 text-slate-400"></i>Diinput: <strong class="text-slate-600 font-semibold">{{ $entry['creator'] }}</strong></span>
                                    @if($entry['contact_name'])
                                        <span>&bull;</span>
                                        <span><i class="fa-regular fa-id-badge text-[9px] mr-1 text-slate-400"></i>Kontak: <strong class="text-slate-700 font-semibold">{{ $entry['contact_name'] }}</strong></span>
                                    @endif
                                </div>
                            </td>

                            <!-- Total Debit & Kredit Transaksi -->
                            <td class="py-2 px-3 text-right align-top font-mono font-bold text-xs text-slate-900">
                                Rp {{ number_format($entry['total_debit'], 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-right align-top font-mono font-bold text-xs text-slate-900">
                                Rp {{ number_format($entry['total_credit'], 0, ',', '.') }}
                            </td>

                            <!-- Tombol Aksi Lihat Bukti -->
                            <td class="py-2 px-2 text-center align-top col-action no-print">
                                <button type="button" 
                                        @click="openVoucher('{{ base64_encode(json_encode($entry)) }}')" 
                                        class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition inline-flex items-center justify-center cursor-pointer shadow-2xs" 
                                        title="Lihat Bukti Voucher Jurnal">
                                    <i class="fa-regular fa-eye text-xs pointer-events-none"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Sub Item Rows (Double Entry Lines) -->
                        @foreach($entry['items'] as $item)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td></td>
                                <td></td>
                                <td class="py-1.5 px-3 {{ $item['credit'] > 0 ? 'pl-8' : 'pl-4' }}">
                                    <div class="flex items-start space-x-2">
                                        @if($item['credit'] > 0)
                                            <span class="text-slate-400 text-xs font-mono select-none mt-0.5">&rdsh;</span>
                                        @endif
                                        <span class="inline-flex items-center justify-center font-mono font-semibold text-[10.5px] px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 shrink-0">
                                            {{ $item['account_code'] }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <span class="font-medium text-slate-800 text-xs {{ $item['credit'] > 0 ? 'text-slate-700' : 'text-slate-900 font-semibold' }}">
                                                {{ $item['account_name'] }}
                                            </span>
                                            @if($item['memo'] && $item['memo'] !== $entry['description'])
                                                <span class="block text-[10px] text-slate-400 font-normal italic mt-0.5">
                                                    {{ $item['memo'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-1.5 px-3 text-right font-mono {{ $item['debit'] > 0 ? 'text-slate-900 font-semibold text-xs' : 'text-slate-300' }}">
                                    {{ $item['debit'] > 0 ? 'Rp ' . number_format($item['debit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-1.5 px-3 text-right font-mono {{ $item['credit'] > 0 ? 'text-slate-900 font-semibold text-xs' : 'text-slate-300' }}">
                                    {{ $item['credit'] > 0 ? 'Rp ' . number_format($item['credit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="col-action no-print"></td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 text-xs">
                                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-base">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </div>
                                <p class="font-medium">Tidak ada data ayat jurnal yang sesuai dengan pencarian atau filter.</p>
                            </td>
                        </tr>
                    @endforelse

                    <!-- Grand Total Footer (Sleek Dark Slate Ribbon) -->
                    @if(count($report['entries']) > 0)
                        <tr class="bg-slate-900 text-white border-t-2 border-blue-500">
                            <td colspan="3" class="py-2.5 px-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-white">
                                        TOTAL JURNAL UMUM
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-blue-500/30 text-blue-300 border border-blue-400/30">
                                        {{ $report['total_entries'] }} Transaksi Terjurnal
                                    </span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-right align-middle font-mono font-bold text-xs sm:text-sm text-white border-b-2 border-double border-white">
                                Rp {{ number_format($report['total_debit'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right align-middle font-mono font-bold text-xs sm:text-sm text-white border-b-2 border-double border-white">
                                Rp {{ number_format($report['total_credit'], 0, ',', '.') }}
                            </td>
                            <td class="col-action no-print"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DETAIL BUKTI JURNAL (VOUCHER) -->
    <div x-show="modalVoucherOpen" x-cloak class="no-print fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden border border-slate-200" @click.away="modalVoucherOpen = false">
            
            <template x-if="activeEntry">
                <div>
                    <!-- Modal Header -->
                    <div class="p-4 border-b border-slate-100 bg-slate-50/80 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
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
                        <button type="button" @click="modalVoucherOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <!-- Modal Body Info -->
                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-400 text-[10px] uppercase font-bold block">Tanggal</span>
                                <span class="font-semibold text-slate-800" x-text="activeEntry.date + ' ' + activeEntry.time"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] uppercase font-bold block">Jenis</span>
                                <span class="font-bold text-blue-600" x-text="activeEntry.type"></span>
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
                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 font-medium" x-text="activeEntry.description"></div>
                        </div>

                        <!-- Double Entry Items Table -->
                        <div>
                            <span class="text-slate-700 text-xs font-bold block mb-1.5">Ayat Jurnal Berpasangan (Double Entry):</span>
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <table class="w-full text-left text-xs text-slate-700">
                                    <thead class="bg-blue-50/60 border-b border-blue-100 text-blue-950 font-bold text-[10px] uppercase">
                                        <tr>
                                            <th class="px-3 py-2">Kode & Nama Akun</th>
                                            <th class="px-3 py-2 text-right w-32">Debit</th>
                                            <th class="px-3 py-2 text-right w-32">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(item, idx) in (activeEntry.items || [])" :key="idx">
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-3 py-2" :class="item.credit > 0 ? 'pl-7' : ''">
                                                    <div class="flex items-start space-x-2">
                                                        <template x-if="item.credit > 0">
                                                            <span class="text-slate-400 text-xs font-mono select-none mt-0.5">&rdsh;</span>
                                                        </template>
                                                        <span class="inline-flex items-center justify-center font-mono font-bold text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 shrink-0" x-text="item.account_code"></span>
                                                        <div class="min-w-0 flex-1">
                                                            <span class="font-medium text-slate-900 text-xs" x-text="item.account_name"></span>
                                                            <template x-if="item.memo && item.memo !== activeEntry.description">
                                                                <span class="block text-[10px] text-slate-400 font-normal italic mt-0.5" x-text="item.memo"></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-2 text-right font-mono font-semibold text-slate-900 text-xs" x-text="item.debit > 0 ? 'Rp ' + Number(item.debit).toLocaleString('id-ID') : '-'"></td>
                                                <td class="px-3 py-2 text-right font-mono font-semibold text-slate-900 text-xs" x-text="item.credit > 0 ? 'Rp ' + Number(item.credit).toLocaleString('id-ID') : '-'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="bg-slate-50 font-bold text-slate-900 border-t border-slate-200 text-xs">
                                        <tr>
                                            <td class="px-3 py-2 text-slate-600">Total Seimbang</td>
                                            <td class="px-3 py-2 text-right text-emerald-700 font-mono" x-text="'Rp ' + Number(getTotalDebit()).toLocaleString('id-ID')"></td>
                                            <td class="px-3 py-2 text-right text-emerald-700 font-mono" x-text="'Rp ' + Number(getTotalCredit()).toLocaleString('id-ID')"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="p-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                        <button type="button" onclick="window.print()" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition flex items-center space-x-1.5 cursor-pointer">
                            <i class="fa-solid fa-print text-xs text-slate-500"></i>
                            <span>Cetak Voucher</span>
                        </button>
                        <button type="button" @click="modalVoucherOpen = false" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition cursor-pointer">
                            Tutup
                        </button>
                    </div>

                </div>
            </template>

        </div>
    </div>

</div>

<!-- SCRIPT EKSPOR EXCEL NATIVE BERSIH UNTUK JURNAL UMUM -->
<script>
function exportJournalToExcel() {
    var companyName = @json($company->name ?? 'Perusahaan');
    var periodStr = @json(\Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y'));
    var todayStr = @json(date('Y-m-d'));

    var excelXml = '<' + 'xml><' + 'x:ExcelWorkbook><' + 'x:ExcelWorksheets><' + 'x:ExcelWorksheet>';
    excelXml += '<' + 'x:Name>Jurnal Umum<' + '/x:Name>';
    excelXml += '<' + 'x:WorksheetOptions><' + 'x:DisplayGridlines/><' + '/x:WorksheetOptions><' + '/x:ExcelWorksheet><' + '/x:ExcelWorksheets><' + '/x:ExcelWorkbook><' + '/xml>';

    var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    html += '<head><meta charset="utf-8"><!--[if gte mso 9]>' + excelXml + '<![endif]--></head><body>';

    var table = document.getElementById('journalTable');
    if (!table) {
        alert('Tabel tidak ditemukan.');
        return;
    }

    html += '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse; font-family:Arial, sans-serif; font-size:10pt;">';
    html += '<tr><th colspan="5" style="font-size:13pt; font-weight:bold; text-align:left; background-color:#1e3a8a; color:#ffffff; padding:8px;">' + companyName + ' - LAPORAN JURNAL UMUM</th></tr>';
    html += '<tr><th colspan="5" style="text-align:left; background-color:#f1f5f9; color:#475569; padding:6px;">Periode: ' + periodStr + '</th></tr>';
    html += '<tr><td colspan="5" style="height:10px;"></td></tr>';

    var rows = table.querySelectorAll('tr');
    rows.forEach(function(row) {
        var ths = row.querySelectorAll('th');
        var tds = row.querySelectorAll('td');

        if (ths.length > 0) {
            html += '<tr style="background-color:#f8fafc; font-weight:bold; text-align:center;">';
            for (var i = 0; i < Math.min(ths.length, 5); i++) {
                html += '<th style="padding:6px; border:1px solid #cbd5e1;">' + ths[i].innerText.trim() + '</th>';
            }
            html += '</tr>';
        } else if (tds.length > 0) {
            var isTotal = row.innerText.indexOf('TOTAL JURNAL UMUM') !== -1;
            if (isTotal) {
                var debitVal = tds[1] ? tds[1].innerText.trim() : '';
                var creditVal = tds[2] ? tds[2].innerText.trim() : '';
                html += '<tr style="background-color:#0f172a; color:#ffffff; font-weight:bold; font-size:10.5pt;">';
                html += '<td colspan="3" style="padding:8px; color:#ffffff; background-color:#0f172a;">TOTAL JURNAL UMUM</td>';
                html += '<td style="text-align:right; padding:8px; color:#ffffff; background-color:#0f172a;">' + debitVal + '</td>';
                html += '<td style="text-align:right; padding:8px; color:#ffffff; background-color:#0f172a;">' + creditVal + '</td>';
                html += '</tr>';
            } else if (tds.length >= 5) {
                var date = tds[0].innerText.trim().replace(/\s+/g, ' ');
                var noJrn = tds[1].innerText.trim().replace(/\s+/g, ' ');
                var desc = tds[2].innerText.trim().replace(/\s+/g, ' ');
                var deb = tds[3].innerText.trim();
                var cre = tds[4].innerText.trim();

                html += '<tr style="' + (date ? 'background-color:#f1f5f9; font-weight:bold;' : '') + '">';
                html += '<td style="padding:5px; border:1px solid #e2e8f0;">' + date + '</td>';
                html += '<td style="padding:5px; border:1px solid #e2e8f0;">' + noJrn + '</td>';
                html += '<td style="padding:5px; border:1px solid #e2e8f0;">' + desc + '</td>';
                html += '<td style="text-align:right; padding:5px; border:1px solid #e2e8f0;">' + deb + '</td>';
                html += '<td style="text-align:right; padding:5px; border:1px solid #e2e8f0;">' + cre + '</td>';
                html += '</tr>';
            }
        }
    });

    html += '</table></body></html>';

    var blob = new Blob(['\ufeff', html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'Jurnal_Umum_' + todayStr + '.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
@endsection
