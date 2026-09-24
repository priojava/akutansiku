@extends('layouts.app')

@section('title', 'Master Tipe Aset')

@section('content')
<div class="space-y-6" x-data="{
    search: '',
    modalOpen: false,
    editMode: false,
    formAction: '{{ route('master.asset_types.store') }}',
    formData: {
        id: null,
        name: '',
        code: '',
        useful_life_years: 4,
        asset_account_id: '',
        expense_account_id: '',
        accumulated_account_id: '',
        is_depreciated: true,
        description: ''
    },
    openCreate() {
        this.editMode = false;
        this.formAction = '{{ route('master.asset_types.store') }}';
        this.formData = {
            id: null,
            name: '',
            code: '',
            useful_life_years: 4,
            asset_account_id: '',
            expense_account_id: '',
            accumulated_account_id: '',
            is_depreciated: true,
            description: ''
        };
        this.modalOpen = true;
    },
    openEdit(item) {
        this.editMode = true;
        this.formAction = '{{ url('/master/asset-types') }}/' + item.id;
        this.formData = {
            id: item.id,
            name: item.name,
            code: item.code || '',
            useful_life_years: item.useful_life_years || 4,
            asset_account_id: item.asset_account_id || '',
            expense_account_id: item.expense_account_id || '',
            accumulated_account_id: item.accumulated_account_id || '',
            is_depreciated: Boolean(item.is_depreciated),
            description: item.description || ''
        };
        this.modalOpen = true;
    }
}">

    <!-- Page Header & Action -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('assets.index') }}" class="open-in-tab text-xs font-semibold text-blue-600 hover:text-blue-800" data-tab-title="Daftar Aset" data-tab-icon="fa-solid fa-boxes-stacked">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar Aset
                </a>
            </div>
            <h1 class="text-xl font-bold text-slate-900 mt-1">Master Tipe & Kategori Aset Tetap</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola tipe aktiva tetap, masa manfaat standar, dan pemetaan akun penyusutan otomatis</p>
        </div>
        <div class="flex items-center space-x-2.5">
            <a href="{{ route('assets.index') }}" class="open-in-tab px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center space-x-1.5" data-tab-title="Daftar Aset" data-tab-icon="fa-solid fa-boxes-stacked">
                <i class="fa-solid fa-list text-xs"></i>
                <span>Lihat Daftar Aset</span>
            </a>
            <button type="button" @click="openCreate()" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-md shadow-blue-500/20 transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Tipe Aset</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 flex items-center space-x-2 shadow-2xs">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-800 flex items-center space-x-2 shadow-2xs">
            <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Guide Banner -->
    <div class="p-4 bg-gradient-to-r from-blue-50/80 via-indigo-50/50 to-slate-50 border border-blue-200/80 rounded-2xl flex items-start space-x-3.5 shadow-2xs">
        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
            <i class="fa-solid fa-tags text-sm"></i>
        </div>
        <div>
            <h4 class="text-xs font-bold text-slate-800">Tentang Master Tipe Aset Tetap</h4>
            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                Tipe Aset memudahkan pengelompokan aktiva (misal: <strong>Kendaraan</strong>, <strong>Bangunan</strong>, <strong>Peralatan Kantor</strong>, <strong>Mesin</strong>, dll). 
                Setiap tipe menyimpan akun default untuk <em>Beban Penyusutan</em> dan <em>Akumulasi Penyusutan</em> sehingga saat menginput aset baru, Anda tidak perlu memilih akun manual satu per satu.
            </p>
        </div>
    </div>

    <!-- Tabel Master Tipe Aset -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <div class="text-xs font-bold text-slate-700">
                Total {{ $assetTypes->count() }} Tipe Aset
            </div>
            <div class="relative">
                <input type="text" x-model="search" placeholder="Cari tipe aset..."
                       class="w-64 pl-8 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-slate-400 text-xs"></i>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-100/75 border-b border-slate-200 text-slate-600 uppercase text-[10px] tracking-wider font-bold">
                    <tr>
                        <th class="px-4 py-3 w-12 text-center">NO</th>
                        <th class="px-4 py-3 w-20">KODE</th>
                        <th class="px-4 py-3">NAMA TIPE ASET</th>
                        <th class="px-4 py-3 text-center">MASA MANFAAT</th>
                        <th class="px-4 py-3">AKUN HARTA TETAP</th>
                        <th class="px-4 py-3">AKUN BEBAN PENYUSUTAN</th>
                        <th class="px-4 py-3">AKUN AKUMULASI PENYUSUTAN</th>
                        <th class="px-4 py-3 text-center">STATUS</th>
                        <th class="px-4 py-3 text-center">JML ASET</th>
                        <th class="px-4 py-3 text-center w-24">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assetTypes as $index => $type)
                        <tr class="hover:bg-slate-50/80 transition"
                            x-show="!search || '{{ strtolower($type->name . ' ' . $type->code . ' ' . ($type->assetAccount?->name ?? '') . ' ' . ($type->expenseAccount?->name ?? '') . ' ' . ($type->accumulatedAccount?->name ?? '')) }}'.includes(search.toLowerCase())">
                            <td class="px-4 py-3.5 text-center text-slate-400 font-semibold">{{ $index + 1 }}</td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $type->code ?: '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-800">{{ $type->name }}</div>
                                @if($type->description)
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $type->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center font-semibold">
                                @if($type->is_depreciated && $type->useful_life_years > 0)
                                    <span class="inline-flex items-center text-slate-700">
                                        <i class="fa-solid fa-clock text-blue-500 mr-1 text-[10px]"></i>
                                        {{ $type->useful_life_years }} Tahun
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-700">
                                @if($type->assetAccount)
                                    <div class="font-medium">{{ $type->assetAccount->name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">({{ $type->assetAccount->code }})</div>
                                @else
                                    <span class="text-slate-400 italic">Belum diatur</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-700">
                                @if($type->is_depreciated)
                                    @if($type->expenseAccount)
                                        <div class="font-medium text-rose-700">{{ $type->expenseAccount->name }}</div>
                                        <div class="text-[10px] font-mono text-slate-400">({{ $type->expenseAccount->code }})</div>
                                    @else
                                        <span class="text-amber-600 font-medium">⚠️ Belum dipilih</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Tidak disusutkan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-700">
                                @if($type->is_depreciated)
                                    @if($type->accumulatedAccount)
                                        <div class="font-medium text-indigo-700">{{ $type->accumulatedAccount->name }}</div>
                                        <div class="text-[10px] font-mono text-slate-400">({{ $type->accumulatedAccount->code }})</div>
                                    @else
                                        <span class="text-amber-600 font-medium">⚠️ Belum dipilih</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Tidak disusutkan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($type->is_depreciated)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Disusutkan
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        Non-Depresiasi
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-slate-700">
                                {{ $type->assets->count() }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button type="button" @click="openEdit({{ json_encode($type) }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Tipe Aset">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    @if($type->assets->count() === 0)
                                        <form action="{{ route('master.asset_types.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Hapus tipe aset {{ $type->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus Tipe Aset">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-400">
                                Belum ada Tipe Aset yang tersimpan. Klik <strong>Tambah Tipe Aset</strong> untuk mulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Tambah / Edit Tipe Aset -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 animate-in fade-in duration-200">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden" @click.away="modalOpen = false">
            <div class="p-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-tags text-base"></i>
                    <h3 class="font-bold text-sm" x-text="editMode ? 'Edit Tipe Aset' : 'Tambah Tipe Aset Baru'"></h3>
                </div>
                <button type="button" @click="modalOpen = false" class="text-white/80 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="formAction" method="POST" class="p-5 space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Tipe Aset <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="formData.name" required placeholder="Contoh: Kendaraan Operasional"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kode / Singkatan</label>
                        <input type="text" name="code" x-model="formData.code" placeholder="KND"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs uppercase focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none font-mono">
                    </div>
                </div>

                <!-- Checkbox Depresiasi -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <label class="flex items-center space-x-2.5 cursor-pointer">
                        <input type="checkbox" name="is_depreciated" value="1" x-model="formData.is_depreciated" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-xs font-bold text-slate-800">Aset tipe ini mengalami penyusutan nilai (Depresiasi)</span>
                    </label>
                    <p class="text-[11px] text-slate-500 mt-1 pl-6.5">Hilangkan centang untuk aset seperti Tanah yang nilainya tidak berkurang.</p>
                </div>

                <!-- Masa Manfaat Default -->
                <div x-show="formData.is_depreciated">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Masa Manfaat Standar (Tahun)</label>
                    <input type="number" name="useful_life_years" x-model="formData.useful_life_years" min="1" max="50"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium">
                </div>

                <!-- Akun Harta Tetap -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Harta Tetap (Neraca)</label>
                    <select name="asset_account_id" x-model="formData.asset_account_id"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Pilih Akun Harta Tetap --</option>
                        @foreach($assetAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Akun Beban Penyusutan -->
                <div x-show="formData.is_depreciated">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Beban Penyusutan (Laba Rugi)</label>
                    <select name="expense_account_id" x-model="formData.expense_account_id"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Pilih Akun Beban Penyusutan --</option>
                        @foreach($expenseAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Akun Akumulasi Penyusutan -->
                <div x-show="formData.is_depreciated">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Akumulasi Penyusutan (Contra Asset)</label>
                    <select name="accumulated_account_id" x-model="formData.accumulated_account_id"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Pilih Akun Akumulasi Penyusutan --</option>
                        @foreach($accumulatedAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Deskripsi</label>
                    <textarea name="description" x-model="formData.description" rows="2" placeholder="Catatan tambahan..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>

                <!-- Tombol Submit -->
                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition">
                        Simpan Tipe Aset
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
