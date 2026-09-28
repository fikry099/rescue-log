<?php

namespace App\Http\Controllers\Lapangan;

use App\Http\Controllers\Controller;
use App\Models\PengajuanKebutuhan;
use App\Models\PengirimanInventaris;
use App\Models\StokInventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StokController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $poskoId = $user->posko_id;

        // 1. Ambil Seluruh Pengajuan Logistik Sub-Posko ini
        $pengajuans = PengajuanKebutuhan::with(['pengiriman'])
            ->where(function ($q) use ($user, $poskoId) {
                if (!empty($poskoId) && Schema::hasColumn('pengajuan_kebutuhan', 'posko_id')) {
                    $q->where('posko_id', $poskoId);
                } else {
                    $q->where('user_id', $user->id);
                }
            })
            ->latest()
            ->get();

        // 2. Ambil Pengiriman Inventaris ke Sub-Posko ini (Aman dari null relation)
        $pengirimansQuery = PengirimanInventaris::with(['pengajuan', 'posko']);

        if (!empty($poskoId)) {
            $pengirimansQuery->where(function ($q) use ($poskoId) {
                $q->where('posko_id', $poskoId)
                  ->orWhereHas('pengajuan', function ($pq) use ($poskoId) {
                      $pq->where('posko_id', $poskoId);
                  });
            });
        } else {
            // Jika user belum punya posko_id, hanya ambil pengajuan berdasarkan user_id
            $pengirimansQuery->whereHas('pengajuan', function ($pq) use ($user) {
                $pq->where('user_id', $user->id);
            });
        }

        $pengirimans = $pengirimansQuery->latest()->get();

        // 3. Ambil Stok HANYA milik Posko ini
        $stoks = collect();
        if (!empty($poskoId)) {
            $stoks = StokInventaris::where('posko_id', $poskoId)
                ->select('nama_barang', 'kategori', 'satuan')
                ->selectRaw('SUM(jumlah) as jumlah')
                ->selectRaw('MAX(updated_at) as updated_at')
                ->groupBy('nama_barang', 'kategori', 'satuan')
                ->get();
        }

        return view('dashboard.lapangan.stok.index', compact('pengirimans', 'stoks', 'pengajuans'));
    }

    public function konfirmasiSampai($id)
    {
        $pengiriman = PengirimanInventaris::with(['pengajuan'])->where('id', $id)->firstOrFail();

        $statusCurrent = strtolower($pengiriman->status_distribusi ?? '');
        if (in_array($statusCurrent, ['diterima di posko', 'selesai', 'sampai'])) {
            return redirect()->back()->with('success', 'Pengiriman ini sudah dikonfirmasi sebelumnya.');
        }

        DB::transaction(function () use ($pengiriman) {
            $waktuSekarang = now();

            // 1. Update status pengiriman
            $pengiriman->update([
                'status_distribusi' => 'Diterima di Posko',
                'waktu_diterima'    => $waktuSekarang,
            ]);

            // 2. Update status pengajuan & kreditkan stok ke Sub-Posko
            $p = $pengiriman->pengajuan;
            if ($p) {
                $p->update(['status' => 'selesai']);

                $poskoId = $pengiriman->posko_id ?? ($p->posko_id ?? Auth::user()->posko_id);

                if ($poskoId) {
                    // Mapping 12 item baku (Nama diselaraskan dengan Master Seeder)
                    $items = [
                        ['nama' => 'Beras', 'kategori' => 'Makanan Pokok', 'jumlah' => $p->beras_kg ?? 0, 'satuan' => 'Kg'],
                        ['nama' => 'Air Minum', 'kategori' => 'Konsumsi', 'jumlah' => $p->air_minum_dus ?? 0, 'satuan' => 'Dus'],
                        ['nama' => 'Makanan Kaleng', 'kategori' => 'Makanan Cepat Saji', 'jumlah' => $p->makanan_kaleng_pack ?? 0, 'satuan' => 'Pack'],
                        ['nama' => 'Makanan Bayi', 'kategori' => 'Nutrisi Bayi', 'jumlah' => $p->makanan_bayi_pack ?? 0, 'satuan' => 'Pack'],
                        ['nama' => 'Minyak Goreng', 'kategori' => 'Bahan Pokok', 'jumlah' => $p->minyak_goreng_liter ?? 0, 'satuan' => 'Liter'],
                        ['nama' => 'Popok Bayi', 'kategori' => 'Kebutuhan Bayi', 'jumlah' => $p->popok_bayi_pcs ?? 0, 'satuan' => 'Pcs'],
                        ['nama' => 'Popok Dewasa', 'kategori' => 'Sanitasi', 'jumlah' => $p->popok_dewasa_pcs ?? 0, 'satuan' => 'Pcs'],
                        ['nama' => 'Pembalut Wanita', 'kategori' => 'Sanitasi', 'jumlah' => $p->pembalut_wanita_pack ?? 0, 'satuan' => 'Pack'],
                        ['nama' => 'Hygiene Kit', 'kategori' => 'Kebersihan', 'jumlah' => $p->hygiene_kit_paket ?? 0, 'satuan' => 'Paket'],
                        ['nama' => 'Selimut', 'kategori' => 'Perlengkapan', 'jumlah' => $p->selimut_pcs ?? 0, 'satuan' => 'Pcs'],
                        ['nama' => 'Matras / Terpal', 'kategori' => 'Tenda/Perlengkapan', 'jumlah' => $p->matras_terpal_pcs ?? 0, 'satuan' => 'Pcs'],
                        ['nama' => 'Obat P3K', 'kategori' => 'Kesehatan', 'jumlah' => $p->obat_p3k_paket ?? 0, 'satuan' => 'Paket'],
                    ];

                    foreach ($items as $item) {
                        if ($item['jumlah'] > 0) {
                            $jumlahFix = (float) $item['jumlah'];

                            $stokExisting = StokInventaris::where('nama_barang', $item['nama'])
                                ->where('posko_id', $poskoId)
                                ->first();

                            if ($stokExisting) {
                                $stokExisting->increment('jumlah', $jumlahFix);
                            } else {
                                StokInventaris::create([
                                    'posko_id'    => $poskoId,
                                    'nama_barang' => $item['nama'],
                                    'kategori'    => $item['kategori'],
                                    'jumlah'      => $jumlahFix,
                                    'satuan'      => $item['satuan'],
                                ]);
                            }
                        }
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Logistik telah diterima dan otomatis menambahkan stok Sub-Posko.');
    }
}