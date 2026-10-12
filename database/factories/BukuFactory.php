<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Buku>
 */
class BukuFactory extends Factory
{
    protected $model = Buku::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $publishers = [
            'Informatika Bandung',
            'Andi Offset',
            'Erlangga Mahameru',
            'Gava Media',
            'Del Press',
            'O\'Reilly Media',
            'Pearson Education',
            'Prentice Hall',
            'Packt Publishing',
            'Springer Nature'
        ];

        return [
            'isbn' => fake()->unique()->isbn13(),
            'judul' => fake()->sentence(fake()->numberBetween(3, 7)),
            'penulis' => fake()->name(),
            'penerbit' => fake()->randomElement($publishers),
            'tahun_terbit' => fake()->numberBetween(2016, 2026),
            'kategori_id' => Kategori::inRandomOrder()->first()?->id ?? Kategori::factory(),
            'stok' => fake()->numberBetween(1, 30),
            'sinopsis' => fake()->paragraphs(2, true),
        ];
    }
}
