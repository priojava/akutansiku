@extends('layouts.app')

@section('title', 'History Transaksi')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">History Transaksi</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar transaksi dan jurnal umum yang telah tercatat dalam sistem</p>
        </div>
        <a href="{{ route('transactions.create') }}" class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Transaksi Baru</span>
        </a>
    </div>

    @if(isset($currentRole) && $currentRole === 'cashier')
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-2xs">
            <div class="flex items-center space-x-2.5">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <span class="text-xs font-bold block">Mode Kasir Terisolasi</span>
                    <span class="text-[11px] text-emerald-600">Menampilkan riwayat transaksi khusus yang dicatat oleh kasir <strong>{{ $currentUser->name ?? 'Anda' }}</strong> pada perusahaan ini.</span>
                </div>
            </div>
            <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full bg-emerald-200/70 text-emerald-900 border border-emerald-300">
                Khusus Kasir
            </span>
        </div>
    @endif

    <!-- Filter & Search -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('transactions.history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cari Transaksi</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor TRX / Catatan..."
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Jenis Transaksi</label>
                <select name="type" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:outline-none">
                    <option value="">Semua Jenis</option>
                    <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer Kas</option>
                    <option value="journal" {{ request('type') == 'journal' ? 'selected' : '' }}>Jurnal Majemuk</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:outline-none">
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    Filter
                </button>
                <a href="{{ route('transactions.history') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">No. Transaksi</th>
                        <th class="px-5 py-3.5">Tanggal & Jam</th>
                        <th class="px-5 py-3.5">Jenis</th>
                        <th class="px-5 py-3.5">Debit (Simpan ke)</th>
                        <th class="px-5 py-3.5">Kredit (Diterima dari)</th>
                        <th class="px-5 py-3.5">Catatan</th>
                        <th class="px-5 py-3.5 text-right">Nominal</th>
                        <th class="px-5 py-3.5">Diinput Oleh</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-semibold text-blue-600">
                                {{ $trx->transaction_number }}
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $trx->date->format('d M Y') }} <span class="text-[10px] text-slate-400">{{ $trx->time }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if($trx->type == 'income')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold text-[10px] border border-emerald-200">Pemasukan</span>
                                @elseif($trx->type == 'expense')
                                    <span class="px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 font-semibold text-[10px] border border-rose-200">Pengeluaran</span>
                                @elseif($trx->type == 'transfer')
                                    <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-semibold text-[10px] border border-blue-200">Transfer</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 font-semibold text-[10px] border border-purple-200">Jurnal Majemuk</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-800 font-medium">
                                @if($trx->debitAccount)
                                    {{ $trx->debitAccount->name }} <span class="text-[10px] text-slate-400">({{ $trx->debitAccount->code }})</span>
                                @else
                                    <span class="text-purple-600 font-semibold text-xs"><i class="fa-solid fa-list-check mr-1"></i>Multi-Akun (Lihat Detail)</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-800 font-medium">
                                @if($trx->creditAccount)
                                    {{ $trx->creditAccount->name }} <span class="text-[10px] text-slate-400">({{ $trx->creditAccount->code }})</span>
                                @else
                                    <span class="text-purple-600 font-semibold text-xs"><i class="fa-solid fa-list-check mr-1"></i>Multi-Akun (Lihat Detail)</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $trx->notes ?: '-' }}
                            </td>
                            <td class="px-5 py-4 text-right font-bold text-slate-900">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[10px] flex-shrink-0">
                                        {{ strtoupper(substr($trx->creator?->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div class="truncate">
                                        <div class="font-semibold text-slate-800 text-xs truncate">{{ $trx->creator?->name ?? 'Admin / System' }}</div>
                                        <div class="text-[10px] text-slate-400 capitalize">{{ $trx->creator ? $trx->creator->getRoleInCompany($company->id) : 'System' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center text-[10px] text-emerald-600 font-semibold">
                                    <i class="fa-solid fa-check-double mr-1 text-emerald-500"></i> Dijurnal
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-10 text-slate-400">
                                Belum ada transaksi yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
