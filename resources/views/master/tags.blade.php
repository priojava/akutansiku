@extends('layouts.app')

@section('title', 'Master Tag / Label Transaksi')

@section('content')
<div class="space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    editId: null,
    search: '',
    form: {
        name: '',
        color: '#3b82f6'
    },
    colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#64748b'],
    openAddModal() {
        this.editMode = false;
        this.editId = null;
        this.form = {
            name: '',
            color: '#3b82f6'
        };
        this.modalOpen = true;
    },
    openEditModal(tag) {
        this.editMode = true;
        this.editId = tag.id;
        this.form = {
            name: tag.name,
            color: tag.color || '#3b82f6'
        };
        this.modalOpen = true;
    }
}">

    <!-- Info Card -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 sm:p-5 text-slate-700 shadow-sm flex items-start space-x-3.5">
        <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
            <i class="fa-solid fa-tags text-sm"></i>
        </div>
        <div>
            <h2 class="text-sm font-bold text-slate-800">Tentang Tag / Label Transaksi</h2>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                <strong>Tag</strong> digunakan sebagai <strong>penanda bebas fleksibel</strong> (seperti <em>hashtag #</em>) untuk mempermudah pencarian, penandaan cepat, atau filter operasional (contoh: <code>#Reimbursement</code>, <code>#KasKecil</code>, <code>#Urgent</code>, <code>#Audit2026</code>). Tag tidak terikat pada struktur organisasi.<br>
                <span class="text-slate-500 mt-0.5 inline-block">💡 <em>Catatan:</em> Untuk pembagian unit kerja/divisi resmi, gunakan menu <strong>Departemen</strong>. Untuk kontrak/pekerjaan yang memiliki batas waktu dan anggaran, gunakan menu <strong>Proyek</strong>.</span>
            </p>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-4">
        
        <!-- Header & Action -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <button type="button" 
                    @click="openAddModal()" 
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Tambah Tag Baru</span>
            </button>

            <!-- Search Bar -->
            <div class="w-full sm:w-64 relative">
                <input type="text" 
                       x-model="search" 
                       placeholder="Cari nama tag..." 
                       class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
            </div>
        </div>

        <!-- Table Master Tag -->
        <div class="overflow-x-auto border-t border-slate-200 pt-2">
            <table class="w-full text-left text-xs text-slate-800">
                <thead class="text-slate-400 font-bold uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-14">NO.</th>
                        <th class="py-3 px-4">NAMA TAG</th>
                        <th class="py-3 px-4">WARNA LABEL</th>
                        <th class="py-3 px-4">PREVIEW BADGE</th>
                        <th class="py-3 px-4 text-center w-32">ACTION</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tags as $idx => $tag)
                        <tr class="hover:bg-slate-50/75 transition" x-show="search === '' || '{{ strtolower($tag->name) }}'.includes(search.toLowerCase())">
                            <td class="py-3 px-4 text-slate-400 font-medium">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $tag->name }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-500">
                                <span class="inline-block w-3.5 h-3.5 rounded-full mr-1.5 align-middle border border-slate-200" style="background-color: {{ $tag->color ?: '#3b82f6' }};"></span>
                                {{ strtoupper($tag->color ?: '#3B82F6') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold text-white shadow-2xs" style="background-color: {{ $tag->color ?: '#3b82f6' }};">
                                    <i class="fa-solid fa-tag text-[9px] mr-1.5 opacity-80"></i>
                                    {{ $tag->name }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center space-x-2">
                                    <button type="button" 
                                            @click="openEditModal({{ json_encode($tag) }})"
                                            class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition" 
                                            title="Edit Tag">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <form method="POST" action="{{ route('master.tags.destroy', $tag->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tag {{ $tag->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition" title="Hapus Tag">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-tags text-2xl mb-2 text-slate-300 block"></i>
                                Belum ada Tag / Label Proyek. Klik "Tambah Tag Baru" untuk membuat tag pertama Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form (Tambah / Edit) -->
    <div x-show="modalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden transform transition-all"
             @click.away="modalOpen = false">
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-sm text-slate-800" x-text="editMode ? 'Edit Tag / Label' : 'Tambah Tag / Proyek Baru'"></h3>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="editMode ? '{{ url('master/tags') }}/' + editId : '{{ route('master.tags.store') }}'" 
                  method="POST" 
                  class="p-6 space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Tag / Proyek / Cabang <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           x-model="form.name"
                           required 
                           placeholder="Contoh: Proyek Web Revamp, Cabang Bekasi, Divisi IT"
                           class="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Warna Label
                    </label>
                    <div class="flex items-center space-x-2 mb-2">
                        <template x-for="c in colors" :key="c">
                            <button type="button" 
                                    @click="form.color = c" 
                                    class="w-7 h-7 rounded-full transition-transform border-2"
                                    :class="form.color.toLowerCase() === c.toLowerCase() ? 'scale-110 border-slate-800 shadow-sm' : 'border-transparent hover:scale-105'"
                                    :style="'background-color: ' + c">
                            </button>
                        </template>
                    </div>
                    <div class="flex items-center space-x-2 mt-2">
                        <input type="color" 
                               name="color" 
                               x-model="form.color" 
                               class="w-9 h-9 rounded-lg border border-slate-300 p-0.5 cursor-pointer bg-white">
                        <span class="text-xs font-mono text-slate-500" x-text="form.color.toUpperCase()"></span>
                    </div>
                </div>

                <!-- Preview Box -->
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">Tampilan Badge:</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold text-white shadow-2xs transition-colors" 
                          :style="'background-color: ' + (form.color || '#3b82f6')">
                        <i class="fa-solid fa-tag text-[10px] mr-1.5 opacity-80"></i>
                        <span x-text="form.name || 'Nama Tag'"></span>
                    </span>
                </div>

                <div class="pt-2 flex justify-end space-x-2 border-t border-slate-100">
                    <button type="button" 
                            @click="modalOpen = false" 
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                        Simpan Tag
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
