<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Bencana;
use App\Models\BencanaPending;
use App\Models\Bpbd;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BencanaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Master BPBD Kabupaten Bantul, DIY
            $bpbd = Bpbd::firstOrCreate(
                ['nama_kabupaten_kota' => 'Kabupaten Bantul'],
                ['alamat_kantor' => 'Jl. Jend. A. Yani No. 1, Badegan, Bantul, DIY']
            );

            // 2. Master Barang Logistik Kebencanaan
            $items = [
                'Beras', 'Air Minum', 'Makanan Kaleng', 'Makanan Bayi',
                'Minyak Goreng', 'Popok Bayi', 'Popok Dewasa', 'Pembalut Wanita',
                'Hygiene Kit', 'Selimut', 'Matras / Terpal', 'Obat P3K'
            ];

            foreach ($items as $namaBarang) {
                Barang::firstOrCreate(['nama_barang' => $namaBarang]);
            }

            // 3. SAMPLE POLIGON GIS ZONA DAMPAK
            $polygonParangtritis = [
                ["lat" => -8.0050, "lng" => 110.2850],
                ["lat" => -8.0080, "lng" => 110.3550],
                ["lat" => -8.0280, "lng" => 110.3580],
                ["lat" => -8.0250, "lng" => 110.2820],
                ["lat" => -8.0050, "lng" => 110.2850]
            ];

            $polygonPiyungan = [
                ["lat" => -7.8350, "lng" => 110.4600],
                ["lat" => -7.8380, "lng" => 110.4850],
                ["lat" => -7.8550, "lng" => 110.4800],
                ["lat" => -7.8500, "lng" => 110.4550],
                ["lat" => -7.8350, "lng" => 110.4600]
            ];

            // 4. BENCANA 1: STATUS SEDANG BERJALAN (Aktif di Pesisir Parangtritis)
            Bencana::firstOrCreate(
                ['jenis_bencana' => 'Gempa Bumi & Tsunami Pesisir Selatan'],
                [
                    'lokasi_bencana'            => 'Kawasan Pantai Parangtritis, Kretek, Bantul, DIY',
                    'koordinat_operasional_lat' => -8.0255,
                    'koordinat_operasional_lng' => 110.3325,
                    'geojson_polygon'           => $polygonParangtritis,
                    'luas_area_km2'             => 3.85,
                    'total_jiwa_terdampak'      => 1850,
                    'total_kk_terdampak'        => 460,
                    'total_bangunan_terdampak'  => 320,
                    'estimasi_pengungsi_awal'   => 647,
                    'tanggal_aktivasi'          => now()->subHours(4),
                    'status'                    => 'sedang_berjalan',
                ]
            );

            // 5. BENCANA 2 & 3: STATUS SELESAI (Histori Operasi Penanganan Bencana)
            Bencana::firstOrCreate(
                ['jenis_bencana' => 'Tanah Longsor Piyungan'],
                [
                    'lokasi_bencana'            => 'Kapanewon Piyungan, Bantul, DIY',
                    'koordinat_operasional_lat' => -7.8420,
                    'koordinat_operasional_lng' => 110.4710,
                    'geojson_polygon'           => $polygonPiyungan,
                    'luas_area_km2'             => 1.42,
                    'total_jiwa_terdampak'      => 420,
                    'total_kk_terdampak'        => 105,
                    'total_bangunan_terdampak'  => 38,
                    'estimasi_pengungsi_awal'   => 147,
                    'tanggal_aktivasi'          => now()->subDays(14),
                    'tanggal_selesai'           => now()->subDays(2),
                    'status'                    => 'selesai',
                ]
            );

            Bencana::firstOrCreate(
                ['jenis_bencana' => 'Angin Puting Beliung Kasihan'],
                [
                    'lokasi_bencana'            => 'Bangunjiwo, Kasihan, Bantul, DIY',
                    'koordinat_operasional_lat' => -7.8310,
                    'koordinat_operasional_lng' => 110.3200,
                    'geojson_polygon'           => null,
                    'luas_area_km2'             => 0.75,
                    'total_jiwa_terdampak'      => 210,
                    'total_kk_terdampak'        => 52,
                    'total_bangunan_terdampak'  => 29,
                    'estimasi_pengungsi_awal'   => 73,
                    'tanggal_aktivasi'          => now()->subDays(30),
                    'tanggal_selesai'           => now()->subDays(20),
                    'status'                    => 'selesai',
                ]
            );

            // 6. BENCANA PENDING (Sistem Deteksi Otomatis BMKG / Laporan TRC untuk Uji Coba Validasi)
            BencanaPending::firstOrCreate(
                ['external_id' => 'BMKG-BANTUL-002'],
                [
                    'jenis_bencana'  => 'Banjir Luapan Sungai Opak',
                    'wilayah'        => 'Kapanewon Imogiri, Bantul, DIY',
                    'latitude'       => -7.9250,
                    'longitude'      => 110.3850,
                    'waktu_kejadian' => now()->subMinutes(30),
                    'status'         => 'pending',
                ]
            );

            BencanaPending::firstOrCreate(
                ['external_id' => 'TRC-BANTUL-003'],
                [
                    'jenis_bencana'  => 'Banjir Bandang & Genangan Air',
                    'wilayah'        => 'Kapanewon Sewon, Bantul, DIY',
                    'latitude'       => -7.8480,
                    'longitude'      => 110.3620,
                    'waktu_kejadian' => now()->subHours(2),
                    'status'         => 'pending',
                ]
            );
        });
    }
}