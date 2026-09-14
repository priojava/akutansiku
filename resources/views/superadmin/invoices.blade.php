@extends('layouts.app')

@section('title', 'Master Invoices Tagihan SaaS')

@section('content')
<div class="space-y-6 animate-in fade-in duration-300">

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Semua Invoice & Tagihan Langganan</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar tagihan pembayaran SaaS dari semua tenant perusahaan</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('superadmin.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                &larr; Dashboard Super Admin
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('superadmin.invoices') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no invoice, nama tenant..."
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none">
            </div>
            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none">
                    <option value="">Semua Status Tagihan</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak / Batal</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('superadmin.invoices') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs transition" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">No. Invoice</th>
                        <th class="px-5 py-3.5">Perusahaan (Tenant)</th>
                        <th class="px-5 py-3.5">Paket & Durasi</th>
                        <th class="px-5 py-3.5">Metode Bayar</th>
                        <th class="px-5 py-3.5 text-right">Nominal</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-bold text-purple-700 font-mono">
                                <a href="{{ route('subscription.invoice', $inv->id) }}" target="_blank" class="hover:underline flex items-center space-x-1">
                                    <span>{{ $inv->invoice_number }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                                <span class="text-[10px] text-slate-400 font-sans block font-normal">{{ $inv->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $inv->company?->name }}
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <span class="capitalize font-bold text-slate-800">{{ $inv->plan_name }}</span>
                                <span class="text-[10px] text-slate-400 block">({{ $inv->duration_months }} Bulan)</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $inv->payment_method ?: 'Transfer' }}
                            </td>
                            <td class="px-5 py-4 text-right font-bold text-slate-900 font-mono">
                                Rp {{ number_format($inv->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($inv->status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-circle-check mr-1 text-emerald-500"></i> Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fa-solid fa-clock mr-1 text-amber-500"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($inv->status === 'pending')
                                    <form action="{{ route('superadmin.invoices.approve', $inv->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                            Setujui & Aktifkan
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('subscription.invoice', $inv->id) }}" target="_blank" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-200">
                                        Lihat
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">Belum ada invoice tagihan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
