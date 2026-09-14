<div class="space-y-8 animate-in fade-in duration-300">

    <!-- 1. HERO CASHIER SHIFT BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-teal-950 to-slate-900 text-white p-6 sm:p-8 shadow-xl border border-emerald-800/40">
        <!-- Glow Orbs in Background -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Left: Greeting & Cashier Info -->
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 backdrop-blur-md">
                        <i class="fa-solid fa-cart-shopping text-emerald-300 mr-1.5 text-[11px]"></i>
                        Terminal Kasir & Shift Operasional
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-400/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-1.5 animate-ping"></span> Shift Aktif
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                    <span>Halo, {{ $currentUser->name ?? 'Kasir' }}</span>
                    <span class="text-emerald-400 font-normal text-lg sm:text-xl">({{ $company->name ?? 'Dapur Gemoy' }})</span>
                </h1>
                <p class="text-sm text-emerald-100/80 max-w-2xl leading-relaxed">
                    Panel pencatatan kas, penjualan langsung, dan kontrol uang laci harian shift Anda. Transaksi yang Anda catat langsung tersinkronisasi ke laporan keuangan pusat.
                </p>
            </div>

            <!-- Right: Fast Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('transactions.create') }}" class="inline-flex items-center space-x-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white text-xs sm:text-sm font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] border border-emerald-400/30">
                    <i class="fa-solid fa-plus-circle text-sm"></i>
                    <span>+ Catat Transaksi Baru</span>
                </a>
                <a href="{{ route('transactions.history') }}" class="inline-flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold px-4 py-2.5 rounded-xl backdrop-blur-md border border-white/15 transition duration-200">
                    <i class="fa-solid fa-clock-rotate-left text-xs text-emerald-300"></i>
                    <span>Riwayat Transaksi Saya</span>
                </a>
            </div>
        </div>

        <!-- Shift Info Bar inside Banner -->
        <div class="mt-6 pt-5 border-t border-white/10 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-3 text-xs text-emerald-200/90">
                <div class="flex items-center space-x-1.5">
                    <i class="fa-regular fa-calendar-check text-emerald-400"></i>
                    <span>Tanggal Hari Ini:</span>
                    <strong class="text-white">{{ \Carbon\Carbon::today()->isoFormat('dddd, D MMMM Y') }}</strong>
                </div>
                <span>&bull;</span>
                <div class="flex items-center space-x-1.5">
                    <i class="fa-solid fa-user text-emerald-400"></i>
                    <span>Kasir ID:</span>
                    <strong class="text-white">#{{ $currentUser->id }}</strong>
                </div>
            </div>

            <div class="text-xs text-emerald-300 font-medium">
                <i class="fa-solid fa-shield-halved mr-1"></i> Data riwayat transaksi Anda terisolasi aman
            </div>
        </div>
    </div>

    <!-- 2. 4 CASHIER METRIC CARDS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- CARD 1: UANG DI LACI (DRAWER CASH) -->
        <div class="group bg-white rounded-2xl border border-emerald-200/80 p-5 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600"></div>
            <div>
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shadow-2xs group-hover:scale-110 transition duration-200">
                            <i class="fa-solid fa-cash-register text-base"></i>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Posisi Shift</span>
                            <h3 class="font-bold text-slate-800 text-sm">Kas di Laci (Net)</h3>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                        Shift
                    </span>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight font-mono">
                        {{ $cashierData['drawer_cash_fmt'] }}
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center">
                        <i class="fa-solid fa-circle-info text-emerald-600 mr-1.5"></i> Saldo fisik uang masuk - uang keluar shift ini
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <span class="text-emerald-700">Pemasukan - Pengeluaran</span>
                <i class="fa-solid fa-calculator text-emerald-500"></i>
            </div>
        </div>

        <!-- CARD 2: TOTAL PEMASUKAN HARI INI -->
        <div class="group bg-white rounded-2xl border border-blue-200/80 p-5 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-600"></div>
            <div>
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shadow-2xs group-hover:scale-110 transition duration-200">
                            <i class="fa-solid fa-arrow-down-long text-base"></i>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Uang Masuk</span>
                            <h3 class="font-bold text-slate-800 text-sm">Pemasukan Hari Ini</h3>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                        Inflow
                    </span>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight font-mono text-emerald-600">
                        + {{ $cashierData['today_income_fmt'] }}
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center">
                        <i class="fa-solid fa-receipt text-blue-600 mr-1.5"></i> Total penjualan & kas diterima Anda hari ini
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <a href="{{ route('transactions.create') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
                    <span>+ Catat Pemasukan</span>
                    <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- CARD 3: TOTAL PENGELUARAN HARI INI -->
        <div class="group bg-white rounded-2xl border border-rose-200/80 p-5 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 via-pink-500 to-rose-600"></div>
            <div>
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shadow-2xs group-hover:scale-110 transition duration-200">
                            <i class="fa-solid fa-arrow-up-long text-base"></i>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Uang Keluar</span>
                            <h3 class="font-bold text-slate-800 text-sm">Pengeluaran Hari Ini</h3>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-100">
                        Outflow
                    </span>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight font-mono text-rose-600">
                        - {{ $cashierData['today_expense_fmt'] }}
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center">
                        <i class="fa-solid fa-wallet text-rose-600 mr-1.5"></i> Petty cash/biaya operasional dikeluarkan
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <a href="{{ route('transactions.create') }}" class="text-rose-600 hover:text-rose-800 flex items-center">
                    <span>- Catat Kas Keluar</span>
                    <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- CARD 4: JUMLAH TRANSAKSI & BULANAN -->
        <div class="group bg-white rounded-2xl border border-purple-200/80 p-5 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-600"></div>
            <div>
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center shadow-2xs group-hover:scale-110 transition duration-200">
                            <i class="fa-solid fa-chart-line text-base"></i>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Aktivitas</span>
                            <h3 class="font-bold text-slate-800 text-sm">Struk / Transaksi</h3>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-100">
                        Total
                    </span>
                </div>

                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight font-mono">
                        {{ $cashierData['today_count'] }} <span class="text-sm font-semibold text-slate-500">Trx Hari Ini</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center">
                        <i class="fa-solid fa-calendar-days text-purple-600 mr-1.5"></i> Bulan ini: {{ $cashierData['month_count'] }} transaksi ({{ $cashierData['month_income_fmt'] }})
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <span class="text-slate-500">Performa Kasir</span>
                <span class="text-purple-600 font-bold">Aktif</span>
            </div>
        </div>

    </div>

    <!-- 3. ANALYTICS CHART & QUICK SHORTCUTS GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: 7 Days Trend Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pergerakan 7 Hari</span>
                        <h3 class="font-bold text-slate-800 text-base">Tren Transaksi Kasir Anda</h3>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i class="fa-solid fa-chart-column mr-1"></i> Data Shift Pribadi
                    </span>
                </div>

                <div class="h-64 relative">
                    <canvas id="cashierTrendChart"></canvas>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <div class="flex items-center space-x-4">
                    <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-1.5"></span> Pemasukan</span>
                    <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-rose-500 mr-1.5"></span> Pengeluaran</span>
                </div>
                <span>Data diupdate otomatis</span>
            </div>
        </div>

        <!-- Right: Fast POS Operational Shortcuts -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Pintasan Kasir</h3>
                        <p class="text-[11px] text-slate-400">Aksi cepat operasional shift</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('transactions.create') }}" class="flex items-center justify-between p-3 rounded-xl bg-emerald-50/70 hover:bg-emerald-100/70 border border-emerald-200 text-emerald-900 transition group">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shadow-xs">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold group-hover:text-emerald-800">Catat Penjualan / Masuk</div>
                                <div class="text-[10px] text-emerald-700">Debit Kas, Kredit Pendapatan</div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-emerald-500 group-hover:translate-x-1 transition"></i>
                    </a>

                    <a href="{{ route('transactions.create') }}" class="flex items-center justify-between p-3 rounded-xl bg-rose-50/70 hover:bg-rose-100/70 border border-rose-200 text-rose-900 transition group">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-rose-600 text-white flex items-center justify-center shadow-xs">
                                <i class="fa-solid fa-minus text-xs"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold group-hover:text-rose-800">Catat Pengeluaran Kasir</div>
                                <div class="text-[10px] text-rose-700">Petty cash & biaya shift</div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-rose-500 group-hover:translate-x-1 transition"></i>
                    </a>

                    <a href="{{ route('transactions.history') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-800 transition group">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-700 text-white flex items-center justify-center shadow-xs">
                                <i class="fa-solid fa-list-check text-xs"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold">Semua Riwayat Saya</div>
                                <div class="text-[10px] text-slate-500">Cek daftar struk & mutasi</div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:translate-x-1 transition"></i>
                    </a>
                </div>
            </div>

            <div class="mt-4 p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11px] flex items-start space-x-2">
                <i class="fa-solid fa-circle-exclamation text-amber-600 mt-0.5 flex-shrink-0"></i>
                <div>
                    <strong>SOP Kasir:</strong> Pastikan fisik uang di laci sesuai dengan angka <strong>Kas di Laci (Net)</strong> sebelum serah terima shift berakhir.
                </div>
            </div>
        </div>

    </div>

    <!-- 4. TABEL TRANSAKSI TERAKHIR KASIR INI -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Aktivitas Terkini</span>
                <h3 class="font-bold text-slate-900 text-base">10 Transaksi Terakhir yang Anda Input</h3>
            </div>
            <a href="{{ route('transactions.history') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-800 transition">
                <span>Buka Riwayat Lengkap</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">No. Transaksi</th>
                        <th class="px-5 py-3.5">Waktu</th>
                        <th class="px-5 py-3.5">Jenis</th>
                        <th class="px-5 py-3.5">Akun Debit</th>
                        <th class="px-5 py-3.5">Akun Kredit</th>
                        <th class="px-5 py-3.5">Catatan / Kontak</th>
                        <th class="px-5 py-3.5 text-right">Nominal</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cashierData['recent_transactions'] as $trx)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-semibold text-emerald-600">
                                {{ $trx->transaction_number }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $trx->date->format('d/m/Y') }} <span class="text-[10px] text-slate-400">{{ $trx->time }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($trx->type == 'income')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold text-[10px] border border-emerald-200">Pemasukan</span>
                                @elseif($trx->type == 'expense')
                                    <span class="px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 font-semibold text-[10px] border border-rose-200">Pengeluaran</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-semibold text-[10px] border border-blue-200">Transfer</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-800 font-medium">
                                {{ $trx->debitAccount?->name }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-800 font-medium">
                                {{ $trx->creditAccount?->name }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                <div>{{ $trx->notes ?: '-' }}</div>
                                @if($trx->contact)
                                    <span class="text-[10px] text-blue-600 font-medium">{{ $trx->contact->name }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold {{ $trx->type == 'income' ? 'text-emerald-600' : ($trx->type == 'expense' ? 'text-rose-600' : 'text-slate-900') }}">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center text-[10px] text-emerald-600 font-semibold">
                                    <i class="fa-solid fa-circle-check mr-1 text-emerald-500"></i> Tersimpan
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-400">
                                <i class="fa-solid fa-receipt text-3xl text-slate-300 mb-2 block"></i>
                                Belum ada transaksi yang Anda catat pada shift ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- CHART SCRIPT FOR CASHIER -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('cashierTrendChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($cashierData['chart_days']),
            datasets: [
                {
                    label: 'Pemasukan (Rp)',
                    data: @json($cashierData['chart_incomes']),
                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Pengeluaran (Rp)',
                    data: @json($cashierData['chart_expenses']),
                    backgroundColor: 'rgba(244, 63, 94, 0.85)',
                    borderRadius: 8,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: "'Plus Jakarta Sans', sans-serif",
                            size: 11
                        }
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(241, 245, 249, 1)'
                    },
                    ticks: {
                        font: {
                            family: "'Plus Jakarta Sans', sans-serif",
                            size: 10
                        },
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                            return value;
                        }
                    }
                }
            }
        }
    });
});
</script>
