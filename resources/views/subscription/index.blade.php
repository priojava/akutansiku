@extends('layouts.app')

@section('title', 'Langganan & Tagihan')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 animate-in fade-in duration-300" x-data="{ renewModalOpen: false, selectedPlan: 'premium', selectedDuration: 1, paymentMethod: 'Transfer BCA' }">

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

    <!-- TOP HEADER PROFILE & RENEW ACTION -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-8 shadow-sm flex flex-col items-center justify-center text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600"></div>

        <!-- Avatar / Icon -->
        <div class="w-20 h-20 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 mb-3 shadow-inner">
            <i class="fa-regular fa-image text-3xl"></i>
        </div>

        <h2 class="text-xl font-bold text-slate-800">{{ $company->name ?? 'Dapur Gemoy' }}</h2>
        <div class="text-xs text-slate-500 mt-1 flex items-center space-x-2">
            <span>Status :</span>
            <span class="font-bold uppercase px-2.5 py-0.5 rounded-full text-[11px] {{ $company->subscription_plan === 'premium' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                {{ $company->subscription_plan ?? 'premium' }}
            </span>
        </div>

        <!-- Tombol Perpanjang Langganan -->
        <div class="mt-5">
            <button @click="renewModalOpen = true" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-arrows-rotate text-xs"></i>
                <span>Perpanjang Langganan</span>
            </button>
        </div>
    </div>

    <!-- CARD 1: TAGIHAN TERBARU -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tagihan Terbaru</h3>
        </div>

        <div class="p-6 space-y-4 text-xs">
            @if($latestInvoice)
                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">Tanggal</span>
                    <span class="font-bold text-slate-800 text-sm">
                        {{ $latestInvoice->created_at->format('d M Y H:i:s') }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">No Invoice</span>
                    <a href="{{ route('subscription.invoice', $latestInvoice->id) }}" class="font-bold text-blue-600 hover:text-blue-800 transition">
                        {{ $latestInvoice->invoice_number }}
                    </a>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">Keterangan</span>
                    <span class="font-bold text-slate-800">{{ $latestInvoice->description ?: 'Trial fitur premium' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">Total</span>
                    <span class="font-extrabold text-slate-900 text-base">
                        Rp {{ number_format($latestInvoice->amount, 0, ',', '.') }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">Status</span>
                    @if($latestInvoice->status === 'paid')
                        <span class="inline-flex items-center text-emerald-600 font-bold text-xs mt-0.5">
                            <i class="fa-solid fa-circle-check mr-1.5 text-emerald-500"></i> Pembayaran Sukses
                        </span>
                    @elseif($latestInvoice->status === 'pending')
                        <span class="inline-flex items-center text-amber-600 font-bold text-xs mt-0.5">
                            <i class="fa-solid fa-clock mr-1.5 text-amber-500"></i> Menunggu Konfirmasi
                        </span>
                    @else
                        <span class="inline-flex items-center text-rose-600 font-bold text-xs mt-0.5">
                            <i class="fa-solid fa-circle-xmark mr-1.5 text-rose-500"></i> {{ ucfirst($latestInvoice->status) }}
                        </span>
                    @endif
                </div>

                <div class="pt-2">
                    <a href="{{ route('subscription.invoice', $latestInvoice->id) }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold text-xs transition border border-slate-200 shadow-2xs">
                        <i class="fa-solid fa-file-invoice text-slate-500 text-[11px]"></i>
                        <span>Lihat Invoice</span>
                    </a>
                </div>
            @else
                <div class="text-slate-400 py-4 text-center">Belum ada data tagihan.</div>
            @endif
        </div>
    </div>

    <!-- CARD 2: STATUS LANGGANAN -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Status Langganan</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] text-slate-400 uppercase tracking-wider font-bold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Kode</th>
                        <th class="px-6 py-3.5">Produk</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Masa Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-slate-50/50 transition font-medium">
                        <td class="px-6 py-4 font-mono text-slate-600">100{{ $company->id }}</td>
                        <td class="px-6 py-4 capitalize font-bold text-slate-900">{{ $company->subscription_plan ?? 'premium' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $company->subscription_status === 'trial' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                {{ ucfirst($company->subscription_status ?? 'Trial') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-700">
                            @if($company->subscription_expires_at)
                                <span>Sampai {{ $company->remaining_days }} hari lagi</span>
                                <span class="text-[10px] text-slate-400 block">({{ $company->subscription_expires_at->isoFormat('D MMMM Y') }})</span>
                            @else
                                <span>Sampai 13 hari lagi</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- CARD 3: RIWAYAT PEMBAYARAN SEBELUMNYA -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Riwayat Pembayaran Sebelumnya</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[10px] text-slate-400 uppercase tracking-wider font-bold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">No.</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">No. Invoice</th>
                        <th class="px-6 py-3.5 text-right">Total</th>
                        <th class="px-6 py-3.5">Tipe Pembayaran</th>
                        <th class="px-6 py-3.5">Keterangan</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $idx => $inv)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $inv->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 font-bold text-blue-600">
                                <a href="{{ route('subscription.invoice', $inv->id) }}" class="hover:underline">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">
                                Rp {{ number_format($inv->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $inv->payment_method ?: 'Transfer' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $inv->description ?: 'Langganan' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($inv->status === 'paid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Sukses
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ ucfirst($inv->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">Belum ada riwayat pembayaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL PERPANJANG LANGGANAN -->
    <div x-show="renewModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="renewModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="renewModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="renewModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-100">
                
                <form action="{{ route('subscription.renew') }}" method="POST">
                    @csrf
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center">
                                <i class="fa-solid fa-crown text-amber-300 text-sm"></i>
                            </div>
                            <h3 class="font-bold text-base">Perpanjang Langganan SaaS</h3>
                        </div>
                        <button type="button" @click="renewModalOpen = false" class="text-white/80 hover:text-white text-lg">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-5 text-xs text-slate-700">
                        <!-- Pilih Paket -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-2">1. Pilih Paket Layanan</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="p-3.5 rounded-xl border-2 cursor-pointer transition flex flex-col justify-between" :class="selectedPlan === 'standard' ? 'border-blue-600 bg-blue-50/50' : 'border-slate-200 hover:border-slate-300'">
                                    <input type="radio" name="plan_name" value="standard" x-model="selectedPlan" class="sr-only">
                                    <div>
                                        <span class="font-bold text-slate-900 block text-sm">Standard</span>
                                        <span class="text-[11px] text-slate-500">Maks 2 Cabang, Kasir & Laporan Dasar</span>
                                    </div>
                                    <span class="font-extrabold text-blue-600 mt-2 block">Rp 99.000 / bln</span>
                                </label>

                                <label class="p-3.5 rounded-xl border-2 cursor-pointer transition flex flex-col justify-between relative" :class="selectedPlan === 'premium' ? 'border-indigo-600 bg-indigo-50/50' : 'border-slate-200 hover:border-slate-300'">
                                    <span class="absolute -top-2.5 right-3 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-xs">POPULER</span>
                                    <input type="radio" name="plan_name" value="premium" x-model="selectedPlan" class="sr-only">
                                    <div>
                                        <span class="font-bold text-slate-900 block text-sm">Pro Enterprise</span>
                                        <span class="text-[11px] text-slate-500">Unlimited Cabang, AI Jurnal & Pajak DJP</span>
                                    </div>
                                    <span class="font-extrabold text-indigo-600 mt-2 block">Rp 249.000 / bln</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pilih Durasi -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-2">2. Pilih Durasi Berlangganan</label>
                            <div class="grid grid-cols-3 gap-2.5">
                                <label class="p-2.5 rounded-xl border text-center cursor-pointer transition" :class="selectedDuration == 1 ? 'border-blue-600 bg-blue-50 text-blue-700 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                                    <input type="radio" name="duration_months" value="1" x-model="selectedDuration" class="sr-only">
                                    <span>1 Bulan</span>
                                </label>
                                <label class="p-2.5 rounded-xl border text-center cursor-pointer transition relative" :class="selectedDuration == 6 ? 'border-blue-600 bg-blue-50 text-blue-700 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                                    <span class="text-[8px] px-1 bg-emerald-500 text-white rounded font-bold absolute -top-1.5 right-1">Hemat 10%</span>
                                    <input type="radio" name="duration_months" value="6" x-model="selectedDuration" class="sr-only">
                                    <span>6 Bulan</span>
                                </label>
                                <label class="p-2.5 rounded-xl border text-center cursor-pointer transition relative" :class="selectedDuration == 12 ? 'border-blue-600 bg-blue-50 text-blue-700 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                                    <span class="text-[8px] px-1 bg-purple-600 text-white rounded font-bold absolute -top-1.5 right-1">Hemat 20%</span>
                                    <input type="radio" name="duration_months" value="12" x-model="selectedDuration" class="sr-only">
                                    <span>1 Tahun</span>
                                </label>
                            </div>
                        </div>

                        <!-- Metode Pembayaran -->
                        <div>
                            <label class="block font-bold text-slate-800 mb-2">3. Metode Pembayaran</label>
                            <select name="payment_method" x-model="paymentMethod" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none">
                                <option value="Transfer Bank BCA (Otomatis)">Bank Central Asia (BCA) - Virtual Account</option>
                                <option value="Transfer Bank Mandiri">Bank Mandiri</option>
                                <option value="QRIS Instant (Gopay / OVO / ShopeePay)">QRIS Instant (Gopay, OVO, ShopeePay)</option>
                                <option value="Kartu Kredit / Debit Online">Kartu Kredit / Debit Visa & Mastercard</option>
                            </select>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="renewModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                            Konfirmasi & Perpanjang Sekarang
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
