@props(['stoks' => []])

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden p-3.5 sm:p-6 font-sans">
    <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-3.5 flex items-center gap-2 border-b border-slate-100 pb-3">
        <div class="p-1.5 bg-blue-50 text-blue-600 rounded-lg shrink-0">
            <x-heroicon-s-cube class="w-4 h-4 sm:w-5 sm:h-5" />
        </div>
        <span>Stok Logistik Tersedia di Pos Lapangan</span>
    </h3>

    <!-- 1. TAMPILAN MOBILE: KARTU RINGKAS (HP) -->
    <div class="block sm:hidden space-y-2.5">
        @forelse($stoks as $stok)
            @php
                $jumlah = $stok->jumlah ?? 0;
            @endphp
            <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-200/70 flex flex-col gap-2">
                <div class="flex items-start justify-between gap-2 border-b border-slate-200/50 pb-1.5">
                    <div>
                        <h4 class="font-bold text-slate-900 text-xs leading-snug">
                            {{ $stok->nama_barang ?? $stok->nama }}
                        </h4>
                        <span class="text-[10px] text-slate-500 font-medium">
                            {{ $stok->kategori ?? 'Logistik Umum' }}
                        </span>
                    </div>
                    <div>
                        @if ($jumlah <= 0)
                            <span class="px-2 py-0.5 text-[10px] bg-rose-100 text-rose-800 rounded-full font-bold border border-rose-200">Habis</span>
                        @elseif($jumlah <= 10)
                            <span class="px-2 py-0.5 text-[10px] bg-amber-100 text-amber-800 rounded-full font-bold border border-amber-200">Menipis</span>
                        @else
                            <span class="px-2 py-0.5 text-[10px] bg-emerald-100 text-emerald-800 rounded-full font-bold border border-emerald-200">Aman</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-0.5">
                    <div>
                        <span class="text-slate-400 block text-[9px] uppercase font-bold">Sisa Stok</span>
                        <span class="font-bold text-xs {{ $jumlah <= 10 ? 'text-amber-700' : 'text-emerald-700' }}">
                            {{ $jumlah }} {{ $stok->satuan ?? 'Unit' }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 block text-[9px] uppercase font-bold">Diperbarui</span>
                        <span class="text-slate-500 text-[11px] font-medium">
                            {{ $stok->updated_at ? $stok->updated_at->format('d M H:i') : '-' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-6 text-center text-slate-400 text-xs font-medium bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                Belum ada data stok inventaris yang terdaftar di pos lapangan ini.
            </div>
        @endforelse
    </div>

    <!-- 2. TAMPILAN DESKTOP: TABEL STANDAR (KOMPUTER) -->
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    <th class="py-3 px-4">Nama Barang</th>
                    <th class="py-3 px-4">Kategori</th>
                    <th class="py-3 px-4">Jumlah / Stok</th>
                    <th class="py-3 px-4">Kondisi</th>
                    <th class="py-3 px-4 text-right">Terakhir Diperbarui</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm text-slate-700">
                @forelse($stoks as $stok)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $stok->nama_barang ?? $stok->nama }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $stok->kategori ?? 'Logistik Umum' }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold {{ ($stok->jumlah ?? 0) <= 10 ? 'text-amber-600' : 'text-emerald-600' }}">
                            {{ $stok->jumlah ?? 0 }} {{ $stok->satuan ?? 'Unit' }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if (($stok->jumlah ?? 0) <= 0)
                                <span class="px-2.5 py-0.5 text-xs bg-rose-50 text-rose-700 rounded-md font-medium border border-rose-200">Habis</span>
                            @elseif(($stok->jumlah ?? 0) <= 10)
                                <span class="px-2.5 py-0.5 text-xs bg-amber-50 text-amber-700 rounded-md font-medium border border-amber-200">Menipis</span>
                            @else
                                <span class="px-2.5 py-0.5 text-xs bg-emerald-50 text-emerald-700 rounded-md font-medium border border-emerald-200">Aman</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right text-xs text-slate-400">
                            {{ $stok->updated_at ? $stok->updated_at->format('d M Y, H:i') . ' WIB' : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 text-xs font-medium">
                            Belum ada data stok inventaris yang terdaftar di pos lapangan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>