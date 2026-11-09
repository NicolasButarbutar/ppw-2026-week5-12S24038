<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BukuApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);
        $this->token = $this->user->createToken('test')->plainTextToken;

        $this->kategori = Kategori::create([
            'kode_kategori' => 'KAT-TEK',
            'nama_kategori' => 'Teknologi Informasi',
        ]);
    }

    public function test_get_bukus_index_with_pagination_and_filter(): void
    {
        Buku::create([
            'isbn' => '978-001',
            'judul' => 'Pemrograman PHP Modern',
            'penulis' => 'Penulis A',
            'penerbit' => 'Penerbit A',
            'tahun_terbit' => 2024,
            'kategori_id' => $this->kategori->id,
            'stok' => 5,
        ]);
        Buku::create([
            'isbn' => '978-002',
            'judul' => 'Basis Data Lanjut',
            'penulis' => 'Penulis B',
            'penerbit' => 'Penerbit B',
            'tahun_terbit' => 2023,
            'kategori_id' => $this->kategori->id,
            'stok' => 0,
        ]);

        // Filter tersedia=1
        $res = $this->getJson('/api/v1/bukus?tersedia=1');
        $res->assertStatus(200);
        $res->assertJsonCount(1, 'data');
        $this->assertEquals('Pemrograman PHP Modern', $res->json('data.0.judul'));
        $this->assertArrayHasKey('meta', $res->json());
        $this->assertEquals(1, $res->json('meta.total_items'));

        // Search
        $searchRes = $this->getJson('/api/v1/bukus?search=Basis');
        $searchRes->assertStatus(200);
        $searchRes->assertJsonCount(1, 'data');
        $this->assertEquals('Basis Data Lanjut', $searchRes->json('data.0.judul'));
    }

    public function test_hateoas_dynamic_links(): void
    {
        $bukuAvailable = Buku::create([
            'isbn' => '978-003',
            'judul' => 'Arsitektur REST',
            'penulis' => 'Roy Fielding',
            'penerbit' => 'MIT',
            'tahun_terbit' => 2025,
            'kategori_id' => $this->kategori->id,
            'stok' => 2,
        ]);

        $bukuEmpty = Buku::create([
            'isbn' => '978-004',
            'judul' => 'Microservices',
            'penulis' => 'Martin Fowler',
            'penerbit' => 'O-Reilly',
            'tahun_terbit' => 2025,
            'kategori_id' => $this->kategori->id,
            'stok' => 0,
        ]);

        $resAvailable = $this->getJson("/api/v1/bukus/{$bukuAvailable->id}");
        $resAvailable->assertStatus(200);
        $this->assertArrayHasKey('pinjam_buku', $resAvailable->json('data._links'));

        $resEmpty = $this->getJson("/api/v1/bukus/{$bukuEmpty->id}");
        $resEmpty->assertStatus(200);
        $this->assertArrayNotHasKey('pinjam_buku', $resEmpty->json('data._links'));
    }

    public function test_post_buku_requires_token_and_validates(): void
    {
        // Unauthenticated -> 401
        $unauth = $this->postJson('/api/v1/bukus', [
            'isbn' => '978-005',
            'judul' => 'Test',
        ]);
        $unauth->assertStatus(401);

        // Validation error -> 422
        $valError = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/bukus', []);
        $valError->assertStatus(422);
        $valError->assertHeader('Content-Type', 'application/problem+json');
        $this->assertArrayHasKey('errors', $valError->json());

        // Valid POST -> 201 Created + Header Location
        $validRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/bukus', [
                'isbn' => '978-005-999',
                'judul' => 'Buku Baru 2026',
                'penulis' => 'Chandro P',
                'penerbit' => 'Del Press',
                'tahun_terbit' => 2026,
                'kategori_id' => $this->kategori->id,
                'stok' => 10,
                'sinopsis' => 'Buku teks praktikum.',
            ]);

        $validRes->assertStatus(201);
        $this->assertStringContainsString('/api/v1/bukus/', $validRes->headers->get('Location'));
        $this->assertEquals('Buku Baru 2026', $validRes->json('data.judul'));
    }

    public function test_put_vs_patch_semantics(): void
    {
        $buku = Buku::create([
            'isbn' => '978-010',
            'judul' => 'Pemrograman Web',
            'penulis' => 'Penulis Lama',
            'penerbit' => 'Penerbit Lama',
            'tahun_terbit' => 2022,
            'kategori_id' => $this->kategori->id,
            'stok' => 4,
            'sinopsis' => 'Sinopsis awal yang ada isinya.',
        ]);

        // PATCH: partial update only stok
        $patchRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->patchJson("/api/v1/bukus/{$buku->id}", [
                'stok' => 20,
            ]);
        $patchRes->assertStatus(200);
        $this->assertEquals(20, $patchRes->json('data.stok'));
        $this->assertEquals('Pemrograman Web', $patchRes->json('data.judul'));
        $this->assertEquals('Sinopsis awal yang ada isinya.', $patchRes->json('data.sinopsis'));

        // PUT: full replacement without sinopsis -> sinopsis reset to null
        $putRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/bukus/{$buku->id}", [
                'isbn' => '978-010',
                'judul' => 'Pemrograman Web Revisi Penuh',
                'penulis' => 'Penulis Baru',
                'penerbit' => 'Penerbit Baru',
                'tahun_terbit' => 2026,
                'kategori_id' => $this->kategori->id,
                'stok' => 30,
            ]);
        $putRes->assertStatus(200);
        $this->assertEquals('Pemrograman Web Revisi Penuh', $putRes->json('data.judul'));
        $this->assertNull($putRes->json('data.sinopsis'));
    }

    public function test_delete_buku_conflict_when_active_loans(): void
    {
        $buku = Buku::create([
            'isbn' => '978-020',
            'judul' => 'Sistem Terdistribusi',
            'penulis' => 'Tanenbaum',
            'penerbit' => 'Pearson',
            'tahun_terbit' => 2020,
            'kategori_id' => $this->kategori->id,
            'stok' => 1,
        ]);

        Peminjaman::create([
            'kode_pinjam' => 'PINJ-DEL-01',
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24038',
            'nama_peminjam' => 'Nicolas J Grace',
            'tanggal_pinjam' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(7),
            'status' => 'dipinjam',
        ]);

        // Attempt DELETE -> 409 Conflict
        $delConflict = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/v1/bukus/{$buku->id}");
        $delConflict->assertStatus(409);
        $delConflict->assertHeader('Content-Type', 'application/problem+json');
        $this->assertDatabaseHas('bukus', ['id' => $buku->id]);

        // Complete loan
        Peminjaman::where('buku_id', $buku->id)->update(['status' => 'dikembalikan']);

        // Retry DELETE -> 204 No Content
        $delSuccess = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/v1/bukus/{$buku->id}");
        $delSuccess->assertStatus(204);
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }
}
