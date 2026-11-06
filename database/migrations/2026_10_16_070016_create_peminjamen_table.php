<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pinjam', 30)->unique();
            $table->foreignId('buku_id')
                  ->constrained('bukus')
                  ->cascadeOnDelete();
            $table->string('nim_peminjam', 15);
            $table->string('nama_peminjam', 150);
            $table->date('tanggal_pinjam');
            $table->date('tanggal_jatuh_tempo');
            $table->date('tanggal_kembali')->nullable();
            $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};