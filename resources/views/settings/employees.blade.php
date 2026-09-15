@extends('layouts.app')

@section('title', 'Karyawan & Hak Akses')

@section('content')
<div class="space-y-6" x-data="{ modalTambahOpen: false }">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Karyawan & Level Akses (RBAC)</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola hak akses akun, pembatasan menu, dan wewenang kunci saldo awal</p>
        </div>
        <button @click="modalTambahOpen = true" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition flex items-center space-x-1.5">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span>+ Tambah Karyawan</span>
        </button>
    </div>

    <!-- 1. Daftar Karyawan Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">Daftar Pengguna / Karyawan</h3>
            <span class="text-xs text-slate-500 font-medium">{{ count($employees) }} Pengguna Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Nama</th>
                        <th class="px-6 py-3.5">Email & No. HP</th>
                        <th class="px-6 py-3.5">Peran / Level Akses</th>
                        <th class="px-6 py-3.5">Wewenang Kunci COA</th>
                        <th class="px-6 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($employees as $emp)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $emp->name }}</div>
                                        <div class="text-[11px] text-slate-400">ID: USER-{{ str_pad($emp->id, 4, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-700">{{ $emp->email }}</div>
                                <div class="text-[11px] text-slate-400">{{ $emp->phone ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $r = $emp->pivot->role ?? 'admin';
                                    $matrix = $rolesMatrix[$r] ?? $rolesMatrix['admin'];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[11px] font-bold border {{ $matrix['badge'] }}">
                                    {{ $matrix['name'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($r === 'admin')
                                    <span class="inline-flex items-center text-[11px] font-semibold text-emerald-700">
                                        <i class="fa-solid fa-circle-check mr-1.5 text-emerald-500"></i> Diizinkan (Owner)
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-[11px] font-medium text-slate-400">
                                        <i class="fa-solid fa-circle-xmark mr-1.5 text-slate-300"></i> Tidak Diizinkan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center text-[11px] text-emerald-600 font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Aktif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Tabel Matriks Hak Akses (Permissions Matrix) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-6">
        <h3 class="font-bold text-slate-800 text-sm mb-2">Matriks Wewenang & Batasan Hak Akses</h3>
        <p class="text-xs text-slate-500 mb-5">Detail pembatasan fitur dan menu untuk setiap level peran pengguna</p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-800 border border-slate-200 rounded-lg">
                <thead class="bg-blue-50/70 border-b border-blue-100 text-blue-900 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Level Peran</th>
                        <th class="py-3 px-4 text-center">Kunci / Buka Saldo COA</th>
                        <th class="py-3 px-4 text-center">Catat Transaksi</th>
                        <th class="py-3 px-4 text-center">Master Data COA</th>
                        <th class="py-3 px-4 text-center">Laporan Keuangan</th>
                        <th class="py-3 px-4 text-center">Pengaturan Sistem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rolesMatrix as $key => $r)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">{{ $r['name'] }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $r['description'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                {!! $r['can_lock_coa'] ? '<i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>' : '<i class="fa-solid fa-circle-xmark text-slate-300 text-sm"></i>' !!}
                            </td>
                            <td class="py-3 px-4 text-center">
                                {!! $r['can_transactions'] ? '<i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>' : '<i class="fa-solid fa-circle-xmark text-slate-300 text-sm"></i>' !!}
                            </td>
                            <td class="py-3 px-4 text-center">
                                {!! $r['can_master_data'] ? '<i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>' : '<i class="fa-solid fa-circle-xmark text-slate-300 text-sm"></i>' !!}
                            </td>
                            <td class="py-3 px-4 text-center">
                                {!! $r['can_reports'] ? '<i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>' : '<i class="fa-solid fa-circle-xmark text-slate-300 text-sm"></i>' !!}
                            </td>
                            <td class="py-3 px-4 text-center">
                                {!! $r['can_settings'] ? '<i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>' : '<i class="fa-solid fa-circle-xmark text-slate-300 text-sm"></i>' !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH KARYAWAN -->
    <div x-show="modalTambahOpen" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden" @click.away="modalTambahOpen = false">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 text-base">Tambah Karyawan / Pengguna</h3>
                <button @click="modalTambahOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('settings.employees.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" placeholder="Misal: Ahmad Zaky" required
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" placeholder="email@perusahaan.com" required
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">No. HP / WhatsApp</label>
                    <input type="text" name="phone" placeholder="+628123456789"
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Level Peran (Role) <span class="text-rose-500">*</span></label>
                    <select name="role" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-blue-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="admin">Owner (Administrator) - Akses Penuh & Pengaturan</option>
                        <option value="accountant">Akuntan (Finance) - Jurnal, Tutup Buku & Laporan Keuangan</option>
                        <option value="auditor">Auditor - Pengawas (Hanya Lihat Laporan / Read-Only)</option>
                        <option value="staff">Staff Operasional - Input Kas & Transaksi Harian</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalTambahOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Simpan Pengguna</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
