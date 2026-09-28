<?php

namespace Database\Seeders;

use App\Models\Bpbd;
use App\Models\Bencana;
use App\Models\Posko;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PoskoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $bpbd = Bpbd::where('nama_kabupaten_kota', 'Kabupaten Bantul')->first() ?? Bpbd::first();
            $bencanaAktif = Bencana::where('status', 'sedang_berjalan')->first();

            $userKomandan      = User::where('email', 'komando.bantul@rescuelog.id')->first();
            $userKomandanSiaga = User::where('email', 'komando.siaga@rescuelog.id')->first();
            $userLapangan1     = User::where('email', 'petugas.lapangan@rescuelog.id')->first();
            $userLapangan2     = User::where('email', 'petugas.depok@rescuelog.id')->first();

            // 1. POSKO KOMANDO AKTIF
            $poskoKomando = Posko::updateOrCreate(
                ['nama_posko' => 'Posko Komando Taktis Kretek'],
                [
                    'tipe_posko'       => 'komando',
                    'user_id'          => $userKomandan?->id,
                    'bpbd_id'          => $bpbd?->id,
                    'bencana_id'       => $bencanaAktif?->id,
                    'penanggung_jawab' => 'Budi Santoso',
                    'kontak_hp'        => '081234567890',
                    'lokasi'           => 'Kantor Kapanewon Kretek, Bantul (Titik Tengah Logistik)',
                    'latitude'         => -7.9620,
                    'longitude'        => 110.3280,
                    'status'           => 'aktif',
                    'kode_undangan'    => 'KOMANDO-BANTUL-01',
                ]
            );

            if ($userKomandan) {
                $userKomandan->update(['posko_id' => $poskoKomando->id]);
            }

            // 2. POSKO KOMANDO SIAGA (TERIKAT DENGAN AKUN LOG IN SIAGA)
            $poskoSiaga = Posko::updateOrCreate(
                ['nama_posko' => 'Posko Komando Induk BPBD Bantul (Siaga)'],
                [
                    'tipe_posko'       => 'komando',
                    'user_id'          => $userKomandanSiaga?->id,
                    'bpbd_id'          => $bpbd?->id,
                    'bencana_id'       => null,
                    'penanggung_jawab' => 'Tim Reaksi Cepat (TRC) BPBD',
                    'kontak_hp'        => '0274-368222',
                    'lokasi'           => 'Gudang Logistik Utama BPBD, Badegan, Bantul',
                    'latitude'         => -7.8893,
                    'longitude'        => 110.3288,
                    'status'           => 'terdaftar_nonaktif',
                    'kode_undangan'    => 'KOMANDO-SIAGA-02',
                ]
            );

            if ($userKomandanSiaga) {
                $userKomandanSiaga->update(['posko_id' => $poskoSiaga->id]);
            }

            // 3. Sub-Posko Lapangan 1 (Parangtritis)
            $poskoLapangan1 = Posko::updateOrCreate(
                ['nama_posko' => 'Sub-Posko Pengungsian Parangtritis'],
                [
                    'tipe_posko'       => 'lapangan_kecil',
                    'parent_id'        => $poskoKomando->id,
                    'user_id'          => $userLapangan1?->id,
                    'bpbd_id'          => $bpbd?->id,
                    'bencana_id'       => $bencanaAktif?->id,
                    'penanggung_jawab' => 'Petugas Lapangan A',
                    'kontak_hp'        => '089876543210',
                    'jumlah_petugas'   => 8,
                    'lokasi'           => 'Balai Desa Parangtritis (Zona Terdampak Utama)',
                    'latitude'         => -8.0120,
                    'longitude'        => 110.3340,
                    'status'           => 'aktif',
                    'kode_undangan'    => 'PARANGTRITIS-2026',
                ]
            );

            if ($userLapangan1) {
                $userLapangan1->update(['posko_id' => $poskoLapangan1->id]);
            }

            // 4. Sub-Posko Lapangan 2 (Depok)
            $poskoLapangan2 = Posko::updateOrCreate(
                ['nama_posko' => 'Sub-Posko Darurat Pantai Depok'],
                [
                    'tipe_posko'       => 'lapangan_kecil',
                    'parent_id'        => $poskoKomando->id,
                    'user_id'          => $userLapangan2?->id,
                    'bpbd_id'          => $bpbd?->id,
                    'bencana_id'       => $bencanaAktif?->id,
                    'penanggung_jawab' => 'Petugas Lapangan B',
                    'kontak_hp'        => '089876543211',
                    'jumlah_petugas'   => 5,
                    'lokasi'           => 'TPI Pantai Depok, Parangtritis',
                    'latitude'         => -8.0100,
                    'longitude'        => 110.2920,
                    'status'           => 'aktif',
                    'kode_undangan'    => 'DEPOK-2026',
                ]
            );

            if ($userLapangan2) {
                $userLapangan2->update(['posko_id' => $poskoLapangan2->id]);
            }
        });
    }
}