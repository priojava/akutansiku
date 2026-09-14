@extends('layouts.app')

@section('title', 'Master Pajak')

@section('content')
<div class="space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    editId: null,
    search: '',
    form: {
        name: '',
        rate: '',
        is_withholding: false,
        sales_account_id: '{{ $defaultSalesAccount ? $defaultSalesAccount->id : '' }}',
        purchase_account_id: '{{ $defaultPurchaseAccount ? $defaultPurchaseAccount->id : '' }}'
    },
    openAddModal() {
        this.editMode = false;
        this.editId = null;
        this.form = {
            name: '',
            rate: '',
            is_withholding: false,
            sales_account_id: '{{ $defaultSalesAccount ? $defaultSalesAccount->id : '' }}',
            purchase_account_id: '{{ $defaultPurchaseAccount ? $defaultPurchaseAccount->id : '' }}'
        };
        this.modalOpen = true;
    },
    openEditModal(tax) {
        this.editMode = true;
        this.editId = tax.id;
        this.form = {
            name: tax.name,
            rate: parseFloat(tax.rate),
            is_withholding: Boolean(tax.is_withholding),
            sales_account_id: tax.sales_account_id || '',
            purchase_account_id: tax.purchase_account_id || ''
        };
        this.modalOpen = true;
    }
}">

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-4">
        
        <!-- Header Button (Matching Screenshot 1) -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <button type="button" 
                    @click="openAddModal()" 
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center space-x-1.5 cursor-pointer">
                <span>Tambah Pajak</span>
            </button>

            <!-- Search Bar (Matching Screenshot 1 Cari...) -->
            <div class="w-full sm:w-64">
                <input type="text" 
                       x-model="search" 
                       placeholder="Cari..." 
                       class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
        </div>

        <!-- Table Master Pajak (Matching Screenshot 1 Exactly) -->
        <div class="overflow-x-auto border-t border-slate-200 pt-2">
            <table class="w-full text-left text-xs text-slate-800">
                <thead class="text-slate-400 font-bold uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-14">NO.</th>
                        <th class="py-3 px-4">NAMA</th>
                        <th class="py-3 px-4 w-28">JUMLAH</th>
                        <th class="py-3 px-4">PENJUALAN</th>
                        <th class="py-3 px-4">PEMBELIAN</th>
                        <th class="py-3 px-4 text-center w-28">ACTION</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($taxes as $idx => $tax)
                        <tr class="hover:bg-slate-50/75 transition" x-show="search === '' || '{{ strtolower($tax->name) }}'.includes(search.toLowerCase())">
                            <td class="py-3 px-4 text-slate-400 font-medium">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $tax->name }}
                                @if($tax->is_withholding)
                                    <span class="ml-1.5 px-2 py-0.5 rounded text-[10px] bg-amber-50 text-amber-700 border border-amber-200 font-medium">Pemotongan</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-700">{{ rtrim(rtrim(number_format($tax->rate, 2), '0'), '.') }}%</td>
                            <td class="py-3 px-4 text-slate-600">
                                @if($tax->salesAccount)
                                    <span class="font-mono text-[11px] text-slate-500">{{ $tax->salesAccount->code }}</span> - {{ $tax->salesAccount->name }}
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                @if($tax->purchaseAccount)
                                    <span class="font-mono text-[11px] text-slate-500">{{ $tax->purchaseAccount->code }}</span> - {{ $tax->purchaseAccount->name }}
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button type="button" 
                                            @click="openEditModal({{ json_encode($tax) }})" 
                                            class="p-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs transition" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form method="POST" action="{{ route('master.taxes.destroy', $tax->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tarif pajak ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-xs transition" title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                No data available in table
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls (Matching Screenshot 1 footer « ← → ») -->
        <div class="flex items-center justify-center space-x-3 pt-4 border-t border-slate-100 text-slate-400 text-xs font-semibold">
            <span class="hover:text-blue-600 cursor-pointer">&laquo;</span>
            <span class="hover:text-blue-600 cursor-pointer">&larr;</span>
            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-200">1</span>
            <span class="hover:text-blue-600 cursor-pointer">&rarr;</span>
            <span class="hover:text-blue-600 cursor-pointer">&raquo;</span>
        </div>

    </div>

    <!-- MODAL TAMBAH / EDIT DATA (Matching Screenshot 2 Exactly) -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-150" @click.away="modalOpen = false">
            
            <!-- Modal Header -->
            <div class="p-4 border-b border-slate-100 bg-white">
                <h3 class="font-bold text-slate-900 text-sm" x-text="editMode ? 'Edit Data Pajak' : 'Tambah Data'">Tambah Data</h3>
            </div>

            <form :action="editMode ? '{{ url('master/taxes') }}/' + editId : '{{ route('master.taxes.store') }}'" method="POST">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Modal Body (Matching Screenshot 2 Form Fields) -->
                <div class="p-5 space-y-4 text-xs">
                    
                    <!-- Row 1: Nama & Jumlah (%) -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama</label>
                            <input type="text" 
                                   name="name" 
                                   x-model="form.name" 
                                   required 
                                   placeholder="Contoh: PPN 11%" 
                                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jumlah (%)</label>
                            <input type="number" 
                                   step="0.01" 
                                   name="rate" 
                                   x-model="form.rate" 
                                   required 
                                   placeholder="11" 
                                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 font-medium">
                        </div>
                    </div>

                    <!-- Row 2: Checkbox Pemotongan -->
                    <div class="flex items-center space-x-2 pt-1">
                        <input type="checkbox" 
                               id="is_withholding" 
                               name="is_withholding" 
                               value="1" 
                               x-model="form.is_withholding" 
                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <label for="is_withholding" class="text-xs font-semibold text-slate-700 cursor-pointer select-none">Pemotongan</label>
                    </div>

                    <!-- Row 3: Akun pajak saat penjualan -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Akun pajak saat penjualan</label>
                        <select name="sales_account_id" 
                                x-model="form.sales_account_id" 
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-xs">
                            <option value="">-- Pilih Akun --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->code }} - {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Row 4: Akun pajak saat pembelian -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Akun pajak saat pembelian</label>
                        <select name="purchase_account_id" 
                                x-model="form.purchase_account_id" 
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-xs">
                            <option value="">-- Pilih Akun --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->code }} - {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <!-- Modal Footer (Matching Screenshot 2 Close & Green Submit Buttons) -->
                <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end space-x-2">
                    <button type="button" 
                            @click="modalOpen = false" 
                            class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold transition">
                        Close
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 bg-lime-600 hover:bg-lime-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                        Submit
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
