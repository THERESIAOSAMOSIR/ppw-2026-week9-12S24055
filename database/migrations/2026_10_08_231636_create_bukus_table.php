<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukus', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 20)->unique();
            $table->string('judul', 150);
            $table->string('penulis', 100);
            $table->string('penerbit', 100);
            $table->year('tahun_terbit');
            $table->foreignId('kategori_id')->constrained('kategoris')->cascadeOnDelete();
            $table->integer('stok')->default(0);
            $table->text('sinopsis')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukus');
    }
};