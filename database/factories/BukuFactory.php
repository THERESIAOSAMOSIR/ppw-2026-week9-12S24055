<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BukuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'isbn' => $this->faker->unique()->isbn13(),
            'judul' => $this->faker->sentence(4),
            'penulis' => $this->faker->name(),
            'penerbit' => $this->faker->company(),
            'tahun_terbit' => $this->faker->year(),
            'kategori_id' => $this->faker->numberBetween(1, 5),
            'stok' => $this->faker->numberBetween(5, 50),
            'sinopsis' => $this->faker->paragraph(),
        ];
    }
}