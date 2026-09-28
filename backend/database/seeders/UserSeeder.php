<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Bpbd;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $bpbdBantul = Bpbd::where('nama_kabupaten_kota', 'Kabupaten Bantul')->first() ?? Bpbd::first();

        // 1. BNPB Pusat (Role: bnpb)
        User::updateOrCreate(
            ['email' => 'bnpb@rescuelog.id'],
            [
                'name'     => 'Pusdalops BNPB Indonesia',
                'password' => Hash::make('password123'),
                'role'     => 'bnpb',
                'posko_id' => null,
                'bpbd_id'  => null,
            ]
        );

        // 2. BPBD Provinsi D.I. Yogyakarta (Role: bpbd_provinsi)
        User::updateOrCreate(
            ['email' => 'bpbd.diy@rescuelog.id'],
            [
                'name'     => 'BPBD Provinsi D.I. Yogyakarta',
                'password' => Hash::make('password123'),
                'role'     => 'bpbd_provinsi',
                'posko_id' => null,
                'bpbd_id'  => null,
            ]
        );

        // 3. Admin BPBD Kabupaten Bantul (Role: admin)
        User::updateOrCreate(
            ['email' => 'admin@bpbd.com'],
            [
                'name'     => 'Admin BPBD Bantul Utama',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
                'posko_id' => null,
                'bpbd_id'  => $bpbdBantul?->id,
            ]
        );

        // 4. Komandan Posko Komando Aktif (Role: komando)
        User::updateOrCreate(
            ['email' => 'komando.bantul@rescuelog.id'],
            [
                'name'     => 'Komandan Budi Santoso',
                'password' => Hash::make('password123'),
                'role'     => 'komando',
                'posko_id' => null,
                'bpbd_id'  => $bpbdBantul?->id,
            ]
        );

        // 5. Komandan Posko Komando Siaga / Induk (Role: komando) - BARU
        User::updateOrCreate(
            ['email' => 'komando.siaga@rescuelog.id'],
            [
                'name'     => 'TRC BPBD Bantul (Komando Siaga)',
                'password' => Hash::make('password123'),
                'role'     => 'komando',
                'posko_id' => null,
                'bpbd_id'  => $bpbdBantul?->id,
            ]
        );

        // 6. Petugas Lapangan Sub-Posko 1 (Role: lapangan)
        User::updateOrCreate(
            ['email' => 'petugas.lapangan@rescuelog.id'],
            [
                'name'     => 'Petugas Lapangan A',
                'password' => Hash::make('password123'),
                'role'     => 'lapangan',
                'posko_id' => null,
                'bpbd_id'  => $bpbdBantul?->id,
            ]
        );

        // 7. Petugas Lapangan Sub-Posko 2 (Role: lapangan)
        User::updateOrCreate(
            ['email' => 'petugas.depok@rescuelog.id'],
            [
                'name'     => 'Petugas Lapangan B',
                'password' => Hash::make('password123'),
                'role'     => 'lapangan',
                'posko_id' => null,
                'bpbd_id'  => $bpbdBantul?->id,
            ]
        );
    }
}