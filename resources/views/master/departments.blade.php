@extends('layouts.app')

@section('title', 'Master Departemen / Divisi')

@section('content')
<div class="space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    editId: null,
    search: '{{ request('search') }}',
    form: {
        code: '',
        name: '',
        description: '',
        is_active: true
    },
    openAddModal() {
        this.editMode = false;
        this.editId = null;
        this.form = {
            code: '',
            name: '',
            description: '',
            is_active: true
        };
        this.modalOpen = true;
    },
    openEditModal(dept) {
        this.editMode = true;
        this.editId = dept.id;
        this.form = {
            code: dept.code || '',
            name: dept.name,
            description: dept.description || '',
            is_active: Boolean(dept.is_active)
        };
        this.modalOpen = true;
    }
}">

    <!-- Info Banner Card -->
    <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-sky-50 border border-blue-200 rounded-2xl p-5 text-slate-700 shadow-sm flex items-start space-x-4">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
            <i class="fa-solid fa-building-user text-base"></i>
        </div>
        <div>
            <h2 class="text-sm font-bold text-slate-900">Tentang Master Departemen / Divisi</h2>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                <strong>Departemen</strong> adalah unit kerja atau divisi resmi perusahaan (misal: <em>Divisi Operasional, Divisi TJL, Divisi Konstruksi, Divisi F&B</em>). Setiap departemen dapat menaungi beberapa <strong>Proyek</strong> untuk memisahkan perhitungan biaya (*Cost Center*) dan pendapatan (*Profit Center*).
            </p>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 sm:p-6 space-y-4">
        
        <!-- Header & Action -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <button type="button" 
                    @click="openAddModal()" 
                    class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm shadow-blue-600/20 flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Departemen Baru</span>
            </button>

            <!-- Form Pencarian -->
            <form method="GET" action="{{ route('master.departments') }}" class="flex items-center space-x-2">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari kode atau nama..." 
                           class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none w-56 sm:w-64 transition">
                </div>
                @if(request('search'))
                    <a href="{{ route('master.departments') }}" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition" title="Reset Pencarian">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Departemen -->
        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3.5 w-12 text-center">No.</th>
                        <th class="px-4 py-3.5">Kode</th>
                        <th class="px-4 py-3.5">Nama Departemen</th>
                        <th class="px-4 py-3.5">Keterangan</th>
                        <th class="px-4 py-3.5 text-center">Proyek Terkait</th>
                        <th class="px-4 py-3.5 text-center">Transaksi</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($departments as $index => $dept)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 text-center text-slate-400 font-medium">{{ $index + 1 }}</td>
                            <td class="px-4 py-3.5 font-mono font-bold text-slate-700">
                                @if($dept->code)
                                    <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-200 text-[11px]">{{ $dept->code }}</span>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-bold text-slate-800">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                    <span>{{ $dept->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 max-w-xs truncate">{{ $dept->description ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $dept->projects_count }} Proyek
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center text-slate-500">
                                {{ $dept->transactions_count }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($dept->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1 whitespace-nowrap">
                                <button type="button" 
                                        @click="openEditModal({{ json_encode($dept) }})" 
                                        class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer" 
                                        title="Edit Departemen">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form method="POST" action="{{ route('master.departments.destroy', $dept->id) }}" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus departemen \'{{ $dept->name }}\'? Seluruh proyek di bawahnya akan menjadi tanpa departemen.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Hapus Departemen">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <i class="fa-solid fa-building-user text-xl"></i>
                                </div>
                                <p class="font-bold text-slate-600">Belum ada departemen yang ditambahkan.</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Tambah Departemen Baru" untuk mendaftarkan departemen atau divisi pertama Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal Form Tambah / Edit -->
    <div x-show="modalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.away="modalOpen = false" 
             class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-150">
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-900 text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-building-user text-blue-600"></i>
                    <span x-text="editMode ? 'Edit Departemen' : 'Tambah Departemen Baru'"></span>
                </h3>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 text-sm cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="editMode ? '{{ url('master/departments') }}/' + editId : '{{ route('master.departments.store') }}'" method="POST" class="p-6 space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Kode Departemen -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Departemen (Opsional)</label>
                    <input type="text" 
                           name="code" 
                           x-model="form.code" 
                           placeholder="Contoh: TJL, OPR, MKT" 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono uppercase focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>

                <!-- Nama Departemen -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Departemen <span class="text-rose-500">*</span></label>
                    <input type="text" 
                           name="name" 
                           x-model="form.name" 
                           required 
                           placeholder="Contoh: Divisi Kemitraan TJL, Divisi Operasional" 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan (Opsional)</label>
                    <textarea name="description" 
                              x-model="form.description" 
                              rows="3" 
                              placeholder="Deskripsi fungsi atau lingkup departemen..." 
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none transition"></textarea>
                </div>

                <!-- Status Aktif -->
                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" 
                           name="is_active" 
                           id="is_active" 
                           value="1" 
                           x-model="form.is_active" 
                           class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                    <label for="is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">Departemen Aktif</label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-blue-600/20 cursor-pointer">
                        Simpan Departemen
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
