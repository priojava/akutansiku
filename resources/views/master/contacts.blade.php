@extends('layouts.app')

@section('title', 'Master Kontak')

@section('content')
<div class="space-y-6" x-data="{
    search: '',
    selectedType: 'all',
    modalOpen: false,
    contactType: 'customer',
    filterContact(itemType, itemText) {
        const matchesType = this.selectedType === 'all' || itemType === this.selectedType;
        const matchesSearch = !this.search || itemText.toLowerCase().includes(this.search.toLowerCase());
        return matchesType && matchesSearch;
    }
}">

    <!-- Header Section -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Kontak (Pelanggan & Vendor)</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola data pelanggan, pemasok / vendor, dan mitra bisnis</p>
        </div>
        <div class="flex items-center space-x-3">
            <button type="button" @click="modalOpen = true" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-user-plus text-xs"></i>
                <span>+ Tambah Kontak</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center space-x-2">
            <button type="button" @click="selectedType = 'all'" 
                    :class="selectedType === 'all' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                Semua ({{ count($contacts) }})
            </button>
            <button type="button" @click="selectedType = 'customer'" 
                    :class="selectedType === 'customer' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                Pelanggan (Customer)
            </button>
            <button type="button" @click="selectedType = 'vendor'" 
                    :class="selectedType === 'vendor' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                Pemasok (Vendor)
            </button>
            <button type="button" @click="selectedType = 'employee'" 
                    :class="selectedType === 'employee' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                Karyawan
            </button>
        </div>

        <div class="relative">
            <input type="text" x-model="search" placeholder="Cari nama, email, no HP..."
                   class="w-56 sm:w-72 pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-blue-500 text-xs"></i>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-6 py-4">Nama Kontak</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4">No. Telepon / HP</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Alamat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($contacts as $c)
                        <tr class="hover:bg-slate-50 transition" 
                            x-show="filterContact('{{ $c->type }}', '{{ strtolower($c->name . ' ' . $c->email . ' ' . $c->phone . ' ' . $c->address) }}')">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($c->name, 0, 1)) }}
                                    </div>
                                    <div class="font-bold text-slate-800 text-xs">{{ $c->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($c->type == 'customer')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Pelanggan (Customer)
                                    </span>
                                @elseif($c->type == 'vendor')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        Pemasok (Vendor)
                                    </span>
                                @elseif($c->type == 'employee')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        Karyawan
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ ucfirst($c->type) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $c->phone ?: '-' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $c->email ?: '-' }}</td>
                            <td class="px-6 py-4 text-slate-600 max-w-xs truncate">{{ $c->address ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-slate-400">Belum ada data kontak. Klik tombol + Tambah Kontak di atas untuk menambahkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH KONTAK -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden border border-slate-200" @click.away="modalOpen = false">
            
            <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Tambah Kontak Baru</h3>
                        <p class="text-[11px] text-slate-500">Daftarkan pelanggan, pemasok (vendor), atau mitra bisnis</p>
                    </div>
                </div>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('master.contacts.store') }}" class="p-6 space-y-4">
                @csrf

                <!-- Nama Lengkap / Perusahaan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kontak / Perusahaan <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" placeholder="Contoh: PT Sumber Pangan, Bu Siti Catering" required
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <!-- Tipe Kontak -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Kontak <span class="text-rose-500">*</span></label>
                    <select name="type" x-model="contactType" required
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="customer">Pelanggan (Customer)</option>
                        <option value="vendor">Pemasok / Supplier (Vendor)</option>
                        <option value="employee">Karyawan / Staf</option>
                        <option value="other">Lainnya / Mitra</option>
                    </select>
                </div>

                <!-- Grid Telepon & Email -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" placeholder="081234567890"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" placeholder="kontak@perusahaan.com"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <!-- Alamat -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap</label>
                    <textarea name="address" rows="3" placeholder="Alamat jalan, nomor kantor, kota..."
                              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center space-x-1.5">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Kontak</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
