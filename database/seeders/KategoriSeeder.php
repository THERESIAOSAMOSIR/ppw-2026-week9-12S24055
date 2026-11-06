<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            ['kode_kategori' => 'IS-01', 'nama_kategori' => 'Sistem Informasi'],
            ['kode_kategori' => 'IF-02', 'nama_kategori' => 'Informatika'],
            ['kode_kategori' => 'MR-03', 'nama_kategori' => 'Manajemen Rekayasa'],
            ['kode_kategori' => 'TE-04', 'nama_kategori' => 'Teknik Elektro'],
            ['kode_kategori' => 'BP-05', 'nama_kategori' => 'Bioproses'],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::create($kategori);
        }
    }
}