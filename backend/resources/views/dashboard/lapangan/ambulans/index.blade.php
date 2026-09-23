@extends('layouts.app-lapangan')

@section('title', 'Panggilan Ambulans Darurat - Sub Posko')

@section('content')
<div class="space-y-4 sm:space-y-6 max-w-7xl mx-auto font-sans">

    <!-- 1. HEADER RINGKAS RESPONSIF -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <!-- TAMPILAN MOBILE (SMARTPHONE) -->
        <div class="flex flex-col gap-3 sm:hidden">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('lapangan.dashboard') }}"
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 shrink-0 transition-colors shadow-2xs">
                        <x-heroicon-s-arrow-left class="w-4 h-4" />
                    </a>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 leading-tight">Ambulans Darurat</h1>
                        <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">Evakuasi medis pengungsi ke Posko Utama.</p>
                    </div>
                </div>

                <button onclick="openModalSos()" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <span>🆘</span> Panggil SOS
                </button>
            </div>
        </div>

        <!-- TAMPILAN DESKTOP / TABLET -->
        <div class="hidden sm:flex sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('lapangan.dashboard') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center shrink-0 transition-colors shadow-2xs" title="Kembali ke Dashboard">
                    <x-heroicon-s-arrow-left class="w-5 h-5" />
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Panggilan Ambulans Darurat</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Ajukan evakuasi medis darurat pengungsi ke Posko Komando Utama.</p>
                </div>
            </div>

            <button onclick="openModalSos()" class="px-5 py-3 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0">
                <span class="text-base">🆘</span> PANGGIL AMBULANS SEKARANG
            </button>
        </div>
    </div>

    <!-- 2. ACTIVE REQUEST BANNER (JIKA ADA PERMINTAAN AKTIF) -->
    @if($activeRequest)
        <div class="bg-gradient-to-r from-rose-600 to-amber-600 text-white p-4 sm:p-5 rounded-2xl shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-white/20 text-white text-[10px] font-bold rounded uppercase tracking-wider">STATUS AKTIF: {{ str_replace('_', ' ', strtoupper($activeRequest->status)) }}</span>
                    <span class="text-xs font-mono font-bold">{{ $activeRequest->kode_sos }}</span>
                </div>
                <h3 class="text-base sm:text-lg font-extrabold">Pasien: {{ $activeRequest->nama_pasien }}</h3>
                <p class="text-xs text-rose-100">Kondisi Medis: {{ $activeRequest->kondisi_medis }}</p>
                @if($activeRequest->armada)
                    <p class="text-xs font-semibold text-amber-200">🚑 Unit Armada: {{ $activeRequest->armada->nama_armada }} (Driver: {{ $activeRequest->armada->pengemudi ?? '-' }})</p>
                @else
                    <p class="text-xs italic text-rose-200">Menunggu Posko Komando memplot unit ambulans...</p>
                @endif
            </div>

            <div>
                <form action="{{ route('lapangan.ambulans.konfirmasi', $activeRequest->id) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Konfirmasi pasien telah tertangani / sampai di RS Rujukan?')" class="w-full sm:w-auto px-4 py-2.5 bg-white text-rose-700 hover:bg-rose-50 font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer text-center">
                        ✅ Konfirmasi Selesai Evakuasi
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- 3. RIWAYAT PANGGANG MEDIS DARURAT -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-5 space-y-3.5">
        <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2.5">Riwayat Panggilan Medis Darurat</h3>

        <!-- A. TAMPILAN MOBILE (KARTU / CARD VIEW KHUSUS HP) -->
        <div class="block sm:hidden space-y-3">
            @forelse($requests as $item)
                <div class="p-3.5 bg-slate-50/70 rounded-xl border border-slate-200/80 flex flex-col gap-2.5">
                    <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                        <span class="font-mono font-bold text-slate-900 text-xs">{{ $item->kode_sos }}</span>
                        <div>
                            @if($item->kategori_darurat == 'kritis_nyawa')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">🔴 Kritis Nyawa</span>
                            @elseif($item->kategori_darurat == 'berat')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">🟡 Berat</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">🟢 Sedang</span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 text-[10px] uppercase font-bold">Pasien:</span>
                            <span class="font-bold text-slate-900">{{ $item->nama_pasien }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase font-bold block">Kondisi Medis:</span>
                            <p class="text-slate-700 font-medium leading-snug">{{ $item->kondisi_medis }}</p>
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-slate-400 text-[10px] uppercase font-bold">Ambulans / RS:</span>
                            <span class="font-semibold text-slate-800">{{ $item->armada->nama_armada ?? 'Belum Ditentukan' }}</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase 
                            {{ $item->status == 'selesai' ? 'bg-emerald-100 text-emerald-800' : ($item->status == 'menunggu_penanganan' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ str_replace('_', ' ', $item->status) }}
                        </span>
                        <span class="text-slate-400 font-medium">{{ $item->waktu_request->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs italic bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                    Belum ada panggilan ambulans darurat yang tercatat.
                </div>
            @endforelse
        </div>

        <!-- B. TAMPILAN DESKTOP (TABEL BIASA UNTUK LAPTOP) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 text-xs font-bold uppercase bg-slate-50/80">
                        <th class="p-3">Kode SOS</th>
                        <th class="p-3">Nama Pasien</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Kondisi Medis</th>
                        <th class="p-3">Ambulans / RS Rujukan</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Waktu Request</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    @forelse($requests as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3 font-mono font-bold text-slate-900">{{ $item->kode_sos }}</td>
                            <td class="p-3 font-semibold text-slate-800">{{ $item->nama_pasien }}</td>
                            <td class="p-3">
                                @if($item->kategori_darurat == 'kritis_nyawa')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">🔴 Kritis Nyawa</span>
                                @elseif($item->kategori_darurat == 'berat')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">🟡 Berat</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">🟢 Sedang</span>
                                @endif
                            </td>
                            <td class="p-3 max-w-xs truncate" title="{{ $item->kondisi_medis }}">{{ $item->kondisi_medis }}</td>
                            <td class="p-3">
                                <p class="font-semibold text-slate-800">{{ $item->armada->nama_armada ?? 'Belum Ditentukan' }}</p>
                                <p class="text-[10px] text-slate-500">{{ $item->rs_rujukan ?? '-' }}</p>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase 
                                    {{ $item->status == 'selesai' ? 'bg-emerald-100 text-emerald-800' : ($item->status == 'menunggu_penanganan' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ str_replace('_', ' ', $item->status) }}
                                </span>
                            </td>
                            <td class="p-3 text-slate-500">{{ $item->waktu_request->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                Belum ada panggilan ambulans darurat yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL FORM REQUEST SOS -->
<div id="modalSos" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-100">
        <div class="bg-rose-600 text-white px-5 py-4 flex justify-between items-center">
            <h3 class="font-bold text-sm flex items-center gap-1.5">
                <span>🆘</span> Form Panggilan Ambulans Darurat
            </h3>
            <button onclick="closeModalSos()" class="text-rose-200 hover:text-white font-bold text-xl cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('lapangan.ambulans.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Pasien / Korban</label>
                <input type="text" name="nama_pasien" required placeholder="Contoh: Bpk. Slamet / Korban Longsor Anonim" class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tingkat Kegawatdaruratan Medis</label>
                <select name="kategori_darurat" required class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600">
                    <option value="kritis_nyawa">🔴 Kritis Nyawa (Ancaman Jiwa / Pendarahan Hebat)</option>
                    <option value="berat" selected>🟡 Berat (Patah Tulang / Tidak Bisa Berjalan)</option>
                    <option value="sedang">🟢 Sedang (Luka-luka ringan / Demam Tinggi)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi Kondisi Medis & Gejala</label>
                <textarea name="kondisi_medis" rows="3" required placeholder="Jelaskan detail luka atau penderitaan medis korban..." class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModalSos()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">Kirim Sinyal SOS</button>
            </div>
        </form>
    </div>
</div>

<!-- HANDLER SWEETALERT2 DARI SESSION BACKEND -->
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Berhasil! 🎉',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonColor: '#e11d48',
                customClass: { popup: 'rounded-2xl font-sans' }
            });
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Perhatian! ⚠️',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonColor: '#e11d48',
                customClass: { popup: 'rounded-2xl font-sans' }
            });
        });
    </script>
@endif
@endsection

@push('scripts')
<script>
    function openModalSos() {
        document.getElementById('modalSos').classList.remove('hidden');
    }
    function closeModalSos() {
        document.getElementById('modalSos').classList.add('hidden');
    }
</script>
@endpush