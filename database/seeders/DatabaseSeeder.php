<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Pustakawan Uji (Sesuai Standar Modul Minggu 6 & Minggu 9)
        User::updateOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            [
                'name'     => 'Pustakawan Del',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Sample Kategori Buku
        $katPemrograman = Kategori::updateOrCreate(
            ['kode_kategori' => 'KAT-001'],
            ['nama_kategori' => 'Pemrograman & Rekayasa Perangkat Lunak']
        );

        $katSistem = Kategori::updateOrCreate(
            ['kode_kategori' => 'KAT-002'],
            ['nama_kategori' => 'Sistem Informasi & Basis Data']
        );

        $katJaringan = Kategori::updateOrCreate(
            ['kode_kategori' => 'KAT-003'],
            ['nama_kategori' => 'Arsitektur Terdistribusi & Cloud']
        );

        // 3. Sample Buku (Termasuk ISBN publik nyata untuk uji OpenLibrary API Consumer)
        $buku1 = Buku::updateOrCreate(
            ['isbn' => '9780132350884'],
            [
                'kategori_id'  => $katPemrograman->id,
                'judul'        => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'penulis'      => 'Robert C. Martin',
                'penerbit'     => 'Prentice Hall',
                'tahun_terbit' => 2008,
                'stok'         => 3,
                'sinopsis'     => 'Panduan klasik menulis kode program yang bersih, rapi, dan mudah dipelihara.',
            ]
        );

        $buku2 = Buku::updateOrCreate(
            ['isbn' => '978-602-0001-01'],
            [
                'kategori_id'  => $katPemrograman->id,
                'judul'        => 'Pemrograman Web dengan Laravel 11',
                'penulis'      => 'Chandro Pardede',
                'penerbit'     => 'Del Press',
                'tahun_terbit' => 2026,
                'stok'         => 1,
                'sinopsis'     => 'Buku ajar utama Pemrograman dan Pengujian Aplikasi Web di Institut Teknologi Del.',
            ]
        );

        $buku3 = Buku::updateOrCreate(
            ['isbn' => '978-602-0001-02'],
            [
                'kategori_id'  => $katSistem->id,
                'judul'        => 'Arsitektur Basis Data Lanjut',
                'penulis'      => 'Dosen FITE',
                'penerbit'     => 'Informatika Del',
                'tahun_terbit' => 2025,
                'stok'         => 0,
                'sinopsis'     => 'Membahas transaksi ACID, Pessimistic Locking, dan optimasi kueri.',
            ]
        );

        Buku::updateOrCreate(
            ['isbn' => '9781449358068'],
            [
                'kategori_id'  => $katJaringan->id,
                'judul'        => 'RESTful Web APIs',
                'penulis'      => 'Leonard Richardson & Mike Amundsen',
                'penerbit'     => "O'Reilly Media",
                'tahun_terbit' => 2013,
                'stok'         => 5,
                'sinopsis'     => 'Referensi utama Richardson Maturity Model, HATEOAS, dan desain Web Services modern.',
            ]
        );

        // 4. Sample Data Sirkulasi Peminjaman untuk Uji SOAP CheckClearanceStatus & REST API
        // Mahasiswa 12S24055 (Theresia) masih memiliki 1 pinjaman aktif -> is_cleared = false
        Peminjaman::updateOrCreate(
            ['kode_pinjam' => 'PMJ-WEEK9001'],
            [
                'buku_id'             => $buku1->id,
                'nim_peminjam'        => '12S24055',
                'nama_peminjam'       => 'Theresia Oktaviani Samosir',
                'tanggal_pinjam'      => now()->subDays(2)->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(5)->toDateString(),
                'tanggal_kembali'     => null,
                'status'              => 'dipinjam',
            ]
        );

        // Mahasiswa 12S22001 (Bona) sudah mengembalikan buku -> is_cleared = true (0 pinjaman aktif)
        Peminjaman::updateOrCreate(
            ['kode_pinjam' => 'PMJ-WEEK9002'],
            [
                'buku_id'             => $buku2->id,
                'nim_peminjam'        => '12S22001',
                'nama_peminjam'       => 'Bona Pandiangan',
                'tanggal_pinjam'      => now()->subDays(10)->toDateString(),
                'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
                'tanggal_kembali'     => now()->subDays(4)->toDateString(),
                'status'              => 'dikembalikan',
            ]
        );
    }
}
