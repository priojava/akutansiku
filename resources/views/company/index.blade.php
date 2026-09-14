@extends('layouts.app')

@section('title', 'Kelola Multi-Perusahaan')

@section('content')
<div class="space-y-8 animate-in fade-in duration-300" x-data="{ 
    openCreateModal: false, 
    openEditModal: false,
    editData: { id: null, name: '', city: '', address: '', phone: '', email: '', plan_type: 'premium' }
}">

    <!-- 1. Header Title & Top Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-building text-sm"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Multi-Perusahaan</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola banyak entitas bisnis, cabang pembukuan, atau unit usaha dengan isolasi finansial 100%.
            </p>
        </div>

        <button @click="openCreateModal = true" class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs sm:text-sm font-bold px-4 py-2.5 rounded-xl shadow-md shadow-blue-500/20 hover:shadow-lg transition-all duration-200 cursor-pointer">
            <i class="fa-solid fa-plus-circle"></i>
            <span>+ Tambah Perusahaan</span>
        </button>
    </div>

    <!-- 2. Grid Daftar Perusahaan -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($companies as $comp)
            @php
                $isActive = ($comp->id == $activeCompanyId);
            @endphp
            <div class="group bg-white rounded-2xl border {{ $isActive ? 'border-blue-500 ring-2 ring-blue-100 shadow-md' : 'border-slate-200/80 shadow-xs' }} overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col justify-between relative">
                
                @if($isActive)
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
                @endif

                <div class="p-6">
                    <!-- Top Status & Actions -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-2xl {{ $isActive ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center font-bold text-lg">
                                <i class="fa-solid fa-shapes"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base group-hover:text-blue-600 transition">{{ $comp->name }}</h3>
                                <p class="text-xs text-slate-400 flex items-center mt-0.5">
                                    <i class="fa-solid fa-location-dot text-slate-400 mr-1 text-[10px]"></i>
                                    {{ $comp->city ?: 'Indonesia' }}
                                </p>
                            </div>
                        </div>

                        <div>
                            @if($isActive)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-ping"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    Standby
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Meta Summary Details -->
                    <div class="grid grid-cols-2 gap-3 mt-6 pt-4 border-t border-slate-100 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-semibold block uppercase">Bagan Akun (COA)</span>
                            <span class="font-bold text-slate-800 text-sm mt-0.5 block font-mono">{{ $comp->accounts_count ?? 120 }} Akun</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] text-slate-400 font-semibold block uppercase">Total Transaksi</span>
                            <span class="font-bold text-slate-800 text-sm mt-0.5 block font-mono">{{ $comp->transactions_count ?? 0 }} Transaksi</span>
                        </div>
                    </div>

                    <!-- Contact details -->
                    <div class="mt-4 space-y-1.5 text-xs text-slate-500">
                        @if($comp->email)
                            <div class="flex items-center space-x-2">
                                <i class="fa-regular fa-envelope text-slate-400 w-4"></i>
                                <span class="truncate">{{ $comp->email }}</span>
                            </div>
                        @endif
                        @if($comp->phone)
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-phone text-slate-400 w-4 text-[10px]"></i>
                                <span>{{ $comp->phone }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card Footer Actions -->
                <div class="p-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="inline-flex items-center font-extrabold text-[10px] tracking-wider uppercase px-2 py-0.5 rounded {{ $comp->plan_type === 'premium' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600' }}">
                        <i class="fa-solid fa-crown mr-1 text-amber-500"></i> {{ strtoupper($comp->plan_type ?? 'PREMIUM') }}
                    </span>

                    <div class="flex items-center space-x-2">
                        <!-- Edit Button -->
                        <button type="button" @click="
                            editData = {
                                id: {{ $comp->id }},
                                name: '{{ addslashes($comp->name) }}',
                                city: '{{ addslashes($comp->city) }}',
                                address: '{{ addslashes($comp->address) }}',
                                phone: '{{ addslashes($comp->phone) }}',
                                email: '{{ addslashes($comp->email) }}',
                                plan_type: '{{ $comp->plan_type }}'
                            };
                            openEditModal = true;
                        " class="px-2.5 py-1.5 bg-white hover:bg-slate-100 text-slate-700 font-semibold border border-slate-200 rounded-lg shadow-2xs transition">
                            <i class="fa-solid fa-pencil text-[11px]"></i> Edit
                        </button>

                        <!-- Delete Button (If > 1 company) -->
                        @if(count($companies) > 1)
                            <form method="POST" action="{{ route('company.destroy', $comp->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus perusahaan \'{{ addslashes($comp->name) }}\' beserta seluruh datanya?');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1.5 bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-600 border border-slate-200 hover:border-rose-200 rounded-lg shadow-2xs transition" title="Hapus Perusahaan">
                                    <i class="fa-regular fa-trash-can text-[11px]"></i>
                                </button>
                            </form>
                        @endif

                        <!-- Switch / Activate Button -->
                        @if($isActive)
                            <button disabled class="px-3 py-1.5 bg-blue-50 text-blue-700 font-bold rounded-lg border border-blue-200 text-xs flex items-center space-x-1 cursor-default">
                                <i class="fa-solid fa-circle-check text-blue-600 text-xs"></i>
                                <span>Aktif</span>
                            </button>
                        @else
                            <form method="POST" action="{{ route('company.switch.post', $comp->id) }}">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition text-xs flex items-center space-x-1 cursor-pointer">
                                    <span>Pilih Pembukuan</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            </div>
        @endforeach
    </div>

    <!-- ============================================================ -->
    <!-- MODAL 1: TAMBAH PERUSAHAAN BARU -->
    <!-- ============================================================ -->
    <div x-show="openCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="openCreateModal = false"></div>

        <div class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden z-10 animate-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-700 to-indigo-700 p-6 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-white text-lg border border-white/20">
                        <i class="fa-solid fa-building-circle-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Tambah Perusahaan Baru</h3>
                        <p class="text-xs text-blue-200">Inisialisasi entitas bisnis & bagan akun terpisah</p>
                    </div>
                </div>
                <button @click="openCreateModal = false" class="text-white/70 hover:text-white p-2 rounded-xl hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form method="POST" action="{{ route('company.store') }}" class="p-6 space-y-4">
                @csrf

                <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs leading-relaxed flex items-start space-x-2">
                    <i class="fa-solid fa-circle-info text-blue-600 mt-0.5 text-sm"></i>
                    <span>Sistem akan <strong>otomatis membuat 120 Bagan Akun (COA) standar</strong>, rekening kas, serta template laporan keuangan untuk entitas baru ini.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perusahaan / Unit Bisnis <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: CV Gemoy Logistik / Cabang Bandung" class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kota</label>
                        <input type="text" name="city" placeholder="Contoh: Jakarta Selatan" class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Telepon / WhatsApp</label>
                        <input type="text" name="phone" placeholder="Contoh: 08123456789" class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Resmi</label>
                    <input type="email" name="email" placeholder="Contoh: finance@gemoylogistik.com" class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Kantor / Operasional</label>
                    <textarea name="address" rows="2" placeholder="Alamat lengkap perusahaan..." class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai Pembukuan</label>
                        <input type="date" name="conversion_date" value="{{ \Carbon\Carbon::now()->startOfMonth()->toDateString() }}" class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Paket Lisensi</label>
                        <select name="plan_type" class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition bg-white">
                            <option value="premium">Enterprise PRO (Premium)</option>
                            <option value="free">Standard (Free)</option>
                        </select>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md shadow-blue-500/25 transition">
                        Simpan & Inisialisasi COA
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL 2: EDIT INFORMASI PERUSAHAAN -->
    <!-- ============================================================ -->
    <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="openEditModal = false"></div>

        <div class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden z-10 animate-in zoom-in-95 duration-200">
            <div class="bg-slate-900 p-6 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-white text-lg border border-white/20">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Edit Informasi Perusahaan</h3>
                        <p class="text-xs text-slate-400" x-text="editData.name"></p>
                    </div>
                </div>
                <button @click="openEditModal = false" class="text-white/70 hover:text-white p-2 rounded-xl hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('company') }}/' + editData.id" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perusahaan <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kota</label>
                        <input type="text" name="city" x-model="editData.city" class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Telepon</label>
                        <input type="text" name="phone" x-model="editData.phone" class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" x-model="editData.email" class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat</label>
                    <textarea name="address" rows="2" x-model="editData.address" class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Paket</label>
                    <select name="plan_type" x-model="editData.plan_type" class="w-full text-xs sm:text-sm border border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition bg-white">
                        <option value="premium">Enterprise PRO (Premium)</option>
                        <option value="free">Standard (Free)</option>
                    </select>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-md transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
