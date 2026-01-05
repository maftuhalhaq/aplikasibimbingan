<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Skripsi;   // Pastikan model di-import jika nanti butuh
use App\Models\Bimbingan; // Pastikan model di-import jika nanti butuh
use App\Models\Chat;      // Pastikan model di-import jika nanti butuh

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. BUAT AKUN DOSEN
        User::create([
            'name' => 'Ara',
            'email' => 'ara@dosen.com',
            'password' => bcrypt('123456'),
            'role' => 'dosen',
            'nomor_induk' => '1001'
        ]);

        User::create([
            'name' => 'Bintang',
            'email' => 'bintang@dosen.com',
            'password' => bcrypt('123456'),
            'role' => 'dosen',
            'nomor_induk' => '1002'
        ]);

        // 2. BUAT AKUN MAHASISWA
        User::create([
            'name' => 'Maftuh',
            'email' => 'maftuh@mhs.com',
            'password' => bcrypt('123456'),
            'role' => 'mahasiswa',
            'nomor_induk' => '2001'
        ]);

        User::create([
            'name' => 'Inas',
            'email' => 'inas@mhs.com',
            'password' => bcrypt('123456'),
            'role' => 'mahasiswa',
            'nomor_induk' => '2002'
        ]);

        User::create([
            'name' => 'Rena',
            'email' => 'rena@mhs.com',
            'password' => bcrypt('123456'),
            'role' => 'mahasiswa',
            'nomor_induk' => '2003'
        ]);

        // Opsional: Jika ingin memastikan tabel lain benar-benar kosong (meskipun migrate:fresh sudah melakukan ini)
        // Skripsi::truncate();
        // Bimbingan::truncate();
        // Chat::truncate();
    }
}