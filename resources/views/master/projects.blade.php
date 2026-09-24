@extends('layouts.app')

@section('title', 'Master Proyek (Job Costing)')

@section('content')
<div class="space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    editId: null,
    form: {
        code: '',
        name: '',
        department_id: '',
        contract_amount: 0,
        start_date: '',
        end_date: '',
        status: 'active',
        description: ''
    },
    openAddModal() {
        this.editMode = false;
        this.editId = null;
        this.form = {
            code: '',
            name: '',
            department_id: '{{ request('department_id', '') }}',
            contract_amount: '',
            start_date: '',
            end_date: '',
            status: 'active',
            description: ''
        };
        this.modalOpen = true;
    },
    openEditModal(prj) {
        this.editMode = true;
        this.editId = prj.id;
        this.form = {
            code: prj.code || '',
            name: prj.name,
            department_id: prj.department_id || '',
            contract_amount: prj.contract_amount || '',
            start_date: prj.start_date ? prj.start_date.substring(0, 10) : '',
            end_date: prj.end_date ? prj.end_date.substring(0, 10) : '',
            status: prj.status || 'active',
            description: prj.description || ''
        };
        this.modalOpen = true;
    }
}">

    <!-- Info Banner Card -->
    <div class="bg-gradient-to-r from-indigo-50 via-purple-50 to-blue-50 border border-indigo-200 rounded-2xl p-5 text-slate-700 shadow-sm flex items-start space-x-4">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-500/20">
            <i class="fa-solid fa-diagram-project text-base"></i>
        </div>
        <div>
            <h2 class="text-sm font-bold text-slate-900">Tentang Master Proyek (Job Costing)</h2>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                <strong>Proyek</strong> adalah aktivitas pekerjaan berbatas waktu yang dinaungi oleh <strong>Departemen</strong> (misal: <em>Project A TJL, Project B TJL, Pembangunan Gedung</em>). Setiap transaksi belanja material dan penerimaan termin pembayaran dapat dialokasikan ke Proyek untuk menghitung laba rugi per proyek (*Project Profitability*).
            </p>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 sm:p-6 space-y-4">
        
        <!-- Header, Filter & Action -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <button type="button" 
                    @click="openAddModal()" 
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm shadow-indigo-600/20 flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Proyek Baru</span>
            </button>

            <!-- Form Filter & Pencarian -->
            <form method="GET" action="{{ route('master.projects') }}" class="flex flex-wrap items-center gap-2">
                <!-- Filter Departemen -->
                <select name="department_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">-- Semua Departemen --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Status -->
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>Ditunda (On Hold)</option>
                </select>

                <!-- Input Pencarian -->
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari kode/nama proyek..." 
                           class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none w-48 sm:w-56 transition">
                </div>

                @if(request('search') || request('department_id') || request('status'))
                    <a href="{{ route('master.projects') }}" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition" title="Reset Filter">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Proyek -->
        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3.5 w-12 text-center">No.</th>
                        <th class="px-4 py-3.5">Kode</th>
                        <th class="px-4 py-3.5">Nama Proyek</th>
                        <th class="px-4 py-3.5">Departemen</th>
                        <th class="px-4 py-3.5 text-right">Nilai Kontrak / Anggaran</th>
                        <th class="px-4 py-3.5 text-center">Masa Kerja</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Transaksi</th>
                        <th class="px-4 py-3.5 text-right w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($projects as $index => $prj)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 text-center text-slate-400 font-medium">{{ $index + 1 }}</td>
                            <td class="px-4 py-3.5 font-mono font-bold text-slate-700">
                                @if($prj->code)
                                    <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-200 text-[11px]">{{ $prj->code }}</span>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900">{{ $prj->name }}</div>
                                @if($prj->description)
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5">{{ $prj->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if($prj->department)
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fa-solid fa-building-user text-[9px]"></i>
                                        <span>{{ $prj->department->name }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Tanpa Departemen</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-800">
                                Rp {{ number_format($prj->contract_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-center text-[11px] text-slate-500 whitespace-nowrap">
                                @if($prj->start_date || $prj->end_date)
                                    <span>{{ $prj->start_date ? $prj->start_date->format('d/m/Y') : '?' }}</span>
                                    <span class="mx-1 text-slate-300">&rarr;</span>
                                    <span>{{ $prj->end_date ? $prj->end_date->format('d/m/Y') : '?' }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($prj->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Berjalan
                                    </span>
                                @elseif($prj->status === 'completed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fa-solid fa-check text-[9px] mr-1"></i> Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Ditunda
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center text-slate-500 font-medium">
                                {{ $prj->transactions_count }}
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1 whitespace-nowrap">
                                <button type="button" 
                                        @click="openEditModal({{ json_encode($prj) }})" 
                                        class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition cursor-pointer" 
                                        title="Edit Proyek">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form method="POST" action="{{ route('master.projects.destroy', $prj->id) }}" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus proyek \'{{ $prj->name }}\'?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Hapus Proyek">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <i class="fa-solid fa-diagram-project text-xl"></i>
                                </div>
                                <p class="font-bold text-slate-600">Belum ada proyek yang terdaftar.</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Tambah Proyek Baru" untuk mendaftarkan proyek (misal: Project A, Project B) di bawah Departemen.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal Form Tambah / Edit Proyek -->
    <div x-show="modalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.away="modalOpen = false" 
             class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-150">
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-900 text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-diagram-project text-indigo-600"></i>
                    <span x-text="editMode ? 'Edit Data Proyek' : 'Tambah Proyek Baru'"></span>
                </h3>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 text-sm cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="editMode ? '{{ url('master/projects') }}/' + editId : '{{ route('master.projects.store') }}'" method="POST" class="p-6 space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Kode Proyek -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Proyek (Opsional)</label>
                        <input type="text" 
                               name="code" 
                               x-model="form.code" 
                               placeholder="Contoh: PRJ-TJL-01" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono uppercase focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>

                    <!-- Departemen Terkait -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Departemen / Divisi</label>
                        <select name="department_id" 
                                x-model="form.department_id" 
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                            <option value="">-- Pilih Departemen --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Nama Proyek -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Proyek <span class="text-rose-500">*</span></label>
                    <input type="text" 
                           name="name" 
                           x-model="form.name" 
                           required 
                           placeholder="Contoh: Project A - Pembangunan Mess TJL" 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nilai Kontrak / Anggaran -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Kontrak / Budget (Rp)</label>
                        <input type="number" 
                               step="any" 
                               name="contract_amount" 
                               x-model="form.contract_amount" 
                               placeholder="0" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>

                    <!-- Status Proyek -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Proyek <span class="text-rose-500">*</span></label>
                        <select name="status" 
                                x-model="form.status" 
                                required 
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                            <option value="active">Berjalan (Active)</option>
                            <option value="completed">Selesai (Completed)</option>
                            <option value="on_hold">Ditunda (On Hold)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Tanggal Mulai -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" 
                               name="start_date" 
                               x-model="form.start_date" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai (Target)</label>
                        <input type="date" 
                               name="end_date" 
                               x-model="form.end_date" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Catatan Proyek</label>
                    <textarea name="description" 
                              x-model="form.description" 
                              rows="3" 
                              placeholder="Deskripsi ruang lingkup, lokasi, atau klien..." 
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-indigo-600/20 cursor-pointer">
                        Simpan Proyek
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
