<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kategori;
use App\Models\Buku;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Pustakawan Uji
        User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        // 2. Sample Kategori (Lengkap dengan kode_kategori)
        $katPemrograman = Kategori::create([
            'kode_kategori' => 'KAT-001',
            'nama_kategori' => 'Pemrograman'
        ]);
        
        $katSistem = Kategori::create([
            'kode_kategori' => 'KAT-002',
            'nama_kategori' => 'Sistem Informasi'
        ]);

        // 3. Sample Buku dengan Stok 1 dan Stok 0 (Disertai penerbit & tahun_terbit)
        Buku::create([
            'kategori_id' => $katPemrograman->id,
            'judul' => 'Pemrograman Web dengan Laravel 11',
            'penulis' => 'Chandro Pardede',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'isbn' => '978-602-0001-01',
            'stok' => 1,
        ]);

        Buku::create([
            'kategori_id' => $katSistem->id,
            'judul' => 'Arsitektur Basis Data Lanjut',
            'penulis' => 'Dosen FITE',
            'penerbit' => 'Informatika Del',
            'tahun_terbit' => 2025,
            'isbn' => '978-602-0001-02',
            'stok' => 0,
        ]);
    }
}