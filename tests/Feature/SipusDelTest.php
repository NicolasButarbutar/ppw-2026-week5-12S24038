<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SipusDelTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_buku_index(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('buku.index'));
    }

    public function test_kategori_crud_lifecycle(): void
    {
        // 1. Create form
        $response = $this->get(route('kategori.create'));
        $response->assertStatus(200);
        $response->assertSee('Formulir Tambah Kategori');

        // 2. Store validation fail
        $response = $this->post(route('kategori.store'), [
            'kode_kategori' => '',
            'nama_kategori' => 'ab',
        ]);
        $response->assertSessionHasErrors(['kode_kategori', 'nama_kategori']);

        // 3. Store success
        $response = $this->post(route('kategori.store'), [
            'kode_kategori' => 'KAT-TEST',
            'nama_kategori' => 'Kategori Uji Coba',
        ]);
        $response->assertRedirect(route('kategori.index'));
        $this->assertDatabaseHas('kategoris', ['kode_kategori' => 'KAT-TEST']);

        $kategori = Kategori::where('kode_kategori', 'KAT-TEST')->first();

        // 4. Index
        $response = $this->get(route('kategori.index'));
        $response->assertStatus(200);
        $response->assertSee('Kategori Uji Coba');

        // 5. Show
        $response = $this->get(route('kategori.show', $kategori));
        $response->assertStatus(200);
        $response->assertSee('Kategori Uji Coba');

        // 6. Edit
        $response = $this->get(route('kategori.edit', $kategori));
        $response->assertStatus(200);
        $response->assertSee('KAT-TEST');

        // 7. Update
        $response = $this->put(route('kategori.update', $kategori), [
            'kode_kategori' => 'KAT-TEST-UPD',
            'nama_kategori' => 'Kategori Diperbarui',
        ]);
        $response->assertRedirect(route('kategori.index'));
        $this->assertDatabaseHas('kategoris', ['kode_kategori' => 'KAT-TEST-UPD']);

        // 8. Destroy
        $kategori->refresh();
        $response = $this->delete(route('kategori.destroy', $kategori));
        $response->assertRedirect(route('kategori.index'));
        $this->assertDatabaseMissing('kategoris', ['id' => $kategori->id]);
    }

    public function test_buku_crud_lifecycle_and_validation(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        // 1. Create form
        $response = $this->get(route('buku.create'));
        $response->assertStatus(200);
        $response->assertSee('Formulir Tambah Koleksi Buku');

        // 2. Validation error test (title min:5, invalid isbn, negative stock)
        $response = $this->post(route('buku.store'), [
            'isbn' => '',
            'judul' => 'abc', // min:5
            'penulis' => '',
            'penerbit' => '',
            'tahun_terbit' => '1800', // min:1900
            'kategori_id' => 999, // not exists
            'stok' => -5, // min:0
        ]);
        $response->assertSessionHasErrors(['isbn', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'kategori_id', 'stok']);

        // 3. Store valid buku
        $bukuData = [
            'isbn' => '978-602-1234-56-7',
            'judul' => 'Pemrograman Web Modern dengan Laravel 11',
            'penulis' => 'Dosen Del',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'kategori_id' => $kategori->id,
            'stok' => 15,
            'sinopsis' => 'Buku praktikum komprehensif implementasi MVC dan Eloquent.',
        ];

        $response = $this->post(route('buku.store'), $bukuData);
        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseHas('bukus', ['isbn' => '978-602-1234-56-7']);

        $buku = Buku::where('isbn', '978-602-1234-56-7')->first();

        // 4. Show buku
        $response = $this->get(route('buku.show', $buku));
        $response->assertStatus(200);
        $response->assertSee('Pemrograman Web Modern dengan Laravel 11');
        $response->assertSee('Rekayasa Perangkat Lunak');

        // 5. Edit buku
        $response = $this->get(route('buku.edit', $buku));
        $response->assertStatus(200);
        $response->assertSee('978-602-1234-56-7');

        // 6. Update buku (with unique isbn check allowing same isbn for this record)
        $response = $this->put(route('buku.update', $buku), [
            'isbn' => '978-602-1234-56-7',
            'judul' => 'Pemrograman Web Modern dengan Laravel 11 (Edisi Revisi)',
            'penulis' => 'Dosen Del & Tim Asisten',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'kategori_id' => $kategori->id,
            'stok' => 20,
            'sinopsis' => 'Edisi revisi terbaru praktikum web.',
        ]);
        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseHas('bukus', [
            'id' => $buku->id,
            'judul' => 'Pemrograman Web Modern dengan Laravel 11 (Edisi Revisi)',
            'stok' => 20,
        ]);

        // 7. Delete buku
        $buku->refresh();
        $response = $this->delete(route('buku.destroy', $buku));
        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }

    public function test_eager_loading_prevents_n_plus_one_problem(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        // Create 15 books
        for ($i = 1; $i <= 15; $i++) {
            Buku::create([
                'isbn' => "978-602-0000-$i",
                'judul' => "Buku Uji Coba Ke-$i",
                'penulis' => "Penulis $i",
                'penerbit' => "Penerbit $i",
                'tahun_terbit' => 2024,
                'kategori_id' => $kategori->id,
                'stok' => $i,
                'sinopsis' => "Sinopsis buku ke-$i",
            ]);
        }

        // Count queries executed when rendering index view
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->get(route('buku.index'));
        $response->assertStatus(200);

        // Without Eager Loading, 10 items would execute 1 (count) + 1 (buku items) + 10 (each kategori) + 1 (kategoris dropdown) = 13 queries.
        // With Eager Loading (Buku::with('kategori')), it executes:
        // 1. count(*) for pagination
        // 2. select bukus limit 10
        // 3. select kategoris where id in (...) (eager loading)
        // 4. select all kategoris for dropdown filter
        // Total query is strictly controlled (4 queries total, only 2 for the list + eager relation), completely preventing N+1.
        $this->assertLessThanOrEqual(5, $queryCount, "Eager Loading failed: expected <= 5 queries, got {$queryCount}");
    }
}
