@extends('layouts.app')

@section('title', 'Master Tenants SaaS')

@section('content')
<div class="space-y-6 animate-in fade-in duration-300" x-data="{ editModalOpen: false, selectedTenant: {} }">

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
            <h1 class="text-xl font-bold text-slate-900">Kelola Semua Tenant & Perusahaan</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar semua pemilik usaha/klien yang terdaftar di platform SaaS</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('superadmin.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                &larr; Dashboard Super Admin
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('superadmin.tenants') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama perusahaan, kota, email..."
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none">
            </div>
            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none">
                    <option value="">Semua Status Langganan</option>
                    <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial (Uji Coba)</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif (Berbayar)</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired (Kadaluwarsa)</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended (Dibekukan)</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('superadmin.tenants') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs transition" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">Nama Perusahaan</th>
                        <th class="px-5 py-3.5">Email & Reset Password</th>
                        <th class="px-5 py-3.5">Kota / Kontak</th>
                        <th class="px-5 py-3.5">Paket SaaS</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Masa Berlaku</th>
                        <th class="px-5 py-3.5 text-center">Aksi / Kontrol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($companies as $comp)
                        @php
                            $ownerUser = $comp->getOwnerUser();
                            $ownerEmail = $ownerUser?->email ?? $comp->email ?? '-';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-bold text-slate-900">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($comp->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span>{{ $comp->name }}</span>
                                        <span class="text-[10px] text-slate-400 font-normal block font-mono">ID: #{{ $comp->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-1">
                                    <div class="flex items-center flex-wrap gap-1.5">
                                        <span class="font-mono text-xs font-semibold text-slate-800 bg-slate-100 hover:bg-slate-200/70 px-2.5 py-1 rounded-lg border border-slate-200 select-all inline-flex items-center space-x-1.5" title="Email Login Pemilik">
                                            <i class="fa-regular fa-envelope text-[10px] text-slate-500"></i>
                                            <span>{{ $ownerEmail }}</span>
                                        </span>
                                        @if($ownerEmail !== '-')
                                            <form action="{{ route('superadmin.tenants.reset_password', $comp->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin me-reset password akun \'{{ addslashes($ownerEmail) }}\' menjadi default: password123 ?');" class="inline-block">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 hover:text-amber-900 border border-amber-300 rounded-lg text-[10px] font-bold transition inline-flex items-center space-x-1 shadow-2xs cursor-pointer" title="Reset password user ini ke default: password123">
                                                    <i class="fa-solid fa-key text-[9px] text-amber-600"></i>
                                                    <span>Reset (password123)</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    @if($ownerUser)
                                        <div class="text-[10px] text-slate-500 flex items-center space-x-1 pl-0.5">
                                            <i class="fa-regular fa-user text-[9px] text-slate-400"></i>
                                            <span class="font-medium text-slate-600">{{ $ownerUser->name }}</span>
                                            <span class="text-[9px] text-purple-600 bg-purple-50 border border-purple-200/60 px-1.5 py-0.2 rounded font-bold uppercase">Owner</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <div>{{ $comp->city ?: '-' }}</div>
                                <span class="text-[10px] text-slate-400">{{ $comp->phone ?: '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $comp->subscription_plan === 'premium' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                    {{ $comp->subscription_plan ?: 'premium' }}
                                </span>
                                <div class="text-[10px] text-slate-500 mt-0.5 font-medium">
                                    Kuota: <strong class="text-purple-700">{{ $comp->max_companies ?? ($comp->subscription_plan === 'premium' ? 3 : 1) }}</strong> Entitas
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold {{ $comp->subscription_status === 'trial' ? 'bg-amber-50 text-amber-700 border border-amber-200' : ($comp->subscription_status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                    {{ ucfirst($comp->subscription_status ?? 'Trial') }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if($comp->subscription_expires_at)
                                    <span class="font-bold text-slate-800">{{ $comp->remaining_days }} Hari Lagi</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $comp->subscription_expires_at->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-slate-400">14 Hari</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button @click="selectedTenant = { id: {{ $comp->id }}, name: '{{ addslashes($comp->name) }}', plan: '{{ $comp->subscription_plan ?: 'premium' }}', status: '{{ $comp->subscription_status ?: 'active' }}', maxCompanies: {{ $comp->max_companies ?? ($comp->subscription_plan === 'premium' ? 3 : 1) }}, remainingDays: {{ $comp->remaining_days }}, expiresAt: '{{ $comp->subscription_expires_at ? $comp->subscription_expires_at->format('Y-m-d') : '' }}' }; editModalOpen = true" class="px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-lg text-xs font-bold transition flex items-center space-x-1 cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                        <span>Edit Paket</span>
                                    </button>

                                    <form action="{{ route('superadmin.tenants.destroy', $comp->id) }}" method="POST" onsubmit="return confirm('PERINGATAN SUPER ADMIN:\nApakah Anda yakin ingin MENGHAPUS PERMANEN perusahaan \'{{ addslashes($comp->name) }}\' beserta seluruh transaksi, akun COA, dan laporannya?');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold transition flex items-center space-x-1 cursor-pointer" title="Hapus Perusahaan">
                                            <i class="fa-regular fa-trash-can text-[10px]"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">Tidak ada data tenant yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($companies->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $companies->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL EDIT TENANT SUBSCRIPTION -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-data="{ durationMode: 'set_days' }">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editModalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="editModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="editModalOpen" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                <form :action="'{{ url('/superadmin/tenants') }}/' + selectedTenant.id + '/update-plan'" method="POST">
                    @csrf
                    <div class="bg-gradient-to-r from-purple-700 to-indigo-700 px-6 py-4 text-white flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-sm" x-text="'Kelola Langganan: ' + selectedTenant.name"></h3>
                            <div class="text-[11px] text-purple-200 mt-0.5">
                                Masa aktif saat ini: <strong class="text-white" x-text="(selectedTenant.remainingDays ?? 0) + ' hari lagi'"></strong>
                            </div>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-white/80 hover:text-white">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Paket Layanan SaaS</label>
                            <select name="subscription_plan" x-model="selectedTenant.plan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none">
                                <option value="standard">Standard Business (1 Cabang, COA Standar)</option>
                                <option value="premium">Premium Pro Enterprise (Multi-Cabang, AI, Lengkap)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kuota Maksimal Perusahaan (Entitas)</label>
                            <div class="relative">
                                <input type="number" name="max_companies" x-model="selectedTenant.maxCompanies" min="1" max="100" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none" placeholder="Default: 1 (Standard) atau 3 (Pro)">
                                <span class="absolute right-3 top-2 text-slate-400 text-xs">Entitas</span>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">Tingkatkan angka ini jika pelanggan membeli add-on kuota perusahaan ke-4, ke-5, dst.</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Langganan</label>
                            <select name="subscription_status" x-model="selectedTenant.status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none">
                                <option value="trial">Trial (Masa Uji Coba)</option>
                                <option value="active">Active (Aktif Berbayar)</option>
                                <option value="expired">Expired (Kadaluwarsa / Terkunci Read-Only)</option>
                                <option value="suspended">Suspended (Dibekukan / Nunggak)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Atur Masa Berlaku / Kadaluwarsa</label>
                            <div class="grid grid-cols-3 gap-1 p-1 bg-slate-100 rounded-xl mb-2.5">
                                <button type="button" @click="durationMode = 'set_days'" :class="durationMode === 'set_days' ? 'bg-white text-purple-700 shadow-2xs font-bold' : 'text-slate-600'" class="py-1.5 px-2 rounded-lg text-[10px] transition text-center cursor-pointer">
                                    Set Hari Ini
                                </button>
                                <button type="button" @click="durationMode = 'calendar'" :class="durationMode === 'calendar' ? 'bg-white text-purple-700 shadow-2xs font-bold' : 'text-slate-600'" class="py-1.5 px-2 rounded-lg text-[10px] transition text-center cursor-pointer">
                                    Pilih Tanggal
                                </button>
                                <button type="button" @click="durationMode = 'add_days'" :class="durationMode === 'add_days' ? 'bg-white text-purple-700 shadow-2xs font-bold' : 'text-slate-600'" class="py-1.5 px-2 rounded-lg text-[10px] transition text-center cursor-pointer">
                                    Tambah (+)
                                </button>
                            </div>

                            <!-- Mode 1: Set Tepat X Hari dari Sekarang -->
                            <div x-show="durationMode === 'set_days'" class="space-y-2">
                                <div class="relative">
                                    <input type="number" id="super_set_days" name="set_days" placeholder="Misal: 30 (akan aktif 30 hari dari hari ini)" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none">
                                    <span class="absolute right-3 top-2 text-slate-400 text-xs">Hari</span>
                                </div>
                                <div class="flex items-center flex-wrap gap-1.5 pt-0.5">
                                    <span class="text-[10px] text-slate-400">Pintasan Cepat:</span>
                                    <button type="button" @click="document.getElementById('super_set_days').value = 14" class="px-2 py-0.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded text-[10px] font-bold cursor-pointer">14 Hari (Trial)</button>
                                    <button type="button" @click="document.getElementById('super_set_days').value = 30" class="px-2 py-0.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded text-[10px] font-bold cursor-pointer">30 Hari (Trial/1 Bln)</button>
                                    <button type="button" @click="document.getElementById('super_set_days').value = 90" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded text-[10px] font-bold cursor-pointer">90 Hari</button>
                                    <button type="button" @click="document.getElementById('super_set_days').value = 365" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded text-[10px] font-bold cursor-pointer">365 Hari (1 Thn)</button>
                                </div>
                            </div>

                            <!-- Mode 2: Pilih Tanggal Jatuh Tempo Langsung -->
                            <div x-show="durationMode === 'calendar'">
                                <input type="date" name="expires_at" x-model="selectedTenant.expiresAt" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none">
                                <span class="text-[10px] text-slate-400 mt-1 block">Tentukan tanggal pasti kapan masa aktif tenant ini berakhir.</span>
                            </div>

                            <!-- Mode 3: Tambah Hari dari Masa Aktif Sekarang -->
                            <div x-show="durationMode === 'add_days'">
                                <input type="number" name="extend_days" placeholder="Misal: 30 (menambahkan 30 hari ke masa aktif berjalan)" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none">
                                <span class="text-[10px] text-slate-400 mt-1 block">Menambahkan jumlah hari ke masa aktif yang sedang berjalan.</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
