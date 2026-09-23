@props(['pengirimans', 'pengajuans' => []])

@php
    // Kumpulkan ID Pengajuan yang sudah memiliki record pengiriman
    $pengajuanIdsInPengiriman = $pengirimans->pluck('pengajuan_id')->filter()->toArray();

    // Filter pengajuan yang belum masuk ke tabel pengiriman
    $pengajuanUnsent = collect($pengajuans)->filter(function ($p) use ($pengajuanIdsInPengiriman) {
        return !in_array($p->id, $pengajuanIdsInPengiriman);
    });
@endphp

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden p-3.5 sm:p-6 font-sans">
    <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-3.5 flex items-center gap-2 border-b border-slate-100 pb-3">
        <div class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg shrink-0">
            <x-heroicon-s-truck class="w-4 h-4 sm:w-5 sm:h-5" />
        </div>
        <span>Status Pengiriman Logistik dari Posko Komando</span>
    </h3>

    <!-- 1. TAMPILAN MOBILE: KARTU SERBA RAPI (HP / LAYAR KECIL) -->
    <div class="block sm:hidden space-y-3">
        <!-- Render Pengajuan yang Masih Pending / Menunggu Verifikasi -->
        @foreach($pengajuanUnsent as $p)
            @php
                $details = [];
                if ($p->beras_kg > 0) $details[] = ['nama' => 'Beras', 'jumlah' => $p->beras_kg . ' Kg'];
                if ($p->air_minum_dus > 0) $details[] = ['nama' => 'Air Minum', 'jumlah' => $p->air_minum_dus . ' Dus'];
                if ($p->makanan_kaleng_pack > 0) $details[] = ['nama' => 'Makanan Kaleng', 'jumlah' => $p->makanan_kaleng_pack . ' Pack'];
                if ($p->makanan_bayi_pack > 0) $details[] = ['nama' => 'Makanan Bayi', 'jumlah' => $p->makanan_bayi_pack . ' Pack'];
                if ($p->minyak_goreng_liter > 0) $details[] = ['nama' => 'Minyak Goreng', 'jumlah' => $p->minyak_goreng_liter . ' L'];
                if ($p->popok_bayi_pcs > 0) $details[] = ['nama' => 'Popok Bayi', 'jumlah' => $p->popok_bayi_pcs . ' Pcs'];
                if ($p->popok_dewasa_pcs > 0) $details[] = ['nama' => 'Popok Dewasa', 'jumlah' => $p->popok_dewasa_pcs . ' Pcs'];
                if ($p->pembalut_wanita_pack > 0) $details[] = ['nama' => 'Pembalut', 'jumlah' => $p->pembalut_wanita_pack . ' Pack'];
                if ($p->hygiene_kit_paket > 0) $details[] = ['nama' => 'Hygiene Kit', 'jumlah' => $p->hygiene_kit_paket . ' Pkt'];
                if ($p->selimut_pcs > 0) $details[] = ['nama' => 'Selimut', 'jumlah' => $p->selimut_pcs . ' Pcs'];
                if ($p->matras_terpal_pcs > 0) $details[] = ['nama' => 'Matras/Terpal', 'jumlah' => $p->matras_terpal_pcs . ' Pcs'];
                if ($p->obat_p3k_paket > 0) $details[] = ['nama' => 'Obat P3K', 'jumlah' => $p->obat_p3k_paket . ' Pkt'];
            @endphp
            <div class="p-3.5 bg-amber-50/50 rounded-xl border border-amber-200/80 flex flex-col gap-2.5">
                <div class="flex items-center justify-between border-b border-amber-200/60 pb-2">
                    <span class="font-mono font-bold text-slate-900 text-xs">#{{ $p->kode_pengajuan }}</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded-full inline-flex items-center gap-1 border border-amber-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu Verifikasi
                    </span>
                </div>
                
                <div class="space-y-1.5">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Item Diajukan</span>
                    <!-- ITEM BADGES FLEX CHIPS -->
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($details as $d)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-white text-slate-700 border border-amber-200/80 shadow-2xs">
                                {{ $d['nama'] }}: <strong class="ml-1 text-slate-900">{{ $d['jumlah'] }}</strong>
                            </span>
                        @empty
                            <span class="text-xs text-slate-500 italic">Logistik Bantuan</span>
                        @endforelse
                    </div>
                </div>

                <div class="pt-2 border-t border-amber-200/50 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400 font-medium">Waktu Pengajuan:</span>
                    <span class="text-amber-900 font-bold">{{ \Carbon\Carbon::parse($p->created_at)->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>
        @endforeach

        <!-- Render Pengajuan yang Sudah Memiliki Status Pengiriman -->
        @forelse($pengirimans as $item)
            @php
                $p = $item->pengajuan;
                $details = [];
                if ($p) {
                    if ($p->beras_kg > 0) $details[] = ['nama' => 'Beras', 'jumlah' => $p->beras_kg . ' Kg'];
                    if ($p->air_minum_dus > 0) $details[] = ['nama' => 'Air Minum', 'jumlah' => $p->air_minum_dus . ' Dus'];
                    if ($p->makanan_kaleng_pack > 0) $details[] = ['nama' => 'Makanan Kaleng', 'jumlah' => $p->makanan_kaleng_pack . ' Pack'];
                    if ($p->makanan_bayi_pack > 0) $details[] = ['nama' => 'Makanan Bayi', 'jumlah' => $p->makanan_bayi_pack . ' Pack'];
                    if ($p->minyak_goreng_liter > 0) $details[] = ['nama' => 'Minyak Goreng', 'jumlah' => $p->minyak_goreng_liter . ' L'];
                    if ($p->popok_bayi_pcs > 0) $details[] = ['nama' => 'Popok Bayi', 'jumlah' => $p->popok_bayi_pcs . ' Pcs'];
                    if ($p->popok_dewasa_pcs > 0) $details[] = ['nama' => 'Popok Dewasa', 'jumlah' => $p->popok_dewasa_pcs . ' Pcs'];
                    if ($p->pembalut_wanita_pack > 0) $details[] = ['nama' => 'Pembalut', 'jumlah' => $p->pembalut_wanita_pack . ' Pack'];
                    if ($p->hygiene_kit_paket > 0) $details[] = ['nama' => 'Hygiene Kit', 'jumlah' => $p->hygiene_kit_paket . ' Pkt'];
                    if ($p->selimut_pcs > 0) $details[] = ['nama' => 'Selimut', 'jumlah' => $p->selimut_pcs . ' Pcs'];
                    if ($p->matras_terpal_pcs > 0) $details[] = ['nama' => 'Matras/Terpal', 'jumlah' => $p->matras_terpal_pcs . ' Pcs'];
                    if ($p->obat_p3k_paket > 0) $details[] = ['nama' => 'Obat P3K', 'jumlah' => $p->obat_p3k_paket . ' Pkt'];
                }

                $statusDistribusi = strtolower($item->status_distribusi ?? '');
                $statusPengajuan = strtolower($p->status ?? '');

                $isSelesai = in_array($statusDistribusi, ['selesai', 'diterima di posko']) || $statusPengajuan == 'selesai';
                $isDalamPengiriman = in_array($statusDistribusi, ['dalam_pengiriman', 'dalam_perjalanan']) || $statusPengajuan == 'dalam_pengiriman';
                $isDisetujui = in_array($statusPengajuan, ['disetujui', 'disetujui_sebagian']) && !$isDalamPengiriman && !$isSelesai;
            @endphp

            <div class="p-3.5 bg-white rounded-xl border border-slate-200/80 shadow-2xs flex flex-col gap-2.5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <span class="font-mono font-bold text-slate-900 text-xs">#{{ $p->kode_pengajuan ?? 'REQ-' . $item->id }}</span>
                    <div>
                        @if ($isSelesai)
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded-full inline-flex items-center gap-1 border border-emerald-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Diterima
                            </span>
                        @elseif($isDalamPengiriman)
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-800 rounded-full inline-flex items-center gap-1 border border-blue-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Dalam Pengiriman
                            </span>
                        @elseif($isDisetujui)
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded-full inline-flex items-center gap-1 border border-amber-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Disetujui
                            </span>
                        @else
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-700 rounded-full border border-slate-200">
                                {{ ucfirst(str_replace('_', ' ', $item->status_distribusi)) }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="space-y-1.5">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Item Dikirim</span>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($details as $d)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-50 text-slate-700 border border-slate-200">
                                {{ $d['nama'] }}: <strong class="ml-1 text-slate-900">{{ $d['jumlah'] }}</strong>
                            </span>
                        @empty
                            <span class="text-xs text-slate-500 italic">Logistik Bantuan Bencana</span>
                        @endforelse
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400 font-medium">Waktu Sampai/Estimasi:</span>
                    <span class="font-bold">
                        @if ($item->waktu_diterima)
                            <span class="text-emerald-700">{{ \Carbon\Carbon::parse($item->waktu_diterima)->format('d M Y, H:i') }} WIB</span>
                        @elseif($isDalamPengiriman)
                            <span class="text-blue-700">{{ $item->estimasi_waktu ?? 'Dalam Perjalanan' }}</span>
                        @else
                            <span class="text-slate-400 italic">Menunggu Pengiriman</span>
                        @endif
                    </span>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    @if ($isDalamPengiriman)
                        <button type="button"
                            onclick="openDetailModal('{{ $p->kode_pengajuan ?? 'REQ-' . $item->id }}', '{{ route('lapangan.stok.konfirmasi', $item->id) }}', {{ json_encode($details) }}, '{{ $item->status_distribusi }}', '{{ $p->catatan_komando ?? '' }}')"
                            class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                            <x-heroicon-s-check-circle class="w-4 h-4 shrink-0" />
                            <span>Konfirmasi Sampai</span>
                        </button>
                    @else
                        <button type="button"
                            onclick="openDetailModal('{{ $p->kode_pengajuan ?? 'REQ-' . $item->id }}', null, {{ json_encode($details) }}, '{{ $item->status_distribusi }}', '{{ $p->catatan_komando ?? '' }}')"
                            class="w-full py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition text-center cursor-pointer">
                            Lihat Detail
                        </button>
                    @endif
                </div>
            </div>
        @empty
            @if(count($pengajuanUnsent) === 0)
                <div class="py-6 text-center text-slate-400 text-xs font-medium bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                    Belum ada pengiriman logistik aktif dari Posko Komando.
                </div>
            @endif
        @endforelse
    </div>

    <!-- 2. TAMPILAN DESKTOP: TABEL STANDAR (KOMPUTER/TABLET) -->
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    <th class="py-3 px-4">ID Pengajuan</th>
                    <th class="py-3 px-4">Item Dikirim</th>
                    <th class="py-3 px-4">Status Distribusi</th>
                    <th class="py-3 px-4">Estimasi / Waktu Sampai</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm text-slate-700">
                @foreach($pengajuanUnsent as $p)
                    @php
                        $details = [];
                        if ($p->beras_kg > 0) $details[] = ['nama' => 'Beras', 'jumlah' => $p->beras_kg . ' Kg'];
                        if ($p->air_minum_dus > 0) $details[] = ['nama' => 'Air Minum', 'jumlah' => $p->air_minum_dus . ' Dus'];
                        if ($p->makanan_kaleng_pack > 0) $details[] = ['nama' => 'Makanan Kaleng', 'jumlah' => $p->makanan_kaleng_pack . ' Pack'];
                        if ($p->makanan_bayi_pack > 0) $details[] = ['nama' => 'Makanan Bayi', 'jumlah' => $p->makanan_bayi_pack . ' Pack'];
                        if ($p->minyak_goreng_liter > 0) $details[] = ['nama' => 'Minyak Goreng', 'jumlah' => $p->minyak_goreng_liter . ' Liter'];
                        if ($p->popok_bayi_pcs > 0) $details[] = ['nama' => 'Popok Bayi', 'jumlah' => $p->popok_bayi_pcs . ' Pcs'];
                        if ($p->popok_dewasa_pcs > 0) $details[] = ['nama' => 'Popok Dewasa', 'jumlah' => $p->popok_dewasa_pcs . ' Pcs'];
                        if ($p->pembalut_wanita_pack > 0) $details[] = ['nama' => 'Pembalut Wanita', 'jumlah' => $p->pembalut_wanita_pack . ' Pack'];
                        if ($p->hygiene_kit_paket > 0) $details[] = ['nama' => 'Hygiene Kit', 'jumlah' => $p->hygiene_kit_paket . ' Paket'];
                        if ($p->selimut_pcs > 0) $details[] = ['nama' => 'Selimut', 'jumlah' => $p->selimut_pcs . ' Pcs'];
                        if ($p->matras_terpal_pcs > 0) $details[] = ['nama' => 'Matras / Terpal', 'jumlah' => $p->matras_terpal_pcs . ' Pcs'];
                        if ($p->obat_p3k_paket > 0) $details[] = ['nama' => 'Obat-obatan / P3K', 'jumlah' => $p->obat_p3k_paket . ' Paket'];
                    @endphp
                    <tr class="bg-amber-50/30 hover:bg-amber-50/60 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-900">#{{ $p->kode_pengajuan }}</td>
                        <td class="py-3.5 px-4 max-w-xs">
                            <div class="truncate text-slate-800 font-medium">
                                {{ count($details) > 0 ? implode(', ', array_map(fn($d) => $d['nama'] . ' (' . $d['jumlah'] . ')', array_slice($details, 0, 3))) : 'Logistik Bantuan' }}
                                @if (count($details) > 3)
                                    <span class="text-xs text-blue-600 font-bold">+{{ count($details) - 3 }} barang lagi</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800 rounded-full inline-flex items-center gap-1.5 border border-amber-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu Verifikasi Komando
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 italic text-xs">
                            Diajukan {{ \Carbon\Carbon::parse($p->created_at)->format('d M Y, H:i') }} WIB
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button type="button"
                                onclick="openDetailModal('{{ $p->kode_pengajuan }}', null, {{ json_encode($details) }}, 'Menunggu Verifikasi', 'Pengajuan baru berhasil dibuat dan menunggu persetujuan Posko Komando.')"
                                class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-semibold transition cursor-pointer">
                                Lihat Detail
                            </button>
                        </td>
                    </tr>
                @endforeach

                @forelse($pengirimans as $item)
                    @php
                        $p = $item->pengajuan;
                        $details = [];
                        if ($p) {
                            if ($p->beras_kg > 0) $details[] = ['nama' => 'Beras', 'jumlah' => $p->beras_kg . ' Kg'];
                            if ($p->air_minum_dus > 0) $details[] = ['nama' => 'Air Minum', 'jumlah' => $p->air_minum_dus . ' Dus'];
                            if ($p->makanan_kaleng_pack > 0) $details[] = ['nama' => 'Makanan Kaleng', 'jumlah' => $p->makanan_kaleng_pack . ' Pack'];
                            if ($p->makanan_bayi_pack > 0) $details[] = ['nama' => 'Makanan Bayi', 'jumlah' => $p->makanan_bayi_pack . ' Pack'];
                            if ($p->minyak_goreng_liter > 0) $details[] = ['nama' => 'Minyak Goreng', 'jumlah' => $p->minyak_goreng_liter . ' Liter'];
                            if ($p->popok_bayi_pcs > 0) $details[] = ['nama' => 'Popok Bayi', 'jumlah' => $p->popok_bayi_pcs . ' Pcs'];
                            if ($p->popok_dewasa_pcs > 0) $details[] = ['nama' => 'Popok Dewasa', 'jumlah' => $p->popok_dewasa_pcs . ' Pcs'];
                            if ($p->pembalut_wanita_pack > 0) $details[] = ['nama' => 'Pembalut Wanita', 'jumlah' => $p->pembalut_wanita_pack . ' Pack'];
                            if ($p->hygiene_kit_paket > 0) $details[] = ['nama' => 'Hygiene Kit', 'jumlah' => $p->hygiene_kit_paket . ' Paket'];
                            if ($p->selimut_pcs > 0) $details[] = ['nama' => 'Selimut', 'jumlah' => $p->selimut_pcs . ' Pcs'];
                            if ($p->matras_terpal_pcs > 0) $details[] = ['nama' => 'Matras / Terpal', 'jumlah' => $p->matras_terpal_pcs . ' Pcs'];
                            if ($p->obat_p3k_paket > 0) $details[] = ['nama' => 'Obat-obatan / P3K', 'jumlah' => $p->obat_p3k_paket . ' Paket'];
                        }

                        $statusDistribusi = strtolower($item->status_distribusi ?? '');
                        $statusPengajuan = strtolower($p->status ?? '');

                        $isSelesai = in_array($statusDistribusi, ['selesai', 'diterima di posko']) || $statusPengajuan == 'selesai';
                        $isDalamPengiriman = in_array($statusDistribusi, ['dalam_pengiriman', 'dalam_perjalanan']) || $statusPengajuan == 'dalam_pengiriman';
                        $isDisetujui = in_array($statusPengajuan, ['disetujui', 'disetujui_sebagian']) && !$isDalamPengiriman && !$isSelesai;
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-900">#{{ $p->kode_pengajuan ?? 'REQ-' . $item->id }}</td>
                        <td class="py-3.5 px-4 max-w-xs">
                            <div class="truncate text-slate-800 font-medium">
                                {{ count($details) > 0 ? implode(', ', array_map(fn($d) => $d['nama'] . ' (' . $d['jumlah'] . ')', array_slice($details, 0, 3))) : 'Logistik Bantuan' }}
                                @if (count($details) > 3)
                                    <span class="text-xs text-blue-600 font-bold">+{{ count($details) - 3 }} barang lagi</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if ($isSelesai)
                                <span class="px-3 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full inline-flex items-center gap-1.5 border border-emerald-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Diterima di Posko
                                </span>
                            @elseif($isDalamPengiriman)
                                <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full inline-flex items-center gap-1.5 border border-blue-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Dalam Pengiriman
                                </span>
                            @elseif($isDisetujui)
                                <span class="px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-700 rounded-full inline-flex items-center gap-1.5 border border-amber-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Disetujui
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700 rounded-full inline-flex items-center gap-1.5">
                                    {{ ucfirst(str_replace('_', ' ', $item->status_distribusi)) }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            @if ($item->waktu_diterima)
                                <span class="text-emerald-700 font-medium">{{ \Carbon\Carbon::parse($item->waktu_diterima)->format('d M Y, H:i') }} WIB</span>
                            @elseif($isDalamPengiriman)
                                <span class="text-blue-700 font-medium">{{ $item->estimasi_waktu ?? 'Dalam Perjalanan' }}</span>
                            @else
                                <span class="text-slate-400 italic">Menunggu Pengiriman</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if ($isDalamPengiriman)
                                <button type="button"
                                    onclick="openDetailModal('{{ $p->kode_pengajuan ?? 'REQ-' . $item->id }}', '{{ route('lapangan.stok.konfirmasi', $item->id) }}', {{ json_encode($details) }}, '{{ $item->status_distribusi }}', '{{ $p->catatan_komando ?? '' }}')"
                                    class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer inline-flex items-center gap-1.5">
                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5 shrink-0" />
                                    <span>Konfirmasi Sampai</span>
                                </button>
                            @else
                                <button type="button"
                                    onclick="openDetailModal('{{ $p->kode_pengajuan ?? 'REQ-' . $item->id }}', null, {{ json_encode($details) }}, '{{ $item->status_distribusi }}', '{{ $p->catatan_komando ?? '' }}')"
                                    class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-semibold transition cursor-pointer">
                                    Lihat Detail
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    @if(count($pengajuanUnsent) === 0)
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs font-medium">
                                Belum ada pengiriman logistik aktif dari Posko Komando.
                            </td>
                        </tr>
                    @endif
                @endforelse
            </tbody>
        </table>
    </div>
</div>